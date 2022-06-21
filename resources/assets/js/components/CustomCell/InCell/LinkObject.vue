<template>

    <a v-if="showLink(link, 'Record') && (!link.link_display || link.link_display == 'Popup')"
       :title="link ? link.tooltip : ''"
       @mouseenter="showLinkPrev"
       @mouseleave="$root.leaveLinkPreview"
       @mousedown.stop=""
       @mouseup.stop=""
       @click.stop="showSrcRecord()"
    >
        <span v-if="showField" v-html="showField"></span>
        <link-icon v-else="" :icon="link.icon"></link-icon>
    </a>

    <a v-else-if="showLink(link, 'Record') && link.link_display == 'Table'"
       :title="link ? link.tooltip : ''"
       @mouseenter="showLinkPrev"
       @mouseleave="$root.leaveLinkPreview"
       @mousedown.stop=""
       @mouseup.stop=""
       @click.stop=""
       target="_blank"
       :href="tb_link()"
    >
        <span v-if="showField" v-html="showField"></span>
        <link-icon v-else="" :icon="link.icon"></link-icon>
    </a>

    <a v-else-if="showLink(link, 'Record') && link.link_display == 'RorT'"
       :title="link ? link.tooltip : ''"
       @mouseenter="showLinkPrev"
       @mouseleave="$root.leaveLinkPreview"
       @mousedown.stop=""
       @mouseup.stop=""
       @click.stop="showRortModal()"
    >
        <span v-if="showField" v-html="showField"></span>
        <link-icon v-else="" :icon="link.icon"></link-icon>
    </a>

    <a v-else-if="showLink(link, 'Web') && (web_link || !link.hide_empty_web)"
       :title="link ? link.tooltip : ''"
       @mousedown.stop=""
       @mouseup.stop=""
       @click.stop=""
       target="_blank"
       :href="web_link"
    >
        <span v-if="showField" v-html="showField"></span>
        <link-icon v-else="" :icon="link.icon"></link-icon>
    </a>

    <a v-else-if="showLink(link, 'App')"
       target="_blank"
       :title="link ? link.tooltip : ''"
       :href="tb_app && tb_app.open_as_popup ? null : app_link"
       @mousedown.stop=""
       @mouseup.stop=""
       @click.stop="tb_app && tb_app.open_as_popup ? openAppAsPopup() : null"
    >
        <span v-if="showField" v-html="showField"></span>
        <link-icon v-else="" :icon="link.icon"></link-icon>
    </a>

    <a v-else-if="showLink(link, 'GMap')"
       target="_blank"
       :title="link ? link.tooltip : ''"
       :href="gmap_link(false)"
       @mousedown.stop=""
       @mouseup.stop=""
       @click.stop=""
    >
        <span v-if="showField" v-html="showField"></span>
        <link-icon v-else="" :icon="'GoogleMap'"></link-icon>
    </a>

    <a v-else-if="showLink(link, 'GEarth')"
       target="_blank"
       :title="link ? link.tooltip : ''"
       :href="gmap_link(true)"
       @mousedown.stop=""
       @mouseup.stop=""
       @click.stop=""
    >
        <span v-if="showField" v-html="showField"></span>
        <link-icon v-else="" :icon="'GoogleEarth'"></link-icon>
    </a>

    <span v-else></span>

</template>

<script>
import {SpecialFuncs} from '../../../classes/SpecialFuncs';

import {eventBus} from '../../../app';

import TableLinkMixin from '../../_Mixins/TableLinkMixin';
import ShowLinkMixin from '../../_Mixins/ShowLinkMixin';

import LinkIcon from './LinkIcon';

export default {
        name: "LinkObject",
        mixins: [
            TableLinkMixin,
            ShowLinkMixin
        ],
        components: {
            LinkIcon
        },
        data: function () {
            return {
                rortShow: false,
                rortModal: {
                    top: 0,
                    left: 0
                },
                uuid: uuidv4()
            }
        },
        props:{
            globalMeta: Object,
            tableMeta: Object,
            tableHeader: Object,
            tableRow: Object,
            cellValue: String|Number,
            link: Object,
            showField: String|Number,
            user: Object,
        },
        computed: {
            web_link() {
                let res = (this.link.web_prefix || '');
                if (this.link.address_field_id) {
                    let fld = _.find(this.tableMeta._fields, {id: Number(this.link.address_field_id)});
                    if (fld && this.tableRow[fld.field]) {
                        res += this.tableRow[fld.field];
                    }
                }
                return res;
            },
            tb_app() {
                let tb_app = null;
                if (this.link.link_type === 'App') {
                    tb_app = _.find(this.$root.settingsMeta.table_apps_data, {id: Number(this.link.table_app_id)});
                }
                return tb_app;
            },
            app_link() {
                let lnk = '#';
                if (this.tb_app && this.tb_app.name && this.link) {
                    let queryParams = [];
                    if (this.link.table_app_id == this.$root.settingsMeta.payment_app_id) {
                        queryParams.push('link=' + this.link.id);
                        queryParams.push('row=' + this.tableRow.id);
                    } else {
                        _.each(this.link._params, (queryObj) => {
                            if (queryObj.value === '{$table_id}') {
                                queryParams.push(queryObj.param.toLowerCase() + '=' + this.tableMeta.id);
                            }
                            else if (queryObj.value === '{$column_id}' && queryObj.column_id) {
                                queryParams.push(queryObj.param.toLowerCase() + '=' + queryObj.column_id);
                            }
                            else if (queryObj.column_id) {
                                let fld = _.find(this.tableMeta._fields, {id: Number(queryObj.column_id)});
                                queryParams.push(queryObj.param.toLowerCase() + '=' + this.tableRow[fld.field]);
                            }
                            else {
                                queryParams.push(queryObj.param.toLowerCase() + '=' + queryObj.value);
                            }
                        });
                    }
                    lnk = this.$root.clear_url.replace('://', '://'+this.tb_app.subdomain+'.') //APPs subdomain
                        + '/apps'
                        + this.tb_app.app_path
                        + '/?' + queryParams.join('&');
                }
                return lnk;
            },
        },
        methods: {
            gmap_link(earth) {
                let lnk = earth ? 'https://earth.google.com/web/search/' : 'https://www.google.com/maps/search/';

                let lat_header = _.find(this.tableMeta._fields, {id: Number(this.link.link_field_lat)});
                let long_header = _.find(this.tableMeta._fields, {id: Number(this.link.link_field_lng)});
                let address_header = _.find(this.tableMeta._fields, {id: Number(this.link.link_field_address)});
                let lnk_header = _.find(this.tableMeta._fields, {id: Number(this.link.table_field_id)});

                if (lat_header && long_header) {
                    lnk += this.tableRow[lat_header.field]+','+this.tableRow[long_header.field];
                }
                else
                if (address_header) {
                    lnk += this.tableRow[address_header.field];
                }
                else
                if (lnk_header) {
                    lnk += this.tableRow[lnk_header.field];
                }

                return lnk;
            },
            showRortModal() {
                eventBus.$emit('show-rort-modal', this.uuid, this.link, this.tb_link());
            },
            openAppAsPopup() {
                this.$emit('open-app-as-popup', this.tb_app, this.app_link);
            },
            showSrcRecord() {
                this.rortShow = false;
                this.link._c_value = this.cellValue;
                this.$emit('show-src-record', this.link, this.tableHeader, this.tableRow);
            },
            tb_link() {
                this.link._c_value = this.cellValue;
                return this.m_table_link(this.tableRow, this.link);
            },

            clickedRortSrcHandler(uuid) {
                if (this.uuid === uuid) {
                    this.showSrcRecord();
                }
            },
            showLinkPrev(e) {
                let availfields = SpecialFuncs.parseMsel(this.link.link_preview_fields);
                if (availfields.length) {
                    this.link._c_value = this.cellValue;
                    this.$root.showLinkPreview(e, this.link, this.tableHeader, this.tableRow, this.globalMeta);
                }
            },
        },
        created() {
            eventBus.$on('clicked-rort-src', this.clickedRortSrcHandler);
        },
        beforeDestroy() {
            eventBus.$off('clicked-rort-src', this.clickedRortSrcHandler);
        }
    }
</script>