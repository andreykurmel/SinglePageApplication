<script>
    /**
     *  should be present:
     *
     *  this.settingsMeta: Object
     *  this.$root.tableMeta: Object
     *  this.table_id: Number
     *  */
    import {LinkHelper} from "../../classes/helpers/LinkHelper";
    import {RefCondHelper} from "../../classes/helpers/RefCondHelper";

    import {eventBus} from "../../app";
    import {SpecialFuncs} from "../../classes/SpecialFuncs";

    export default {
        data: function () {
            return {
                subLinkTab: 'options',
                draw_table: true,
                activeTab: 'outgo',
                activeLinkTab: 'list',
                selectedCol: -1,
                selectedLink: -1,
                availableLinks: [
                    'table_field_id',
                    'lnk_header',
                    'name',
                    'link_type',
                    'link_pos',
                    'icon',
                    '_link_reverse',
                ],
                addingRow: {
                    active: true,
                    position: 'bottom'
                },
            }
        },
        computed: {
            dli_metaTable() {
                if (this.linkTableMeta !== undefined) {
                    return this.linkTableMeta;
                } else if (this.globalMeta !== undefined) {
                    return this.globalMeta;
                } else {
                    return this.$root.tableMeta;
                }
            },
            availableLinkColumns() {
                let arr = [];
                if (this.linkRow) {
                    switch (this.linkRow.link_type) {
                        case 'Record':
                            if (this.linkRow.link_display == 'Table') {
                                arr = [];
                            } else {
                                arr = ['popup_can_table','popup_can_list','popup_can_board','popup_display','show_sum','floating_action'];
                            }
                            if (this.dli_metaTable.add_email) {
                                arr.push('email_addon_fields');
                            }
                            _.each(
                                ['pop_width_px','link_display','tooltip','table_ref_condition_id','listing_field_id',
                                    'link_preview_fields','avail_addons','inline_is_opened','listing_panel_status','inline_style','inline_width',
                                    'link_preview_show_flds','inline_in_vert_table','inline_hide_tab','inline_hide_boundary','inline_hide_padding',
                                    'max_height_in_vert_table','listing_rows_width','listing_rows_min_width','listing_header_wi',
                                    'pop_width_px_min','pop_width_px_max','pop_height','pop_height_min','pop_height_max'],
                                (key) => { arr.push(key); }
                            );
                            break;
                        case 'Web':
                            arr = this.linkRow.share_is_web
                                ? ['tooltip','share_is_web','share_record_link_id','address_field_id','multiple_web_label_fld_id','web_prefix','hide_empty_web']
                                : ['tooltip','share_is_web','address_field_id','multiple_web_label_fld_id','web_prefix','hide_empty_web'];
                            break;
                        case 'App':
                            if (this.linkRow.table_app_id == this.$root.settingsMeta.payment_app_id) {
                                arr = ['tooltip','table_app_id','payment_method_fld_id','payment_paypal_keys_id','payment_stripe_keys_id',
                                    'payment_amount_fld_id','payment_history_payee_fld_id','payment_history_amount_fld_id',
                                    'payment_history_date_fld_id','payment_description_fld_id','payment_customer_fld_id'];
                            } else if (this.linkRow.table_app_id == this.$root.settingsMeta.json_import_app_id) {
                                arr = ['tooltip','table_app_id','json_import_field_id'];
                            } else if (this.linkRow.table_app_id == this.$root.settingsMeta.json_export_app_id) {
                                arr = ['tooltip','table_app_id','json_export_field_id','json_auto_export','json_auto_remove_after_export','link_export_drilled_fields',
                                    'json_export_filename_table','json_export_filename_link','json_export_filename_fields','json_export_filename_year','json_export_filename_time'];
                            } else {
                                arr = ['tooltip','table_app_id'];
                            }
                            break;
                        case 'GMap':
                        case 'GEarth':
                            arr = ['tooltip','link_field_lat','link_field_lng','link_field_address','inline_is_opened',
                                'inline_in_vert_table','max_height_in_vert_table','inline_style','inline_width','inline_hide_tab',
                                'inline_hide_boundary','inline_hide_padding'];
                            break;
                        case 'History':
                            arr = ['tooltip','history_fld_id','inline_is_opened','inline_in_vert_table','max_height_in_vert_table',
                                'can_row_add','can_row_delete','inline_style','inline_width','inline_hide_tab','inline_hide_boundary','inline_hide_padding'];
                            break;
                        case 'Add-on (Report)':
                            arr = ['tooltip','linked_report_id'];
                            break;
                    }
                }
                return arr;
            },
            availableLinkPermisColumns() {
                let arr = [];
                if (this.linkRow) {
                    switch (this.linkRow.link_type) {
                        case 'Record':
                            arr = ['always_available', 'editability_rced_fields', 'lnk_dcr_permission_id','lnk_srv_permission_id','lnk_mrv_permission_id',
                                'can_row_add', 'can_row_delete', 'add_record_limit'];
                            break;
                        default:
                            arr = [];
                            break;
                    }
                }
                return arr;
            },
            headersChang() {
                let changer = null;
                if (this.linkRow && this.linkRow.link_type === 'Web' && this.linkRow.share_is_web) {
                    changer = {
                        'address_field_id': 'Field for the suffix of the link URL:',
                        'web_prefix': 'MRV prefix of the link URL:',
                    };
                }
                return changer;
            },
            linkRow() {
                let res = null;
                if (this.dli_metaTable._fields[this.selectedCol]) {
                    res = this.dli_metaTable._fields[this.selectedCol]._links[this.selectedLink];
                }
                return res;
            },
            allLinks() {
                let links = [];
                _.each(this.dli_metaTable._fields, (fld) => {
                    _.each(fld._links, (lnk) => {
                        if (!this.filter_id || this.filter_id == lnk.id) {
                            links.push(lnk);
                        }
                    });
                });
                return _.orderBy(links, 'row_order');
            }
        },
        watch: {
            table_id: function(val) {
                this.selectedCol = -1;
            },
            selectedCol(val) {
                this.subLinkTab = 'options';
            },
        },
        methods: {
            setSub(tab) {
                this.subLinkTab = '';
                this.$nextTick(() => {
                    this.subLinkTab = tab;
                });
            },
            getSubLinkColumns() {
                switch (this.subLinkTab) {
                    case 'options': return ['popup_can_table','popup_can_list','popup_can_board','popup_display'];
                    case 'table': return ['show_sum','table_def_align','table_fit_width','floating_action'];
                    case 'listing': return ['listing_field_id','listing_panel_status','listing_header_wi',
                        'listing_rows_width','listing_rows_min_width'];
                    case 'board': return [/* used board-setting-block component */];
                    case 'inline': return ['inline_in_vert_table','inline_is_opened','inline_width','inline_style','inline_hide_tab',
                        'inline_hide_boundary','inline_hide_padding','max_height_in_vert_table'];
                    case 'popup': return ['pop_width_px','pop_width_px_min','pop_width_px_max','pop_height','pop_height_min','pop_height_max'];
                }
            },
            linkOpts() {
                return _.map(this.allLinks, (lnk) => {
                    return { val:lnk.id, show:lnk.name };
                });
            },
            linkChange(opt) {
                let link = _.find(this.allLinks, {id: Number(opt.val)});
                this.rowIndexClickedLink(null, link);
            },
            //sys methods
            rowIndexClickedLink(index, link) {
                this.selectedCol = -1;
                this.selectedLink = -1;
                if (link) {
                    this.$nextTick(() => {
                        this.selectedCol = _.findIndex(this.dli_metaTable._fields, {id: Number(link.table_field_id)});
                        let fld = this.dli_metaTable._fields[this.selectedCol] || {};
                        this.selectedLink = _.findIndex(fld._links || [], {id: Number(link.id)});
                        this.activeLinkTab = 'details';

                        if (this.linkRow && this.linkRow._eri_tables && this.linkRow._eri_tables.length) {
                            this.selectedEri = 0;
                        }
                    });
                }
            },

            //Settings Column Links Functions
            addLink(tableRow) {
                if (this.notUniq(tableRow)) {
                    Swal('Info', 'This name was already used!');
                    return;
                }
                this.$root.sm_msg_type = 1;

                if ($.inArray(tableRow.link_type, ['GMap','GEarth']) > -1) {
                    tableRow.icon = tableRow.link_type === 'GMap' ? 'map' : 'earth';
                }
                if ($.inArray(tableRow.link_type, ['History']) > -1) {
                    tableRow.icon = tableRow.icon || 'History';
                }

                let fields = _.cloneDeep(tableRow);//copy object
                this.$root.deleteSystemFields(fields);

                axios.post('/ajax/settings/data/link', {
                    table_field_id: tableRow.table_field_id,
                    fields: fields,
                }).then(({ data }) => {
                    let fld = _.find(this.dli_metaTable._fields, {id: Number(tableRow.table_field_id)});
                    if (fld) {
                        fld._links.push(data);
                    }
                    RefCondHelper.setUses(this.dli_metaTable);
                }).catch(errors => {
                    Swal('Info', getErrors(errors));
                }).finally(() => {
                    this.$root.sm_msg_type = 0;
                });
            },
            eventCreateLink(table_id, tableRow) {
                if (this.dli_metaTable.id == table_id) {
                    this.addLink(tableRow);
                }
            },
            eventUpdateLink(table_id, tableRow) {
                if (this.dli_metaTable.id == table_id) {
                    this.updateLink(tableRow);
                }
            },
            notUniq(tableRow) {
                let thesame = false;
                _.each(this.dli_metaTable._fields, (fld) => {
                    let link = _.find(fld._links, (lnk) => {
                        return lnk.name === tableRow.name && lnk.id !== tableRow.id;
                    });
                    thesame = thesame || !!link;
                });
                return thesame;
            },
            updateBoardLink(prop_name, val) {
                this.linkRow[prop_name] = val;
                this.updateLink(this.linkRow);
            },
            updateLink(tableRow) {
                if (this.notUniq(tableRow)) {
                    Swal('Info', 'This name was already used!');
                    return;
                }
                this.fixChanged(tableRow);
                this.$root.sm_msg_type = 1;

                let link_id = tableRow.id;
                let fields = _.cloneDeep(tableRow);//copy object
                this.$root.deleteSystemFields(fields);
                RefCondHelper.setUses(this.dli_metaTable);

                axios.put('/ajax/settings/data/link', {
                    table_link_id: link_id,
                    fields: fields
                }).then(({ data }) => {
                    _.each(data, (val, key) => {
                        tableRow[key] = val;
                    });
                    if (tableRow['_changed_field'] == 'table_field_id') {
                        eventBus.$emit('reload-meta-tb__fields');
                    }
                    if (in_array(tableRow['_changed_field'], ['inline_is_opened','inline_width'])) {
                        LinkHelper.updateInlinedState(this.dli_metaTable);
                    }
                }).catch(errors => {
                    Swal('Info', getErrors(errors));
                }).finally(() => {
                    this.$root.sm_msg_type = 0;
                });
            },
            deleteLink(tableRow) {
                this.$root.sm_msg_type = 1;
                axios.delete('/ajax/settings/data/link', {
                    params: {
                        table_link_id: tableRow.id
                    }
                }).then(({ data }) => {
                    let fld = _.find(this.dli_metaTable._fields, {id: Number(tableRow.table_field_id)});
                    if (fld) {
                        let idx = _.findIndex(fld._links, {id: Number(tableRow.id)});
                        if (idx > -1) {
                            fld._links.splice(idx, 1);
                        }
                    }
                    this.selectedLink = -1;
                    RefCondHelper.setUses(this.dli_metaTable);
                }).catch(errors => {
                    Swal('Info', getErrors(errors));
                }).finally(() => {
                    this.$root.sm_msg_type = 0;
                });
            },
            rowsReordered() {
                this.$root.sm_msg_type = 2;
                axios.post('/ajax/table-data/get-headers', SpecialFuncs.tableMetaRequest(this.table_id))
                .then(({ data }) => {
                    this.dli_metaTable._fields = data._fields;
                    this.redrawTb();
                }).catch(errors => {
                    Swal('Info', getErrors(errors));
                }).finally(() => {
                    this.$root.sm_msg_type = 0;
                });
            },
            redrawTb() {
                this.draw_table = false;
                this.$nextTick(() => {
                    this.draw_table = true;
                });
            },
            fixChanged(tableRow) {
                let prop_name = tableRow._changed_field;
                if (this.$root.inArray(prop_name, ['listing_rows_width','listing_rows_min_width'])) {
                    tableRow[prop_name] = Number(tableRow[prop_name]);
                    if (tableRow[prop_name] < 1) {
                        tableRow[prop_name] = Math.max(tableRow[prop_name], 0.1);
                        tableRow[prop_name] = Math.min(tableRow[prop_name], 0.7);
                    } else {
                        tableRow[prop_name] = Math.max(tableRow[prop_name], 70);
                        tableRow[prop_name] = Math.min(tableRow[prop_name], 1000);
                    }
                }
                if (prop_name === 'inline_in_vert_table' && !Number(tableRow[prop_name])) {
                    tableRow.inline_is_opened = 0;
                }
                if (prop_name === 'inline_is_opened' && Number(tableRow[prop_name])) {
                    tableRow.inline_in_vert_table = 1;
                }
                if (this.$root.inArray(prop_name, ['popup_can_table','popup_can_list','popup_can_board','popup_display'])) {
                    if ((tableRow.popup_display === 'Table' && ! tableRow.popup_can_table)
                        || (tableRow.popup_display === 'Listing' && ! tableRow.popup_can_list)
                        || (tableRow.popup_display === 'Boards' && ! tableRow.popup_can_board)
                    ) {
                        tableRow.popup_display = this.getAvailDefault(tableRow);
                    }
                }
            },
            getAvailDefault(tableRow) {
                return tableRow.popup_can_table ? 'Table'
                    : (tableRow.popup_can_list ? 'Listing'
                        : (tableRow.popup_can_board ? 'Boards' : 'Table'));
            },
        },
    }
</script>