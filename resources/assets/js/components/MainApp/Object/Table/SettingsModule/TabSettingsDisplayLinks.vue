<template>
    <div class="container-fluid full-height">
        <div class="row full-height permissions-tab">
            <!--LEFT SIDE-->
            <div class="col-xs-12 full-height" :style="{width: '25%', paddingRight: 0}">

                <div class="top-text top-text--height">
                    <span>Select a Column</span>
                </div>
                <div class="permissions-panel no-padding">
                    <div class="full-frame">
                        <custom-table
                                :cell_component_name="'custom-cell-settings-display'"
                                :global-meta="tableMeta"
                                :table-meta="settingsMeta['table_fields']"
                                :settings-meta="settingsMeta"
                                :all-rows="ActiveLinkFields"
                                :adding-row="addingRowFields"
                                :rows-count="ActiveLinkFields.length"
                                :cell-height="$root.cellHeight"
                                :max-cell-rows="$root.maxCellRows"
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

            </div>

            <!--RIGHT SIDE-->

            <div class="col-xs-12 full-height" :style="{width: '75%'}">

                <div class="full-height">
                    <div class="top-text top-text--height">
                        <span v-if="selectedCol < 0">You should select column</span>
                        <span v-else="">Link(s) for <span>{{ $root.uniqName(tableMeta._fields[selectedCol].name) }}</span></span>

                        <info-sign-link
                                class="right-elem"
                                :app_sett_key="'help_link_settings_links'"
                                :hgt="26"
                        ></info-sign-link>
                    </div>
                    <div class="permissions-panel no-padding" :style="{height: 'calc(40% - 35px)'}">
                        <div class="full-frame">
                            <custom-table
                                    :cell_component_name="'custom-cell-display-links'"
                                    :global-meta="tableMeta"
                                    :table-meta="settingsMeta['table_field_links']"
                                    :settings-meta="settingsMeta"
                                    :all-rows="selectedCol > -1 ? tableMeta._fields[selectedCol]._links : []"
                                    :rows-count="selectedCol > -1 ? tableMeta._fields[selectedCol]._links.length : 0"
                                    :cell-height="$root.cellHeight"
                                    :max-cell-rows="$root.maxCellRows"
                                    :is-full-width="true"
                                    :adding-row="addingRow"
                                    :user="user"
                                    :behavior="'settings_display_links'"
                                    :available-columns="availableLinks"
                                    :selected-row="selectedLink"
                                    :fixed_ddl_pos="true"
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

                    <div class="top-text top-text--height">
                        <span v-if="selectedLink < 0 || selectedCol < 0">You should select link</span>
                        <span v-else="">Details for Link #{{ selectedLink+1 }} of <span>{{ $root.uniqName(tableMeta._fields[selectedCol].name) }}</span></span>
                    </div>
                    <div class="permissions-panel no-padding" :style="{height: 'calc(60% - 30px)'}">
                        <div class="full-frame" v-if="selectedLink > -1 && selectedCol > -1">

                            <vertical-table
                                    class="spaced-table__fix"
                                    :td="'custom-cell-display-links'"
                                    :global-meta="tableMeta"
                                    :table-meta="settingsMeta['table_field_links']"
                                    :settings-meta="settingsMeta"
                                    :table-row="linkRow"
                                    :user="user"
                                    :cell-height="$root.cellHeight"
                                    :max-cell-rows="$root.maxCellRows"
                                    :available-columns="availableLinkColumns"
                                    :widths="{name: '35%', col: '65%', history: 0, no_margins: true}"
                                    @show-add-ref-cond="showAddRefCond"
                                    @updated-cell="updateLink"
                            ></vertical-table>

                            <template v-if="linkRow && linkRow.link_type === 'App' && linkRow.table_app_id != $root.settingsMeta.payment_app_id">
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
                                            :cell-height="$root.cellHeight"
                                            :max-cell-rows="$root.maxCellRows"
                                            :is-full-width="true"
                                            :fixed_ddl_pos="true"
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
</template>

<script>
    import DisplayLinksMixin from '../../../../_Mixins/DisplayLinksMixin';

    import CustomTable from '../../../../CustomTable/CustomTable';
    import VerticalTable from '../../../../CustomTable/VerticalTable';
    import InfoSignLink from "../../../../CustomTable/Specials/InfoSignLink";

    import {eventBus} from '../../../../../app';

    export default {
        name: "TabSettingsDisplayLinks",
        mixins: [
            DisplayLinksMixin
        ],
        components: {
            InfoSignLink,
            CustomTable,
            VerticalTable
        },
        data: function () {
            return {
                selectedForLinksCol: -1,
                selectedCol: -1,
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
            }
        },
        props:{
            tableMeta: Object,
            settingsMeta: Object,
            cellHeight: Number,
            maxCellRows: Number,
            table_id: Number|null,
            user:  Object,
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
                this.selectedCol = _.findIndex(this.tableMeta._fields, {id: Number(fld ? fld.id : -1)});
                this.selectedLink = -1;
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
        },
        mounted() {
            eventBus.$on('ref-conditions-popup-closed', this.redrawTbs);
        },
        beforeDestroy() {
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
</style>