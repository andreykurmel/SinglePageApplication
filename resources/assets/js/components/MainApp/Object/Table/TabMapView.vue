<template>
    <div v-if="tableMeta && $root.settingsMeta.is_loaded" class="tab-settings full-height">
        <div v-if="!$root.AddonAvailableToUser(tableMeta, 'map')" class="row full-frame flex flex--center">
            <label>Addon is unavailable!</label>
        </div>
        <div v-else="" class="full-height">
            <div class="menu-header" :style="textSysStyle">
                <button class="btn btn-default btn-sm left-btn"
                        :class="{active : acttab === 'settings'}"
                        :style="textSysStyle"
                        @click="changeTab('settings')"
                >Settings</button>
                <template v-for="map in tableMeta._map_addons" v-if="map.map_active">
                    <button class="btn btn-default btn-sm left-btn"
                            :class="{active : acttab === map.id}"
                            :style="textSysStyle"
                            style="margin-right: 3px;"
                            @click="changeTab(map.id)"
                    ><i class="fas fa-chart-bar"></i>&nbsp;{{ map.name }}</button>
                </template>

                <div class="right-elm" v-if="firstVisible && acttab !== 'settings'">
                    <map-radius-button
                        v-if="selectedMap && radiusObjects[acttab]"
                        :table-meta="tableMeta"
                        :selected-map="selectedMap"
                        :radius-object="radiusObjects[acttab]"
                        @clear-radius="newRadiusObject(acttab)"
                    ></map-radius-button>
                </div>
                <div class="right-elm flex flex--center" v-if="isVisible && selectedMap" v-show="acttab !== 'settings'" style="height: 32px;">
                    <label style="margin: 0">
                        <span class="indeterm_check__wrap pub-check">
                            <span class="indeterm_check"
                                  @click="() => { selectedMap.map_multiinfo = selectedMap.map_multiinfo ? 0 : 1; updateMap(selectedMap); }"
                                  :style="$root.checkBoxStyle"
                            >
                                <i v-if="selectedMap.map_multiinfo" class="glyphicon glyphicon-ok group__icon"></i>
                            </span>
                        </span>
                        <span>Multiple info panels</span>
                    </label>
                </div>
                <div class="right-elm">
                    <div v-show="acttab === 'settings'" class="pull-right" style="margin: 0 5px 0 15px">
                        <info-sign-link v-show="activeSub === 'list'" :app_sett_key="'help_link_map_tab'" :txt="'for GSI/List'"></info-sign-link>
                        <info-sign-link v-show="activeSub === 'basics'" :app_sett_key="'help_link_map_tab_basics'" :txt="'for GSI/General'"></info-sign-link>
                        <info-sign-link v-show="activeSub === 'fields'" :app_sett_key="'help_link_map_tab_basics'" :txt="'for GSI/General'"></info-sign-link>
                        <info-sign-link v-show="activeSub === 'icons'" :app_sett_key="'help_link_map_tab_icons'" :txt="'for GSI/Icons'"></info-sign-link>
                    </div>
                    <div v-show="acttab !== 'settings'" class="pull-right" style="margin: 0 5px 0 15px">
                        <info-sign-link :app_sett_key="'help_link_map_tab_data'" :hgt="26" :txt="'for GSI/Data Tab'"></info-sign-link>
                    </div>
                </div>
            </div>
            <div class="menu-body">

                <!--SETTINGS TAB-->

                <div class="full-frame" v-show="acttab === 'settings'">
                    <map-settings
                        :table_id="tableMeta.id"
                        :table-meta="tableMeta"
                        :can-edit="canEdit"
                        :column-values="columnValues"
                        @set-sub-tab="(key) => { activeSub = key; }"
                        @set-columns-values="setColValues"
                        @should-redraw-map="shouldRedrawMap()"
                    ></map-settings>
                </div>

                <!--BASICS TAB-->

                <div class="full-frame flex flex--col" v-show="acttab !== 'settings'">
                    <map-elem
                        v-if="selectedMap"
                        :table-meta="tableMeta"
                        :selected-map="selectedMap"
                        :radius-object="radiusObjects[acttab]"
                        :column-values="columnValues[selectedMap.map_position_refid || 'current']"
                        :request_params="request_params"
                        @show-src-record="showSrcRecord"
                    ></map-elem>
                </div>

            </div>
        </div>
    </div>
</template>

<script>
    import {eventBus} from '../../../../app';

    import {SpecialFuncs} from '../../../../classes/SpecialFuncs';

    import InfoSignLink from "../../../CustomTable/Specials/InfoSignLink.vue";
    import MapElem from "./MapAddon/MapElem.vue";
    import MapSettings from "./MapAddon/MapSettings.vue";
    import MapRadiusButton from './../../../Buttons/MapRadiusButton';

    import CellStyleMixin from "../../../_Mixins/CellStyleMixin";

    export default {
        name: "TabMapView",
        components: {
            MapSettings,
            MapElem,
            InfoSignLink,
            MapRadiusButton,
        },
        mixins: [
            CellStyleMixin,
        ],
        data: function () {
            return {
                activeSub: 'list',
                acttab: 'settings',

                firstVisible: false,
                should_map_redraw: false,
                radiusObjects: {},
                columnValues: { current: [] },
            }
        },
        props:{
            table_id: Number,
            tableMeta: Object,
            canEdit: Boolean,
            isVisible: Boolean,
            request_params: Object,
        },
        computed: {
            //map module is visible AND map should be redrawn AND currently is visible
            shouldRedrawAndActive() {
                return this.isVisible && this.should_map_redraw && this.acttab !== 'settings';
            },
            selectedMap() {
                return _.find(this.tableMeta._map_addons, { id: Number(this.acttab) });
            },
        },
        watch: {
            table_id(val) {
                this.changeTab('settings');
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
            setColValues(map, values) {
                this.$set(this.columnValues, map.map_position_refid || 'current', values);
                this.shouldRedrawMap();
            },
            changeTab(key) {
                this.acttab = null;
                this.$nextTick(() => {
                    this.acttab = key;
                    if (key !== 'settings') {
                        this.newRadiusObject();
                        this.shouldRedrawMap();
                    }
                });
            },
            newRadiusObject(deleteKey) {
                //clear radius object
                if (deleteKey) {
                    this.radiusObjects[deleteKey] = null;
                }

                //no creation if already present
                if (this.radiusObjects[this.acttab]) {
                    return;
                }

                let radius = {
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
                this.$set(this.radiusObjects, this.acttab, radius);
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
            updateMap(tableRow) {
                let fields = _.cloneDeep(tableRow);//copy object
                this.$root.deleteSystemFields(fields);

                $.LoadingOverlay('show');
                axios.put('/ajax/map-addon', {
                    model_id: tableRow.id,
                    fields: fields
                }).then(({ data }) => {
                    this.tableMeta._map_addons = data;
                }).catch(errors => {
                    Swal('Info', getErrors(errors));
                }).finally(() => {
                    $.LoadingOverlay('hide');
                });
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
            top: 505px;
            left: -10px;
            width: 500px;
            height: 20px;
            transform-origin: top left;
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