
<script>
    import {eventBus} from "../../../app";

    import {mapState, mapActions, mapMutations} from 'vuex';

    import {MetaPermissions} from '../../../classes/MetaPermissions';
    import {AddingRow} from '../../../classes/AddingRow';
    import {TabldaTableFuncHandler} from '../../../classes/TabldaTableFuncHandler';
    import {MetaTabldaRows} from '../../../classes/MetaTabldaRows';

    export default {
        data() {
            return {
                show_table: true,
                is_ready: false,
                pre_delete_master_popup: false,

                del_additional_tbls: [],

                addingRows: {},
                handlers: {},
                permis: {},
            }
        },
        computed: {
            ...mapState({
                vuex_settings: state => state.stim_settings,
                vuex_links: state => state.stim_links_container,
                vuex_fm: state => state.found_models,
            }),
            metaTable() {
                return this.found_model.meta;
            },
            allRows() {
                return this.found_model.rows;
            },
            modelUser() {
                return this.found_model._id && this.$root.user.id;
            },
            canUpdate() {
                return !!(this.$root.user.id && !this.metaTable.can_edit);
            },
        },
        props: {
        },
        watch: {
        },
        methods: {
            ...mapMutations([
                'REDRAW_3D',
            ]),
            ...mapActions([
                'SET_SELECTED_MODEL_ROW',
            ]),

            //Change Master
            insertedMaster(data) {
                if (data.rows && data.rows.length) {
                    this.found_model.setSelectedRow(data.rows[0]);
                    this.SET_SELECTED_MODEL_ROW(data.rows[0]);
                }
            },
            updatedMaster(data) {
                if (data.rows && data.rows.length) {
                    this.found_model.setSelectedRow(data.rows[0]);
                    this.SET_SELECTED_MODEL_ROW(data.rows[0]);
                }
            },
            preDeleteMaster() {
                if (this.found_model._id) {
                    this.pre_delete_master_popup = true;
                }
            },
            deleteMaster() {
                this.deleteModelMixin(this.found_model, this.tab_object.master_table, this.del_additional_tbls).then(({data}) => {
                    this.found_model.setSelectedRow(null);
                    this.SET_SELECTED_MODEL_ROW(null);
                    eventBus.$emit('recheck-backend', this.found_model.meta.table_id);
                });
                this.pre_delete_master_popup = false;
            },
            copyMaster() {
                if (this.found_model._id) {
                    this.copyModelMixin(this.found_model, this.tab_object.master_table, this.tab_object.copy_child_tbls).then(({data}) => {
                        this.found_model.setSelectedRow(data.row);
                        this.SET_SELECTED_MODEL_ROW(data.row);
                    });
                }
            },
            saveMaster() {
                if (!this.$root.user.id) {
                    Swal('Please login to save.');
                    return;
                }

                this.handlers[this.tbkey(this.tab_object)].insert_clicked++;
            },

            //Change Inherits
            insertinlineClicked(tb) {
                if (this.addingRows[this.tbkey(tb)].active) {
                    this.handlers[this.tbkey(tb)].insert_clicked++;
                } else {
                    this.handlers[this.tbkey(tb)].popup_clicked++;
                }
            },
            copyRowsClicked(tb) {
                this.handlers[this.tbkey(tb)].copy_rows_clicked++;
            },
            copyFromModelClicked(tb) {
                this.handlers[this.tbkey(tb)].copy_from_model_clicked++;
            },
            showCondPopupClicked(tb) {
                eventBus.$emit('show-cond-format-popup', tb.db_name);
            },
            showViewsPopupClicked(tb) {
                eventBus.$emit('show-table-views-popup', tb.db_name);
            },
            showParsePaste(tb) {
                this.handlers[this.tbkey(tb)].parse_paste_clicked++;
            },
            showRTS(tb) {
                this.handlers[this.tbkey(tb)].rts_popup_clicked++;
            },
            parseSections(tb) {
                this.handlers[this.tbkey(tb)].parse_sections_clicked++;
            },
            fillEqptAttachments(tb) {
                this.handlers[this.tbkey(tb)].fill_attachments_clicked++;
            },
            doRLCalculation(tb) {
                this.handlers[this.tbkey(tb)].rl_calculation_clicked++;
            },
            afterInsertRow(data) {
                //this.REDRAW_3D('soft'); //The same functions work in 'Three3dWid::intervalTickHandler'
            },
            afterUpdateRow(data) {
                //this.REDRAW_3D('soft');
            },
            afterDeleteRow(data) {
                //this.REDRAW_3D('soft');
            },

            //Permissions
            setMetaPermis(permissions, tb) {
                this.permis[this.tbkey(tb)] = permissions;
            },

            //redraw
            redrawTb() {
                this.show_table = false;
                this.$nextTick(() => {
                    this.show_table = true;
                });
            },

            //sys
            is_visible(tb) {
                let available_tables = this.vuex_settings._app_cur_view && this.vuex_settings._app_cur_view.visible_tab_ids
                    ? JSON.parse(this.vuex_settings._app_cur_view.visible_tab_ids)
                    : null;

                return !available_tables || available_tables.indexOf(tb.id) > -1;
            },
            no_hidden(tb) {
                return !tb.options || tb.options.indexOf('is_hidden:true') === -1;
            },
            tbkey(tb) {
                if (tb.id && tb.table) {
                    return tb.id + '_' + tb.table;
                } else {
                    return tb.master_id + '_' + tb.master_table;
                }
            },
            prepareTab() {
                this.del_additional_tbls = _.map(this.tab_object.del_child_tbls, (el) => {
                    return {
                        to_del: false,
                        table: el,
                        stim: _.find(this.vuex_settings.plain_settings, (stm) => {
                            return String(stm.table).toLowerCase() === String(el).toLowerCase()
                                && String(stm.type_tablda).toLowerCase() === 'table';
                        }),
                    };
                });

                //Adding Rows
                let addingRows = {};
                _.each(this.tab_object.tables, (tb) => {
                    addingRows[this.tbkey(tb)] = new AddingRow();
                });
                this.addingRows = addingRows;

                //Click Handlers
                let handlers = {};
                _.each(this.tab_object.tables, (tb) => {
                    handlers[this.tbkey(tb)] = new TabldaTableFuncHandler();
                });
                this.handlers = handlers;

                //Permissions
                let permis = {};
                _.each(this.tab_object.tables, (tb) => {
                    permis[this.tbkey(tb)] = new MetaPermissions();
                });
                this.permis = permis;

                //Run Component Draw
                this.is_ready = true;
            },

            emitSearchWordChanged(metaTabldaTable) {
                eventBus.$emit('stim-search-word-changed', metaTabldaTable.table_id);
            },
        },
    }
</script>