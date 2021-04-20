<template>
    <div v-if="tableMeta && settingsMeta" class="tab-settings full-height">
        <div v-if="!$root.AddonAvailableToUser(tableMeta, 'map')" class="row full-frame flex flex--center">
            <label>Addon is unavailable!</label>
        </div>
        <div v-else="" class="full-height">
            <div class="menu-header">
                <button class="btn btn-default btn-sm left-btn"
                        :class="{active : mapsTab === 'maps'}"
                        @click="mapsTab = 'maps'"
                >Map</button>
                <button v-if="canEdit"
                        class="btn btn-default btn-sm left-btn"
                        :class="{active : mapsTab === 'basics'}"
                        @click="mapsTab = 'basics';redraw_table++"
                >Basics</button>
                <button v-if="canEdit"
                        class="btn btn-default btn-sm left-btn"
                        :class="{active : mapsTab === 'icons'}"
                        @click="mapsTab = 'icons'"
                >Icons</button>

                <div class="right-elm" v-if="isVisible" v-show="mapsTab === 'maps'">
                    <map-radius-button :table_id="table_id" :table-meta="tableMeta" :radius-object="radiusObject"></map-radius-button>
                </div>
                <div class="right-elm flex flex--center" v-if="isVisible" v-show="mapsTab === 'maps'" style="height: 32px;">
                    <label style="margin: 0">
                        <span class="indeterm_check__wrap pub-check">
                            <span class="indeterm_check" @click="updateTable()" :style="$root.checkBoxStyle">
                                <i v-if="tableMeta.map_multiinfo" class="glyphicon glyphicon-ok group__icon"></i>
                            </span>
                        </span>
                        <span>Multiple info panels</span>
                    </label>
                </div>
            </div>
            <div class="menu-body">

                <!--MAP TAB-->

                <div class="full-frame" v-show="mapsTab === 'maps'">
                    <map-elem
                            v-if="!full_map_rebuild"
                            :table-meta="tableMeta"
                            :radius-object="radiusObject"
                            :column-values="columnValues"
                            :request_params="request_params"
                            :user="user"
                            @show-src-record="showSrcRecord"
                    ></map-elem>
                </div>

                <!--BASICS TAB-->

                <div class="full-frame" v-show="canEdit && mapsTab === 'basics'">
                    <custom-table
                            :cell_component_name="'custom-cell-settings-display'"
                            :global-meta="tableMeta"
                            :table-meta="settingsMeta['table_fields']"
                            :settings-meta="settingsMeta"
                            :all-rows="tableMeta._fields"
                            :rows-count="tableMeta._fields.length"
                            :cell-height="$root.cellHeight"
                            :is-full-width="true"
                            :user="user"
                            :behavior="'settings_display'"
                            :redraw_table="redraw_table"
                            :available-columns="$root.availableMapColumns"
                            @updated-row="updateRow"
                            @row-index-clicked="rowIndexClicked"
                    ></custom-table>
                    <for-settings-pop-up
                            v-if="settingsMeta['table_fields'] && editPopUpRow"
                            :global-meta="tableMeta"
                            :table-meta="settingsMeta['table_fields']"
                            :settings-meta="settingsMeta"
                            :table-row="editPopUpRow"
                            :ext-avail-tabs="['map_tab']"
                            :user="user"
                            :cell-height="$root.cellHeight"
                            @popup-update="updateRow"
                            @popup-close="closePopUp"
                            @another-row="anotherRowPopup"
                    ></for-settings-pop-up>
                </div>

                <!--ICONS TAB-->

                <div class="full-frame" v-show="canEdit && mapsTab === 'icons'">
                    <map-icons
                            :table-meta="tableMeta"
                            :settings-meta="settingsMeta"
                            :table_id="table_id"
                            :user="user"
                            :column-values="columnValues"
                            @icons-changed="iconsChanged"
                    ></map-icons>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import CustomTable from './../../../CustomTable/CustomTable';
    import ForSettingsPopUp from "../../../CustomPopup/ForSettingsPopUp";
    import MapRadiusButton from './../../../Buttons/MapRadiusButton';
    import MapElem from './MapAddon/MapElem';
    import MapIcons from './MapAddon/MapIcons';

    import {eventBus} from './../../../../app';

    export default {
        name: "TabMapView",
        components: {
            ForSettingsPopUp,
            CustomTable,
            MapRadiusButton,
            MapElem,
            MapIcons,
        },
        data: function () {
            return {
                full_map_rebuild: false,
                redraw_table: 0,
                should_map_redraw: false,
                should_reload_rows: false,
                editPopUpRow: null,
                radiusObject: this.newRadiusObject(),
                mapsTab: 'maps',
                columnValues: [],

                lat_header: _.find(this.tableMeta._fields, {is_lat_field: 1}) || {id: 0},
                long_header: _.find(this.tableMeta._fields, {is_long_field: 1}) || {id: 0}
            }
        },
        props:{
            table_id: Number,
            tableMeta: Object,
            settingsMeta: Object,
            user: Object,
            cellHeight: Number,
            canEdit: Boolean,
            isVisible: Boolean,
            request_params: Object,
        },
        computed: {
            //map module is visible AND map should be redrawn AND currently is visible
            shouldRedrawAndActive() {
                return this.isVisible && this.should_map_redraw && this.mapsTab === 'maps';
            },
        },
        watch: {
            table_id: function(val) {
                this.radiusObject = this.newRadiusObject();
                this.shouldRedrawMap();
            },
            //should redraw Map
            shouldRedrawAndActive: function(val) {
                if (val) {
                    this.$nextTick(() => {
                        //new markerRows should be transferred before redrawing
                        eventBus.$emit('should-redraw-map');
                    });
                    this.should_map_redraw = false;
                }
            },
        },
        methods: {
            newRadiusObject() {
                return {
                    distance: null,
                        type: '',
                        address: {
                        street: null,
                            city: null,
                            state: null,
                            county: null,
                            zip: null,
                    },
                    dms: {
                        lat_d: null,
                            lat_m: null,
                            lat_s: null,
                            long_d: null,
                            long_m: null,
                            long_s: null,
                    },
                    decimal: {
                        lat: null,
                            long: null,
                    }
                };
            },
            updateTable() {
                this.tableMeta.map_multiinfo = !this.tableMeta.map_multiinfo;
                this.$root.sm_msg_type = 1;

                let data = {
                    table_id: this.tableMeta.id,
                };
                Object.assign(data, this.tableMeta);
                Object.assign(data, this.tableMeta._theme);
                Object.assign(data, this.tableMeta._cur_settings);

                axios.put('/ajax/table', data).then(({ data }) => {
                }).catch(errors => {
                    Swal('', getErrors(errors));
                }).finally(() => {
                    this.$root.sm_msg_type = 0;
                });
            },
            updateRow(tableRow) {
                this.$root.sm_msg_type = 1;
                axios.put('/ajax/settings/data', {
                    table_field_id: tableRow.id,
                    field: tableRow._changed_field,
                    val: tableRow[tableRow._changed_field],
                }).then(({ data }) => {
                    if ($.inArray(tableRow._changed_field, this.$root.columnSettRadioFields) > -1)
                    {
                        eventBus.$emit('reload-meta-tb__fields');
                    }
                    let new_lat_header = _.find(this.tableMeta._fields, {is_lat_field: 1}) || {id: 0};
                    let new_long_header = _.find(this.tableMeta._fields, {is_long_field: 1}) || {id: 0};
                    //reload rows only if changed lat/long headers
                    if ((this.lat_header.id !== new_lat_header.id) || (this.long_header.id !== new_long_header.id)) {
                        this.lat_header = new_lat_header;
                        this.long_header = new_long_header;
                    }
                    this.shouldRedrawMap();
                }).catch(errors => {
                    Swal('', getErrors(errors));
                }).finally(() => {
                    this.$root.sm_msg_type = 0;
                });
            },
            rowIndexClicked(index) {
                this.editPopUpRow = this.tableMeta._fields[index];
            },
            closePopUp() {
                this.editPopUpRow = null;
            },
            iconsChanged(data) {
                this.columnValues = data;
                this.shouldRedrawMap();
            },
            shouldRedrawMap() {
                this.should_map_redraw = true;
            },
            fullMapRebuild() {
                this.full_map_rebuild = true;
                this.$nextTick(() => {
                    this.full_map_rebuild = false;
                });
            },

            newRequestParamsHandler(type) {
                if ($.inArray(type, ['load', 'filter', 'search', 'circle', 'rows-changed']) > -1) {
                    this.shouldRedrawMap();
                }
            },

            showSrcRecord(lnk, header, tableRow) {
                this.$emit('show-src-record', lnk, header, tableRow, 'map');
            },
            //change rows in popup
            anotherRowPopup(is_next) {
                let row_id = (this.editPopUpRow ? this.editPopUpRow.id : null);
                this.$root.anotherPopup(this.tableMeta._fields, row_id, is_next, this.rowIndexClicked);
            },
        },
        mounted() {
            eventBus.$on('new-request-params', this.newRequestParamsHandler);
        },
        beforeDestroy() {
            eventBus.$off('new-request-params', this.newRequestParamsHandler);
        }
    }
</script>

<style lang="scss" scoped>
    .tab-settings {
        position: relative;
        height: 100%;
        background-color: #FFF;

        .menu-header {
            position: relative;
            margin-left: 10px;
            top: 3px;

            .left-btn {
                position: relative;
                top: 5px;
            }

            button {
                background-color: #CCC;
                outline: none;
                &.active {
                    background-color: #FFF;
                }
                &:not(.active):hover {
                    color: black;
                }
            }

            .right-elm {
                float: right;
                margin-left: 10px;
            }
        }

        .menu-body {
            position: absolute;
            top: 35px;
            right: 5px;
            left: 5px;
            bottom: 5px;
            background-color: #005fa4;
            border-radius: 5px;
        }

        .addon-view {
            width: 130px;
            padding: 3px;
            display: inline-block;
            height: 30px;
            margin-right: 5px;
        }
    }
</style>