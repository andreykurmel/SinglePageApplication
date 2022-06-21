<template>
    <div class="full-height">
        <label v-if="mapWarning" class="map-warning">{{ mapWarning }}</label>
        <div :style="{zIndex: loading ? 500 : -1}" ref="loader" class="loader"></div>
        <div ref="map_google" class="full-height"></div>

        <template v-for="(marker_row,idx) in marker_rows_all">
            <div :ref="'marker_info_'+marker_row._row_id" class="iw-wrapper flex flex--col">
                <h4 v-if="marker_hdr_tb && marker_header && marker_row"
                    class="iw-header flex flex--center-v"
                    :style="hdrBgClr"
                >
                    <div style="max-width: 35%">{{ $root.uniqName(marker_header.name) }}:&nbsp;</div>
                    <single-td-field
                            :table-meta="marker_hdr_tb"
                            :table-header="marker_header"
                            :td-value="marker_row[marker_header.field]"
                            :ext-row="marker_row"
                            :no_width="true"
                            :with_edit="false"
                            style="display: inline-block;background-color: transparent;"
                            @show-src-record="showSrcRecord"
                    ></single-td-field>
                </h4>

                <div v-if="marker_fields_meta && marker_row"
                     class="iw-body flex flex--elem_remain"
                     :style="{ height: tableMeta.map_popup_height+'px', width: tableMeta.map_popup_width+'px' }"
                >
                    <div class="table_part" :style="tablePartStyle">
                        <vertical-table
                            :td="$root.tdCellComponent(marker_fields_meta.is_system)"
                            :table-meta="marker_fields_meta"
                            :settings-meta="$root.settingsMeta"
                            :table-row="marker_row"
                            :user="user"
                            :cell-height="$root.cellHeight"
                            :max-cell-rows="$root.maxCellRows"
                            :behavior="'map_view'"
                            :is_small_spacing="'yes'"
                            :with_edit="false"
                            style="background-color: transparent; table-layout: fixed;"
                            @show-src-record="showSrcRecord"
                        ></vertical-table>
                    </div>

                    <div v-if="imgHeader" class="attach_part" :style="{width: (tableMeta.map_picture_width)+'%'}">
                        <show-attachments-block
                            :show-type="tableMeta.map_picture_style"
                            :table-header="imgHeader"
                            :table-meta="tableMeta"
                            :table-row="marker_row"
                            :just-first="true"
                        ></show-attachments-block>
                    </div>

                </div>
            </div>
        </template>

    </div>
</template>

<script>
import {SpecialFuncs} from '../../../../../classes/SpecialFuncs';

import {eventBus} from '../../../../../app';

import CellStyleMixin from '../../../../_Mixins/CellStyleMixin.vue';
import TableLinkMixin from '../../../../_Mixins/TableLinkMixin.vue';

import VerticalTable from "../../../../CustomTable/VerticalTable";
import SingleTdField from "../../../../CommonBlocks/SingleTdField";
import ShowAttachmentsBlock from "../../../../CommonBlocks/ShowAttachmentsBlock";

export default {
        name: "MapElem",
        components: {
            ShowAttachmentsBlock,
            SingleTdField,
            VerticalTable,
        },
        mixins: [
            CellStyleMixin,
            TableLinkMixin,
        ],
        data: function () {
            return {
                map: null,
                markers: [],
                addressCenter: null,
                radius_circle: null,
                markerRows: [],
                mapWarning: '',
                loading: false,

                marker_hdr_tb: null,
                marker_rows_all: [],
                marker_fields_tb: null,

                uuid: uuidv4(),
                headerArr: [],
                widths: {
                    name: '35%',
                    col: '65%',
                },
            }
        },
        computed: {
            widths_name() {
                return this.tableMeta.vert_tb_hdrwidth ? this.tableMeta.vert_tb_hdrwidth+'%' : this.widths.name;
            },
            widths_col() {
                return this.tableMeta.vert_tb_hdrwidth ? (100 - this.tableMeta.vert_tb_hdrwidth)+'%' : this.widths.col;
            },
            marker_header() {
                return this.marker_hdr_tb && this.marker_hdr_tb._fields
                    ? _.first(this.marker_hdr_tb._fields)
                    : null;
            },
            tablePartStyle() {
                if (this.imgHeader) {
                    return {
                        paddingRight: '10px',
                        width: (100 - this.tableMeta.map_picture_width)+'%',
                    };
                } else {
                    return {};
                }
            },
            imgHeader() {
                return _.find(this.tableMeta._fields, {id: Number(this.tableMeta.map_picture_field)});
            },
            marker_fields_meta() {
                let fields = this.tableMeta.map_position_refid ? this.marker_fields_tb : this.tableMeta._fields;
                return {
                    _fields: _.filter(fields, (fld) => {
                        return fld.info_box && !fld.is_info_header_field;
                    }),
                }
            },
            hdrBgClr() {
                let bg = this.tableMeta.map_popup_header_color || '#CCC';
                return {
                    backgroundColor: bg,
                    color: SpecialFuncs.smartTextColorOnBg(bg)
                };
            },
        },
        props:{
            tableMeta: Object,
            radiusObject: Object,
            should_redraw: Boolean,
            columnValues: Array,
            request_params: Object,
            user: Object,
        },
        methods: {
            loadingIcon(bool) {
                this.loading = bool;
                this.$nextTick(function () {
                    $(this.$refs.loader).LoadingOverlay(bool ? 'show' : 'hide');
                });
            },

            //Build and Draw Map functions
            loadBounds() {
                let request = this.requestObject();
                axios.post('/ajax/table-data/get-map-bounds', request).then(({data}) => {
                    let can_apply = 0;
                    let bounds = new google.maps.LatLngBounds();
                    //left-top data corner
                    if (isNumber(data.top) && isNumber(data.left)) {
                        can_apply++;
                        bounds.extend( new google.maps.LatLng(data.top, data.left) );
                    }
                    //bottom-right data corner
                    if (isNumber(data.bottom) && isNumber(data.right)) {
                        can_apply++;
                        bounds.extend( new google.maps.LatLng(data.bottom, data.right) );
                    }
                    //finding circle
                    if (this.addressCenter) {
                        let g_circle = new google.maps.Circle({
                            center: this.addressCenter,
                            radius: (this.radiusObject ? this.radiusObject.distance : 1) * 1600,
                        });
                        let c_bounds = g_circle.getBounds();
                        bounds.extend(c_bounds.getSouthWest());
                        bounds.extend(c_bounds.getNorthEast());
                    }
                    //apply
                    if (can_apply === 2) {
                        this.map.panToBounds(bounds);
                        this.map.fitBounds(bounds);
                    }
                    //applied in 'zoom_changed'
                    //this.loadRows();
                }).catch(errors => {
                    Swal('', getErrors(errors));
                });
            },
            loadRows() {
                let request = this.requestObject();
                //transfer zoom for clustering
                request.map_bounds = { zoom: this.map.getZoom() };

                this.loadingIcon(true);
                axios.post('/ajax/table-data/get-map-markers', request).then(({data}) => {
                    this.loadingIcon(false);
                    this.markerRows = data.markers;
                    this.mapWarning = data.warning;
                    this.buildMap();
                }).catch(errors => {
                    $(this.$refs.loader).LoadingOverlay('hide');
                    Swal('', getErrors(errors));
                });
            },
            requestObject() {
                let request = _.cloneDeep(this.request_params);
                request.special_params = SpecialFuncs.specialParams();
                request.page = 1;
                request.rows_per_page = 0;
                request.sort = [];

                if (this.radiusObject.distance && this.addressCenter) {
                    request.radius_search = {
                        km: this.radiusObject.distance * 1.6,
                        center_lat: this.addressCenter.lat(),
                        center_long: this.addressCenter.lng(),
                    };
                }

                //for caching on backend
                request.special_params.list_view_hash = justHash(JSON.stringify(request));

                return request;
            },
            buildMap() {

                //remove markers
                for (let i = 0; i < this.markers.length; i++) {
                    this.markers[i].setMap(null);
                }
                this.markers = [];

                //remove radius circle
                if (this.radius_circle) {
                    this.radius_circle.setMap(null);
                    this.radius_circle = null;
                }

                //draw radius circle if needed
                if (this.addressCenter) {
                    this.radius_circle = new google.maps.Circle({
                        strokeColor: '#FF0000',
                        strokeOpacity: 0.8,
                        strokeWeight: 1,
                        fillColor: '#FF0000',
                        fillOpacity: 0,
                        center: this.addressCenter,
                        radius: this.radiusObject.distance * 1600,
                        clickable: false,
                        map: this.map
                    });
                }

                //for auto zoom and center
                let bounds = new google.maps.LatLngBounds();

                //draw markers
                _.each(this.markerRows, (markerRow) => {
                    let lat_val = parseFloat(markerRow['lat']);
                    let long_val = parseFloat(markerRow['lng']);
                    if (lat_val && long_val/* && this.markerAvailable(lat_val, long_val)*/) {
                        //set settings for marker
                        let m_settings = {
                            position: {lat: lat_val, lng: long_val},
                            map: this.map
                        };

                        //add special icon for marker
                        if (markerRow['id']) {
                            if (this.tableMeta.map_icon_style === 'dist' && this.columnValues.length) {
                                let map_fld = _.find(this.columnValues, {row_val: markerRow['icon']});
                                map_fld = (map_fld && map_fld.icon_path) ? map_fld : _.find(this.columnValues, {row_val: 'Default'});

                                if (map_fld && map_fld.icon_path) {
                                    m_settings.icon = {
                                        url: this.$root.fileUrl({url:map_fld.icon_path})
                                    };

                                    let height = Number(map_fld.height) || 0;
                                    let width = Number(map_fld.width) || 0;
                                    if (height && width) {
                                        m_settings.icon.scaledSize = new google.maps.Size(width, height);
                                    }
                                }
                            }
                            if (this.tableMeta.map_icon_style === 'comp' && markerRow['icon']) {
                                m_settings.icon = {
                                    url: this.$root.fileUrl({url:markerRow['icon']})
                                };
                                m_settings.icon.scaledSize = new google.maps.Size(50, 50);
                            }
                        }
                        //add icon for clusters
                        else {
                            m_settings.label = String(markerRow['cnt']);
                            m_settings.icon = {
                                url: this.$root.app_url + markerRow['icon']
                            };
                        }

                        let marker = new google.maps.Marker(m_settings);

                        bounds.extend( new google.maps.LatLng(marker.position.lat(), marker.position.lng()) );

                        //set info popup for marker
                        if (markerRow['id']) {
                            marker._row_id = markerRow['id'];
                            marker.addListener('click', () => {
                                axios.get('/ajax/table-data/marker-popup', {
                                    params: {
                                        table_id: this.tableMeta.id,
                                        row_id: markerRow['id'],
                                        special_params: SpecialFuncs.specialParams(),
                                    }
                                }).then(({ data }) => {
                                    if (!this.tableMeta.map_multiinfo) {
                                        this.marker_rows_all = [];
                                    }
                                    data.marker_row._row_id = marker._row_id;
                                    this.marker_rows_all.push(data.marker_row);
                                    this.marker_fields_tb = data.marker_fields_tb;
                                    this.marker_hdr_tb = data.marker_hdr_tb;
                                    this.$nextTick(() => {
                                        this.markerInfoWindow(marker, data.marker_row);
                                    });

                                });
                            });
                        } else {
                            //add zoom on cluster click
                            marker.addListener('click', () => {
                                let pt = new google.maps.LatLng(lat_val, long_val);
                                this.map.setCenter(pt);
                                this.map.setZoom( this.map.getZoom()+3 );
                            });
                        }

                        this.markers.push(marker);
                    }
                });
                this.reopenMarkers();

            },
            markerInfoWindow(marker, m_row) {
                marker.InfoWindow = new google.maps.InfoWindow({
                    content: _.first(this.$refs['marker_info_' + m_row._row_id]),
                });
                marker.InfoWindow.addListener('closeclick', () => {
                    this.marker_rows_all = _.filter(this.marker_rows_all, (el) => {
                        return el._row_id != marker._row_id;
                    });
                    this.reopenMarkers();
                });
                marker.InfoWindow.open(this.map, marker);
            },
            reopenMarkers() {
                _.each(this.markers, (marker) => {
                    if (marker._row_id) {
                        //reopen all marker's InfoWindow
                        _.each(this.marker_rows_all, (m_row, mi) => {
                            if (marker._row_id == m_row._row_id) {
                                this.$nextTick(() => {
                                    this.markerInfoWindow(marker, m_row);
                                });
                            }
                        });
                    }
                });
            },
            markerAvailable(lat_val, long_val) {
                let res = true;
                if (this.addressCenter) {
                    let marker_lat_lng = new google.maps.LatLng(lat_val, long_val);
                    //distance in meters between your location and the marker
                    let distance_from_location = google.maps.geometry.spherical.computeDistanceBetween(this.addressCenter, marker_lat_lng);
                    return (distance_from_location <= this.radiusObject.distance*1600);
                }
                return res;
            },

            //marker popup functions
            get_icon(link) {
                let lnk = '';
                switch (link.icon) {
                    case 'Record': lnk = '<i class="fa fa-info-circle"></i>';break;
                    case 'Table': lnk = '<i class="fa fa-link"></i>';break;
                    case 'Web': lnk = '<img src="/assets/img/web_link.png" width="17" height="17">';break;
                    default: lnk = '<i v-else class="link_content">' + link.icon.toUpperCase() + '</i>';break;
                }
                return lnk;
            },
            availableTable(lnk) {
                let res = false;
                if (lnk.table_ref_condition_id) {
                    let i = _.findIndex(this.tableMeta._ref_conditions, {id: Number(lnk.table_ref_condition_id)});
                    if (i > -1) {
                        let table_id = this.tableMeta._ref_conditions[i].ref_table_id;
                        res = !!(_.find(this.$root.settingsMeta.available_tables, {id: Number(table_id)}));
                    }
                }
                return res;
            },

            //recalc Address Center functions (Only for RadiusButton)
            recalcSearchCenter() {
                //prepare center Address for searching
                if (this.radiusObject.decimal.lat === null && this.radiusObject.decimal.long === null) {
                    this.addressCenter = null;
                } else {
                    let center_lat = this.$root.getFloat(this.radiusObject.decimal.lat);
                    let center_long = this.$root.getFloat(this.radiusObject.decimal.long);
                    this.addressCenter = new google.maps.LatLng(center_lat, center_long);
                }
                this.loadBounds();
            },

            showSrcRecord(lnk, header, tableRow) {
                this.$emit('show-src-record', lnk, header, tableRow);
            },

            globalKeyHandler(e) {
                if (e.keyCode === 27) {//esc - hide marker's infoBoxes
                    _.each(this.markers, (mrk) => {
                        if (mrk.InfoWindow) {
                            mrk.InfoWindow.close();
                        }
                    });
                    this.marker_rows_all = [];
                }
            },

            //mapker popup
            getHeader(name) {
                return _.last(name.split(','));
            },
            getSubHeaders(name) {
                let arr = _.uniq( name.split(',') );
                let res = '';
                if (arr.length > 1) {
                    _.each(arr, (el, idx) => {
                        res += '<div style="position: relative;">';
                        if (idx < (arr.length-1) && (!this.headerArr[idx] || this.headerArr[idx] !== el)) {
                            this.headerArr[idx] = el;
                            if (el.trim()) {
                                res += '<label style="margin-top: 5px;">' + this.$root.strip_tags(el) + '</label>';
                            }
                        }
                        res += '</div>';
                    });
                } else {
                    this.headerArr.length = 0;
                }
                return res;
            },
        },
        mounted() {
            this.map = new google.maps.Map(this.$refs.map_google, {
                zoom: 5,
                center: new google.maps.LatLng(0, 0)//USA center
            });
            this.map.addListener('dragend', () => {
                this.loadRows(true);
            });
            this.map.addListener('zoom_changed', () => {
                this.loadRows(true);
            });

            eventBus.$on('should-redraw-map', this.loadBounds);
            eventBus.$on('run-search-on-map', this.recalcSearchCenter);
            eventBus.$on('global-keydown', this.globalKeyHandler);
        },
        beforeDestroy() {
            eventBus.$off('should-redraw-map', this.loadBounds);
            eventBus.$off('run-search-on-map', this.recalcSearchCenter);
            eventBus.$off('global-keydown', this.globalKeyHandler);
        }
    }
</script>

<style lang="scss" scoped>
    .loader {
        position: absolute;
        z-index: 500;
        width: 50px;
        height: 50px;
        right: 0;
    }
    .map-warning {
        position: absolute;
        right: 60px;
        z-index: 150;
        top: 7px;
    }

    .iw-body {
        .table_part {
            overflow-x: hidden;
            overflow-y: auto;
        }
        .attach_part {
            background-color: #EEE;
            position: relative;
            overflow: auto;
        }
    }
</style>