<template>
    <vertical-table
            :td="$root.tdCellComponent(tableMeta.is_system)"
            :global-meta="tableMeta"
            :table-meta="tableMeta"
            :settings-meta="$root.settingsMeta"
            :table-row="tableRow"
            :user="user"
            :with_edit="with_edit"
            :is_def_fields="true"
            :cell-height="cellHeight"
            :max-cell-rows="maxCellRows"
            :forbidden-columns="$root.systemFields"
            :available-columns="availDefaults()"
            :extra-pivot-fields="extraPivotFields"
            :widths="{name: '35%', col: '65%', history: 0}"
            @updated-cell="changeDefFields"
    ></vertical-table>
</template>

<script>
    export default {
        name: "DefaultFieldsTable",
        components: {
        },
        data: function () {
            return {
                headerArr: [],
                defaultFieldsObj: {
                    id: 0
                },
                info_row: {},
            };
        },
        props:{
            tableDcr: Object,
            tablePermissionId: Number,
            UserGroupId: Number,
            tableMeta: Object,
            defaultFields: Array,
            user: Object,
            cellHeight: Number,
            maxCellRows: Number,
            with_edit: Boolean,
            extraPivotFields: Array,
        },
        computed: {
            tableRow() {
                return _.merge(this.info_row, this.defaultFieldsObj);
            },
        },
        methods: {
            availDefaults() {
                let fields = undefined;
                if (this.tableDcr && this.tableDcr.id) {
                    let colgr = _.map(
                        _.filter(this.tableDcr._data_request_columns, {view: 1}),
                        'table_column_group_id'
                    );

                    let metaColGroups = this.tableMeta._column_groups && this.tableMeta._column_groups.length > 0
                        ? this.tableMeta._column_groups
                        : this.tableMeta._gen_col_groups;

                    fields = [];
                    _.each(metaColGroups, (colGroup) => {
                        if (this.$root.inArray(colGroup.id, colgr)) {
                            fields = fields.concat( _.map(colGroup._fields, 'field') );
                        }
                    })
                }
                return fields;
            },
            inArray(item, array) {
                return $.inArray(item, array) > -1;
            },
            changeDefFields(row, hdr) {
                this.$root.sm_msg_type = 1;
                this.defaultFieldsObj[hdr.field] = row[hdr.field];
                let path = this.tableDcr.id ? 'table-data-request' : 'table-permission';
                axios.put('/ajax/'+path+'/default-field', {
                    table_data_request_id: this.tableDcr.id,
                    table_permission_id: this.tablePermissionId,
                    user_group_id: this.UserGroupId,
                    table_field_id: hdr.id,
                    default_val: this.defaultFieldsObj[hdr.field] || '',
                }).then(({ data }) => {
                    let idx = _.findIndex(this.defaultFields, {'table_field_id': hdr.id});
                    if (idx > -1) {
                        this.defaultFields[idx].default = this.defaultFieldsObj[hdr.field];
                    } else {
                        this.defaultFields.push(data);
                    }
                    this.getInfoRow();
                }).catch(errors => {
                    Swal('Info', getErrors(errors));
                }).finally(() => {
                    this.$root.sm_msg_type = 0;
                });
            },
            getInfoRow() {
                axios.post('/ajax/table-data/info-row', {
                    table_id: this.tableMeta.id,
                    table_row: this.tableRow,
                }).then(({ data }) => {
                    this.info_row = data.row || {};
                });
            }
        },
        mounted() {
            _.each(this.tableMeta._fields, (fld) => {
                let idx = _.findIndex(this.defaultFields, {'table_field_id': fld.id});
                if (idx > -1) {
                    this.$set(this.defaultFieldsObj, fld.field, this.defaultFields[idx].default);
                } else {
                    this.$set(this.defaultFieldsObj, fld.field, null);
                }
            });
            this.getInfoRow();
        }
    }
</script>