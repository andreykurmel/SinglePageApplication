<template>
    <div class="popup-wrapper" v-show="show_this" @click.self="hide()" :style="{zIndex: zIdx}">
        <div class="popup" :style="getPopupStyle()">
            <div class="flex flex--col">
                <div class="popup-header">
                    <div class="drag-bkg" draggable="true" @dragstart="dragPopSt()" @drag="dragPopup()"></div>
                    <div class="flex">
                        <div class="flex__elem-remain">Filtering</div>
                        <div class="" style="position: relative">
                            <span class="glyphicon glyphicon-remove pull-right header-btn" @click="hide()"></span>
                        </div>
                    </div>
                </div>
                <div class="flex__elem-remain popup-content">
                    <div class="flex__elem__inner popup-main">
                        <div class="full-frame" style="font-size: 1.2em;">
                            <div>
                                <label>Enter or select value(s) for field(s):</label>
                            </div>
                            <table style="width: 100%">
                                <tr v-for="$filt in filterParams">
                                    <td>{{ $filt.name }}</td>
                                    <td>
                                        <input v-if="$filt.input_only" class="form-control" v-model="$filt.search" style="margin-bottom: 4px;"/>
                                        <single-td-field
                                            v-else=""
                                            :table-meta="tableMeta || tempMeta"
                                            :table-header="$filt.all_header"
                                            :td-value="$filt.search"
                                            :with_edit="true"
                                            :force_edit="true"
                                            @updated-td-val="(val) => {$filt.search = val}"
                                            style="border: 1px solid #777; width: 100%"
                                        ></single-td-field>
                                    </td>
                                </tr>
                            </table>
                            <div class="right-txt">
                                <button class="btn btn-success" @click="findRecord">OK</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import {SpecialFuncs} from "../../classes/SpecialFuncs";

    import {eventBus} from '../../app';

    import PopupAnimationMixin from './../_Mixins/PopupAnimationMixin';

    export default {
        name: "TableViewFilteringPopup",
        mixins: [
            PopupAnimationMixin,
        ],
        components: {
        },
        data: function () {
            return {
                show_this: false,
                //PopupAnimationMixin
                getPopupWidth: 500,
                idx: 0,
                //request
                filterParams: [],
                limit: 3,
            }
        },
        props: {
            tableMeta: Object,
            tableView: Object,
        },
        computed: {
            getPopupHeight() {
                return 135 + (this.filterParams.length*40) + 'px';
            },
            tempMeta() {
                return {
                    id: this.tableView.table_id,
                    is_system: 0,
                };
            },
        },
        methods: {
            //finding
            findRecord() {
                let $err = false;
                _.each(this.filterParams, (el) => {
                    $err = $err || !String(el.search || '').length;
                });
                if ($err) {
                    Swal('Info','Empty values are not allowed!');
                    return;
                }
                $.LoadingOverlay('show');
                axios.post('/ajax/table-data/get', {
                    ...SpecialFuncs.tableMetaRequest(this.tableView.table_id),
                    ...{
                        table_id: this.tableView.table_id,
                        page: 1,
                        rows_per_page: 0,
                        search_view: this.filterParams,
                    }
                }).then(({ data }) => {
                    if (data.rows && data.rows.length) {
                        this.$emit('record-found', this.filterParams);
                        this.hide();
                    } else {
                        if (this.limit <= 0) {
                            window.location = '/';
                        } else {
                            this.limit--;
                            _.each(this.filterParams, (el) => { el.search = '' });
                            Swal('Info','Record not found!');
                        }
                    }
                }).catch(errors => {
                    Swal('Info', getErrors(errors));
                }).finally(() => {
                    $.LoadingOverlay('hide');
                });
            },
            //additionals
            hide() {
                this.show_this = false;
                this.$root.tablesZidxDecrease();
                this.$emit('popup-close');
            },
            showTableViewsPopupHandler() {
                this.show_this = true;
                this.$root.tablesZidxIncrease();
                this.zIdx = this.$root.tablesZidx;
                this.runAnimation();
            },
        },
        mounted() {
            _.each(this.tableView._filtering, (el) => {
                this.filterParams.push({
                    name: el._field.name,
                    field: el._field.field,
                    search: '',
                    criteria: el.criteria,
                    input_only: el.input_only,
                    all_header: el._field,
                });
            });
            if (this.$root.user.view_filtering_row) {
                _.each(this.filterParams, (filter) => {
                    filter.search = this.$root.user.view_filtering_row[filter.field];
                });
                this.findRecord();
            } else {
                this.showTableViewsPopupHandler();
            }
        },
        beforeDestroy() {
        }
    }
</script>

<style lang="scss" scoped>
    @import "./CustomEditPopUp";

    .popup {
        .right-txt {
            text-align: right;
            margin-top: 15px;
        }
    }
</style>