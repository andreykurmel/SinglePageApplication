<template>
    <div class="container-fluid full-height" v-if="tableMeta && settingsMeta">
        <div class="row full-height permissions-tab">

            <!--LEFT SIDE-->
            <div class="col-xs-6 full-height" style="padding-right: 0;">
                <div class="top-text" :style="textSysStyleSmart">
                    <span>List</span>
                </div>
                <div class="full-frame bkp_top" style="height: calc(40% - 30px)">
                    <custom-table
                            :cell_component_name="'custom-cell-connection'"
                            :global-meta="tableMeta"
                            :table-meta="settingsMeta.table_backups"
                            :settings-meta="settingsMeta"
                            :all-rows="tableMeta._backups"
                            :rows-count="tableMeta._backups.length"
                            :selected-row="tbBkpIdx"
                            :cell-height="1"
                            :max-cell-rows="0"
                            :is-full-width="true"
                            :use_theme="true"
                            :user="user"
                            :behavior="'user_conn'"
                            :adding-row="addingRow"
                            :available-columns="ava_table_bkp_cols"
                            :forbidden-columns="$root.systemFields"
                            @row-index-clicked="bkpClick"
                            @added-row="addStorageRow"
                            @updated-row="updateStorageRow"
                            @delete-row="deleteStorageRow"
                    ></custom-table>
                </div>

                <div class="top-text" :style="textSysStyleSmart">
                    <span>Notifications - Email</span>
                </div>
                <div class="bkp_bot" style="height: calc(60% - 35px)">
                    <backup-add-settings
                            v-if="tbBackup"
                            :table-meta="tableMeta"
                            :tb-backup="tbBackup"
                            :style="textSysStyle"
                            @updated-row="updateStorageRow"
                    ></backup-add-settings>
                </div>
            </div>

            <!--RIGHT SIDE-->
            <div class="col-xs-6 full-height">
                <div class="top-text" :style="textSysStyleSmart">
                    <span>Settings {{ tbBackup ? ' - '+tbBackup.name : '' }}</span>
                </div>
                <div class="permissions-panel" style="height: calc(100% - 35px); overflow: auto;">
                    <vertical-table
                            v-if="tbBackup"
                            :td="'custom-cell-connection'"
                            :global-meta="tableMeta"
                            :table-meta="settingsMeta.table_backups"
                            :settings-meta="settingsMeta"
                            :table-row="tbBackup"
                            :cell-height="1"
                            :max-cell-rows="0"
                            :user="user"
                            :behavior="'user_conn'"
                            :disabled_sel="true"
                            :tooltip_pos="'bottom'"
                            :available-columns="ava_vert_bkp_cols"
                            :forbidden-columns="$root.systemFields"
                            @updated-cell="updateStorageRow"
                    ></vertical-table>
                </div>
            </div>

        </div>
    </div>
</template>

<script>
    import CustomTable from './../../../CustomTable/CustomTable';
    import BackupAddSettings from "./BackupAddSettings";
    import NoteBlock from "../../../CommonBlocks/NoteBlock";

    import CellStyleMixin from "../../../_Mixins/CellStyleMixin";

    export default {
        name: "TabStorageBackup",
        mixins: [
            CellStyleMixin,
        ],
        components: {
            NoteBlock,
            BackupAddSettings,
            CustomTable,
        },
        data: function () {
            return {
                tbBkpIdx: -1,
                tbBackup: null,
                addingRow: {
                    active: true,
                    position: 'bottom'
                },
                ava_table_bkp_cols: ['name','user_cloud_id','is_active'],
                ava_vert_bkp_cols: ['table_view_id','root_folder','overwrite','day','timezone','time','mysql','csv','attach','ddl_attach','notes'],
            }
        },
        props:{
            tableMeta: Object,
            settingsMeta: Object,
            user:  Object,
            cellHeight: Number,
            maxCellRows: Number,
        },
        methods: {
            bkpClick(idx) {
                this.tbBkpIdx = -1;
                this.tbBackup = null;
                this.$nextTick(() => {
                    this.tbBkpIdx = idx;
                    this.tbBackup = this.tableMeta._backups[idx];
                });
            },
            //Connections Functions
            addStorageRow(tableRow) {
                this.$root.sm_msg_type = 1;

                let fields = _.cloneDeep(tableRow);//copy object
                this.$root.deleteSystemFields(fields);

                axios.post('/ajax/table/backup', {
                    table_id: this.tableMeta.id,
                    fields: fields,
                    table_url: location.href,
                }).then(({ data }) => {
                    this.tableMeta._backups.push( data );
                }).catch(errors => {
                    Swal('Info', getErrors(errors));
                }).finally(() => {
                    this.$root.sm_msg_type = 0;
                });
            },
            updateStorageRow(tableRow) {
                this.$root.sm_msg_type = 1;

                let row_id = tableRow.id;
                let fields = _.cloneDeep(tableRow);//copy object
                this.$root.deleteSystemFields(fields);

                axios.put('/ajax/table/backup', {
                    table_backup_id: row_id,
                    fields: fields,
                    table_url: location.href,
                }).then(({ data }) => {
                    this.$root.assignObject(data, tableRow);
                    this.$forceUpdate();
                }).catch(errors => {
                    Swal('Info', getErrors(errors));
                }).finally(() => {
                    this.$root.sm_msg_type = 0;
                });
            },
            deleteStorageRow(tableRow) {
                this.$root.sm_msg_type = 1;
                let row_id = tableRow.id;
                axios.delete('/ajax/table/backup', {
                    params: {
                        table_backup_id: row_id,
                    }
                }).then(({ data }) => {
                    let idx = _.findIndex(this.tableMeta._backups, {id: tableRow.id});
                    if (idx > -1) {
                        this.tableMeta._backups.splice(idx, 1);
                    }
                }).catch(errors => {
                    Swal('Info', getErrors(errors));
                }).finally(() => {
                    this.$root.sm_msg_type = 0;
                });
            },
        },
        mounted() {
            this.bkpClick(0);
        },
    }
</script>

<style lang="scss" scoped>
    @import "./SettingsModule/TabSettingsPermissions";

    .section-text {
        padding: 5px 10px;
        font-size: 16px;
        font-weight: bold;
        background-color: #CCC;
    }
    .bkp_top, .bkp_bot {
        background-color: #FFF;
    }
    .bkp_bot {
        padding: 0 0 10px 10px;
    }
</style>