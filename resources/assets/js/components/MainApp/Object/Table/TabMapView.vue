<template>
    <div v-if="tableMeta && settingsMeta" class="tab-settings full-height">
        <div v-if="!$root.AddonAvailableToUser(tableMeta, 'map')" class="row full-frame flex flex--center">
            <label>Addon is unavailable!</label>
        </div>
        <div v-else="" class="full-height">
            <div class="menu-header" :style="textSysStyle">
                <div class="left-elm">
                    <button class="btn btn-default btn-sm left-btn"
                            :class="{active : mapsTab === 'maps'}"
                            :style="textSysStyle"
                            @click="mapsTab = 'maps';shouldRedrawMap();"
                    >Map</button>
                    <button v-if="canEdit"
                            class="btn btn-default btn-sm left-btn"
                            :class="{active : mapsTab === 'basics'}"
                            :style="textSysStyle"
                            @click="mapsTab = 'basics'"
                    >Settings</button>
                    <button v-if="canEdit"
                            class="btn btn-default btn-sm left-btn"
                            :class="{active : mapsTab === 'icons'}"
                            :style="textSysStyle"
                            @click="mapsTab = 'icons'"
                    >Icons</button>
                </div>

                <div v-if="mapsTab === 'basics'" class="left-elm flex flex--center-v" style="margin-left: 10px;">
                    <label class="no-margin" style="white-space: nowrap;">Lat./Long. source:</label>
                    <select class="form-control input-sm" v-model="tableMeta.map_position_refid" @change="updateTable('map_position_refid')">
                        <option :value="null">This table</option>
                        <option disabled>Tables ref'd by RCs:</option>
                        <option v-for="rc in tableMeta._ref_conditions" :value="rc.id">{{ rc.name +": "+ (rc._ref_table ? rc._ref_table.name : '') }}</option>
                    </select>
                    <label class="no-margin" style="white-space: nowrap;">&nbsp;&nbsp;&nbsp;Info Panel Header:</label>
                    <select class="form-control input-sm" v-model="tableMeta.map_popup_hdr_id" @change="updateTable()">
                        <option :value="null">This table</option>
                        <option disabled>Tables ref'd by RCs:</option>
                        <option v-if="positionRef" :value="positionRef.id">{{ positionRef.name +": "+ (positionRef._ref_table ? positionRef._ref_table.name : '') }}</option>
                    </select>
                </div>

                <div class="right-elm" v-if="firstVisible && mapsTab === 'maps'">
                    <map-radius-button
                            :table-meta="tableMeta"
                            :radius-object="radiusObject"
                            @clear-radius="newRadiusObject()"
                    ></map-radius-button>
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
                            :column-values="columnValues[mapBasicTab]"
                            :request_params="request_params"
                            :user="user"
                            @show-src-record="showSrcRecord"
                    ></map-elem>
                </div>

                <!--BASICS TAB-->

                <div class="full-frame" v-show="canEdit && mapsTab === 'basics'">
                    <div class="menu-header vert-menu">
                        <button class="btn btn-default btn-sm left-btn pull-right"
                                :style="textSysStyle"
                                :class="{active : mapBasicTab === 'current'}"
                                @click="setmapBasicTab('current')"
                        >This</button>
                        <button class="btn btn-default btn-sm left-btn pull-right"
                                v-if="positionRef"
                                :class="{active : mapBasicTab === 'rc_'+positionRef.id}"
                                :style="textSysStyle"
                                @click="setmapBasicTab('rc_'+positionRef.id)"
                        >
                            <span>{{ positionRef.name }}</span>
                            <span style="color: #F00; cursor: pointer;" @click.prevent.stop="removeRC()">&times;</span>
                        </button>
                    </div>

                    <div class="menu-body vert-body" style="background: #FFF; height: 60%;">
                        <map-basic-settings
                                v-if="mapBasicTab && refMetaTables[mapBasicTab]"
                                :table-meta="refMetaTables[mapBasicTab]"
                                :limit-columns="availAll"
                                @upd-settings-row="updateRow"
                        ></map-basic-settings>
                        <div v-else="" class="full-frame flex flex--center" style="background: #FFF;font-size: 2em;">Loading...</div>
                    </div>
                    <div class="menu-body vert-body" style="top: auto; height: calc(40% - 15px);">
                        <div class="top-text top-text--height" :style="textSysStyle">Additional Settings</div>
                        <additional-map-settings
                            :table-meta="tableMeta"
                            @upd-table="updateTable"
                        ></additional-map-settings>
                    </div>
                </div>

                <!--ICONS TAB-->

                <div class="full-frame" v-show="canEdit && mapsTab === 'icons'">
                    <div class="menu-header vert-menu">
                        <button class="btn btn-default btn-sm left-btn pull-right"
                                :class="{active : mapBasicTab === 'current'}"
                                :style="textSysStyle"
                                @click="setmapBasicTab('current')"
                        >This</button>
                        <button class="btn btn-default btn-sm left-btn pull-right"
                                v-if="positionRef"
                                :class="{active : mapBasicTab === 'rc_'+positionRef.id}"
                                :style="textSysStyle"
                                @click="setmapBasicTab('rc_'+positionRef.id)"
                        >
                            <span>{{ positionRef.name }}</span>
                            <span style="color: #F00; cursor: pointer;" @click.prevent.stop="removeRC()">&times;</span>
                        </button>
                    </div>

                    <div class="menu-body vert-body" style="background: #FFF;">
                        <map-icons
                                v-if="mapBasicTab && refMetaTables[mapBasicTab]"
                                :table-meta="refMetaTables[mapBasicTab]"
                                :column-values="columnValues[mapBasicTab]"
                                @icons-changed="iconsChanged"
                        ></map-icons>
                        <div v-else="" class="full-frame flex flex--center" style="background: #FFF;font-size: 2em;">Loading...</div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import {eventBus} from '../../../../app';

    import {SpecialFuncs} from '../../../../classes/SpecialFuncs';

    import MapRadiusButton from './../../../Buttons/MapRadiusButton';
    import MapElem from './MapAddon/MapElem';
    import MapIcons from './MapAddon/MapIcons';
    import MapBasicSettings from "./MapAddon/MapBasicSettings";
    import AdditionalMapSettings from "./MapAddon/AdditionalMapSettings";

    import CellStyleMixin from "../../../_Mixins/CellStyleMixin";

    export default {
        name: "TabMapView",
        components: {
            AdditionalMapSettings,
            MapBasicSettings,
            MapRadiusButton,
            MapElem,
            MapIcons,
        },
        mixins: [
            CellStyleMixin,
        ],
        data: function () {
            return {
                firstVisible: false,
                full_map_rebuild: false,
                should_map_redraw: false,
                radiusObject: null,
                mapsTab: 'maps',

                mapBasicTab: '',
                refMetaTables: { current: this.tableMeta },
                columnValues: { current: [] },
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
            positionRef() {
                return _.find(this.tableMeta._ref_conditions, { id: Number(this.tableMeta.map_position_refid) });
            },
            availAll() {
                let selmaptab = this.tableMeta.map_position_refid ? 'rc_'+this.tableMeta.map_position_refid : 'current';
                return selmaptab !== this.mapBasicTab;
            },
        },
        watch: {
            table_id(val) {
                this.newRadiusObject();
                this.shouldRedrawMap();
            },
            //should redraw Map
            shouldRedrawAndActive(val) {
                if (val) {
                    this.$nextTick(() => {
                        //new markerRows should be transferred before redrawing
                        eventBus.$emit('should-redraw-map');
                    });
                    this.should_map_redraw = false;
                }
            },
            isVisible(val) {
                this.firstVisible = this.firstVisible || val;
            },
        },
        methods: {
            setmapBasicTab(val) {
                this.mapBasicTab = '';
                this.$nextTick(() => {
                    this.mapBasicTab = val;
                    if (!this.refMetaTables[val]) {
                        this.loadRefHeaders(val);
                    }
                });
            },
            loadRefHeaders(str_ref_id) {
                axios.post('/ajax/table-data/get-headers', {
                    ref_cond_id: String(str_ref_id).replace(/[^\d]/gi,''),
                    user_id: !this.$root.user.see_view ? this.$root.user.id : null,
                }).then(({ data }) => {
                    this.$set(this.refMetaTables, str_ref_id, data);
                    this.$forceUpdate();
                }).catch(errors => {
                    Swal('', getErrors(errors));
                });
            },
            removeRC(del_idx) {
                this.tableMeta.map_position_refid = null;
                this.setmapBasicTab('current');
                this.updateTable();
            },
            newRadiusObject() {
                this.radiusObject = {
                    entered_address: null,
                    distance: null,
                    type: '',
                    address: {//hidden
                        _number: null,
                        full: null,
                        street: null,
                        city: null,
                        state: null,
                        county: null,
                        zip: null,
                        lat: null,
                        long: null,
                    },
                    dms: {//visible
                        lat_d: null,
                        lat_m: null,
                        lat_s: null,
                        long_d: null,
                        long_m: null,
                        long_s: null,
                    },
                    decimal: {//visible
                        lat: null,
                        long: null,
                    }
                };
            },
            updateTable(changed) {
                if (changed === 'map_position_refid') {
                    this.tableMeta.map_popup_hdr_id = this.tableMeta.map_position_refid;
                }
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
            updateRow(tableMeta, tableRow) {
                this.$root.updateSettingsColumn(tableMeta, tableRow).then(({ data }) => {
                    this.shouldRedrawMap();
                });
            },
            iconsChanged(data) {
                this.$set(this.columnValues, this.mapBasicTab, data);
                this.shouldRedrawMap();
            },
            shouldRedrawMap() {
                this.should_map_redraw = true;
            },
            newRequestParamsHandler(type) {
                if ($.inArray(type, ['load', 'filter', 'search', 'circle', 'rows-changed']) > -1) {
                    this.shouldRedrawMap();
                }
            },
            showSrcRecord(lnk, header, tableRow) {
                this.$emit('show-src-record', lnk, header, tableRow, 'map');
            },
        },
        mounted() {
            this.newRadiusObject();
            this.setmapBasicTab(this.tableMeta.map_position_refid ? 'rc_'+this.tableMeta.map_position_refid : 'current');
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

            .left-elm {
                float: left;
                margin-right: 10px;
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

        .vert-menu {
            position: absolute;
            top: -10px;
            left: -220px;
            width: 230px;
            height: 20px;
            transform-origin: bottom right;
            transform: rotate(-90deg);

            .left-btn {
                margin-left: 5px;
            }
        }
        .vert-body {
            left: 32px;
            top: 5px;
        }

        .top-text {
            font-size: 16px;
            color: #FFF;
            padding: 5px 0;
            height: 30px;
        }
        .top-text--height {
            height: 30px;
            overflow: hidden;
        }

        .addon-view {
            width: 130px;
            padding: 3px;
            display: inline-block;
            height: 30px;
            margin-right: 5px;
        }

        .btn-default {
            height: 30px;
        }
    }
</style>