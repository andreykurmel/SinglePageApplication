<template>
    <span>
        <template v-for="lnk in tableHeader._links" v-if="canLink(lnk, 'before') || canLink(lnk, 'front')">
            <link-object :table-meta="tableMeta"
                         :global-meta="globalMeta"
                         :table-header="tableHeader"
                         :table-row="tableRow"
                         :cell-value="htmlConvValue"
                         :link="lnk"
                         :user="user"
                         :class="[canLink(lnk, 'front') ? 'link-absolute link-left' : '']"
                         :style="{left: linkAbsPos(lnk, 'front')}"
                         @show-src-record="showSrcRecord"
                         @open-app-as-popup="openAppAsPopup"
            ></link-object>
        </template>

        <template v-if="isAvail && underlinedLink && htmlConvValue && showLink(underlinedLink, underlinedLink.link_type)">
            <link-object :table-meta="tableMeta"
                         :global-meta="globalMeta"
                         :table-header="tableHeader"
                         :table-row="tableRow"
                         :link="underlinedLink"
                         :cell-value="htmlConvValue"
                         :user="user"
                         :show-field="htmlConvValue"
                         @show-src-record="showSrcRecord"
                         @open-app-as-popup="openAppAsPopup"
            ></link-object>
        </template>
        <template v-else="">
            <template v-if="tableHeader.f_type === 'User' && htmlConvValue">
                <a v-if="canShowUser()"
                   :target="user.is_admin ?  '_blank' : ''"
                   :href="user.is_admin ? userHref() : 'javascript:void(0)'"
                   :class="{'is_select': is_select, 'm_sel__wrap': $root.isMSEL(tableHeader.input_type), 'pr5': !can_edit}"
                   :style="{whiteSpace: 'nowrap'}"
                >
                    <span v-html="htmlConvValue"></span>
                    <span v-if="is_select && can_edit && $root.isMSEL(tableHeader.input_type)"
                          class="m_sel__remove"
                          @click.prevent.stop=""
                          @mousedown.prevent.stop="$emit('unselect-val')"
                          @mouseup.prevent.stop=""
                    >&times;</span>
                </a>
            </template>
            <template v-else-if="show_html()">
                <img v-if="is_select && specObjImgs.length"
                     :src="$root.fileUrl(specObjImgs[0], 'sm')"
                     class="item-image"
                     @click="fullSizeImg"
                     :height="lineHeight"/>
                <span :class="{'is_select': is_select, 'm_sel__wrap': is_select, 'pr5': !can_edit}" :style="selectBG">
                    <span v-html="show_html()"></span>
                    <span v-if="is_select && can_edit"
                          class="m_sel__remove"
                          @click.prevent.stop=""
                          @mousedown.prevent.stop="$emit('unselect-val')"
                          @mouseup.prevent.stop=""
                    >&times;</span>
                </span>
            </template>
            <span v-else-if="placeholderAvail"
                  style="color: #CCC">{{ tableHeader.placeholder_content }}</span>
        </template>

        <template v-for="lnk in tableHeader._links" v-if="canLink(lnk, 'after') || canLink(lnk, 'end')">
            <link-object :table-meta="tableMeta"
                         :global-meta="globalMeta"
                         :table-header="tableHeader"
                         :table-row="tableRow"
                         :cell-value="htmlConvValue"
                         :link="lnk"
                         :user="user"
                         :class="[canLink(lnk, 'end') ? 'link-absolute link-right' : '']"
                         :style="{right: linkAbsPos(lnk, 'end')}"
                         @show-src-record="showSrcRecord"
                         @open-app-as-popup="openAppAsPopup"
            ></link-object>
        </template>
    </span>
</template>

<script>
import {SpecialFuncs} from "../../../classes/SpecialFuncs";

import ShowLinkMixin from '../../_Mixins/ShowLinkMixin.vue';
import CellStyleMixin from '../../_Mixins/CellStyleMixin.vue';

import LinkObject from './LinkObject.vue';

export default {
        name: "CellTableContentData",
        mixins: [
            ShowLinkMixin,
            CellStyleMixin,
        ],
        components: {
            LinkObject,
        },
        data: function () {
            return {
                avail_behave_links: ['list_view','favorite','link_popup','bi_module','map_view','single-record-view',
                    'kanban_view','request_view','grouping_table'],
            }
        },
        props: {
            user: Object,
            globalMeta: Object,
            tableMeta: Object,
            tableHeader: Object,
            tableRow: Object,
            htmlValue: String|Number,
            realValue: String|Number,
            is_select: Boolean,
            isVertTable: Boolean,
            behavior: String,
            is_def_fields: Boolean,
            is_td_single: Object,
            no_height_limit: Boolean,
            can_edit: Boolean|Number,
        },
        computed: {
            placeholderAvail() {
                return this.tableHeader.placeholder_content
                    && (!this.tableHeader.placeholder_only_form || this.isVertTable)
                    && (!this.tableRow.id || !this.tableHeader._links.length);//hide placeholder for saved record with links.
            },
            underlinedLink() {
                return _.find(this.tableHeader._links, {icon: 'Underlined'});
            },
            isAvail() {
                return $.inArray(this.behavior, this.avail_behave_links) > -1
                    && (this.tableRow.id || this.$root.is_dcr_page)
                    && !this.is_td_single;
            },
            specObjImgs() {
                let imgs = [];
                if (this.is_select) {
                    let firstValuePart = this.realValue ? _.first(_.split(this.realValue, '<br>')) : '';
                    imgs = SpecialFuncs.rcObj(this.tableRow, this.tableHeader.field, firstValuePart).img_vals || [];
                    if (!imgs.length) {
                        try {
                            //OLD (new 'sys' columns are not create
                            let obj = JSON.parse(this.tableRow[this.tableHeader.field+'_sys']);
                            imgs = obj && obj.s_img ? [obj.s_img] : '';
                        } catch (e) {}
                    }
                }
                return _.map(imgs, (el) => { return {url:el} });
            },
            htmlConvValue() {
                if (this.is_def_fields && !this.htmlValue && this.tableHeader.f_type === 'User') {
                    return '{$user}';
                }
                return String(this.htmlValue);
            },
            selectBG() {
                let bg = this.is_select ? SpecialFuncs.rcObj(this.tableRow, this.tableHeader.field, this.realValue).ref_bg_color : '';
                return bg
                    ? {
                        backgroundColor: bg,
                        color: SpecialFuncs.smartTextColorOnBg(bg),
                    }
                    : {};
            },
        },
        methods: {
            show_html() {
                if (this.is_td_single && this.is_td_single.draw_links) {//HeaderResizer::resizeToContent only
                    return this.htmlConvValue
                        + '<br>'
                        + SpecialFuncs.showhtml(this.tableHeader, this.tableRow, this.htmlConvValue, this.tableMeta);
                }
                return SpecialFuncs.showhtml(this.tableHeader, this.tableRow, this.htmlConvValue, this.tableMeta);
            },
            //
            linkAbsPos(lnk, pos) {
                if (! this.canLink(lnk, pos)) {
                    return null;
                }

                let offset = 0;
                let idx = 0;
                _.each(this.tableHeader._links, (ll, ii) => {
                    if (ll.id == lnk.id || idx < ii) {
                        idx = ii - 1;
                        return;
                    }
                    if (this.canLink(ll, pos) && lnk.icon.length) {
                        offset += Number(lnk.icon.length) * Number(this.$root.themeTextFontSize) * 0.5 + 6;
                    }
                });
                if (offset) {
                    offset += 4;
                }
                return offset + 'px';
            },
            canLink(lnk, needed_pos) {
                return this.isAvail
                    && lnk.icon !== 'Underlined'
                    && lnk.link_pos === needed_pos;
            },
            canShowUser() {
                return !this.$root.isMSEL(this.tableHeader.input_type) //no mselect
                    || this.tableMeta.user_id == this.user.id //owner
                    || SpecialFuncs.managerOfRow(this.tableMeta, this.tableRow) //manager
                    || String(this.realValue).charAt(0) === '_' //group
                    || this.realValue == this.user.id; //same user as current
            },
            //show link popup
            showSrcRecord(link, header, tableRow) {
                this.$emit('show-src-record', link, header, tableRow);
            },
            openAppAsPopup(tb_app, app_link) {
                this.$emit('open-app-as-popup', tb_app, app_link);
            },
            userHref() {
                let usr = this.$root.smallUserStr(this.tableRow, this.tableHeader, this.tableRow[this.tableHeader.field], 'object');
                return usr && usr.id && !isNaN(usr.id)
                    ? '/profile/'+usr.id
                    : 'javascript:void(0)';
            },
            fullSizeImg() {
                window.event.stopPropagation();
                window.event.preventDefault();
                this.$emit('full-size-image', this.specObjImgs, 0);
            },
        },
        mounted() {
        }
    }
</script>

<style lang="scss" scoped>
    span {
        /*display: inline-block;*/
        text-decoration: inherit;
    }
    i {
        cursor: pointer;
    }
    p, ul, ol, h1, h2, h3 {
        margin-bottom: 16px;
    }
    .link-absolute {
        position: absolute;
        //z-index: 10; - creates "web link popup overflow issue"
        background: white;
        top: 0;
    }
    .link-left {
        left: 0;
    }
    .link-right {
        right: 0;
    }
</style>