<template>
    <div class="full-height">
        <div class="string_replace_menu">
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
                        :ext-row="firstSelected"
                        style="border: 1px solid #CCC;"
                        @updated-td-val="(val) => {replace_string = val}"
                ></single-td-field>
                <input v-else="" class="form-control input-sm" v-model="replace_string"/>
            </div>

            <button class="btn btn-primary btn-sm blue-gradient go__btn"
                    @click="goReplace()"
                    :style="$root.themeButtonStyle"
            >Go</button>
        </div>

        <div class="column_search_menu">
            <div class="form-group">
                <label class="setting_label">Rows:</label>
                <select class="form-control input-sm" v-model="request_type">
                    <option value="all_table">Entire Table</option>
                    <option value="list_view">Grid View (All Pages)</option>
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
</template>

<script>
    import {SpecialFuncs} from '../../classes/SpecialFuncs';

    import {eventBus} from '../../app';

    import CanEditMixin from "../_Mixins/CanViewEditMixin";

    import SingleTdField from "../CommonBlocks/SingleTdField";
    import TabldaSelectSimple from "../CustomCell/Selects/TabldaSelectSimple";

    export default {
        name: "ReplaceBlock",
        mixins: [
            CanEditMixin,
        ],
        components: {
            TabldaSelectSimple,
            SingleTdField,
        },
        data: function () {
            return {
                find_string: '',
                replace_string: '',
                edit_columns: [],
                single_field_vals: [],
                request_type: 'list_view',
            }
        },
        props:{
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
            firstSelected() {
                return _.find(this.$root.listTableRows, {_checked_row: true});
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

            loadFieldvals() {
                let field = _.find(this.tableMeta._fields, {field: _.first(this.edit_columns)});
                this.single_field_vals = [];
                axios.post('/ajax/table-data/field/get-all-values', {
                    table_id: this.table_id,
                    field_id: field.id,
                    special_params: SpecialFuncs.specialParams(),
                }).then(({data}) => {
                    this.single_field_vals = data;
                }).catch(errors => {
                    Swal('', getErrors(errors));
                });
            },
            getRequestParams() {
                let check_obj = this.$root.checkedRowObject(this.$root.listTableRows);

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
                        request_opt = _.cloneDeep(this.$root.request_params);
                        request_opt.page = 1;
                        request_opt.rows_per_page = 0;
                        break;

                    case 'current_page':
                        request_opt = _.cloneDeep(this.$root.request_params);
                        request_opt.page = 1;
                        request_opt.rows_per_page = 0;
                        request_opt.row_id = _.map(this.$root.listTableRows, (row) => { return row.id });
                        break;

                    case 'checked_rows':
                        if (check_obj.all_checked) {
                            request_opt = _.cloneDeep(this.$root.request_params);
                            request_opt.rows_per_page = 0;
                        } else if (check_obj.rows_ids.length) {
                            request_opt = {
                                table_id: this.table_id,
                                row_id: check_obj.rows_ids,
                                page: 1,
                                rows_per_page: 0,
                            };
                        } else {
                            Swal('No record has been selected.');
                        }
                        break;

                    default: Swal('Incorrect Request Type! Check the replace settings');
                }
                request_opt.special_params = SpecialFuncs.specialParams();
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
                            title: 'Total ' + data.total + ' item(s) found. Total ' + data.can_replace + ' item(s) will be replaced. Confirm to proceed?',
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
        },
        created() {
            this.setAll();
        },
        beforeDestroy() {
        }
    }
</script>

<style lang="scss" scoped>
    .string_replace_menu {
        height: 50px;
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
        height: calc(100% - 50px);
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
            margin: 0;
        }
    }
</style>