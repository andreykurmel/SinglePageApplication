<template>
    <div ref="search_button" class="string_replace_button" title="Replace Values">
        <button class="btn btn-primary btn-sm blue-gradient"
                @click="toggleSearch(!this.menu_opened)"
                :style="$root.themeButtonStyle"
        >
            <img src="/assets/img/replace1.png" width="25" height="25">
        </button>
        <div v-show="menu_opened" class="string_replace_menu">

            <i class="glyphicon glyphicon-cog" @click="col_menu_opened = !col_menu_opened"></i>

            <label>Replace:</label>
            <div class="inp-wrap">
                <tablda-select-simple
                        v-if="onlyOneHeader && single_field_vals.length"
                        :options="single_field_vals"
                        :table-row="{'find_string':find_string}"
                        :hdr_field="'find_string'"
                        :init_no_open="true"
                        style="border: 1px solid #CCC; font-size: 12px;"
                        @selected-item="(val) => {find_string = val}"
                ></tablda-select-simple>
                <input v-else="" class="form-control input-sm" v-model="find_string"/>
            </div>

            <label> With:</label>
            <div class="inp-wrap">
                <single-td-field
                        v-if="onlyOneHeader"
                        :table-meta="tableMeta"
                        :table-header="onlyOneHeader"
                        :td-value="replace_string"
                        :fixed-width="150"
                        :with_edit="true"
                        :force_edit="true"
                        style="border: 1px solid #CCC;"
                        @updated-td-val="(val) => {replace_string = val}"
                ></single-td-field>
                <input v-else="" class="form-control input-sm" v-model="replace_string"/>
            </div>

            <button class="btn btn-primary btn-sm blue-gradient go__btn"
                    @click="goReplace()"
                    :style="$root.themeButtonStyle"
            >Go</button>

            <div v-show="col_menu_opened" class="column_search_menu">
                <div class="form-group">
                    <label class="setting_label">Rows:</label>
                    <select class="form-control input-sm" v-model="request_type">
                        <option value="all_table">Entire Table</option>
                        <option value="list_view">List View (All Pages)</option>
                        <option value="current_page">Current Page</option>
                        <option value="checked_rows">Checked / Selected</option>
                    </select>
                </div>
                <div class="fields_checker">
                    <div>
                        <label class="setting_label">Columns:</label>
                    </div>
                    <div>
                        <label>
                            <span class="indeterm_check__wrap">
                                <span class="indeterm_check" @click="toggleAll()">
                                    <i v-if="allChecked" class="glyphicon glyphicon-ok group__icon"></i>
                                </span>
                            </span>
                            <span>Check/Uncheck All</span>
                        </label>
                    </div>
                    <div v-for="fld in tableMeta._fields" v-if="canEditHdr(fld)">
                        <label :style="{backgroundColor: (checkFunc(fld) ? '#CCC;' : '')}">
                            <span class="indeterm_check__wrap">
                                <span class="indeterm_check" @click="toggleColumn(fld)">
                                    <i v-if="checkFunc(fld)" class="glyphicon glyphicon-ok group__icon"></i>
                                </span>
                            </span>
                            <span>{{ $root.uniqName(fld.name) }}</span>
                        </label>
                    </div>
                </div>
            </div>

        </div>
    </div>
</template>

<script>
    import {eventBus} from './../../app';

    import CanEditMixin from "../_Mixins/CanViewEditMixin";

    import SingleTdField from "../CommonBlocks/SingleTdField";
    import TabldaSelectSimple from "../CustomCell/Selects/TabldaSelectSimple";

    export default {
        name: "StringReplaceButton",
        mixins: [
            CanEditMixin,
        ],
        components: {
            TabldaSelectSimple,
            SingleTdField,
        },
        data: function () {
            return {
                menu_opened: false,
                col_menu_opened: false,
                
                find_string: '',
                replace_string: '',
                edit_columns: [],
                single_field_vals: [],
                request_type: 'list_view',
            }
        },
        props:{
            request_params: Object,
            tableMeta: Object,
            table_id: Number,
        },
        watch: {
            table_id(val) {
                this.edit_columns = [];
                this.setAll();
            }
        },
        computed: {
            allChecked() {
                return this.edit_columns.length === _.filter(this.tableMeta._fields, (fld) => {
                        return this.canEditHdr(fld);
                    }).length;
            },
            onlyOneHeader() {
                return this.edit_columns.length === 1
                    ? _.find(this.tableMeta._fields, {field: this.edit_columns[0]})
                    : null;
            },
        },
        methods: {
            checkFunc(header) {
                return $.inArray(header.field, this.edit_columns) > -1;
            },
            toggleColumn(hdr) {
                let field = hdr.field;
                let idx = _.findIndex(this.edit_columns, function(el) { return el === field; });
                if (idx > -1) {
                    this.edit_columns.splice(idx, 1);
                } else {
                    this.edit_columns.push(field);
                }
                if (this.edit_columns.length === 1) {
                    this.loadFieldvals();
                }
//                this.find_string = '';
//                this.replace_string = '';
            },
            toggleAll() {
                if (this.allChecked) {
                    this.edit_columns = [];
                } else {
                    this.edit_columns = [];
                    this.setAll();
                }
                this.find_string = '';
                this.replace_string = '';
            },
            setAll() {
                _.each(this.tableMeta._fields, (fld) => {
                    if (this.canEditHdr(fld)) {
                        this.edit_columns.push( fld.field );
                    }
                });
            },

            toggleSearch(val) {
                this.menu_opened = val;
                this.col_menu_opened = false;
            },
            loadFieldvals() {
                let field = _.find(this.tableMeta._fields, {field: _.first(this.edit_columns)});
                this.single_field_vals = [];
                axios.post('/ajax/table-data/field/get-all-values', {
                    table_id: this.table_id,
                    field_id: field.id,
                }).then(({data}) => {
                    this.single_field_vals = data;
                }).catch(errors => {
                    Swal('', getErrors(errors));
                });
            },
            getRequestParams() {
                let rows_ids = [];
                let all_rows_checked = true;
                _.each(this.$root.listTableRows, (row) => {
                    if (row._checked_row) {
                        rows_ids.push(row.id);
                    } else {
                        all_rows_checked = false;
                    }
                });

                let request_opt;
                switch (this.request_type)
                {
                    case 'all_table':
                        request_opt = {
                            table_id: this.table_id,
                            page: 1,
                            rows_per_page: 0,
                        };
                        break;

                    case 'list_view':
                        request_opt = _.cloneDeep(this.request_params);
                        request_opt.page = 1;
                        request_opt.rows_per_page = 0;
                        break;

                    case 'current_page':
                        request_opt = _.cloneDeep(this.request_params);
                        request_opt.page = 1;
                        request_opt.rows_per_page = 0;
                        request_opt.row_id = _.map(this.$root.listTableRows, (row) => { return row.id });
                        break;

                    case 'checked_rows':
                        if (all_rows_checked) {
                            request_opt = _.cloneDeep(this.request_params);
                            request_opt.rows_per_page = 0;
                        } else if (rows_ids.length) {
                            request_opt = {
                                table_id: this.table_id,
                                row_id: rows_ids,
                                page: 1,
                                rows_per_page: 0,
                            };
                        } else {
                            Swal('No record has been selected.');
                        }
                        break;

                    default: Swal('Incorrect Request Type! Check the replace settings');
                }
                return request_opt;
            },
            goReplace() {
                let request_params = this.getRequestParams();

                if (request_params) {
                    $.LoadingOverlay('show');
                    axios.post('/ajax/table-data/find-replace', {
                        table_id: this.table_id,
                        term: String(this.find_string),
                        replace: String(this.replace_string),
                        columns: this.edit_columns,
                        request_params: request_params,
                    }).then(({data}) => {
                        Swal({
                            title: 'Total ' + data.total + ' items found. Total ' + data.can_replace + ' items can be replaced. Confirm to replace?',
                            confirmButtonClass: 'btn-danger',
                            confirmButtonText: 'Yes',
                            showCancelButton: true,
                            animation: 'slide-from-top'
                        }).then(response => {
                            if (response.value) {
                                $.LoadingOverlay('show');
                                axios.post('/ajax/table-data/do-replace', {
                                    table_id: this.table_id,
                                    term: String(this.find_string),
                                    replace: String(this.replace_string),
                                    columns: this.edit_columns,
                                    request_params: request_params,
                                }).then(({data}) => {
                                    eventBus.$emit('reload-page');
                                }).catch(errors => {
                                    Swal('', getErrors(errors));
                                }).finally(() => $.LoadingOverlay('hide'));
                            }
                        });
                    }).catch(errors => {
                        Swal('', getErrors(errors));
                    }).finally(() => $.LoadingOverlay('hide'));
                }
            },
            hideMenu(e) {
                let container = $(this.$refs.search_button);
                if (container.has(e.target).length === 0 && this.menu_opened){
                    this.toggleSearch(false);
                }
            }
        },
        created() {
            eventBus.$on('global-click', this.hideMenu);
            eventBus.$on('global-keydown', this.hideMenu);

            this.setAll();
        },
        beforeDestroy() {
            eventBus.$off('global-click', this.hideMenu);
            eventBus.$off('global-keydown', this.hideMenu);
        }
    }
</script>

<style lang="scss" scoped>
    .string_replace_button {
        cursor: pointer;
        width: 35px;
        height: 30px;
        padding: 0;
        font-size: 22px;
        background-color: transparent;
        position: relative;
        outline: none;
    }
    .string_replace_button > button {
        width: 100%;
        height: 100%;
        font-size: 0.9em;
        padding: 0;
        display: flex;
        justify-content: center;
        align-items: center;
    }

    .string_replace_menu {
        position: absolute;
        right: 100%;
        top: 100%;
        width: max-content;
        z-index: 500;
        background-color: #FFF;
        display: flex;
        align-items: center;
        padding: 3px;
        border: 1px solid #777;
        border-radius: 5px;

        .inp-wrap {
            width: 150px;
            position: relative;
            height: 24px;

            input {
                height: 100%;
            }
        }
        .go__btn {
            height: 30px;
            font-size: 16px;
            line-height: 20px;
            margin-left: 5px;
        }
    }
    .string_replace_menu > label {
        margin: 0 5px 0 10px;
        font-size: 16px;
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

        .setting_label {
            font-size: 16px;
        }

        .fields_checker > div {
            border-bottom: 1px dashed #CCC;
            display: flex;
        }
        .fields_checker > div > label {
            white-space: nowrap;
            font-size: 0.7em;
            margin: 0;
        }
    }
</style>