<template>
    <div class="storage-backup full-frame" v-if="tableMeta && settingsMeta">
        <div class="full-frame bkp_top" style="height: calc(40% - 18px)">
            <custom-table
                    :cell_component_name="'custom-cell-connection'"
                    :global-meta="settingsMeta.table_backups"
                    :table-meta="settingsMeta.table_backups"
                    :settings-meta="settingsMeta"
                    :all-rows="tableMeta._backups"
                    :rows-count="tableMeta._backups.length"
                    :cell-height="$root.cellHeight"
                    :max-cell-rows="$root.maxCellRows"
                    :is-full-width="true"
                    :use_theme="true"
                    :user="user"
                    :behavior="'user_conn'"
                    :adding-row="addingRow"
                    :available-columns="ava_bkp_cols"
                    :forbidden-columns="$root.systemFields"
                    @row-index-clicked="bkpClick"
                    @added-row="addStorageRow"
                    @updated-row="updateStorageRow"
                    @delete-row="deleteStorageRow"
            ></custom-table>
        </div>
        <div class="section-text">Notifications - Email:</div>
        <div class="bkp_bot" style="height: calc(60% - 18px)">
            <backup-add-settings
                    :table-meta="tableMeta"
                    :tb-backup="tbBackup"
            ></backup-add-settings>
        </div>
    </div>
</template>

<script>
    import CustomTable from './../../../CustomTable/CustomTable';
    import BackupAddSettings from "./BackupAddSettings";

    export default {
        name: "TabStorageBackup",
        components: {
            BackupAddSettings,
            CustomTable,
        },
        data: function () {
            return {
                tbBackup: null,
                addingRow: {
                    active: true,
                    position: 'bottom'
                },
                ava_bkp_cols: ['name','user_cloud_id','day','timezone','time','mysql','csv','attach',],
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
                this.tbBackup = this.tableMeta._backups[idx];
            },
            //Connections Functions
            addStorageRow(tableRow) {
                this.$root.sm_msg_type = 1;

                let fields = _.cloneDeep(tableRow);//copy object
                this.$root.deleteSystemFields(fields);

                axios.post('/ajax/table/backup', {
                    table_id: this.tableMeta.id,
                    fields: fields
                }).then(({ data }) => {
                    this.tableMeta._backups.push( data );
                }).catch(errors => {
                    Swal('', getErrors(errors));
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
                    fields: fields
                }).then(({ data }) => {
                    this.$forceUpdate();
                }).catch(errors => {
                    Swal('', getErrors(errors));
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
                    Swal('', getErrors(errors));
                }).finally(() => {
                    this.$root.sm_msg_type = 0;
                });
            },
        },
    }
</script>

<style lang="scss" scoped="">
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
        padding: 15px 0 15px 15px;
    }
</style>