<script>
/**
 *  should be present:
 *
 *  this.tableMeta: Object
 *  this.link: Object
 *  this.tableRow: String
 *  */
export default {
        computed: {
            isPayment() {
                return this.link
                    && this.link.table_app_id == this.$root.settingsMeta.payment_app_id
                    && window.innerWidth > 600;
            },
            asPopup() {
                return this.tb_app && this.tb_app.open_as_popup;
            },
            tb_app() {
                let tb_app = null;
                if (this.link && this.link.link_type === 'App') {
                    tb_app = _.find(this.$root.settingsMeta.table_apps_data, {id: Number(this.link.table_app_id)});
                }
                return tb_app;
            },
            app_link() {
                let lnk = '#';
                if (this.tb_app && this.tb_app.name && this.link) {
                    let queryParams = [];
                    let host = this.$root.clear_url;
                    if (this.link.table_app_id == this.$root.settingsMeta.payment_app_id) {
                        queryParams.push('h=' + uuidv4());
                        queryParams.push('l=' + this.link.id);
                        queryParams.push('q=' + uuidv4());
                        queryParams.push('r=' + this.tableRow.id);
                    } else if (this.link.table_app_id == this.$root.settingsMeta.json_import_app_id) {
                        queryParams.push('table_id=' + this.tableMeta.id);
                        queryParams.push('file_field_id=' + this.link.json_import_field_id);
                        queryParams.push('row_id=' + this.tableRow.id);
                    } else if (this.link.table_app_id == this.$root.settingsMeta.json_export_app_id) {
                        queryParams.push('table_id=' + this.tableMeta.id);
                        queryParams.push('file_field_id=' + this.link.json_export_field_id);
                        queryParams.push('link_id=' + this.link.id);
                        queryParams.push('row_id=' + this.tableRow.id);
                    } else if (
                        this.link.table_app_id == this.$root.settingsMeta.eri_parser_app_id
                        || this.link.table_app_id == this.$root.settingsMeta.eri_writer_app_id
                        || this.link.table_app_id == this.$root.settingsMeta.da_loading_app_id
                        || this.link.table_app_id == this.$root.settingsMeta.mto_dal_app_id
                        || this.link.table_app_id == this.$root.settingsMeta.mto_geom_app_id
                        || this.link.table_app_id == this.$root.settingsMeta.ai_extractm_app_id
                        || this.link.table_app_id == this.$root.settingsMeta.smart_select_app_id
                    ) {
                        queryParams.push('table_id=' + this.tableMeta.id);
                        queryParams.push('link_id=' + this.link.id);
                        queryParams.push('row_id=' + this.tableRow.id);
                    } else {
                        host = host.replace('://', '://'+this.tb_app.subdomain+'.') //APPs subdomain
                        _.each(this.link._params, (queryObj) => {
                            if (queryObj.value === '{$table_id}') {
                                queryParams.push(queryObj.param.toLowerCase() + '=' + this.tableMeta.id);
                            }
                            else if (queryObj.value === '{$link_id}') {
                                queryParams.push(queryObj.param.toLowerCase() + '=' + this.link.id);
                            }
                            else if (queryObj.value === '{$link_name}') {
                                queryParams.push(queryObj.param.toLowerCase() + '=' + this.link.name);
                            }
                            else if (queryObj.value === '{$column_id}' && queryObj.column_id) {
                                queryParams.push(queryObj.param.toLowerCase() + '=' + queryObj.column_id);
                            }
                            else if (queryObj.column_id) {
                                let fld = _.find(this.tableMeta._fields, {id: Number(queryObj.column_id)});
                                queryParams.push(queryObj.param.toLowerCase() + '=' + this.tableRow[fld.field]);
                            }
                            else {
                                queryParams.push(queryObj.param.toLowerCase() + '=' + queryObj.value);
                            }
                        });
                    }
                    lnk = host
                        + '/apps'
                        + this.tb_app.app_path
                        + '/?' + queryParams.join('&');
                }
                return lnk;
            },
        },
        methods: {
            callOpenAppAsPopup() {
                this.$emit('open-app-as-popup', this.tb_app, this.app_link);
            },
            newTabPopup() {
                let width = window.innerWidth * 0.5;
                let height = window.innerHeight * 0.8;
                let left = window.innerWidth * 0.4;
                let top = window.innerHeight * 0.1;
                window.open(this.app_link, 'newwin', 'width='+width+', height='+height+', top='+top+', left='+left);
            },
        },
    }
</script>