<template>
    <div class="full-height">
        <div :style="{zIndex: loading ? 500 : -1}" ref="loader" class="loader"></div>
        <div ref="map_google" class="full-height"></div>

        <template v-for="(marker_row,idx) in marker_rows_all">
            <div :ref="'marker_info_'+marker_row._row_id" class="iw-wrapper flex flex--col">
                <h4 v-if="marker_hdr && marker_row" class="iw-header">
                    <span>{{ $root.uniqName(marker_hdr.name) + ': ' }}</span>
                    <span style="display: inline;">
                        <cell-table-content
                                :global-meta="tableMeta"
                                :table-meta="tableMeta"
                                :table-row="marker_row"
                                :table-header="marker_hdr"
                                :cell-value="marker_row[marker_hdr.field]"
                                :user="user"
                                :inline="true"
                                @show-src-record="showSrcRecord"
                        ></cell-table-content>
                    </span>
                </h4>

                <div v-if="marker_fields.length && marker_row" class="iw-body flex flex--elem_remain">
                    <table>
                        <colgroup>
                            <col :width="widths_name">
                            <col :width="widths_col">
                        </colgroup>
                        <template v-for="i_hdr in marker_fields">
                            <tr v-if="getSubHeaders(i_hdr.name)">
                                <td><div v-html="getSubHeaders(i_hdr.name)"></div></td>
                                <td></td>
                            </tr>
                            <tr>
                                <td style="text-align: right">
                                    <label :style="textStyle" style="padding-left: 10px;">{{ getHeader(i_hdr.name) }}:&nbsp;</label>
                                </td>
                                <td :is="$root.tdCellComponent(tableMeta.is_system)"
                                    :global-meta="tableMeta"
                                    :table-meta="tableMeta"
                                    :settings-meta="$root.settingsMeta"
                                    :table-row="marker_row"
                                    :table-header="i_hdr"
                                    :cell-value="marker_row[i_hdr.field]"
                                    :user="user"
                                    :cell-height="$root.cellHeight"
                                    :max-cell-rows="$root.maxCellRows"
                                    :row-index="-1"
                                    :table_id="tableMeta.id"
                                    :behavior="'list_view'"
                                    :with_edit="false"
                                    @show-src-record="showSrcRecord"
                                ></td>
                            </tr>
                        </template>
                    </table>
                </div>
            </div>
        </template>

    </div>
</template>

<script>
    import {eventBus} from '../../../../../app';

    import CellStyleMixin from '../../../../_Mixins/CellStyleMixin.vue';
    import TableLinkMixin from '../../../../_Mixins/TableLinkMixin.vue';

    import LinkIcon from "../../../../CustomCell/InCell/LinkIcon.vue";
    import CellTableContent from "../../../../CustomCell/InCell/CellTableContent.vue";
    import VerticalTable from "../../../../CustomTable/VerticalTable";
    import CustomCellTableData from '../../../../CustomCell/CustomCellTableData.vue';
    import CustomCellSystemTableData from '../../../../CustomCell/CustomCellSystemTableData.vue';
    import CustomCellCorrespTableData from '../../../../CustomCell/CustomCellCorrespTableData.vue';

    export default {
        name: "MapElem",
        components: {
            CustomCellTableData,
            CustomCellSystemTableData,
            CustomCellCorrespTableData,
            VerticalTable,
            CellTableContent,
            LinkIcon,
        },
        mixins: [
            CellStyleMixin,
            TableLinkMixin,
        ],
        provide() {
            return {
                reactive_provider: {
                    behavior: 'list_view'
                }
            };
        },
        data: function () {
            return {
                map: null,
                markers: [],
                addressCenter: null,
                radius_circle: null,
                markerRows: [],
                loading: false,

                marker_hdr: null,
                marker_rows_all: [],
                marker_fields: [],

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
                let request = _.cloneDeep(this.request_params);
                request.page = 1;
                request.rows_per_page = 0;
                request.sort = [];

                axios.post('/ajax/table-data/get-map-bounds', request).then(({data}) => {
                    let can_apply = 0;
                    let bounds = new google.maps.LatLngBounds();
                    //left-top data corner
                    if (data.top && data.left) {
                        can_apply++;
                        bounds.extend( new google.maps.LatLng(data.top, data.left) );
                    }
                    //bottom-right data corner
                    if (data.bottom && data.right) {
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
                    //this.loadRows(); will be applied on 'Zoom Changed'
                }).catch(errors => {
                    Swal('', getErrors(errors));
                });
            },
            loadRows() {
                let request = _.cloneDeep(this.request_params);
                request.page = 1;
                request.rows_per_page = 0;
                request.sort = [];

                let bnds = this.map.getBounds();
                request.map_bounds = {
                    zoom: this.map.getZoom(),
                    left_bottom: bnds.getSouthWest(),
                    right_top: bnds.getNorthEast()
                };

                this.loadingIcon(true);
                axios.post('/ajax/table-data/get-map-markers', request).then(({data}) => {
                    this.loadingIcon(false);
                    this.markerRows = data;
                    this.buildMap();
                }).catch(errors => {
                    $(this.$refs.loader).LoadingOverlay('hide');
                    Swal('', getErrors(errors));
                });
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

                let lat_header = _.find(this.tableMeta._fields, {is_lat_field: 1});
                let long_header = _.find(this.tableMeta._fields, {is_long_field: 1});

                //get headers for info-box
                this.marker_hdr = _.find(this.tableMeta._fields, {is_info_header_field: 1});
                this.marker_fields = [];
                _.each(this.tableMeta._fields, (hdr) => {
                    if (hdr.info_box && !hdr.is_info_header_field) {
                        this.marker_fields.push(hdr);
                    }
                });

                //draw markers
                if (lat_header && long_header) {
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
                            if (markerRow['id'] && (this.marker_fields.length || this.marker_hdr)) {
                                marker._row_id = markerRow['id'];
                                marker.addListener('click', () => {
                                    axios.get('/ajax/table-data/marker-popup', {
                                        params: {
                                            table_id: this.tableMeta.id,
                                            row_id: markerRow['id']
                                        }
                                    }).then(({ data }) => {
                                        if (!this.tableMeta.map_multiinfo) {
                                            this.marker_rows_all = [];
                                        }
                                        data._row_id = marker._row_id;
                                        this.marker_rows_all.push(data);
                                        this.$nextTick(() => {
                                            this.markerInfoWindow(marker, data);
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
                }

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
                    if (marker._row_id && (this.marker_fields.length || this.marker_hdr)) {
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
            recalcAddressCenter() {
                //prepare center Address for searching
                this.addressCenter = null;
                if (this.radiusObject.distance && this.radiusObject.type) {
                    let center_lat = this.$root.getFloat(this.radiusObject.decimal.lat);
                    let center_long = this.$root.getFloat(this.radiusObject.decimal.long);
                    let query = '';
                    if (!center_lat || !center_long) {

                        query = String(this.radiusObject.address.street)
                            + (this.radiusObject.address.city ? ', '+String(this.radiusObject.address.city) : '')
                            + (this.radiusObject.address.state ? ', '+String(this.radiusObject.address.state) : '')
                            + (this.radiusObject.address.county ? ', '+String(this.radiusObject.address.county) : '')
                            + (this.radiusObject.address.zip ? ', '+String(this.radiusObject.address.zip) : '');

                        if (query && this.$root.user.__google_table_api) {
                            let request = {
                                query: query,
                                fields: ['name', 'geometry'],
                            };
                            let service = new google.maps.places.PlacesService(this.map);
                            service.findPlaceFromQuery(request, (results, status) => {
                                if (status === google.maps.places.PlacesServiceStatus.OK && results.length) {
                                    this.addressCenter = results[0].geometry.location;
                                } else {
                                    Swal('Address not found');
                                }
                                eventBus.$emit('new-address-center', this.addressCenter);
                            });
                        }

                    } else {
                        this.addressCenter = new google.maps.LatLng(center_lat, center_long);
                        eventBus.$emit('new-address-center', this.addressCenter);
                    }

                } else {
                    eventBus.$emit('new-address-center', this.addressCenter);
                }
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
            eventBus.$on('recalc-address-center', this.recalcAddressCenter);
            eventBus.$on('global-keydown', this.globalKeyHandler);
        },
        beforeDestroy() {
            eventBus.$off('should-redraw-map', this.loadBounds);
            eventBus.$off('recalc-address-center', this.recalcAddressCenter);
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
</style>