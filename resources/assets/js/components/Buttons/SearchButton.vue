<template>
    <div ref="search_button" class="table_search_button" title="Search in columns">
        <button class="btn btn-primary btn-sm blue-gradient"
                @click="toggleSearch(!menu_opened)"
                :style="$root.themeButtonStyle"
        >
            <i class="fas fa-search"></i>
        </button>
        <div v-show="menu_opened" class="table_search_menu">

            <i class="glyphicon glyphicon-cog"
               style="margin: 2px 0 0 3px;"
               @click="col_menu_opened = !col_menu_opened"></i>

            <input class="form-control input-sm"
                   :placeholder="'Search'"
                   @keyup="inputKey(searchObject.keyWords)"
                   v-model="searchObject.keyWords"/>

            <button v-show="hasSearch"
                    class="btn btn-sm btn-danger"
                    title="Clear search"
                    @click="clearSearch()">&times;</button>

            <fields-checker
                    v-show="col_menu_opened"
                    class="column_search_menu"
                    :table-meta="tableMeta"
                    :all-checked="allChecked"
                    :check-func="checkFunc"
                    :only_columns="limitColumns"
                    @toggle-all="toggleAll"
                    @toggle-column="toggleColumn"
            ></fields-checker>

            <div v-if="popup_rows.length" class="search_popup_wrapper">
                <div class="search__results" :class="[total_found > tableMeta.search_results_len ? 'popup_border-r-top' : '']">
                    <div v-for="row in popup_rows" class="search_popup_item" @click="AutoRowClicked(row)">
                        <span v-for="tableHeader in tableMeta._fields" v-if="shouldShow(tableHeader)">
                            {{ getAutopopVal(row, tableHeader) }} |
                        </span>
                    </div>
                </div>
                <div v-if="total_found > tableMeta.search_results_len" class="search__noter">
                    Note: only the first {{ tableMeta.search_results_len }} are displayed. Total {{ total_found }} matching records.
                </div>
            </div>

        </div>
    </div>
</template>

<script>
    import IsShowFieldMixin from '../_Mixins/IsShowFieldMixin';

    import {eventBus} from './../../app';

    import {SpecialFuncs} from './../../classes/SpecialFuncs';

    import FieldsChecker from "../CommonBlocks/FieldsChecker";

    export default {
        name: "SearchButton",
        mixins: [
            IsShowFieldMixin,
        ],
        components: {
            FieldsChecker,
        },
        data: function () {
            return {
                menu_opened: false,
                col_menu_opened: false,
                timerout: null,
                popup_rows: [],
                total_found: 0,
            }
        },
        props:{
            tableMeta: Object,
            searchObject: Object,
            requestParams: Object,
            limitColumns: Array,
        },
        computed: {
            allChecked() {
                return this.searchObject.columns.length === this.tableMeta._fields.length
                    ? 2
                    : this.searchObject.columns.length > 0 ? 1 : 0;
            },
            hasSearch() {
                return !!this.searchObject.direct_row_id || this.searchObject.keyWords;
            }
        },
        methods: {
            getAutopopVal(row, fld) {
                if (fld && fld.f_type === 'User') {
                    return this.$root.getUserFullStr(row, fld, {
                        user_fld_show_first: true,
                        user_fld_show_last: true,
                    });
                } else {
                    return (fld.field === 'id' ? '#' : '') + row[fld.field];
                }
            },
            checkFunc(header) {
                return $.inArray(header.field, this.searchObject.columns) > -1;
            },
            toggleColumn(hdr) {
                let field = hdr.field;
                let idx = _.findIndex(this.searchObject.columns, function(el) { return el === field; });
                if (idx > -1) {
                    this.searchObject.columns.splice(idx, 1);
                } else {
                    this.searchObject.columns.push(field);
                }
            },
            toggleAll() {
                if (this.allChecked) {
                    this.searchObject.columns = [];
                } else {
                    this.searchObject.columns = _.map(this.tableMeta._fields, 'field');
                }
            },
            toggleSearch(val) {
                this.menu_opened = val;
                this.col_menu_opened = false;
                if (!this.menu_opened) {
                    this.popup_rows = _.filter(this.popup_rows, (row) => {
                        return row.id === this.searchObject.direct_row_id;
                    });
                    this.total_found = this.popup_rows.length;
                }
            },
            clearSearch() {
                this.searchObject.direct_row_id = null;
                this.searchObject.keyWords = '';
                this.popup_rows = [];
                this.total_found = 0;
                this.$emit('search-word-changed');
            },
            shouldShow(tableHeader) {
                return tableHeader.is_search_autocomplete_display == 1
                    && (
                        this.tableMeta._is_owner
                        ||
                        this.tableMeta._current_right.view_fields.indexOf(tableHeader.field) > -1
                    );
                    //&& this.isShowField(tableHeader);
            },
            inputKey(word) {
                if (window.event.keyCode == 13) {
                    this.toggleSearch(false);
                    this.$emit('search-word-changed');
                }
                else
                if (word.length >= 3) {
                    clearTimeout(this.timerout);
                    this.timerout = setTimeout(this.sendAutoComplete, 500);
                }
            },
            sendAutoComplete() {
                let obj = this.requestParams ? _.cloneDeep(this.requestParams) : {table_id: this.tableMeta.id, special_params: {}};
                let request = {
                    ...SpecialFuncs.tableMetaRequest(this.tableMeta.id),
                    ...obj
                };
                request.page = 1;
                request.rows_per_page = this.tableMeta.search_results_len;
                request.search_words = this.searchObject.keyWords;
                request.search_columns = this.searchObject.columns;
                request.special_params.for_list_view = false;

                axios.post('/ajax/table-data/get', request).then(({ data }) => {
                    this.popup_rows = data.rows;
                    this.total_found = data.rows_count;
                }).catch(errors => {
                    Swal('', getErrors(errors));
                }).finally(() => {
                    $.LoadingOverlay('hide');
                });
            },
            AutoRowClicked(row) {
                this.searchObject.direct_row_id = row.id;
                this.toggleSearch(false);
                this.$emit('search-word-changed');
            },
            hideMenu(e) {
                let container = $(this.$refs.search_button);
                if (container.has(e.target).length === 0 && this.menu_opened){
                    this.toggleSearch(false);
                }
            }
        },
        created() {
            if (!this.allChecked) {
                this.toggleAll();
            }
            eventBus.$on('global-click', this.hideMenu);
            eventBus.$on('global-keydown', this.hideMenu);
        },
        beforeDestroy() {
            eventBus.$off('global-click', this.hideMenu);
            eventBus.$off('global-keydown', this.hideMenu);
        }
    }
</script>

<style lang="scss" scoped>
    .table_search_button {
        cursor: pointer;
        width: 35px;
        height: 30px;
        padding: 0;
        font-size: 22px;
        background-color: transparent;
        position: relative;
        outline: none;
    }
    .table_search_button > button {
        width: 100%;
        height: 100%;
        font-size: 0.9em;
        padding: 0;
        display: flex;
        justify-content: center;
        align-items: center;

        .fas {
            padding: 0;
            margin: 0;
        }
    }

    .table_search_menu {
        position: absolute;
        right: 100%;
        top: 100%;
        width: max-content;
        height: 30px;
        z-index: 500;
        background-color: #FFF;
    }
    .table_search_menu > i, .table_search_menu > input, .table_search_menu > select {
        float: left;
    }
    .table_search_menu > i {
        top: 3px !important;
        margin-right: 7px;
    }
    .table_search_menu > input {
        width: 250px;
    }
    .table_search_menu > select {
        width: 60px;
        padding: 3px;
    }
    .table_search_menu > .btn-danger {
        position: absolute;
        left: 100%;
        top: 0;
    }

    .column_search_menu {
        position: absolute;
        right: 100%;
        top: 0;
        z-index: 500;

        max-height: 500px;
        overflow: auto;
        padding: 5px;
        background-color: #EEE;
        border: 1px solid #777;
        border-radius: 5px;
    }


    .search_popup_wrapper {
        font-size: 12px;
        position: absolute;
        right: -30px;
        z-index: 550;
        white-space: nowrap;
        top: 100%;

        .search__results {
            top: 100%;
            max-height: 150px;
            overflow-y: auto;
            overflow-x: hidden;
            border: 1px solid #777;
            background-color: #fff;
            border-radius: 5px;
            min-width: 350px;

            .search_popup_item {
                font-size: 12px;
                padding: 0 5px;

                &:hover {
                    background-color: rgba(0,0,0,0.15);
                }
            }
        }

        .popup_border-r-top {
            border-radius: 5px 5px 0 0;
        }

        .search__noter {
            top: 100%;
            overflow: hidden;
            padding: 0 5px;
            border: 1px solid #ccc;
            border-radius: 0 0 5px 5px;
            background-color: #fff;
            width: 100%;
        }
    }
</style>