<template>
    <div title="Search in columns" class="radius_wrapper">
        <div class="map_radius_elem">
            <button class="btn btn-primary btn-sm blue-gradient"
                    :style="$root.themeButtonStyle"
                    :title="$root.user.__google_table_api ? '' : 'Please Add API Key in Settings'"
                    @click="recalcAddressCenter()"
            >Find</button>
        </div>
        <div class="map_radius_elem">
            <label>Items within: </label>
        </div>
        <div class="map_radius_elem">
            <input class="form-control input-sm small-input" type="text" placeholder="Radius, miles" v-model="radiusObject.distance"/>
        </div>
        <div class="map_radius_elem">
            <label>From</label>
        </div>
        <div class="map_radius_elem" ref="search_button">
            <input class="form-control input-sm"
                   type="text"
                   placeholder="Entered address"
                   :value="entered_address"
                   @focus="openMenu()"
            />
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

                    <div v-show="activeTab === 'address'">
                        <div class="row form-group">
                            <div class="col-xs-2">
                                <label>Street</label>
                            </div>
                            <div class="col-xs-10">
                                <input v-model="radiusObject.address.street"
                                       name="address"
                                       class="full-width"
                                       :disabled="!$root.user.__google_table_api"
                                       @change="filledObject('address')"/>
                            </div>
                        </div>
                        <div class="row form-group">
                            <div class="col-xs-2">
                                <label>City</label>
                            </div>
                            <div class="col-xs-5">
                                <input v-model="radiusObject.address.city"
                                       name="city"
                                       class="full-width"
                                       :disabled="!$root.user.__google_table_api"
                                       @change="filledObject('address')"/>
                            </div>
                            <div class="col-xs-2">
                                <label>State</label>
                            </div>
                            <div class="col-xs-3">
                                <input v-model="radiusObject.address.state"
                                       name="state"
                                       class="full-width"
                                       :disabled="!$root.user.__google_table_api"
                                       @change="filledObject('address')"/>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-xs-2">
                                <label>County</label>
                            </div>
                            <div class="col-xs-5">
                                <input v-model="radiusObject.address.county"
                                       name="county"
                                       class="full-width"
                                       :disabled="!$root.user.__google_table_api"
                                       @change="filledObject('address')"/>
                            </div>
                            <div class="col-xs-2">
                                <label>ZIP Code</label>
                            </div>
                            <div class="col-xs-3">
                                <input v-model="radiusObject.address.zip"
                                       name="zip"
                                       class="full-width"
                                       :disabled="!$root.user.__google_table_api"
                                       @change="filledObject('address')"/>
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
                                        v-if="tableMeta"
                                        v-show="col_menu_opened"
                                        class="map-data-tab__search_menu"
                                        :table-meta="tableMeta"
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
    import {eventBus} from './../../app';

    import FieldsChecker from "../CommonBlocks/FieldsChecker.vue";

    export default {
        components: {
            FieldsChecker
        },
        name: "SearchButton",
        mixins: [
        ],
        data: function () {
            return {
                entered_address: null,
                search_columns: _.map(this.tableMeta._fields, 'field'),
                search_results: [],

                menu_opened: false,
                col_menu_opened: false,
                activeTab: 'address'
            }
        },
        props:{
            radiusObject: Object,
            table_id: Number,
            tableMeta: Object,
        },
        watch: {
            table_id: function(val) {
                this.entered_address = null;
            }
        },
        computed: {
            allChecked() {
                return this.tableMeta
                    && this.search_columns.length === this.tableMeta._fields.length
                    ? 2
                    : this.tableMeta && this.search_columns.length > 0 ? 1 : 0;
            }
        },
        methods: {
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
                    this.search_columns = _.map(this.tableMeta._fields, 'field');
                }
            },
            
            openMenu() {
                this.menu_opened = !this.menu_opened;
            },
            toggleRadius() {
                this.menu_opened = false;
                this.sub_menu_opened = false;
                this.address_menu = false;
            },
            hideMenu(e) {
                let container = $(this.$refs.search_button);
                if (container.has(e.target).length === 0 && this.menu_opened){
                    this.toggleRadius();
                }

                container = $(this.$refs.col_menu);
                if (container.has(e.target).length === 0 && this.col_menu_opened){
                    this.col_menu_opened = !this.col_menu_opened;
                }
            },
            filledObject(name, from_search) {

                if (!from_search) {
                    $(this.$refs.search_data).val(null).trigger('change');
                }

                this.radiusObject.type = name;
                switch (name) {
                    case 'address':
                        this.radiusObject.dms = {
                            lat_d: null,
                            lat_m: null,
                            lat_s: null,
                            long_d: null,
                            long_m: null,
                            long_s: null,
                        };
                        this.radiusObject.decimal = {
                            lat: null,
                            long: null,
                        };
                        this.entered_address = [];
                        (this.radiusObject.address.street ? this.entered_address.push( String(this.radiusObject.address.street) ) : null);
                        (this.radiusObject.address.city ? this.entered_address.push( String(this.radiusObject.address.city) ) : null);
                        (this.radiusObject.address.state ? this.entered_address.push( String(this.radiusObject.address.state) ) : null);
                        (this.radiusObject.address.county ? this.entered_address.push( String(this.radiusObject.address.county) ) : null);
                        (this.radiusObject.address.zip ? this.entered_address.push( String(this.radiusObject.address.zip) ) : null);
                        this.entered_address = this.entered_address.join(', ');
                        break;

                    case 'dms':
                        this.radiusObject.address = {
                            street: null,
                            city: null,
                            state: null,
                            county: null,
                            zip: null,
                        };
                        //calc entered value
                        this.entered_address = this.$root.getFloat(this.radiusObject.dms.lat_d) + '°'
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
                        this.radiusObject.address = {
                            street: null,
                            city: null,
                            state: null,
                            county: null,
                            zip: null,
                        };
                        //calc entered value
                        this.entered_address = this.$root.getFloat(this.radiusObject.decimal.lat)
                            + ', ' + this.$root.getFloat(this.radiusObject.decimal.long);
                        //auto-calc near values
                        let lat = this.$root.getFloat(this.radiusObject.decimal.lat);
                        let long = this.$root.getFloat(this.radiusObject.decimal.long);
                        this.radiusObject.dms.lat_d = parseInt(lat);
                        this.radiusObject.dms.lat_m = parseInt((lat - this.radiusObject.dms.lat_d) * 60);
                        this.radiusObject.dms.lat_s = parseInt((lat*60 - (this.radiusObject.dms.lat_d*60 + this.radiusObject.dms.lat_m)) * 60);
                        this.radiusObject.dms.long_d = parseInt(long);
                        this.radiusObject.dms.long_m = parseInt((long - this.radiusObject.dms.long_d) * 60);
                        this.radiusObject.dms.long_s = parseInt((long*60 - (this.radiusObject.dms.long_d*60 + this.radiusObject.dms.long_m)) * 60);
                        break;
                }
            },
            recalcAddressCenter() {
                this.radiusObject.distance = this.radiusObject.distance || 1;
                eventBus.$emit('recalc-address-center');
            },
            showPopup() {
                let selected = _.find(this.search_results, {id: Number($(this.$refs.search_data).val())});
                if (selected) {
                    eventBus.$emit('show-popup', selected.row);
                }
            },
            fillAddress(selectedRow) {
                this.fillField(selectedRow, 'street');
                this.fillField(selectedRow, 'city');
                this.fillField(selectedRow, 'state');
                this.fillField(selectedRow, 'county');
                this.fillField(selectedRow, 'zip');
            },
            fillField(selectedRow, col) {
                let fill_field = null;
                switch (col) {
                    case 'street': fill_field = _.find(this.tableMeta._fields, {map_find_street_field: 1}); break;
                    case 'city': fill_field = _.find(this.tableMeta._fields, {map_find_city_field: 1}); break;
                    case 'state': fill_field = _.find(this.tableMeta._fields, {map_find_state_field: 1}); break;
                    case 'county': fill_field = _.find(this.tableMeta._fields, {map_find_county_field: 1}); break;
                    case 'zip': fill_field = _.find(this.tableMeta._fields, {map_find_zip_field: 1}); break;
                }

                if (!fill_field) {
                    _.each(this.tableMeta._fields, (fld) => {
                        if (RegExp(col,'i').test(fld.name)) {
                            fill_field = fld;
                        }
                    });
                }

                if (fill_field) {
                    this.radiusObject.address[col] = selectedRow[fill_field.field];
                }
            },

            newAddressCenterHandler(location) {
                let lat = 0, long = 0, radius_search = {km: 0};
                if (location) {
                    lat = location.lat();
                    long = location.lng();

                    this.radiusObject.decimal.lat = lat;
                    this.radiusObject.decimal.long = long;
                    this.radiusObject.dms.lat_d = parseInt(lat);
                    this.radiusObject.dms.lat_m = parseInt((lat - this.radiusObject.dms.lat_d) * 60);
                    this.radiusObject.dms.lat_s = parseInt((lat*60 - (this.radiusObject.dms.lat_d*60 + this.radiusObject.dms.lat_m)) * 60);
                    this.radiusObject.dms.long_d = parseInt(long);
                    this.radiusObject.dms.long_m = parseInt((long - this.radiusObject.dms.long_d) * 60);
                    this.radiusObject.dms.long_s = parseInt((long*60 - (this.radiusObject.dms.long_d*60 + this.radiusObject.dms.long_m)) * 60);

                    radius_search = {
                        km: this.radiusObject.distance*1.6,
                        center_lat: lat,
                        center_long: long,
                    };
                }
                eventBus.$emit('new-search-circle', radius_search);
            }
        },
        mounted() {
            $(this.$refs.search_data).select2({
                ajax: {
                    type: 'POST',
                    params: {
                        contentType: "application/json; charset=utf-8",
                    },
                    url: '/ajax/table-data/search',
                    dataType: 'json',
                    delay: 250,
                    data: (params) => {
                        return {
                            table_id: this.table_id,
                            term: params.term,
                            columns: this.search_columns || []
                        };
                    },
                    processResults: (data) => {
                        this.search_results = data.results;
                        return data;
                    }
                },
                minimumInputLength: 3,
                width: '100%',
                height: '100%',
                closeOnSelect: true,
            }).on('select2:close', (e) => {
                let selected = _.find(this.search_results, {id: Number($(this.$refs.search_data).val())});
                if (selected) {
                    this.radiusObject.decimal.lat = selected.lat || 0;
                    this.radiusObject.decimal.long = selected.long || 0;
                    this.filledObject('decimal', true);
                    //this.menu_opened = false;
                    this.fillAddress(selected.row);
                }
            });
            $(this.$refs.search_data).next().css('height', '28px');

            eventBus.$on('global-click', this.hideMenu);
            eventBus.$on('new-address-center', this.newAddressCenterHandler);
        },
        beforeDestroy() {
            eventBus.$off('global-click', this.hideMenu);
            eventBus.$off('new-address-center', this.newAddressCenterHandler);
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
                width: 95px;
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
        }
    }
</style>