<script>
    /**
     *  should be present:
     *
     *  this.linkTableMeta || this.globalMeta || this.$root.tableMeta: Object
     *  this.settingsMeta: Object
     *  */
    export default {
        data: function () {
            return {
                system_support_tables: [
                    'unit_conversion',
                    'user_connections',
                    'user_clouds',
                    'units',
                    'correspondence_apps',
                    'correspondence_tables',
                    'correspondence_fields',
                    'correspondence_stim_3d',
                ],
            }
        },
        computed: {
            cve_metaTable() {
                if (this.linkTableMeta !== undefined) {
                    return this.linkTableMeta;
                } else
                if (this.globalMeta !== undefined) {
                    return this.globalMeta;
                } else
                if (this.tableMeta !== undefined) {
                    return this.tableMeta;
                } else {
                    return this.$root.tableMeta;
                }
            },
            canAdd() {
                if (!this.cve_metaTable || this.$root.user._app_cur_view) {
                    return false;
                }

                return (
                        this.cve_metaTable._is_owner
                        ||
                        (this.cve_metaTable._current_right && this.cve_metaTable._current_right.can_add)
                    )
                    /*&&
                    (this.cve_metaTable.is_system != 1 || this.inArray(this.cve_metaTable.db_name, this.system_support_tables))*/
                    &&
                    (
                        !this.cve_metaTable._global_rows_count
                        ||
                        this.$root.checkAvailable(this.$root.user, 'row_table', this.cve_metaTable._global_rows_count)
                    );
            },
            canSomeEdit() {
                if (!this.cve_metaTable || this.$root.user._app_cur_view) {
                    return false;
                }

                let edit = this.cve_metaTable._is_owner;
                _.each(this.cve_metaTable._fields, (tableHeader) => {
                    edit = edit
                        || (this.cve_metaTable._current_right && this.inArray(tableHeader.field, this.cve_metaTable._current_right.edit_fields));
                });
                return edit
                    /*&&
                    !(this.cve_metaTable.is_system && !this.inArray(this.cve_metaTable.db_name, this.settingsMeta.system_tables_for_all))*/
                    &&
                    this.cve_metaTable.db_name !== 'sum_usages';
            },
            canDelete() {
                if (!this.cve_metaTable || this.$root.user._app_cur_view) {
                    return false;
                }

                return (
                        this.cve_metaTable._is_owner
                        ||
                        (this.cve_metaTable._current_right && this.cve_metaTable._current_right.delete_row_groups.length)
                    );
                    /*&&
                    (!this.cve_metaTable.is_system || this.inArray(this.cve_metaTable.db_name, this.system_support_tables));*/
            },
            hasAttachments() {
                return _.find(this.cve_metaTable._fields, (hdr) => {
                    return hdr.f_type === 'Attachment' && this.canViewHdr(hdr);
                });
            },
        },
        methods: {
            //sys methods
            inArray(item, array) {
                return $.inArray(item, array) > -1;
            },
            _all_present(tableRow) {
                if (!this.cve_metaTable) {
                    return false;
                }
                if (!tableRow._applied_row_groups) {
                    tableRow._applied_row_groups = [];
                }
                return true;
            },
            canEditHdr(tableHeader) {
                if (!this.cve_metaTable) {
                    return false;
                }

                return ( //can edit only owner
                        this.cve_metaTable._is_owner
                        || // OR user with available rights for edit Column
                        (this.cve_metaTable._current_right && this.inArray(tableHeader.field, this.cve_metaTable._current_right.edit_fields))
                    )
                    &&
                    !this.inArray(tableHeader.field, this.$root.systemFields);
            },
            canDeleteRow(tableRow) {
                if (!this._all_present(tableRow)) { return false; }
                // owner OR user with available rights for delete Row
                let del_groups = (this.cve_metaTable._current_right ? this.cve_metaTable._current_right.delete_row_groups || [] : []);
                let has_del_groups = tableRow._applied_row_groups.filter((gr_id) => {
                    return del_groups.includes(gr_id);
                });
                return this.canDelete && (this.cve_metaTable._is_owner || has_del_groups.length);
            },
            canEditRow(tableRow) {
                if (!this._all_present(tableRow)) { return false; }
                //can edit only owner OR RowGroups are not set
                let rg_present = this.cve_metaTable._current_right
                    && this.cve_metaTable._current_right.view_row_groups
                    && this.cve_metaTable._current_right.view_row_groups.length;
                if (this.cve_metaTable._is_owner || !rg_present) {
                    return true;
                }
                // OR user with available rights for edit Row
                let edit_groups = (this.cve_metaTable._current_right ? this.cve_metaTable._current_right.edit_row_groups || [] : []);
                let has_edit_groups = tableRow._applied_row_groups.filter((gr_id) => {
                    return edit_groups.includes(gr_id);
                });
                return !!has_edit_groups.length;
            },
            canEditCell(tableHeader, tableRow) {
                return this.canEditHdr(tableHeader)
                    && this.canEditRow(tableRow);
            },
            canViewHdr(tableHeader) {
                if (!this.cve_metaTable) {
                    return false;
                }

                return (
                        this.cve_metaTable._is_owner
                        ||
                        (this.cve_metaTable._current_right && this.inArray(tableHeader.field, this.cve_metaTable._current_right.view_fields))
                    )
                    && (!this.forbiddenColumns || !this.inArray(tableHeader.field, this.forbiddenColumns))
                    && (!this.availableColumns || this.inArray(tableHeader.field, this.availableColumns));
            },
        },
    }
</script>