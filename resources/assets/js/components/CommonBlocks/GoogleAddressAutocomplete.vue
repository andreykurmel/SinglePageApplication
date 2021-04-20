<template>
    <div ref="addr_wrapper" class="addr_wrapper" :style="curPosition">
        <input ref="addr_input"
               class="form-control full-height"
               :title="$root.user.__google_table_api ? '' : 'Please Add API Key in Settings'"
               v-model="input_string"/>
    </div>
</template>

<script>
    import {eventBus} from './../../app';

    export default {
        name: "GoogleAddressAutocomplete",
        data: function () {
            return {
                input_string: '',

                pos: null,
                tableMeta: null,
                tableHeader: null,
                tableRow: null,

                autocomplete: null,
                selected_place: null,

                table_settings: {
                    street_number: 'address_fld__street_address',
                    route: 'address_fld__street',
                    locality: 'address_fld__city',
                    administrative_area_level_1: 'address_fld__state',
                    administrative_area_level_2: 'address_fld__countyarea',
                    postal_code: 'address_fld__zipcode',
                    country: 'address_fld__country',
                    lat: 'address_fld__lat',
                    lng: 'address_fld__long'
                },
                one_prevent: false,
            }
        },
        props: {
        },
        computed: {
            curPosition() {
                return {
                    left: (this.pos ? this.pos.left : -100)+'px',
                    top: (this.pos ? this.pos.top : -100)+'px',
                    width: (this.pos ? this.pos.width : 1)+'px',
                    height: (this.pos ? this.pos.height : 1)+'px',
                };
            }
        },
        methods: {
            initAutocomplete() {
                this.autocomplete = new google.maps.places.Autocomplete(this.$refs.addr_input, {types: ['geocode']});
                this.autocomplete.setFields(['address_component','geometry']);
                this.autocomplete.addListener('place_changed', this.fillInAddress);
            },

            fillInAddress() {
                this.selected_place = this.autocomplete.getPlace();

                if (this.tableHeader.id == this.tableMeta.address_fld__source_id) {
                    _.each(this.table_settings, (tb_field, key) => {
                        let header = _.find(this.tableMeta._fields, {id: Number(this.tableMeta[tb_field])});
                        if (header) {
                            this.tableRow[header.field] = key == 'lat' || key == 'lng'
                                ? this.getGeometry(key)
                                : this.getAddressComponent(key);
                        }
                    });
                }
                this.tableRow[this.tableHeader.field] = this.getAddressComponent('street_number')+' '+this.getAddressComponent('route')
                    +', '+this.getAddressComponent('locality')+' '+this.getAddressComponent('postal_code')
                    +', '+this.getAddressComponent('administrative_area_level_1', true)
                    +', '+this.getAddressComponent('country', true);
                this.hideAutocomplete(this.tableRow);
            },
            getAddressComponent(key, is_short) {
                let elem = _.find(this.selected_place.address_components, (el) => {
                    return el.types.indexOf(key) > -1;
                });
                return elem
                    ? is_short ? elem.short_name : elem.long_name
                    : '';
            },
            getGeometry(key) {
                let location = this.selected_place.geometry.location;
                if (key == 'lat') {
                    return location ? location.lat() : '';
                } else {
                    return location ? location.lng() : '';
                }
            },

            showAutocomplete(pos, meta, header, row) {
                this.input_string = row[header.field] || '';
                this.pos = pos;
                this.tableMeta = meta;
                this.tableHeader= header;
                this.tableRow = row;
                this.one_prevent = true;
                this.$refs.addr_input.focus();
            },
            hideAutocomplete(row) {
                this.pos = null;
                this.tableMeta = null;
                this.tableHeader= null;
                this.tableRow = null;
                eventBus.$emit('google-addr-autocomplete__hide', row);
            },
            hideMenu(e) {
                if (this.pos) {
                    if (this.one_prevent) {
                        this.one_prevent = false;
                        return;
                    }
                    let container = $(this.$refs.addr_wrapper);
                    let in_the_container = container && container.has(e.target).length > 0;
                    let autocompletes = $('.pac-container');
                    let in_the_autocompletes = autocompletes && autocompletes.has(e.target).length > 0;
                    if (!in_the_container && !in_the_autocompletes) {
                        this.hideAutocomplete();
                    }
                }
            },
        },
        mounted() {
            if (this.$root.user.__google_table_api) {
                this.initAutocomplete();
            }
            eventBus.$on('global-click', this.hideMenu);
            eventBus.$on('google-addr-autocomplete__show', this.showAutocomplete);
        },
        beforeDestroy() {
            eventBus.$off('global-click', this.hideMenu);
            eventBus.$off('google-addr-autocomplete__show', this.showAutocomplete);
        }
    }
</script>

<style lang="scss" scoped>
    .addr_wrapper {
        position: fixed;
        z-index: 5000;
    }
</style>