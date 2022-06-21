<template>
    <!--Add/Edit Table form-->
    <div v-if="tableModal.active" class="modal-wrapper">
        <div class="modal full-height">
            <div class="modal-dialog modal--390">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title">{{ tableModal.type === 'new' ? 'New Table' : 'Edit Table' }}</h4>
                    </div>
                    <div class="modal-body">
                        <table-settings-module
                                :table-meta="tableModal.tb_meta"
                                :tb_meta="tableModal.tb_meta"
                                :tb_theme="tableModal.tb_theme"
                                :tb_views="tableModal.tb_views"
                                :tb_cur_settings="tableModal.tb_cur_settings"
                                :type="tablePopup.type"
                                :max_set_len="365"
                        ></table-settings-module>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-success" @click="tableModal.type === 'new' ? addTable() : editTable()">OK</button>
                        <button type="button" class="btn btn-default" @click="tableModal.active = false;$emit('close');">Cancel</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import TableSettingsModule from "../../CommonBlocks/TableSettingsModule";

    export default {
        name: 'LeftMenuTreeTablePopup',
        components: {
            TableSettingsModule
        },
        data() {
            return {
                tableModal: this.getEmptyTableModal(),
                re_init: false,
            }
        },
        props: {
            tablePopup: Object,
        },
        methods: {
            userHasAddon(code) {
                let idx = _.findIndex(this.$root.user._subscription._addons, {code: code});
                return this.$root.user._is_admin || idx > -1;
            },
            getEmptyTableModal() {
                return {
                    active: false,
                    type: 'new',
                    old_name: null,
                    parent_id: null,
                    $node: null,

                    tb_meta: {
                        name: null,
                        rows_per_page: 50,
                        enabled_activities: 0,
                        autoload_new_data: null,
                        pub_hidden: null,
                        is_public: null,
                        add_map: null,
                        add_bi: null,
                        add_request: null,
                        add_alert: null,
                        add_kanban: null,
                        add_email: null,
                        add_gantt: null,
                        add_calendar: null,
                        board_view_height: null,
                        board_image_width: null,
                        max_rows_in_link_popup: null,
                        search_results_len: null,
                        max_filter_elements: null,
                        google_api_key: null,
                        api_key_mode: 'table',
                        account_api_key_id: null,
                        address_fld__source_id: null,
                        address_fld__street_address: null,
                        address_fld__street: null,
                        address_fld__city: null,
                        address_fld__state: null,
                        address_fld__zipcode: null,
                        address_fld__countyarea: null,
                        address_fld__country: null,
                        address_fld__lat: null,
                        address_fld__long: null,
                        _is_owner: false,
                    },
                    tb_theme: {
                        appsys_tables_font_family: null,
                        appsys_tables_font_size: null,
                        appsys_tables_font_color: null,
                        appsys_font_family: null,
                        appsys_font_size: null,
                        appsys_font_color: null,
                        app_font_family: null,
                        app_font_size: null,
                        app_font_color: null,
                        navbar_bg_color: null,
                        table_hdr_bg_color: null,
                        button_bg_color: null,
                        ribbon_bg_color: null,
                        main_bg_color: null,
                    },
                    tb_views: [],
                    tb_cur_settings: {
                        initial_view_id: -1,
                        user_fld_show_image: 1,
                        user_fld_show_first: 1,
                        user_fld_show_last: 1,
                        user_fld_show_email: 1,
                        user_fld_show_username: 0,
                        history_user_show_image: 1,
                        history_user_show_first: 1,
                        history_user_show_last: 1,
                        history_user_show_email: 1,
                        history_user_show_username: 0,
                        vote_user_show_image: 1,
                        vote_user_show_first: 1,
                        vote_user_show_last: 1,
                        vote_user_show_email: 1,
                        vote_user_show_username: 0,
                    },
                };
            },

            //Menu functions for 'tables' ------------------------------------------------------------------------------
            showTablePopup(popup_type, $node) {
                let type, object, parent_id;
                this.tableModal = this.getEmptyTableModal();
                this.tableModal.type = popup_type;
                if ($node) {
                    type = $node.li_attr['data-type'];
                    object = $node.li_attr['data-object'];//can be instance 'Folder' or 'Table' !!!
                    parent_id = $node.li_attr['data-parent_id'];//can be instance 'Folder' or 'Table' !!!

                    this.tableModal.$node = $node;
                    this.tableModal.parent_id = (type === 'table' ? parent_id : object.id);
                    if (type !== 'folder') {
                        $.LoadingOverlay('show');
                        axios.get('/ajax/table/views-and-settings', {
                            params: {table_id: object.id}
                        }).then(({data}) => {
                            this.tableModal.tb_meta = data.meta;
                            this.tableModal.tb_theme = data.theme;
                            this.tableModal.tb_views = data.views;
                            this.tableModal.tb_cur_settings = data.settings;

                            this.tableModal.parent_id = parent_id;
                            this.tableModal.active = true;
                        }).catch(errors => {
                            Swal('', getErrors(errors));
                        }).finally(() => {
                            $.LoadingOverlay('hide');
                        });
                    } else {
                        this.tableModal.active = true;
                    }
                } else {
                    this.tableModal.active = true;
                }
            },
            addTable() {
                if (this.tableModal.tb_meta.name) {
                    let $node = this.tableModal.$node;
                    this.tableModal.tb_meta.name = this.tableModal.tb_meta.name.replace(/[^\w\d\.-_ ]/gi, '');
                    this.tableModal.active = false;
                    $.LoadingOverlay('show');
                    
                    let data = {
                        folder_id: this.tableModal.parent_id,
                        path: ($node ? $node.a_attr['href'] : ''),
                    };
                    Object.assign(data, this.tableModal.tb_meta);
                    data._tb_theme = this.tableModal.tb_theme;
                    data._cur_settings = this.tableModal.tb_cur_settings;
                    
                    axios.post('/ajax/import/create-table', data).then(({data}) => {
                        this.$emit('add-table', $node, data);
                    }).catch(errors => {
                        Swal('', getErrors(errors));
                    }).finally(() => {
                        $.LoadingOverlay('hide');
                    });
                }
            },
            editTable() {
                if (this.tableModal.tb_meta.name) {
                    let $node = this.tableModal.$node;
                    let object = $node.li_attr['data-object'];//can be instance 'Folder' or 'Table' !!!
                    let parent_id = $node.li_attr['data-parent_id'];//can be instance 'Folder' or 'Table' !!!
                    this.tableModal.tb_meta.name = this.tableModal.tb_meta.name.replace(/[^\w\d\.-_ ]/gi, '');
                    this.tableModal.active = false;
                    $.LoadingOverlay('show');

                    let data = {
                        table_id: this.tableModal.tb_meta.id,
                    };
                    Object.assign(data, this.tableModal.tb_meta);
                    Object.assign(data, this.tableModal.tb_theme);
                    Object.assign(data, this.tableModal.tb_cur_settings);
                    
                    axios.put('/ajax/table', data).then(({ data }) => {
                        let old_name = object.name;
                        let new_name = this.tableModal.tb_meta.name;
                        //modified current table
                        if (object.id == this.$root.tableMeta.id) {
                            Object.assign(this.$root.tableMeta, this.tableModal.tb_meta);
                            //theme colors
                            Object.assign(this.$root.tableMeta._theme, this.tableModal.tb_theme);
                            //initial view
                            Object.assign(this.$root.tableMeta._cur_settings, this.tableModal.tb_cur_settings);
                        }
                        //save modified datas
                        object.name = new_name;
                        object.rows_per_page = this.tableModal.tb_meta.rows_per_page;
                        $node.li_attr['data-object'] = object;

                        this.$emit('edit-table', $node, object, old_name, new_name);
                    }).catch(errors => {
                        Swal('', getErrors(errors));
                    }).finally(() => {
                        $.LoadingOverlay('hide');
                    });
                }
            },
        },
        mounted() {
            if (this.tablePopup) {
                this.showTablePopup(this.tablePopup.type, this.tablePopup.$node);
            }
        },
        beforeDestroy() {
        }
    }
</script>

<style lang="scss" scoped>
    .modal--390 {
        width: 400px;
        margin: auto;
        top: 50%;
        transform: translateY(-50%);
    }
</style>