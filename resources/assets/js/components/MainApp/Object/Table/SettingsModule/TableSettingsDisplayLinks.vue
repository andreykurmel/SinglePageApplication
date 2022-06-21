<template>
    <div class="link-data" style="background-color: inherit;">
        <div class="link-menu">
            <button class="btn btn-default" :class="{active: activeTab === 'outgo'}" :style="textSysStyle" @click="activeTab = 'outgo';">
                Outgoing
            </button>
            <button class="btn btn-default" :class="{active: activeTab === 'incom'}" :style="textSysStyle" @click="activeTab = 'incom';">
                Incoming
            </button>
        </div>

        <!--Tab with link settings-->
        <div class="link-tab" v-show="activeTab === 'outgo'">
            <div class="container-fluid full-height">
                <div class="row full-height permissions-tab">
                    <!--LEFT SIDE-->
                    <div class="col-xs-12 full-height" :style="{width: '40%', paddingRight: 0}">

                        <div class="full-height">
                            <div class="top-text top-text--height" :style="textSysStyle">
                                <span>Select a Column</span>
                            </div>
                            <div class="permissions-panel no-padding" :style="{height: 'calc(50% - 32px)'}">
                                <div class="full-frame">
                                    <custom-table
                                            :cell_component_name="'custom-cell-settings-display'"
                                            :global-meta="tableMeta"
                                            :table-meta="settingsMeta['table_fields']"
                                            :settings-meta="settingsMeta"
                                            :all-rows="ActiveLinkFields"
                                            :adding-row="addingRowFields"
                                            :rows-count="ActiveLinkFields.length"
                                            :cell-height="1"
                                            :max-cell-rows="0"
                                            :is-full-width="true"
                                            :selected-row="selectedForLinksCol"
                                            :user="user"
                                            :behavior="'settings_display_add'"
                                            :available-columns="availableColumns"
                                            :use_theme="true"
                                            :no_width="true"
                                            @added-row="activeField"
                                            @delete-row="inactiveField"
                                            @row-index-clicked="rowIndexClickedColumn"
                                    ></custom-table>
                                </div>
                            </div>

                            <div class="top-text top-text--height" :style="textSysStyle">
                                <span v-if="!tableMeta._fields[selectedCol]">You should select column</span>
                                <span v-else="">Link(s) at Column: <span>{{ $root.uniqName(tableMeta._fields[selectedCol].name) }}</span></span>

                                <info-sign-link
                                        class="right-elem"
                                        :app_sett_key="'help_link_settings_links'"
                                        :hgt="26"
                                ></info-sign-link>
                            </div>
                            <div class="permissions-panel no-padding" :style="{height: 'calc(50% - 33px)'}">
                                <div class="full-frame" v-if="tableMeta._fields[selectedCol]">
                                    <custom-table
                                            :cell_component_name="'custom-cell-display-links'"
                                            :global-meta="tableMeta"
                                            :table-meta="settingsMeta['table_field_links']"
                                            :settings-meta="settingsMeta"
                                            :all-rows="tableMeta._fields[selectedCol]._links || []"
                                            :rows-count="tableMeta._fields[selectedCol]._links.length || 0"
                                            :cell-height="1"
                                            :max-cell-rows="0"
                                            :is-full-width="true"
                                            :adding-row="addingRow"
                                            :user="user"
                                            :behavior="'settings_display_links'"
                                            :available-columns="availableLinks"
                                            :selected-row="selectedLink"
                                            :use_theme="true"
                                            :widths_div="2"
                                            @added-row="addLink"
                                            @updated-row="updateLink"
                                            @delete-row="deleteLink"
                                            @row-index-clicked="rowIndexClickedLink"
                                            @show-add-ref-cond="showAddRefCond"
                                    ></custom-table>
                                </div>
                            </div>
                        </div>

                    </div>

                    <!--RIGHT SIDE-->

                    <div class="col-xs-12 full-height" :style="{width: '60%'}">

                        <div class="full-height">

                            <div class="top-text top-text--height" :style="textSysStyle">
                                <span v-if="!linkRow.id || !tableMeta._fields[selectedCol]">Click # to select a link at a column</span>
                                <span v-else="">Details for Link #{{ selectedLink+1 }} at Column: <span>{{ $root.uniqName(tableMeta._fields[selectedCol].name) }}</span></span>
                            </div>
                            <div class="permissions-panel no-padding">
                                <div class="full-frame" v-if="linkRow.id && tableMeta._fields[selectedCol]">

                                    <vertical-table
                                            class="spaced-table__fix"
                                            :td="'custom-cell-display-links'"
                                            :global-meta="tableMeta"
                                            :table-meta="settingsMeta['table_field_links']"
                                            :settings-meta="settingsMeta"
                                            :table-row="linkRow"
                                            :user="user"
                                            :cell-height="1"
                                            :max-cell-rows="0"
                                            :available-columns="availableLinkColumns"
                                            :widths="{name: '35%', col: '65%', history: 0, no_margins: true}"
                                            @show-add-ref-cond="showAddRefCond"
                                            @updated-cell="updateLink"
                                    ></vertical-table>

                                    <template v-if="linkRow.id && linkRow.link_type === 'App' && linkRow.table_app_id != $root.settingsMeta.payment_app_id">
                                        <button class="btn btn-success"
                                                :style="{marginLeft: '10px'}"
                                                @click="showLinkParams = !showLinkParams"
                                        >Calling / URL Parameters</button>
                                        <div v-show="showLinkParams" class="params-wrapper">
                                            <custom-table
                                                    :cell_component_name="'custom-cell-display-links'"
                                                    :global-meta="tableMeta"
                                                    :table-meta="settingsMeta['table_field_link_params']"
                                                    :all-rows="linkRow._params"
                                                    :rows-count="linkRow._params.length"
                                                    :cell-height="1"
                                                    :max-cell-rows="0"
                                                    :is-full-width="true"
                                                    :behavior="'settings_display_links'"
                                                    :user="user"
                                                    :adding-row="addingRow"
                                                    :use_theme="true"
                                                    :no_width="true"
                                                    @added-row="addLinkParam"
                                                    @updated-row="updateLinkParam"
                                                    @delete-row="deleteLinkParam"
                                            ></custom-table>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>

                    </div>

                </div>
            </div>
        </div>

        <!--Summarize incoming link-->
        <div class="link-tab" v-show="activeTab === 'incom'">
            <div class="full-frame">
                <custom-table
                        v-if="tableMeta && tableMeta.__incoming_links"
                        :cell_component_name="'custom-cell-incoming-links'"
                        :global-meta="tableMeta"
                        :table-meta="settingsMeta['incoming_links']"
                        :settings-meta="settingsMeta"
                        :all-rows="tableMeta.__incoming_links"
                        :rows-count="tableMeta.__incoming_links.length"
                        :cell-height="1"
                        :max-cell-rows="0"
                        :is-full-width="true"
                        :user="user"
                        :available-columns="availableIncom"
                        :behavior="'incoming_links'"
                        :use_theme="true"
                        @updated-row="updateIncomLink"
                ></custom-table>
            </div>
        </div>
    </div>
</template>

<script>
    import {eventBus} from '../../../../../app';

    import DisplayLinksMixin from '../../../../_Mixins/DisplayLinksMixin';
    import CellStyleMixin from "../../../../_Mixins/CellStyleMixin";

    import CustomTable from '../../../../CustomTable/CustomTable';
    import VerticalTable from '../../../../CustomTable/VerticalTable';
    import InfoSignLink from "../../../../CustomTable/Specials/InfoSignLink";

    export default {
        name: "TableSettingsDisplayLinks",
        mixins: [
            DisplayLinksMixin,
            CellStyleMixin,
        ],
        components: {
            InfoSignLink,
            CustomTable,
            VerticalTable
        },
        data: function () {
            return {
                activeTab: 'outgo',
                selectedForLinksCol: 0,
                selectedCol: 0,
                availableColumns: ['name'],
                excluded_row_values: [{
                    field: 'field',
                    excluded: this.$root.systemFields
                }],
                addingRowFields: {
                    active: true,
                    position: 'bottom'
                },
                showLinkParams: false,
                availableIncom: ['incoming_allow', 'user_id', 'table_name', 'ref_cond_name', 'use_category', 'use_name'],
            }
        },
        props:{
            tableMeta: Object,
            settingsMeta: Object,
            table_id: Number|null,
            user:  Object,
            foreign_sel_fld_id: Number,
            foreign_sel_id: Number,
        },
        computed: {
            ActiveLinkFields() {
                return _.filter(this.tableMeta._fields, {active_links: 1});
            }
        },
        methods: {
            redrawTbs() {
                let tmp_col = this.selectedCol;
                let tmp_link = this.selectedLink;
                this.selectedCol = -1;
                this.selectedLink = -1;
                this.$nextTick(() => {
                    this.selectedCol = tmp_col;
                    this.selectedLink = tmp_link;
                });
            },
            //sys methods
            inArray(item, array) {
                return $.inArray(item, array) > -1;
            },
            rowIndexClickedColumn(index) {
                let fld = this.ActiveLinkFields[index];

                this.selectedForLinksCol = index;
                this.selectedCol = _.findIndex(this.tableMeta._fields, {id: Number(fld ? fld.id : 0)});
                this.selectedLink = 0;
            },
            showAddRefCond(refId) {
                eventBus.$emit('show-ref-conditions-popup', this.tableMeta.db_name, refId);
            },

            //Settings Columns Functions
            activeField(tableRow) {
                let col_id = tableRow.id;
                this.toggleField(col_id, 1);
            },
            inactiveField(tableRow) {
                let col_id = tableRow.id;
                this.toggleField(col_id, 0);
            },
            toggleField(col_id, status) {
                this.$root.sm_msg_type = 1;
                axios.put('/ajax/settings/data', {
                    table_field_id: col_id,
                    field: 'active_links',
                    val: status,
                }).then(({ data }) => {
                    let field = _.find(this.tableMeta._fields, {id: Number(col_id)});
                    if (field) {
                        field.active_links = status;
                        if (status) {
                            this.rowIndexClickedColumn( _.findIndex(this.ActiveLinkFields, {id: Number(col_id)}) );
                        } else {
                            field._links = [];
                            this.rowIndexClickedColumn(-1);
                        }
                    }
                }).catch(errors => {
                    Swal('', getErrors(errors));
                }).finally(() => {
                    this.$root.sm_msg_type = 0;
                });
            },

            //Link Param Functions
            addLinkParam(tableRow) {
                this.$root.sm_msg_type = 1;

                let fields = _.cloneDeep(tableRow);//copy object
                this.$root.deleteSystemFields(fields);

                axios.post('/ajax/settings/data/link/param', {
                    table_field_link_id: this.linkRow.id,
                    fields: fields,
                }).then(({ data }) => {
                    this.linkRow._params.push(data);
                }).catch(errors => {
                    Swal('', getErrors(errors));
                }).finally(() => {
                    this.$root.sm_msg_type = 0;
                });
            },
            updateLinkParam(tableRow) {
                this.$root.sm_msg_type = 1;

                let group_id = tableRow.id;
                let fields = _.cloneDeep(tableRow);//copy object
                this.$root.deleteSystemFields(fields);

                axios.put('/ajax/settings/data/link/param', {
                    table_field_link_param_id: group_id,
                    fields: fields
                }).then(({ data }) => {
                }).catch(errors => {
                    Swal('', getErrors(errors));
                }).finally(() => {
                    this.$root.sm_msg_type = 0;
                });
            },
            deleteLinkParam(tableRow) {
                this.$root.sm_msg_type = 1;
                axios.delete('/ajax/settings/data/link/param', {
                    params: {
                        table_field_link_param_id: tableRow.id
                    }
                }).then(({ data }) => {
                    let idx = _.findIndex(this.linkRow._params, {id: Number(tableRow.id)});
                    if (idx > -1) {
                        this.linkRow._params.splice(idx, 1);
                    }
                }).catch(errors => {
                    Swal('', getErrors(errors));
                }).finally(() => {
                    this.$root.sm_msg_type = 0;
                });
            },
            //
            updateIncomLink(tableRow) {
                this.$root.sm_msg_type = 1;
                axios.post('/ajax/ref-condition/incom', {
                    table_ref_condition_id: tableRow.id,
                    incoming_allow: tableRow.incoming_allow,
                }).then(({ data }) => {
                    this.tableMeta.__incoming_links = data.__incoming_links;
                }).catch(errors => {
                    Swal('', getErrors(errors));
                }).finally(() => {
                    this.$root.sm_msg_type = 0;
                });
            },
        },
        mounted() {
            let idxfld = this.foreign_sel_fld_id
                ? _.findIndex(this.ActiveLinkFields, {id: Number(this.foreign_sel_fld_id)})
                : 0;
            this.rowIndexClickedColumn(idxfld);

            if (this.tableMeta._fields[this.selectedCol]) {
                let idx = this.foreign_sel_id
                    ? _.findIndex(this.tableMeta._fields[this.selectedCol]._links || [], {id: Number(this.foreign_sel_id)})
                    : 0;
                this.rowIndexClickedLink(idx);
            }

            eventBus.$on('event-update-field-link', this.eventUpdateLink);
            eventBus.$on('ref-conditions-popup-closed', this.redrawTbs);
        },
        beforeDestroy() {
            eventBus.$off('event-update-field-link', this.eventUpdateLink);
            eventBus.$off('ref-conditions-popup-closed', this.redrawTbs);
        }
    }
</script>

<style lang="scss" scoped>
    @import "TabSettingsPermissions";

    .spaced-table__fix {
        margin: 0 10px;
        width: calc(100% - 20px);
    }
    .params-wrapper {
        margin: 5px;
    }

    .link-data {
        height: 100%;
        padding: 5px 5px 7px 5px;

        .link-menu {
            button {
                background-color: #CCC;
                outline: 0;
            }
            .active {
                background-color: #FFF;
            }
        }

        .link-tab {
            height: calc(100% - 30px);
            position: relative;
            top: -3px;
            border: 1px solid #CCC;
            border-radius: 4px;
            background-color: #047;
        }
    }
    .btn-default {
        height: 36px;
    }
</style>