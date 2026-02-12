<template>
    <div>
        <custom-edit-pop-up
                v-if="metaTable.is_loaded && editPopUpRow"
                :global-meta="metaTable.params"
                :table-meta="metaTable.params"
                :table-row="editPopUpRow"
                :settings-meta="$root.settingsMeta"
                :role="'update'"
                :input_component_name="'custom-cell-table-data'"
                :behavior="'list_view'"
                :user="$root.user"
                :cell-height="$root.cellHeight"
                :max-cell-rows="$root.maxCellRows"
                :available-columns="avail_cols_for_app"
                :shift-object="shiftObject"
                :no_clicks="no_clicks"
                @popup-insert="insertRow"
                @popup-update="updateRow"
                @popup-copy="copyRow"
                @popup-delete="deleteRow"
                @popup-close="closePopUp"
                @another-row="anotherRow"
                @show-src-record="showSrcRecord"
        ></custom-edit-pop-up>

        <!--Link Popups from ListView and MapView.-->
        <template v-for="(linkObj, idx) in linkPopups">
            <header-history-pop-up
                v-if="linkObj.key === 'show' && linkObj.link.link_type === 'History'"
                :idx="linkObj.index"
                :table-meta="tableMeta"
                :table-row="linkObj.row"
                :history-header="linkObj.header"
                :link="linkObj.link"
                :popup-key="idx"
                :is-visible="true"
                @popup-close="closeLinkPopup"
            ></header-history-pop-up>
            <link-pop-up
                v-else-if="linkObj.key === 'show'"
                :source-meta="metaTable"
                :idx="linkObj.index"
                :link="linkObj.link"
                :meta-header="linkObj.header"
                :meta-row="linkObj.row"
                :popup-key="idx"
                :shift-object="shiftObject"
                :view_authorizer="{
                    mrv_marker: $root.is_mrv_page,
                    srv_marker: $root.is_srv_page,
                    dcr_marker: $root.is_dcr_page,
                }"
                @show-src-record="showSrcRecord"
                @link-popup-close="closeLinkPopup"
            ></link-pop-up>
        </template>
    </div>
</template>

<script>
    import {MetaTabldaTable} from '../../../classes/MetaTabldaTable';
    import {MetaTabldaRows} from '../../../classes/MetaTabldaRows';
    import {StimLinkParams} from '../../../classes/StimLinkParams';
    import {ThreeHelper} from "../../../classes/helpers/ThreeHelper";

    import {mapMutations} from "vuex";

    import CustomEditPopUp from "../../../components/CustomPopup/CustomEditPopUp";
    import HeaderHistoryPopUp from "../../../components/CustomPopup/HeaderHistoryPopUp";

    export default {
        name: 'TabldaDirectPopup',
        mixins: [
        ],
        components: {
            HeaderHistoryPopUp,
            CustomEditPopUp,
        },
        data() {
            return {
                no_clicks: false,
                avail_cols_for_app: [],
                allRows: null,
                editPopUpRow: null,
                cannot_close: false,
                linkPopups: [],
                startHash: '',
                ticker: null,
            }
        },
        computed: {
        },
        props: {
            metaTable: MetaTabldaTable,
            stim_link_params: StimLinkParams,
            rowId: Number,
            shiftObject: Object,
        },
        watch: {
            rowId(val) {
                this.loadData();
            },
        },
        methods: {
            ...mapMutations([
                'REDRAW_3D',
            ]),
            //CHANGE ROWS
            copyRow(tableRow) {
                this.insertRow(tableRow, true);
            },
            insertRow(tableRow, copy) {
                if (this.$root.setCheckRequired(this.metaTable.params, tableRow)) {
                    this.$root.sm_msg_type = 1;
                    this.cannot_close = true;
                    this.$emit('pre-insert', this.startHash, tableRow);
                    this.allRows.insertRow(tableRow, true, copy).then((data) => {
                        this.$emit('row-inserted', data);
                    }).finally(() => {
                        this.$root.sm_msg_type = 0;
                        this.cannot_close = false;
                    });
                }
            },
            updateRow(tableRow) {
                if (this.$root.setCheckRequired(this.metaTable.params, tableRow)) {
                    this.$root.sm_msg_type = 1;
                    this.$root.prevent_cell_edit = true;
                    this.cannot_close = true;
                    this.$emit('pre-update', this.startHash, tableRow);
                    this.allRows.updateRow(this.metaTable.params, tableRow, true).then((data) => {
                        let is3dLC = ThreeHelper.watched3d_is_needed_action(this.stim_link_params.app_fields, [tableRow]);
                        if (is3dLC) {
                            this.REDRAW_3D('soft');
                        } else {
                            this.$emit('row-updated', data);
                        }
                    }).finally(() => {
                        this.$root.sm_msg_type = 0;
                        this.$root.prevent_cell_edit = false;
                        this.cannot_close = false;
                    });
                }
            },
            deleteRow(tableRow) {
                this.$root.sm_msg_type = 1;
                this.cannot_close = true;
                this.$emit('pre-delete', this.startHash, tableRow);
                this.allRows.deleteRow(tableRow, true).then((data) => {
                    this.$emit('row-deleted', data);
                }).finally(() => {
                    this.$root.sm_msg_type = 0;
                    this.cannot_close = false;
                });
            },
            closePopUp(is_changing) {
                if (!is_changing && !this.cannot_close) {
                    this.editPopUpRow = null;
                    this.startHash = '';
                    this.$emit('close-popup');
                }
            },
            //LOAD DATA
            loadData() {
                this.no_clicks = true;
                this.avail_cols_for_app = this.stim_link_params.avail_cols_for_app;

                this.allRows = new MetaTabldaRows(this.stim_link_params, this.$root.app_stim_uh);

                if (!this.metaTable.is_loaded) {
                    this.metaTable.loadHeaders();
                }

                this.allRows.setDirectId(this.rowId);

                this.$root.sm_msg_type = 2;
                this.allRows.loadRows(this.metaTable.params).then((data) => {
                    this.editPopUpRow = this.allRows.master_row;
                    this.startHash = this.editPopUpRow ? this.editPopUpRow.row_hash : '';
                }).finally(() => {
                    this.$root.sm_msg_type = 0;
                    this.no_clicks = false;
                });
            },
            //another
            anotherRow(is_next) {
                this.$emit('another-row', is_next);
            },
            showSrcRecord(lnk, header, tableRow, behavior) {
                let index = this.linkPopups.filter((el) => {return el.key === 'show'}).length + 1;
                this.linkPopups.push({
                    key: 'show',
                    index: index,
                    link: lnk,
                    header: header,
                    row: tableRow,
                    behavior: behavior,//['map','link','list_view']
                });
            },
            closeLinkPopup(idx, should_update) {
                if (idx > -1) {
                    this.linkPopups[idx].key = 'hide';
                    this.$forceUpdate();
                }
            },
            directTickHandler(e) {
                if (this.metaTable.is_loaded && this.editPopUpRow && !this.$root.sm_msg_type) {
                    axios.post('/ajax/table/version_hash', {
                        table_id: this.metaTable.params.id,
                        row_list_ids: [this.editPopUpRow.id],
                        row_fav_ids: [],
                    }).then(({ data }) => {
                        if (this.metaTable.params.version_hash !== data.version_hash) {
                            this.metaTable.params.version_hash = data.version_hash;
                            this.loadData();
                        }
                        if (data.job_msg) {
                            Swal('Info', data.job_msg);
                        }
                    });
                }
            },
        },
        mounted() {
            this.loadData();
            //sync datas with collaborators
            this.ticker = setInterval(() => {
                if (!this.$root.debug) {
                    this.directTickHandler();
                }
            }, this.$root.version_hash_delay);
        },
        beforeDestroy() {
            clearInterval(this.ticker);
        }
    }
</script>

<style lang="scss" scoped>
    .tablda-table-wrapper {
        .popup-wrapper {
            z-index: 12000;
        }
    }
</style>