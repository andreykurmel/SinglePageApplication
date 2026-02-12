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
        <link-icon v-else="" :icon="link.icon" :table-header="tableHeader"></link-icon>
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
        <link-icon v-else="" :icon="link.icon" :table-header="tableHeader"></link-icon>
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
        <link-icon v-else="" :icon="link.icon" :table-header="tableHeader"></link-icon>
    </a>

    <div v-else-if="showLink(link, 'Web')" style="display: inline-block">
        <div v-if="webLinkIsArray()" ref="web_link_wrap" class="web_link_wrp">
            <span v-if="showField" v-html="showField"></span>
            <link-icon v-else="" :icon="link.icon" :table-header="tableHeader"></link-icon>

            <div v-if="$refs.web_link_wrap" class="show-web-pop" :style="getWebPopStyle()">
                <span v-for="(arrValue,i) in webLinkArrayValues()" v-if="arrValue">
                    <br v-if="i > 0">
                    <span v-if="webLinkLabel(i)">{{ webLinkLabel(i) }} - </span>
                    <a :title="link ? link.tooltip : ''"
                       @mousedown.stop=""
                       @mouseup.stop=""
                       @click.stop=""
                       target="_blank"
                       :href="getWebLink(arrValue)"
                    >{{ getWebLink(arrValue) }}</a>
                </span>
            </div>
        </div>
        <a v-if="!webLinkIsArray() && (getWebLink() || !link.hide_empty_web)"
           :title="link ? link.tooltip : ''"
           @mousedown.stop=""
           @mouseup.stop=""
           @click.stop=""
           target="_blank"
           :href="getWebLink()"
        >
            <span v-if="showField" v-html="showField"></span>
            <link-icon v-else="" :icon="link.icon" :table-header="tableHeader"></link-icon>
        </a>
    </div>

    <a v-else-if="showLink(link, 'App')"
       target="_blank"
       :title="link ? link.tooltip : ''"
       :href="asPopup || isPayment ? null : app_link"
       @mousedown.stop=""
       @mouseup.stop=""
       @click.stop="asPopup ? callOpenAppAsPopup() : (isPayment ? newTabPopup() : null)"
    >
        <span v-if="showField" v-html="showField"></span>
        <link-icon v-else="" :icon="link.icon" :table-header="tableHeader"></link-icon>
    </a>

    <a v-else-if="showLink(link, 'GMap')"
       target="_blank"
       :title="link ? link.tooltip : ''"
       :href="gmap_link('map')"
       @mousedown.stop=""
       @mouseup.stop=""
       @click.stop=""
    >
        <span v-if="showField" v-html="showField"></span>
        <link-icon v-else="" :icon="'GoogleMap'" :table-header="tableHeader"></link-icon>
    </a>

    <a v-else-if="showLink(link, 'GEarth')"
       target="_blank"
       :title="link ? link.tooltip : ''"
       :href="gmap_link('earth')"
       @mousedown.stop=""
       @mouseup.stop=""
       @click.stop=""
    >
        <span v-if="showField" v-html="showField"></span>
        <link-icon v-else="" :icon="'GoogleEarth'" :table-header="tableHeader"></link-icon>
    </a>

    <a v-else-if="showLink(link, 'History')"
       :title="link ? link.tooltip : ''"
       @mousedown.stop=""
       @mouseup.stop=""
       @click.stop="historyShowRecord()"
    >
        <span v-if="showField" v-html="showField"></span>
        <link-icon v-else="" :icon="link.icon" :table-header="tableHeader"></link-icon>
    </a>

    <a v-else-if="showLink(link, 'Add-on (Report)')"
       :title="link ? link.tooltip : ''"
       @mousedown.stop=""
       @mouseup.stop=""
       @click.stop="runReport()"
    >
        <span v-if="showField" v-html="showField"></span>
        <link-icon v-else="" :icon="link.icon" :table-header="tableHeader"></link-icon>
    </a>

    <span v-else></span>

</template>

<script>
import {SpecialFuncs} from '../../../classes/SpecialFuncs';
import {MapHelper} from "../../../classes/helpers/MapHelper";
import {Endpoints} from "../../../classes/Endpoints";

import {eventBus} from '../../../app';

import LinkAppFunctionsMixin from '../../_Mixins/LinkAppFunctionsMixin';
import TableLinkMixin from '../../_Mixins/TableLinkMixin';
import ShowLinkMixin from '../../_Mixins/ShowLinkMixin';

import LinkIcon from './LinkIcon';

export default {
        name: "LinkObject",
        mixins: [
            LinkAppFunctionsMixin,
            TableLinkMixin,
            ShowLinkMixin
        ],
        components: {
            LinkIcon
        },
        data: function () {
            return {
                show_web_pop: false,
            }
        },
        props: {
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
        },
        methods: {
            getWebPopStyle() {
                let bnd = this.$refs.web_link_wrap.getBoundingClientRect();
                return {
                    left: bnd.left + 'px',
                    top: (bnd.bottom - 3) + 'px',
                };
            },
            webLinkIsArray() {
                let fld = _.find(this.tableMeta._fields, {id: Number(this.link.address_field_id)});
                let string = fld && this.tableRow[fld.field]
                    ? String(this.tableRow[fld.field]).replaceAll(/<br[/]?>/gi, ' ')
                    : '';
                return string && string.match('[,;\\s]')
            },
            webLinkArrayValues() {
                let fld = _.find(this.tableMeta._fields, {id: Number(this.link.address_field_id)});
                let string = fld && this.tableRow[fld.field]
                    ? String(this.tableRow[fld.field]).replaceAll(/<br[/]?>/gi, ' ')
                    : '';
                return _.map(
                    string.replaceAll(/[,;\s]+/gi, ';').split(';'),
                    (el) => {
                        el = _.trim(el).replaceAll(/['"\[\]]/gi, '');
                        return el ? this.getWebLink(el) : '';
                    }
                );
            },
            webLinkLabel(idx) {
                let labelFld = _.find(this.tableMeta._fields, {id: Number(this.link.multiple_web_label_fld_id)}) || {};
                let labelArr = String(this.tableRow[labelFld.field] || '')
                    .replaceAll(/<br[/]?>/gi, ' ')
                    .replaceAll(/[,;\s]+/gi, ';')
                    .split(';');
                return _.trim(labelArr[idx] || '');
            },
            getWebLink(extValue) {
                let res = '';
                if (this.link.address_field_id) {
                    let fld = _.find(this.tableMeta._fields, {id: Number(this.link.address_field_id)});
                    if (fld && fld.id == this.tableHeader.id) {
                        res += extValue || this.cellValue;
                    }
                    else if (fld) {
                        let value = extValue || this.tableRow[fld.field];
                        res += SpecialFuncs.showhtml(fld, this.tableRow, value, this.globalMeta);
                    }
                }
                //shared link to /links/{mrv hash}/{row hash}
                if (this.link.share_record_link_id && ! this.link.address_field_id) {
                    res += this.tableRow.static_hash;
                }
                //add "URL Prefix" if no "http(s)" or "www" in the beginning
                if (this.link.web_prefix && !String(res).toLowerCase().startsWith('http') && !String(res).startsWith('www')) {
                    res = this.link.web_prefix + res;
                }
                //auto add "http://" if needed
                if (res && !String(res).toLowerCase().startsWith('http') && !String(res).startsWith('/')) {
                    res = 'http://' + res;
                }
                return res;
            },
            gmap_link(type) {
                return MapHelper.gmapLink(this.tableMeta, this.tableRow, this.link, type);
            },
            showRortModal() {
                eventBus.$emit('show-rort-modal', this.getLnkUid(), this.link, this.tb_link());
            },
            showSrcRecord(anotherHeader) {
                this.rortShow = false;
                this.link._c_value = this.cellValue;
                this.$emit('show-src-record', this.link, anotherHeader || this.tableHeader, this.tableRow);
            },
            tb_link() {
                this.link._c_value = this.cellValue;
                return this.m_table_link(this.tableRow, this.link);
            },

            getLnkUid() {
                return [this.link.id, this.tableHeader.id, this.tableRow.id, this.cellValue].join('_');
            },
            clickedRortSrcHandler(uuid) {
                if (this.getLnkUid() === uuid) {
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
            historyShowRecord() {
                let fld = _.find(this.tableMeta._fields, {id: Number(this.link.history_fld_id)});
                this.showSrcRecord(fld);
            },
            runReport() {
                let report = _.find(this.tableMeta._reports, {id: Number(this.link.linked_report_id)});
                if (report) {
                    Endpoints.runReportsMaking(report, this.tableMeta, {special_params:{}}, this.tableRow.id);
                } else {
                    Swal('Info','Empty linked report!');
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

<style scoped lang="scss">
a {
    display: inline-block;
    cursor: pointer;
    margin: 0 2px 2px 2px;
}
.show-web-pop {
    display: none;
    text-align: left;
    position: fixed;
    background-color: white;
    padding: 5px;
    border: 1px solid #ccc;
    border-radius: 5px;
    z-index: 100;
    max-width: 350px;
}
.web_link_wrp {
    display: inline-block;
    position: relative;
}
.web_link_wrp:hover {
    .show-web-pop {
        display: block;
    }
}
</style>