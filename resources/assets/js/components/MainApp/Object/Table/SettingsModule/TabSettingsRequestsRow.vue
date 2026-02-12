<template>
    <div class="container-fluid full-height">
        <div class="row full-height permissions-tab" :style="textSysStyleSmart">
            <div class="permissions-panel full-height" :style="$root.themeMainBgStyle">
                <div class="permissions-menu-header">
                    <button class="btn btn-default btn-sm" :class="{active : activeTab === 'overall'}" :style="textSysStyle" @click="changeTab('overall')">
                        Overall
                    </button>
                    <button class="btn btn-default btn-sm" :class="{active : activeTab === 'title'}" :style="textSysStyle" @click="changeTab('title')">
                        Header
                    </button>
                    <button class="btn btn-default btn-sm" :class="{active : activeTab === 'form'}" :style="textSysStyle" @click="changeTab('form')">
                        Form
                    </button>

                    <div class="flex" v-if="with_edit" style="position: absolute;top: -2px;right: 0;">
                        <div class="flex flex--center">
                            <span class="indeterm_check__wrap" style="color: #555">
                                <span class="indeterm_check" @click="templateClick">
                                    <i v-if="requestRow['is_template'] == 1" class="glyphicon glyphicon-ok group__icon"></i>
                                </span>
                            </span>
                            <label style="margin: 0;">&nbsp;Save as a Template &nbsp;</label>
                        </div>
                        <div class="flex flex--center" style="margin-left: 10px;">
                            <button style="padding: 0 5px;"
                                    :style="$root.themeButtonStyle"
                                    class="btn btn-default btn-sm blue-gradient"
                                    :disabled="!from_dcr_id"
                                    @click="copyDcrDesign"
                            >
                                Copy design from template:
                            </button>
                            <select-block
                                :options="tmplts()"
                                :sel_value="from_dcr_id"
                                :link_path="tmpltPath()"
                                style="width: 150px;height: 26px;padding: 0 3px;"
                                @option-select="(opt) => { from_dcr_id = opt.val }"
                            ></select-block>
                        </div>
                    </div>
                </div>
                <div class="permissions-menu-body">

                    <!--OVERALL-->
                    <div class="full-frame defaults-tab" v-show="activeTab === 'overall'" :style="$root.themeMainBgStyle">
                        <tab-settings-requests-row-overall
                            :table_id="table_id"
                            :table-meta="tableMeta"
                            :cell-height="cellHeight"
                            :max-cell-rows="maxCellRows"
                            :table-request="tableRequest"
                            :request-row="requestRow"
                            :with_edit="with_edit"
                            @updated-cell="emitUpdatedCell"
                            @upload-file="emitUploadFile"
                            @del-file="emitDelFile"
                        ></tab-settings-requests-row-overall>
                    </div>

                    <!--TITLE-->
                    <div class="full-frame defaults-tab" v-if="activeTab === 'title'" :style="$root.themeMainBgStyle">
                        <tab-settings-requests-row-title
                            :table_id="table_id"
                            :table-meta="tableMeta"
                            :cell-height="cellHeight"
                            :max-cell-rows="maxCellRows"
                            :table-request="tableRequest"
                            :request-row="requestRow"
                            :with_edit="with_edit"
                            :titleuniqid="titleuniqid"
                            @updated-cell="emitUpdatedCell"
                            @upload-file="emitUploadFile"
                            @del-file="emitDelFile"
                        ></tab-settings-requests-row-title>
                    </div>

                    <!--FORM-->
                    <div class="full-frame defaults-tab" v-show="activeTab === 'form'" :style="$root.themeMainBgStyle">
                        <tab-settings-requests-row-form
                            :table_id="table_id"
                            :table-meta="tableMeta"
                            :cell-height="cellHeight"
                            :max-cell-rows="maxCellRows"
                            :table-request="tableRequest"
                            :request-row="requestRow"
                            :with_edit="with_edit"
                            @updated-cell="emitUpdatedCell"
                        ></tab-settings-requests-row-form>
                    </div>

                </div>

            </div>
        </div>
    </div>
</template>

<script>
    import {SpecialFuncs} from '../../../../../classes/SpecialFuncs';

    import {eventBus} from '../../../../../app';

    import CellStyleMixin from "../../../../_Mixins/CellStyleMixin.vue";
    import ReqRowMixin from "./ReqRowMixin.vue";

    import SelectBlock from "../../../../CommonBlocks/SelectBlock";
    import TabSettingsRequestsRowTitle from "./TabSettingsRequestsRowTitle.vue";
    import TabSettingsRequestsRowForm from "./TabSettingsRequestsRowForm.vue";
    import TabSettingsRequestsRowOverall from "./TabSettingsRequestsRowOverall.vue";

    export default {
        components: {
            TabSettingsRequestsRowOverall,
            TabSettingsRequestsRowForm,
            TabSettingsRequestsRowTitle,
            SelectBlock,
        },
        mixins: [
            CellStyleMixin,
            ReqRowMixin,
        ],
        name: "TabSettingsRequestsRow",
        data: function () {
            return {
                from_dcr_id: null,
                titleuniqid: uuidv4(),
                topmsguniqid: uuidv4(),
                activeTab: 'overall',
            };
        },
        props:{
            table_id: Number,
            cellHeight: Number,
            maxCellRows: Number,
            tableRequest: Object,
            requestRow: Object,
            tableMeta: Object,
            with_edit: Boolean
        },
        computed: {
        },
        watch: {
            table_id(val) {
                this.changeTab('overall');
            },
        },
        methods: {
            changeTab(key) {
                this.activeTab = key;
                this.$emit('set-sub-tab', key);
            },
            copyDcrDesign() {
                this.$root.sm_msg_type = 1;
                axios.post('/ajax/table-data-request/copy', {
                    from_data_request_id: this.from_dcr_id,
                    to_data_request_id: this.requestRow.id,
                }).then(({ data }) => {
                    if (data && _.first(data)) {
                        SpecialFuncs.assignProps(this.requestRow, _.first(data));
                    }
                }).catch(errors => {
                    Swal('Info', getErrors(errors));
                }).finally(() => {
                    this.$root.sm_msg_type = 0;
                });
            },
            templateClick() {
                this.requestRow['is_template'] = this.requestRow['is_template'] == 1 ? 0 : 1;

                this.$root.settingsMeta.template_dcrs = _.filter(this.$root.settingsMeta.template_dcrs, (dcr) => {
                    return dcr.id != this.requestRow.id;
                });
                if (this.requestRow['is_template']) {
                    let permis = _.clone(this.requestRow);
                    permis._table = _.clone(this.tableMeta);
                    this.$root.settingsMeta.template_dcrs.push(permis);
                }

                this.updatedCell();
            },
            tableDataStringUpdateHandler(uniq_id, val) {
                if (uniq_id === this.titleuniqid) {
                    this.requestRow['dcr_title'] = val;
                    this.updatedCell();
                }
                if (uniq_id === this.topmsguniqid) {
                    this.requestRow['dcr_form_message'] = val;
                    this.updatedCell();
                }
            },
            tmplts() {
                return _.filter(this.$root.settingsMeta.template_dcrs, (tmplte) => {
                    return tmplte.id != this.requestRow.id;
                }).map((tmplte) => {
                    return {
                        val: tmplte.id,
                        show: tmplte.name,
                        style: {color: tmplte._table.user_id == this.$root.user.id ? '#00F' : ''},
                    };
                });
            },
            tmpltPath() {
                let tmpDcr = _.find(this.$root.settingsMeta.template_dcrs, {id: Number(this.from_dcr_id)});
                let tbid = tmpDcr ? tmpDcr._table.id : null;
                let availTb = _.find(this.$root.settingsMeta.available_tables, {id: Number(tbid)});
                return availTb ? availTb.__url : '';
            },
            emitUpdatedCell(requestRow, changedKey) {
                this.$emit('updated-cell', requestRow, changedKey);
            },
            emitUploadFile(requestRow, key, file) {
                this.$emit('upload-file', requestRow, key, file);
            },
            emitDelFile(requestRow, key) {
                this.$emit('del-file', requestRow, key);
            },
        },
        mounted() {
            eventBus.$on('table-data-string-popup__update', this.tableDataStringUpdateHandler);
        },
        beforeDestroy() {
            eventBus.$off('table-data-string-popup__update', this.tableDataStringUpdateHandler);
        }
    }
</script>

<style lang="scss" scoped>
    @import "./TabSettingsPermissions";
    @import "./ReqRowStyle";

    .ck_textarea {
        margin-top: 5px;
        height: 300px;
    }

    .add_link {
        white-space: nowrap;
        margin-bottom: 10px;

        label {
            margin: 0;
        }
        select {
            max-width: 250px;
            height: 30px;
            padding: 3px 6px;
        }
    }

    .max-sm {
        max-width: 75px;
    }
    .btn-default {
        height: 30px;
    }
</style>