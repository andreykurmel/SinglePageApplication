<template>
    <div title="Search in columns" class="radius_wrapper">
        <div class="map_radius_elem">
            <button class="btn btn-primary btn-sm blue-gradient"
                    :style="$root.themeButtonStyle"
                    :title="$root.user.__google_table_api ? '' : 'Please Add API Key in Settings'"
                    @click="searchOnMap()"
            >Find</button>
        </div>
        <div class="map_radius_elem">
            <label>items within: </label>
        </div>
        <div class="map_radius_elem">
            <input class="form-control input-sm small-input" type="text" placeholder="Radius" v-model="radiusObject.distance" @keydown="enterToFind"/>
        </div>
        <div class="map_radius_elem">
            <label>miles from</label>
        </div>
        <div class="map_radius_elem" ref="search_button">
            <input class="form-control input-sm d-inline-block w-150"
                   type="text"
                   placeholder="a location"
                   :value="radiusObject.entered_address"
                   @focus="openMenu()"
            />
            <button v-if="radiusObject.entered_address"
                    class="btn btn-danger btn-sm btn-deletable flex flex--center d-inline-block"
                    style="top: -1px; position: relative;padding: 0 5px;"
                    @click="clearSearch()">
                <span style="font-size: 2.2em;line-height: 1em;">×</span>
            </button>

            <div v-show="menu_opened" class="map_radius_menu">
                <div class="menu-header">
                    <button class="btn btn-default btn-sm" :class="{active : activeTab === 'address'}" @click="activeTab = 'address'">
                        Address
                    </button>
                    <button class="btn btn-default btn-sm" :class="{active : activeTab === 'latlng'}" @click="activeTab = 'latlng'">
                        Lat. & Long.
                    </button>
                    <button class="btn btn-default btn-sm" :class="{active : activeTab === 'data'}" @click="activeTab = 'data'">
                        Data
                    </button>
                </div>
                <div class="menu-body">

                    <div v-show="activeTab === 'address'" style="white-space: nowrap;">
                        <div class="row form-group">
                            <!--<div class="col-xs-2">-->
                                <!--<label>Full</label>-->
                            <!--</div>-->
                            <div class="col-xs-12" style="position: relative">
                                <input ref="addr_wrapper"
                                       class="full-width"
                                       @focus="show_google_address = true"
                                       v-model="radiusObject.address.full"/>
                                <div v-if="show_google_address" class="google_addr">
                                    <google-address-viewer
                                            :table-meta="googleTableMeta"
                                            :table-row="radiusObject.address"
                                            :table-header="googleTbHeader"
                                            @update-row="googleSelectDone"
                                            @hide-elem="googleHideInput"
                                    ></google-address-viewer>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div v-show="activeTab === 'latlng'">
                        <div class="row form-group">
                            <div class="col-xs-2">
                                <label>Latitude</label>
                            </div>
                            <div class="col-xs-2">
                                <input v-model="radiusObject.dms.lat_d" class="full-width" placeholder="Degree" @change="filledObject('dms')"/>
                            </div>
                            <div class="col-xs-2">
                                <input v-model="radiusObject.dms.lat_m" class="full-width" placeholder="Minutes" @change="filledObject('dms')"/>
                            </div>
                            <div class="col-xs-2">
                                <input v-model="radiusObject.dms.lat_s" class="full-width" placeholder="Seconds" @change="filledObject('dms')"/>
                            </div>
                            <div class="col-xs-1">
                                <label>OR</label>
                            </div>
                            <div class="col-xs-3">
                                <input v-model="radiusObject.decimal.lat" class="full-width" placeholder="Decimal" @change="filledObject('decimal')"/>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-xs-2">
                                <label>Longitude</label>
                            </div>
                            <div class="col-xs-2">
                                <input v-model="radiusObject.dms.long_d" class="full-width" placeholder="Degree" @change="filledObject('dms')"/>
                            </div>
                            <div class="col-xs-2">
                                <input v-model="radiusObject.dms.long_m" class="full-width" placeholder="Minutes" @change="filledObject('dms')"/>
                            </div>
                            <div class="col-xs-2">
                                <input v-model="radiusObject.dms.long_s" class="full-width" placeholder="Seconds" @change="filledObject('dms')"/>
                            </div>
                            <div class="col-xs-1">
                                <label>OR</label>
                            </div>
                            <div class="col-xs-3">
                                <input v-model="radiusObject.decimal.long" class="full-width" placeholder="Decimal" @change="filledObject('decimal')"/>
                            </div>
                        </div>
                    </div>

                    <div v-show="activeTab === 'data'">
                        <div class="row form-group map-data-tab">
                            <div class="col-xs-12 map-data-tab__search">
                                <label class="map-data-tab__search-label">Select a data record</label>
                            </div>
                            <div class="col-xs-1">
                                <i class="fa fa-info-circle" @click="showPopup()"></i>
                            </div>
                            <div class="col-xs-10 map-data-tab__search">
                                <select ref="search_data" class="form-control"></select>
                            </div>
                            <div class="col-xs-1" ref="col_menu">
                                <i class="glyphicon glyphicon-cog" @click="col_menu_opened  = !col_menu_opened"></i>
                                <fields-checker
                                        v-if="metaData"
                                        v-show="col_menu_opened"
                                        class="map-data-tab__search_menu"
                                        :table-meta="metaData"
                                        :all-checked="allChecked"
                                        :check-func="checkFunc"
                                        @toggle-all="toggleAll"
                                        @toggle-column="toggleColumn"
                                ></fields-checker>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import {eventBus} from '../../app';

    import {SpecialFuncs} from '../../classes/SpecialFuncs';

    import FieldsChecker from "../CommonBlocks/FieldsChecker.vue";
    import GoogleAddressViewer from "../CustomCell/InCell/GoogleAddressViewer";

    export default {
        components: {
            GoogleAddressViewer,
            FieldsChecker,
        },
        name: "MapSettings",
        mixins: [
        ],
        data: function () {
            return {
                metaData: null,
                search_columns: [],
                search_results: [],

                menu_opened: false,
                col_menu_opened: false,
                activeTab: 'address',

                show_google_address: false,
                googleTableMeta: {
                    address_fld__source_id: 1,
                    address_fld__street_address: 10,
                    address_fld__street: 11,
                    address_fld__city: 12,
                    address_fld__state: 13,
                    address_fld__countyarea: 14,
                    address_fld__zipcode: 15,
                    address_fld__lat: 16,
                    address_fld__long: 17,
                    _fields: [
                        { id:10, field: '_number' },
                        { id:11, field: 'street' },
                        { id:12, field: 'city' },
                        { id:13, field: 'state' },
                        { id:14, field: 'county' },
                        { id:15, field: 'zip' },
                        { id:16, field: 'lat' },
                        { id:17, field: 'long' },
                    ],
                },
                googleTbHeader: { id:1, field: 'full' },
            }
        },
        props:{
            radiusObject: Object,
            selectedMap: Object,
            tableMeta: Object,
        },
        computed: {
            allChecked() {
                return this.metaData
                    && this.search_columns.length === this.metaData._fields.length
                    ? 2
                    : this.metaData && this.search_columns.length > 0 ? 1 : 0;
            }
        },
        methods: {
            enterToFind() {
                if (window.event.keyCode === 13 && !window.event.shiftKey) {
                    this.$nextTick(() => {
                        this.searchOnMap();
                    });
                }
            },
            showPopup() {
                let selected = _.find(this.search_results, {id: Number($(this.$refs.search_data).val())});
                if (selected) {
                    eventBus.$emit('show-popup', selected.row);
                }
            },
            clearSearch() {
                this.radiusObject.entered_address = null;
                this.$emit('clear-radius');
                $(this.$refs.search_data).val(null).trigger('change');
                this.$nextTick(() => {
                    this.searchOnMap();
                });
            },
            checkFunc(header) {
                return $.inArray(header.field, this.search_columns) > -1;
            },
            toggleColumn(hdr) {
                let field = hdr.field;
                let idx = _.findIndex(this.search_columns, function(el) { return el === field; });
                if (idx > -1) {
                    this.search_columns.splice(idx, 1);
                } else {
                    this.search_columns.push(field);
                }
            },
            toggleAll() {
                if (this.allChecked) {
                    this.search_columns = [];
                } else {
                    this.search_columns = _.map(this.metaData._fields, 'field');
                }
            },

            //show functions
            openMenu() {
                this.menu_opened = !this.menu_opened;
            },
            hideAll() {
                this.menu_opened = false;
                this.sub_menu_opened = false;
                this.address_menu = false;
            },
            hideMenu(e) {
                let container = $(this.$refs.search_button);
                if (container.has(e.target).length === 0 && this.menu_opened){
                    this.hideAll();
                }

                container = $(this.$refs.col_menu);
                if (container.has(e.target).length === 0 && this.col_menu_opened){
                    this.col_menu_opened = !this.col_menu_opened;
                }
            },

            //Searching: fill RadiusObject
            filledObject(name, from_search) {

                if (!from_search) {
                    $(this.$refs.search_data).val(null).trigger('change');
                }

                this.radiusObject.type = name;
                switch (name) {
                    case 'address':
                        this.radiusObject.decimal.lat = this.radiusObject.address.lat;
                        this.radiusObject.decimal.long = this.radiusObject.address.long;
                        this.radiusObject.entered_address = this.getFullAddr();
                        this.radiusObject.address.full = this.radiusObject.entered_address;
                        this.calcDMS();
                        break;

                    case 'dms':
                        //calc entered value
                        this.radiusObject.entered_address = this.$root.getFloat(this.radiusObject.dms.lat_d) + '°'
                            + this.$root.getFloat(this.radiusObject.dms.lat_m) + '"'
                            + this.$root.getFloat(this.radiusObject.dms.lat_s) + '\''
                            + ', '
                            + this.$root.getFloat(this.radiusObject.dms.long_d) + '°'
                            + this.$root.getFloat(this.radiusObject.dms.long_m) + '"'
                            + this.$root.getFloat(this.radiusObject.dms.long_s) + '\'';
                        //auto-calc near values
                        this.radiusObject.decimal.lat = this.$root.getFloat(this.radiusObject.dms.lat_d)
                            + this.$root.getFloat(this.radiusObject.dms.lat_m)/60
                            + this.$root.getFloat(this.radiusObject.dms.lat_s)/3600;
                        this.radiusObject.decimal.long = this.$root.getFloat(this.radiusObject.dms.long_d)
                            + this.$root.getFloat(this.radiusObject.dms.long_m)/60
                            + this.$root.getFloat(this.radiusObject.dms.long_s)/3600;
                        break;

                    case 'decimal':
                        //calc entered value
                        this.radiusObject.entered_address = this.$root.getFloat(this.radiusObject.decimal.lat)
                            + ', ' + this.$root.getFloat(this.radiusObject.decimal.long);
                        this.calcDMS();
                        break;
                }
            },
            calcDMS() {
                let lat = this.$root.getFloat(this.radiusObject.decimal.lat);
                let long = this.$root.getFloat(this.radiusObject.decimal.long);
                this.radiusObject.dms.lat_d = parseInt(lat);
                this.radiusObject.dms.lat_m = parseInt((lat - this.radiusObject.dms.lat_d) * 60);
                this.radiusObject.dms.lat_s = parseInt((lat*60 - (this.radiusObject.dms.lat_d*60 + this.radiusObject.dms.lat_m)) * 60);
                this.radiusObject.dms.long_d = parseInt(long);
                this.radiusObject.dms.long_m = parseInt((long - this.radiusObject.dms.long_d) * 60);
                this.radiusObject.dms.long_s = parseInt((long*60 - (this.radiusObject.dms.long_d*60 + this.radiusObject.dms.long_m)) * 60);
            },
            getFullAddr() {
                let full_addr = [];
                (this.radiusObject.address.street ? full_addr.push( String(this.radiusObject.address.street) ) : null);
                (this.radiusObject.address.city ? full_addr.push( String(this.radiusObject.address.city) ) : null);
                (this.radiusObject.address.state ? full_addr.push( String(this.radiusObject.address.state) ) : null);
                (this.radiusObject.address.county ? full_addr.push( String(this.radiusObject.address.county) ) : null);
                (this.radiusObject.address.zip ? full_addr.push( String(this.radiusObject.address.zip) ) : null);
                return full_addr.join(', ');
            },

            //Run Search Process
            searchOnMap() {
                this.hideAll();
                this.radiusObject.distance = this.radiusObject.distance || 1;
                eventBus.$emit('run-search-on-map');
            },

            //Google search tab
            googleSelectDone(row) {
                if (this.radiusObject.address._number) {
                    this.radiusObject.address.street = this.radiusObject.address._number + ', ' + this.radiusObject.address.street;
                }
                this.filledObject('address');
                this.show_google_address = false;
            },
            googleHideInput() {
                this.show_google_address = false;
            },
        },
        mounted() {
            this.metaData = this.tableMeta;
            this.search_columns = _.map( _.filter(this.metaData._fields, {is_search_autocomplete_display: 1}) , 'field');

            $(this.$refs.search_data).select2({
                ajax: {
                    type: 'POST',
                    params: {
                        contentType: "application/json; charset=utf-8",
                    },
                    url: '/ajax/table-data/search-on-map',
                    dataType: 'json',
                    delay: 250,
                    data: (params) => {
                        return {
                            map_id: this.selectedMap.id,
                            term: params.term,
                            columns: this.search_columns || [],
                            special_params: SpecialFuncs.specialParams(),
                        };
                    },
                    processResults: (data) => {
                        this.search_results = data.results;
                        return data;
                    }
                },
                minimumInputLength: {val:3, msg:'Please enter 3 or more characters.'},
                width: '100%',
                height: '100%',
                closeOnSelect: true,
            }).on('select2:close', (e) => {
                let selected = _.find(this.search_results, {id: Number($(this.$refs.search_data).val())});
                if (selected) {
                    this.radiusObject.decimal.lat = selected.lat || 0;
                    this.radiusObject.decimal.long = selected.long || 0;
                    this.filledObject('decimal', true);
                }
            });
            $(this.$refs.search_data).next().css('height', '28px');

            eventBus.$on('global-click', this.hideMenu);
        },
        beforeDestroy() {
            eventBus.$off('global-click', this.hideMenu);
        }
    }
</script>

<style lang="scss" scoped>
    .radius_wrapper {
        margin-right: 10px;

        .map_radius_elem {
            display: inline-block;
            position: relative;

            .map_radius_menu {
                position: absolute;
                right: 100%;
                top: 100%;
                width: 420px;
                z-index: 500;
                background-color: #FFF;
                padding: 5px;
                border: 1px solid #CCC;
                border-radius: 5px;

                .menu-header {
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
                }

                .menu-body {
                    position: absolute;
                    top: 32px;
                    right: 0;
                    left: 0;
                    background-color: #FFF;
                    padding: 5px 15px;
                    border: 1px solid #CCC;
                    text-align: right;

                    .col-xs-1, .col-xs-2, .col-xs-3, .col-xs-4, .col-xs-5, .col-xs-6, .col-xs-7, .col-xs-8, .col-xs-9, .col-xs-10, .col-xs-11, .col-xs-12 {
                        padding-left: 5px;
                        padding-right: 5px;

                        label {
                            margin-bottom: 0;
                            margin-top: 3px;
                        }
                        input {
                            padding: 1px 3px;
                        }
                    }

                    .form-group {
                        margin-bottom: 5px;
                    }
                }
            }

            .small-input {
                width: 60px;
            }

            .map-data-tab {
                .glyphicon-cog {
                    font-size: 20px;
                    font-weight: bold;
                    display: flex;
                    padding-top: 3px;
                    cursor: pointer;
                }
                .fa-info-circle {
                    font-size: 20px;
                    padding-top: 4px;
                    cursor: pointer;
                }

                .map-data-tab__search {
                    text-align: left;

                    .map-data-tab__search-label {
                        font-size: 14px;
                    }
                }

                .map-data-tab__search_menu {
                    position: absolute;
                    right: 100%;
                    top: 0;
                    z-index: 99999;

                    max-height: 500px;
                    overflow: auto;
                    padding: 5px;
                    background-color: #EEE;
                    border: 1px solid #777;
                    border-radius: 5px;
                    text-align: left;
                    font-size: 1.7em;
                }
            }

            .miles-label {
                position: absolute;
                right: 7px;
                top: 5px;
                font-weight: bold;
            }
        }
    }
    .google_addr {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
    }
</style>