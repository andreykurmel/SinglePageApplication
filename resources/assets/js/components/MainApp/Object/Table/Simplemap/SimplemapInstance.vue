<template>
    <div class="full-height flex flex--center relative" ref="smp_wrapper">
        <div v-if="smpLegends.length"
             class="smp-legend"
             :style="getLegendPosition()"
             draggable="true"
             @dragstart="dragLegendStart"
             @drag="dragLegendDo"
             @dragend="dragLegendEnd"
        >
            <ul class="smp-legend-ul" :class="{'flex': selectedSimplemap.smp_legend_orientation === 'horizontal'}">
                <li v-for="row in smpLegends" class="smp-legend-item flex flex--center-v">
                    <div class="smp-legend-color" :style="stlLegendColor(row)">
                        <tablda-colopicker
                            v-if="legendFldColor && canAddonEdit()"
                            :init_color="row.color || '#005ea4'"
                            @set-color="(clr) => { updateLegendColor(row, clr) }"
                        ></tablda-colopicker>
                    </div>
                    <span class="smp-legend-label" :style="stlLegendLabel()">{{ row.name }}</span>
                </li>
            </ul>
        </div>

        <div v-if="!tableRows" class="flex flex--center bold">Loading...</div>

        <link-pop-up
            v-if="popRows && popRows.length && selectedSimplemap.smp_theme_pop_style === 'link_pop'"
            :external-rows="selectedSimplemap.smp_theme_pop_link_id ? null : popRows"
            :source-meta="tableMeta"
            :idx="0"
            :link="linkObject"
            :meta-header="linkHeader"
            :meta-row="linkRow"
            :view_authorizer="{
                mrv_marker: $root.is_mrv_page,
                srv_marker: $root.is_srv_page,
                dcr_marker: $root.is_dcr_page,
            }"
            @link-popup-close="hidePopup"
        ></link-pop-up>

        <div v-if="popRows && popRows.length && selectedSimplemap.smp_theme_pop_style === 'simple_pop'" :style="popupStyle()">
            <simplemap-row-card
                :table-rows="popRows"
                :table-meta="tableMeta"
                :selected-simplemap="selectedSimplemap"
                @close-clicked="hidePopup"
            ></simplemap-row-card>
        </div>

        <div :id="'simple_map_' + selectedSimplemap.id"></div>
    </div>
</template>

<script>
    import {eventBus} from "../../../../../app";

    import {SpecialFuncs} from "../../../../../classes/SpecialFuncs";
    import {Endpoints} from "../../../../../classes/Endpoints";

    import CellStyleMixin from "../../../../_Mixins/CellStyleMixin";
    import MixinForAddons from "./../MixinForAddons";

    import SimplemapRowCard from "./SimplemapRowCard.vue";
    import linkObject from "../../../../CustomCell/InCell/LinkObject.vue";
    import TabldaColopicker from "../../../../CustomCell/InCell/TabldaColopicker.vue";

    export default {
        name: "SimplemapInstance",
        components: {
            TabldaColopicker,
            SimplemapRowCard,
        },
        mixins: [
            CellStyleMixin,
            MixinForAddons,
        ],
        data: function () {
            return {
                rowsAreLoaded: false,
                locationsRows: null,
                legendX: 0,
                legendY: 0,
                smpLegends: [],
                linkObject: null,
                linkHeader: null,
                linkRow: null,
                posX: null,
                posY: null,
                popRows: null,
                add_click: 0,
            }
        },
        props: {
            tableMeta: Object,
            selectedSimplemap: Object,
            currentPageRows: Array,
            requestParams: Object
        },
        computed: {
            onHoverFld() {
                return _.find(this.tableMeta._fields, {id: Number(this.selectedSimplemap.smp_on_hover_fld_id)});
            },
            lvlField() {
                return _.find(this.tableMeta._fields, {id: Number(this.selectedSimplemap.level_fld_id)});
            },
            legendFld() {
                return _.find(this.tableMeta._fields, {id: Number(this.selectedSimplemap.smp_value_fld_id)});
            },
            legendFldColor() {
                return _.find(this.tableMeta._fields, {id: Number(this.selectedSimplemap.smp_color_fld_id)});
            },
            inactiveFld() {
                return _.find(this.tableMeta._fields, {id: Number(this.selectedSimplemap.smp_active_status_fld_id)});
            },
        },
        watch: {
        },
        methods: {
            canAddonEdit() {
                return this.$root.addonCanEditGeneral(this.tableMeta, 'simplemap');
            },
            //Drag
            dragLegendStart(e) {
                e = e || window.event;
                this.legendX = e.clientX - this.selectedSimplemap.smp_legend_pos_x;
                this.legendY = e.clientY - this.selectedSimplemap.smp_legend_pos_y;
            },
            dragLegendDo(e) {
                e = e || window.event;
                if (e.clientX && e.clientY) {
                    this.selectedSimplemap.smp_legend_pos_x = e.clientX - this.legendX;
                    this.selectedSimplemap.smp_legend_pos_y = e.clientY - this.legendY;
                }
            },
            dragLegendEnd(e) {
                let fields = _.cloneDeep(this.selectedSimplemap);
                this.$root.deleteSystemFields(fields);

                this.$root.sm_msg_type = 1;
                axios.put('/ajax/addon-simplemap', {
                    model_id: this.selectedSimplemap.id,
                    fields: fields
                }).then(({ data }) => {
                    this.tableMeta._simplemaps = data;
                }).catch(errors => {
                  if (errors.response && errors.response.status === 401) {
                    this.$root.sm_msg_type = 0;
                  } else {
                    Swal('Info', getErrors(errors));
                  }
                }).finally(() => {
                    this.$root.sm_msg_type = 0;
                });
            },
            //Legend
            stlLegendColor(row) {
                let size = Number(this.selectedSimplemap.smp_legend_size) - 2;
                return {
                    backgroundColor: row.color || '#005ea4',
                    height: (size) + 'px',
                    width: (size * 2) + 'px',
                };
            },
            stlLegendLabel() {
                let size = Number(this.selectedSimplemap.smp_legend_size);
                return {
                    fontSize: (size) + 'px',
                };
            },
            updateLegendColor(legendRow, clr) {
                if (legendRow && legendRow.row_ids && this.legendFldColor) {
                    legendRow.color = clr;

                    let updRows = [];
                    _.each(this.tableRows, (row) => {
                        if (this.$root.inArray(row.id, legendRow.row_ids)) {
                            row[this.legendFldColor.field] = clr;
                            updRows.push(row);
                        }
                    });

                    if (updRows.length) {
                        Endpoints.massUpdateRows(this.tableMeta, updRows, this.requestParams).then((data) => {
                            if (this.selectedSimplemap.tb_smp_data_range == '0') {
                                eventBus.$emit('list-view-update-row-sync', data);
                            }
                            this.drawAddon();
                        });
                    }
                }
            },
            //Popup
            showPopup(id) {
                this.posX = Number(window.event.pageX) - 10;
                this.posY = Number(window.event.pageY) - 10;
                this.popRows = _.filter(this.tableRows, (row) => {
                    return this.lvlField && row[this.lvlField.field] === id;
                });

                if (this.selectedSimplemap.smp_theme_pop_link_id) {
                    _.each(this.tableMeta._fields, (fld) => {
                        let link = _.find(fld._links, {id: Number(this.selectedSimplemap.smp_theme_pop_link_id)});
                        if (link) {
                            this.linkObject = link;
                            this.linkHeader = fld;
                            this.linkRow = _.first(this.popRows);
                        }
                    });
                } else {
                    this.linkObject = {pop_height:70};
                    this.linkHeader = {};
                    this.linkRow = {};
                }
            },
            hidePopup(id) {
                this.popRows = null;
            },
            popupStyle() {
                return {
                    position: 'fixed',
                    left: this.posX + 'px',
                    top: this.posY + 'px',
                    zIndex: 100,
                    backgroundColor: 'transparent',
                };
            },
            //Instance
            makeInstance() {
                let key = this.selectedSimplemap.map === 'states'
                    ? 'simplemaps_usmap'
                    : (this.selectedSimplemap.map === 'counties' ? '' : 'simplemaps_countymap');

                if (key && window[key]) {
                    window[key].mapdata.main_settings.div = 'simple_map_' + this.selectedSimplemap.id;
                    window[key].mapdata.main_settings.manual_zoom = 'yes';
                    window[key].mapdata.main_settings.state_url = '';
                    //window[key].mapdata.main_settings.popups = 'off';

                    let bnd = this.$refs.smp_wrapper.getBoundingClientRect();
                    window[key].mapdata.main_settings.width = bnd.width * 0.67 > bnd.height
                        ? Number(bnd.height / 0.67)
                        : Number(bnd.width);

                    window[key].hooks.click_state = this.showPopup;

                    _.each(window[key].mapdata.state_specific, (obj, key) => {
                        obj.inactive = '';
                        obj.description = '';
                        obj.color = 'default';

                        let Frows = _.filter(this.tableRows, (row) => {
                            let value = this.lvlField
                                ? SpecialFuncs.showFullHtml(this.lvlField, row, this.tableMeta)
                                : null;
                            return value === key;
                        });
                        obj.description = this.onHoverFld && Frows.length
                            ? this.getOnHover(Frows)
                            : "Rows: " + Frows.length;
                        if (Frows.length) {
                            obj.color = this.getLegendColor( _.first(Frows) );

                            if (this.inactiveFld && _.find(Frows, (r) => { return ! r[this.inactiveFld.field]; })) {
                                obj.inactive = 'yes';
                            }
                        }
                    });

                    this.smpLegends = [];
                    if (this.legendFld || this.legendFldColor) {
                        let present = [];
                        let legends = [];

                        _.each(this.tableRows, (row) => {
                            let name = SpecialFuncs.showFullHtml(this.legendFld, row, this.tableMeta);
                            if (! this.$root.inArray(name, present)) {
                                present.push(name);
                                legends.push({
                                    name: name,
                                    color: this.getLegendColor(row),
                                    type: "state",
                                    ids: '' + SpecialFuncs.showFullHtml(this.lvlField, row, this.tableMeta),
                                    row_ids: [row.id],
                                });
                            } else {
                                let idx = _.findIndex(legends, {name: name});
                                if (idx > -1) {
                                    legends[idx].ids += ',' + SpecialFuncs.showFullHtml(this.lvlField, row, this.tableMeta);
                                    legends[idx].row_ids.push(row.id);
                                }
                            }
                        });

                        this.smpLegends = legends;
                    }

                    window[key].mapdata.locations = {};
                    if (this.selectedSimplemap.locations_lat_fld_id && this.selectedSimplemap.locations_long_fld_id) {
                        let metaTable = _.find(this.$root.settingsMeta.available_tables, {id: Number(this.selectedSimplemap.locations_table_id)}) || this.tableMeta;
                        let latHdr = _.find(metaTable._fields, {id: Number(this.selectedSimplemap.locations_lat_fld_id)});
                        let longHdr = _.find(metaTable._fields, {id: Number(this.selectedSimplemap.locations_long_fld_id)});
                        let nameHdr = _.find(metaTable._fields, {id: Number(this.selectedSimplemap.locations_name_fld_id)});
                        let descrHdr = _.find(metaTable._fields, {id: Number(this.selectedSimplemap.locations_descr_fld_id)});
                        let iconHdr = _.find(metaTable._fields, {id: Number(this.selectedSimplemap.locations_icon_color_fld_id)});
                        let shapeHdr = _.find(metaTable._fields, {id: Number(this.selectedSimplemap.locations_icon_shape_fld_id)});

                        let idx = 0;
                        let rows = this.locationsRows && this.locationsRows.length ? this.locationsRows : this.tableRows;
                        _.each(rows, (row) => {
                            let latVal = latHdr ? SpecialFuncs.showFullHtml(latHdr, row, metaTable) : '';
                            let longVal = longHdr ? SpecialFuncs.showFullHtml(longHdr, row, metaTable) : '';
                            if (latVal && longVal) {
                                window[key].mapdata.locations[idx] = {
                                    name: nameHdr ? SpecialFuncs.showFullHtml(nameHdr, row, metaTable) : '',
                                    lat: latVal,
                                    lng: longVal,
                                    description: descrHdr ? SpecialFuncs.showFullHtml(descrHdr, row, metaTable) : '',
                                    color: iconHdr ? SpecialFuncs.showFullHtml(iconHdr, row, metaTable) : '',
                                    type: shapeHdr
                                        ? String(SpecialFuncs.showFullHtml(shapeHdr, row, metaTable)).toLowerCase() || 'circle'
                                        : 'circle',
                                };
                                idx += 1;
                            }
                        });
                    }

                    window[key].mapdata.legend = {};
                    window[key].load();

                    window.setTimeout(() => {
                        let el = document.getElementById('simple_map_' + this.selectedSimplemap.id + '_legend');
                        if (el && el.style) {
                            el.style.backgroundColor = 'rgba(255, 255, 255, 0.8)';
                        }
                    }, 100);
                }
            },
            getOnHover(Frows) {
                let descr = [];
                _.each(Frows, (r) => {
                    let value = SpecialFuncs.showFullHtml(this.onHoverFld, r, this.tableMeta);
                    if (value) {
                        descr.push(value);
                    }
                });
                return descr.length > 1
                    ? '<ul style="margin: 0; padding-left: 20px"><li>' + _.join(descr, '</li><li>') + '</li></ul>'
                    : (_.first(descr) || '');
            },
            getLegendColor(tableRow) {
                if (this.legendFld && this.selectedSimplemap.smp_value_ddl_color) {
                    let rcObj = SpecialFuncs.rcObj(tableRow, this.legendFld.field, tableRow[this.legendFld.field]);
                    return rcObj ? rcObj.ref_bg_color : null;
                }
                if (this.legendFldColor) {
                    return SpecialFuncs.showFullHtml(this.legendFldColor, tableRow, this.tableMeta);
                }
                return null;
            },
            getLegendPosition() {
                return {
                    left: this.selectedSimplemap.smp_legend_pos_x + 'px',
                    top: this.selectedSimplemap.smp_legend_pos_y + 'px',
                }
            },
            drawAddon() {
                this.rowsAreLoaded = true;
                if (this.locationsRows) {
                    this.makeInstance();
                }
            },
            mountedFunc() {
                this.getLocations();
                this.getRows(this.selectedSimplemap.tb_smp_data_range, 'simplemap', this.selectedSimplemap.id);
            },
            getLocations() {
                if (this.selectedSimplemap.locations_table_id) {
                    let params = SpecialFuncs.tableMetaRequest(this.selectedSimplemap.locations_table_id);
                    let paramsWithRange = SpecialFuncs.dataRangeRequestParams(
                        this.selectedSimplemap.locations_data_range,
                        this.selectedSimplemap.locations_table_id,
                        params
                    );
                    paramsWithRange.ref_cond_id = 'access';
                    Endpoints.getOnlyRows(paramsWithRange).then((data) => {
                        this.locationsRows = data.rows;
                        if (this.rowsAreLoaded) {
                            this.makeInstance();
                        }
                    });
                } else {
                    this.locationsRows = [];
                }
            },
        },
        mounted() {
            this.mountedFunc();
        },
        beforeDestroy() {
        }
    }
</script>

<style lang="scss" scoped>
.smp-legend {
    cursor: pointer;
    position: absolute;
    z-index: 150;
    padding: 3px 5px;
    background-color: rgba(255, 255, 255, 0.8);

    .smp-legend-ul {
        list-style-type: none;
        margin: 0px;
        padding: 0px;

        .smp-legend-item {
            margin: 3px 5px;
        }
        .smp-legend-color {
            position: relative;
            width: 20px;
            height: 10px;
            margin-right: 5px;
        }
        .smp-legend-label {
            position: relative;
            top: 1px;
        }
    }
}
</style>
