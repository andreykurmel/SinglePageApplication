<template>
    <div ref="search_button" class="table_search_menu">

        <i v-if="!as_input_style" class="glyphicon glyphicon-cog" @click="col_menu_opened = !col_menu_opened"></i>

        <input class="form-control" placeholder="type 3 or more characters to search"
               :disabled="is_disabled"
               :style="as_input_style ? as_input_style.form_control : {}"
               @focus="inputKey(true)"
               @keyup="inputKey(false)"
               v-model="search_obj.string"/>

        <button v-if="!as_input_style"
                v-show="hasSearch || found_model._id"
                class="btn btn-primary btn-sm blue-gradient"
                title="Unload the data"
                :style="$root.themeButtonStyle"
                :is_disabled="is_disabled"
                @click="clearSearch()">
            <img src="/images/unload.png" height="34"/>
        </button>

        <span class="selected_span" :style="as_input_style ? as_input_style.selected_span : {}">
            <span v-if="found_model._id" v-html="GetSelectedModelString()"></span>
            <span v-else="">No record loaded.</span>
        </span>

        <!--Absolutes-->
        <model-checker
                v-show="col_menu_opened"
                class="column_search_menu"
                :columns="search_obj.model_columns"
        ></model-checker>

        <div v-if="popup_rows.length" class="search_popup_wrapper" :style="as_input_style ? as_input_style.search_popup_wrapper : {}">
            <div class="search__results" :class="[total_found > search_results_len ? 'popup_border-r-top' : '']">
<!--                <div>-->
<!--                    <input class="form-control input-sm i-filter" placeholder="Filter" v-model="result_filter">-->
<!--                </div>-->
                <div v-for="(row, idx) in available_popup_rows"
                     class="search_popup_item"
                     @click="AutoRowClicked(row)"
                     @mouseover="hover_row = idx"
                     :style="foundOwnedRow(row)"
                >
                    <span v-for="tableHeader in search_obj.model_columns" v-if="showInAutopop(tableHeader)">
                        {{ getAutopopVal(row, tableHeader) }} |
                    </span>
                    <button class="btn btn-default btn-sm copy_btn"
                            :style="{display: hover_row === idx ? 'block' : 'none'}"
                            @click.stop="copyHandler(row)">Copy</button>
                </div>
            </div>
            <div v-if="total_found && total_found > search_results_len" class="search__noter">
                Note: only the first {{ search_results_len }} are displayed. Total {{ total_found }} matching records.
            </div>
        </div>
        <!--Absolutes ^^^^^-->
    </div>
</template>

<script>
    import {TabObject} from '../../../classes/TabObject';
    import {StimLinkParams} from '../../../classes/StimLinkParams';
    import {FoundModel} from '../../../classes/FoundModel';
    import {SpecialFuncs} from "../../../classes/SpecialFuncs";

    import { mapState } from 'vuex';

    import { eventBus } from "../../../app";

    import ModelChecker from "./ModelChecker.vue";

    export default {
        name: "WidSearchModel",
        mixins: [
        ],
        components: {
            ModelChecker
        },
        data: function () {
            return {
                result_filter: '',
                search_obj: {
                    string: '',
                    model_columns: [],
                },

                col_menu_opened: false,
                timerout: null,
                popup_rows: [],
                total_found: 0,
                search_results_len: 10,
                hover_row: null,
            }
        },
        props:{
            found_model: {
                type: FoundModel,
                required: true,
            },
            stim_link_params: {
                type: StimLinkParams,
                required: true,
            },
            tab_object: TabObject,
            is_visible: Boolean,
            is_disabled: Boolean,
            just_owned: Boolean,
            as_input_style: Object,
            left_offset: Number,
        },
        computed: {
            loaded_model() {
                return this.found_model.meta && this.found_model.meta.is_loaded;
            },
            hasSearch() {
                return this.search_obj.string;
            },
            available_popup_rows() {
                if (this.result_filter) {
                    return _.filter(this.popup_rows, (row) => {
                        return _.find(this.search_obj.model_columns, (col) => {
                            return this.showInAutopop(col)
                                && String(row[col.field]).indexOf(this.result_filter) > -1;
                        });
                    });
                } else {
                    return this.popup_rows;
                }
            },
        },
        watch: {
            loaded_model(val) {
                if (val) {
                    this.setModelColumns();
                }
            },
            is_visible: {
                handler(val) {
                    if (val && !this.loaded_model) {
                        this.found_model.meta.loadHeaders();
                    }
                },
                immediate: true,
            },
        },
        methods: {
            foundOwnedRow(row) {
                let style = {};
                let t_meta = this.found_model.meta.params;
                let first_usr = _.find(t_meta._fields, (fld) => {
                    return !in_array(fld.field, this.$root.systemFields) && fld.f_type === 'User';
                });
                if (first_usr) {
                    style.color = (row[first_usr.field] == this.$root.user.id || String(row[first_usr.field]).indexOf('"'+this.$root.user.id+'"') > -1
                        ? '#00F'
                        : '');
                }
                return style;
            },
            showInAutopop(hdr) {
                return hdr.checked && hdr.is_search_autocomplete_display == 1;
            },
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
            setModelColumns() {
                let t_meta = this.found_model.meta.params;
                let accessible = _.filter(this.found_model.meta.params._fields, (hdr) => {
                    return this.stim_link_params.avail_columns_for_app.indexOf(hdr.field) > -1
                        && (t_meta._is_owner || t_meta._current_right.view_fields.indexOf(hdr.field) > -1);
                });
                this.search_obj.model_columns = _.map(accessible, (hdr) => {
                    return {
                        checked: true,
                        field: hdr.field,
                        name: hdr.name,
                        f_type: hdr.f_type,
                        is_search_autocomplete_display: hdr.is_search_autocomplete_display,
                    };
                });
            },
            GetSelectedModelString() {
                let strings = [];
                if (this.loaded_model) {
                    let t_meta = this.found_model.meta.params;
                    let model_row = this.found_model.masterRow();
                    _.each(this.stim_link_params.top_columns_show, (db_name) => {
                        let fld = _.find(t_meta._fields, {field: db_name});
                        if (fld && fld.f_type === 'User') {
                            let usr_string = this.$root.getUserFullStr(model_row, fld, t_meta._owner_settings);
                            strings.push(usr_string);
                        } else {
                            strings.push(model_row[db_name]);
                        }
                    });
                }

                let str = strings.join('_');
                str = str.replace(/<img[^>]*>/gi, '');
                str = str.replace(/&nbsp;/gi, '');
                this.found_model.selected_html = str;

                return strings.join(' | ');
            },
            setFoundModel(model) {
                if (this.loaded_model) {
                    this.found_model.setSelectedRow(model);
                    this.$emit('set-found-model', model);
                }
                this.search_obj.string = '';
            },
            clearSearch() {
                this.setFoundModel(null);
                this.hideAbsolutes();
            },
            inputKey(immediate) {
                if (immediate) {
                    this.sendAutoComplete();
                } else {
                    clearTimeout(this.timerout);
                    this.timerout = setTimeout(this.sendAutoComplete, 500);
                }
            },
            selectedModelColumnFields() {
                return this.search_obj.model_columns
                    .filter((el) => {
                        return el.checked;
                    })
                    .map((el) => {
                        return el.field;
                    });
            },
            sendAutoComplete() {
                axios.post('?method=search_model', {
                    just_owned: this.just_owned ? 1 : 0,
                    string: this.search_obj.string,
                    columns: this.selectedModelColumnFields(),
                    app_table: this.stim_link_params.app_table,
                }).then(({ data }) => {
                    this.popup_rows = data.rows;
                    this.total_found = data.rows_count;
                    this.search_results_len = data.search_results_len;
                }).catch(errors => {
                    Swal('', getErrors(errors));
                }).finally(() => {});
            },
            AutoRowClicked(row) {
                this.setFoundModel(row);
                this.hideAbsolutes();
            },
            hideAbsolutes() {
                this.col_menu_opened = false;
                this.popup_rows = [];
                this.total_found = 0;
            },
            hideMenu(e) {
                let container = $(this.$refs.search_button);
                if (container.has(e.target).length === 0){
                    this.hideAbsolutes();
                }
            },
            copyHandler(row) {
                let html = [];
                _.each(this.search_obj.model_columns, (hdr) => {
                    if (this.showInAutopop(hdr)) {
                        html.push( this.getAutopopVal(row, hdr) );
                    }
                });
                SpecialFuncs.strToClipboard(html.join(' | '));
            },
        },
        mounted() {
            if (this.as_input_style) {
                this.setModelColumns();
            }
            eventBus.$on('global-click', this.hideMenu);
        },
        beforeDestroy() {
            eventBus.$off('global-click', this.hideMenu);
        }
    }
</script>

<style lang="scss" scoped>
    .table_search_menu {
        position: relative;
        font-size: 20px;
    }
    .table_search_menu > i, .table_search_menu > input, .table_search_menu > select {
        float: left;
    }
    .table_search_menu > i {
        top: 7px;
        margin-right: 7px;
    }
    .table_search_menu > input {
        width: 330px;
    }
    .table_search_menu > select {
        width: 60px;
        padding: 3px;
    }
    .table_search_menu > .btn-primary {
        position: absolute;
        left: 100%;
        top: 0;
        padding: 0;
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
        left: 27px;
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
                overflow: hidden;
                position: relative;
                font-size: 12px;
                padding: 0 5px;

                &:hover {
                    background-color: rgba(0,0,0,0.15);
                }

                .copy_btn {
                    padding: 0 3px;
                    position: absolute;
                    right: 0;
                    top: 0;
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

    .selected_span {
        padding-left: 27px;
        font-size: 13px;
        font-style: italic;
        position: absolute;
        left: 0;
        top: 100%;
        white-space: nowrap;
        max-width: 100%;
        text-overflow: ellipsis;
    }

    .i-filter {
        padding: 3px 6px;
        height: 24px;
    }
</style>