<template>

    <div v-if="tableMeta" class="full-height">
        <div class="full-height permissions-tab p5" :style="$root.themeMainBgStyle">

            <div class="permissions-panel full-height" :style="$root.themeMainBgStyle">

                <div class="permissions-menu-header">
                    <button class="btn btn-default btn-sm left-btn mr3"
                            :class="{active : mapsTab === 'list'}"
                            :style="textSysStyle"
                            @click="changeTab('list');"
                    >List</button>
                    <button v-if="canEdit"
                            class="btn btn-default btn-sm left-btn mr3"
                            :class="{active : mapsTab === 'basics'}"
                            :style="textSysStyle"
                            @click="changeTab('basics')"
                    >General</button>
                    <button v-if="canEdit"
                            class="btn btn-default btn-sm left-btn mr3"
                            :class="{active : mapsTab === 'fields'}"
                            :style="textSysStyle"
                            @click="changeTab('fields')"
                    >Field Specific</button>
                    <button v-if="canEdit"
                            class="btn btn-default btn-sm left-btn"
                            :class="{active : mapsTab === 'icons'}"
                            :style="textSysStyle"
                            @click="changeTab('icons')"
                    >Icons</button>

                    <div v-if="mapsTab === 'basics' && selectedMap.id" class="left-elm flex flex--center-v relative" style="margin-left: 10px; top: -5px">
                        <button class="btn btn-default btn-sm blue-gradient full-height"
                                :style="$root.themeButtonStyle"
                                :disabled="!canPermisEdit()"
                                @click="copyMapSett()"
                        >Copy</button>
                        <select class="form-control input-sm"
                                v-model="sett_for_copy"
                                :disabled="!canPermisEdit()"
                                style="width: 150px;"
                        >
                            <option v-for="map in tableMeta._map_addons"
                                    v-if="map.id != selectedMap.id"
                                    :value="map.id">{{ map.name }}</option>
                        </select>
                    </div>

                    <div v-if="mapsTab === 'fields' && selectedMap.id" class="left-elm flex flex--center-v relative" style="margin-left: 10px; top: -5px">
                        <label class="no-margin" style="white-space: nowrap;" :style="textSysStyleSmart">Lat./Long. source:</label>
                        <select class="form-control input-sm"
                                v-model="selectedMap.map_position_refid"
                                :disabled="!canPermisEdit()"
                                @change="updateMap(selectedMap, 'map_position_refid')"
                        >
                            <option :value="null">This table</option>
                            <option disabled>Tables ref'd by RCs:</option>
                            <option v-for="rc in tableMeta._ref_conditions" :value="rc.id">{{ rc.name +": "+ (rc._ref_table ? rc._ref_table.name : '') }}</option>
                        </select>
                        <label class="no-margin" style="white-space: nowrap;" :style="textSysStyleSmart">&nbsp;&nbsp;&nbsp;Info Panel header:</label>
                        <select class="form-control input-sm"
                                v-model="selectedMap.map_popup_hdr_id"
                                :disabled="!canPermisEdit()"
                                @change="updateMap(selectedMap)"
                        >
                            <option :value="null">This table</option>
                            <option disabled>Tables ref'd by RCs:</option>
                            <option v-if="positionRef" :value="positionRef.id">{{ positionRef.name +": "+ (positionRef._ref_table ? positionRef._ref_table.name : '') }}</option>
                        </select>
                    </div>

                    <div class="flex flex--center-v flex--end" style="position: absolute; top: -2px; right: 0; height: 32px; width: 50%;">
                        <label class="flex flex--center ml15 mr0" style="margin: 0 0 0 5px;white-space: nowrap;" :style="textSysStyleSmart">
                            Loaded Settings for GSI:&nbsp;
                            <select-block
                                :options="mapOpts()"
                                :sel_value="selectedMap.id"
                                :style="{ width:'150px', height:'32px', }"
                                @option-select="rowIndexClicked"
                            ></select-block>
                        </label>
                    </div>
                </div>
                <div class="permissions-menu-body" style="border: 1px solid #CCC;">

                    <!--LIST TAB-->

                    <div class="full-height permissions-panel no-padding" v-show="mapsTab === 'list'" :style="$root.themeMainBgStyle">
                        <custom-table
                            :cell_component_name="'custom-cell-addon'"
                            :global-meta="tableMeta"
                            :table-meta="$root.settingsMeta['table_maps']"
                            :settings-meta="$root.settingsMeta"
                            :all-rows="tableMeta._map_addons"
                            :adding-row="{ active: true, position: 'bottom' }"
                            :rows-count="tableMeta._map_addons.length"
                            :cell-height="1"
                            :max-cell-rows="0"
                            :is-full-width="true"
                            :with_edit="canAddonEdit()"
                            :selected-row="selIdx"
                            :user="$root.user"
                            :behavior="'settings_gantt_add'"
                            :available-columns="['name','map_data_range','description','map_active']"
                            :use_theme="true"
                            @added-row="addMap"
                            @delete-row="deleteMap"
                            @updated-row="updateMap"
                            @row-index-clicked="rowIndexClicked"
                        ></custom-table>
                    </div>

                    <!--BASICS TAB-->

                    <div class="full-height permissions-panel no-padding relative"
                         v-if="selectedMap.id"
                         v-show="mapsTab === 'basics'"
                         :style="$root.themeMainBgStyle"
                    >
                        <additional-map-settings
                            :selected-map="selectedMap"
                            :can-edit="canPermisEdit()"
                            @upd-map="updateMap"
                        ></additional-map-settings>
                    </div>

                    <!--FIELDS TAB-->

                    <div class="full-height permissions-panel no-padding relative"
                         v-if="selectedMap.id"
                         v-show="mapsTab === 'fields'"
                         :style="$root.themeMainBgStyle"
                    >
                        <div class="menu-header vert-menu">
                            <button class="btn btn-default btn-sm left-btn pull-right"
                                    :style="textSysStyle"
                                    :class="{active : mapBasicTab === 'current'}"
                                    @click="setmapBasicTab('current')"
                            >This</button>
                            <button class="btn btn-default btn-sm left-btn pull-right"
                                    v-if="positionRef"
                                    :class="{active : mapBasicTab === positionRef.id}"
                                    :style="textSysStyle"
                                    @click="setmapBasicTab(positionRef.id)"
                            >
                                <span>{{ positionRef.name }}</span>
                                <span style="color: #F00; cursor: pointer;" @click.prevent.stop="removeRC()">&times;</span>
                            </button>
                        </div>

                        <div class="menu-body vert-body" style="background: #FFF;">
                            <map-basic-settings
                                v-if="mapBasicTab && refMetaTables[mapBasicTab] && selectedMap"
                                :table-meta="refMetaTables[mapBasicTab]"
                                :limit-columns="availColmns"
                                :selected-map="selectedMap"
                                :can-edit="canPermisEdit()"
                                @upd-settings-row="updateRow"
                            ></map-basic-settings>
                            <div v-else="" class="full-frame flex flex--center" style="background: #FFF;font-size: 2em;">Loading...</div>
                        </div>
                    </div>

                    <!--ICONS TAB-->

                    <div class="full-height permissions-panel no-padding relative"
                         v-for="map in tableMeta._map_addons"
                         v-show="mapsTab === 'icons' && selectedMap.id === map.id"
                         :style="$root.themeMainBgStyle"
                    >
                        <map-icons
                            v-if="refMetaTables[map.map_position_refid || 'current']"
                            :global-meta="tableMeta"
                            :table-meta="refMetaTables[map.map_position_refid || 'current']"
                            :column-values="columnValues[map.map_position_refid || 'current']"
                            :selected-map="map"
                            :can-edit="canPermisEdit()"
                            @icons-changed="iconsChanged"
                            @upd-map="updateMap"
                        ></map-icons>
                        <div v-else="" class="full-frame flex flex--center" style="background: #FFF;font-size: 2em;">Loading...</div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import {eventBus} from '../../../../../app';

    import {SpecialFuncs} from '../../../../../classes/SpecialFuncs';

    import MapRadiusButton from './../../../../Buttons/MapRadiusButton';
    import MapIcons from './../MapAddon/MapIcons';
    import MapBasicSettings from "./../MapAddon/MapBasicSettings";
    import AdditionalMapSettings from "./../MapAddon/AdditionalMapSettings";
    import SelectBlock from "../../../../CommonBlocks/SelectBlock.vue";
    import CustomTable from "../../../../CustomTable/CustomTable.vue";

    import CellStyleMixin from "../../../../_Mixins/CellStyleMixin";

    export default {
        name: "MapSettings",
        components: {
            CustomTable,
            SelectBlock,
            AdditionalMapSettings,
            MapBasicSettings,
            MapRadiusButton,
            MapIcons,
        },
        mixins: [
            CellStyleMixin,
        ],
        data: function () {
            return {
                sett_for_copy: null,
                selIdx: 0,
                mapsTab: 'list',

                mapBasicTab: 'current',
                refMetaTables: { current: this.tableMeta },
            }
        },
        props:{
            table_id: Number,
            tableMeta: Object,
            columnValues: Object,
            canEdit: Boolean,
        },
        computed: {
            copyMapSett() {
                this.$root.sm_msg_type = 1;
                axios.post('/ajax/map-addon/copy', {
                    from_adn_id: this.sett_for_copy,
                    to_adn_id: this.selectedMap.id,
                }).then(({ data }) => {
                    this.$root.assignObject(data, this.tableMeta._map_addons[this.selIdx]);
                }).catch(errors => {
                    Swal('Info', getErrors(errors));
                }).finally(() => {
                    this.$root.sm_msg_type = 0;
                });
            },
            selectedMap() {
                return this.tableMeta._map_addons[this.selIdx] || {};
            },
            positionRef() {
                return _.find(this.tableMeta._ref_conditions, { id: Number(this.selectedMap.map_position_refid) });
            },
            availColmns() {
                if (!this.selectedMap.id) {
                    return [];
                }
                if (this.mapBasicTab === 'current') {
                    return !this.selectedMap.map_position_refid
                        ? ['name', 'is_lat_field', 'is_long_field', 'info_box', 'is_info_header_field', 'is_info_header_value']
                        : ['name', 'info_box', 'is_info_header_field', 'is_info_header_value'];
                } else {
                    return this.selectedMap.map_popup_hdr_id
                            ? ['name', 'is_lat_field', 'is_long_field', 'info_box', 'is_info_header_field', 'is_info_header_value']
                            : ['name', 'is_lat_field', 'is_long_field', 'info_box'];
                }
            },
        },
        methods: {
            mapOpts() {
                return _.map(this.tableMeta._map_addons, (fld) => {
                    return { val:fld.id, show:fld.name, };
                });
            },
            canPermisEdit() {
                return this.canEdit && this.$root.addonCanPermisEdit(this.tableMeta, this.selectedMap, '_map_rights');
            },
            canAddonEdit() {
                return this.canEdit && this.$root.addonCanEditGeneral(this.tableMeta, 'map');
            },
            changeTab(key) {
                this.mapsTab = key;
                this.$emit('set-sub-tab', key);
            },
            setmapBasicTab(val) {
                this.mapBasicTab = '';
                this.$nextTick(() => {
                    this.mapBasicTab = val;
                    if (!this.refMetaTables[this.mapBasicTab]) {
                        this.loadRefHeaders(this.mapBasicTab);
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
                    Swal('Info', getErrors(errors));
                });
            },
            removeRC(del_idx) {
                this.selectedMap.map_position_refid = null;
                this.setmapBasicTab('current');
                this.updateTable(this.selectedMap);
            },
            updateRow(tableMeta, tableRow) {
                $.LoadingOverlay('show');
                axios.post('/ajax/map-addon/settings', {
                    model_id: this.selectedMap.id,
                    field_id: tableRow.table_field_id,
                    fields: tableRow
                }).then(({ data }) => {
                    this.selectedMap._map_field_settings = data;
                    this.$emit('should-redraw-map');
                    this.setmapBasicTab(this.mapBasicTab);
                }).catch(errors => {
                    Swal('Info', getErrors(errors));
                }).finally(() => {
                    $.LoadingOverlay('hide');
                });
            },
            iconsChanged(map, data) {
                this.$emit('set-columns-values', map, data);
            },
            //LIST
            rowIndexClicked(optOrIdx) {
                this.selIdx = null;
                this.$nextTick(() => {
                    if (typeof optOrIdx === 'object') {
                        this.selIdx = _.findIndex(this.tableMeta._map_addons, {id: Number(optOrIdx.val)});
                    } else {
                        this.selIdx = optOrIdx;
                    }
                });
            },
            incorrectNaming(tableRow) {
                return _.find(this.tableMeta._map_addons, (map) => {
                    return map.id !== tableRow.id && map.name === tableRow.name;
                });
            },
            //Map
            addMap(tableRow) {
                if (this.incorrectNaming(tableRow)) {
                    Swal('Info', 'Name already used. Enter another one.');
                    return;
                }
                let fields = _.cloneDeep(tableRow);//copy object
                this.$root.deleteSystemFields(fields);

                $.LoadingOverlay('show');
                axios.post('/ajax/map-addon', {
                    table_id: this.tableMeta.id,
                    fields: fields
                }).then(({ data }) => {
                    this.tableMeta._map_addons = data;
                }).catch(errors => {
                    Swal('Info', getErrors(errors));
                }).finally(() => {
                    $.LoadingOverlay('hide');
                });
            },
            updateMap(tableRow, changed) {
                if (this.incorrectNaming(tableRow)) {
                    Swal('Info', 'Name already used. Enter another one.');
                    return;
                }
                if (changed === 'map_position_refid') {
                    this.selectedMap.map_popup_hdr_id = this.selectedMap.map_position_refid;
                    this.setmapBasicTab(this.selectedMap.map_position_refid || 'current');
                }

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
            deleteMap(tableRow) {
                $.LoadingOverlay('show');
                axios.delete('/ajax/map-addon', {
                    params: {
                        model_id: tableRow.id
                    }
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
            _.each(this.tableMeta._map_addons, (map) => {
                let key = map.map_position_refid || 'current';
                if (!this.refMetaTables[key]) {
                    this.loadRefHeaders(key);
                }
            });
        },
        beforeDestroy() {
        }
    }
</script>

<style lang="scss" scoped>
    @import "./../SettingsModule/TabSettingsPermissions";

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