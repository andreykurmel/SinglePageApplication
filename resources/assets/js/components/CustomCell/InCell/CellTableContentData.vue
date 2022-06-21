<template>
    <span>
        <template v-for="lnk in tableHeader._links" v-if="canLink(lnk, 'before')">
            <link-object :table-meta="tableMeta"
                         :global-meta="globalMeta"
                         :table-header="tableHeader"
                         :table-row="tableRow"
                         :cell-value="htmlConvValue"
                         :link="lnk"
                         :user="user"
                         @show-src-record="showSrcRecord"
                         @open-app-as-popup="openAppAsPopup"
            ></link-object>
        </template>

        <template v-if="isAvail && underlinedLink && showLink(underlinedLink, underlinedLink.link_type)">
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
                   :class="{'is_select': is_select, 'm_sel__wrap': $root.isMSEL(tableHeader.input_type)}"
                   :style="{whiteSpace: 'nowrap'}"
                >
                    <span v-html="htmlConvValue"></span>
                    <span v-if="is_select && $root.isMSEL(tableHeader.input_type)"
                          class="m_sel__remove"
                          @click.prevent.stop=""
                          @mousedown.prevent.stop="$emit('unselect-val')"
                          @mouseup.prevent.stop=""
                    >&times;</span>
                </a>
            </template>
            <template v-else-if="show_html()">
                <img v-if="is_select && specObjImgs.length"
                     :src="$root.fileUrl(specObjImgs[0])"
                     class="item-image"
                     @click="fullSizeImg"
                     :height="lineHeight"/>
                <span :class="{'is_select': is_select, 'm_sel__wrap': $root.isMSEL(tableHeader.input_type)}" :style="selectBG">
                    <span v-html="show_html()"></span>
                    <span v-if="is_select && $root.isMSEL(tableHeader.input_type)"
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

        <template v-for="lnk in tableHeader._links" v-if="canLink(lnk, 'after')">
            <link-object :table-meta="tableMeta"
                         :global-meta="globalMeta"
                         :table-header="tableHeader"
                         :table-row="tableRow"
                         :cell-value="htmlConvValue"
                         :link="lnk"
                         :user="user"
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
                avail_behave_links: ['list_view','favorite','link_popup','bi_module','map_view','single-record-view'],
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
            is_td_single: Boolean,
            no_height_limit: Boolean,
        },
        computed: {
            placeholderAvail() {
                return this.tableHeader.placeholder_content
                    && (!this.tableHeader.placeholder_only_form || this.isVertTable)
                    && (!this.tableRow.id || !this.tableHeader._links.length);//hide placeholder for saved record with links.
            },
            underlinedLink() {
                return this.tableHeader.active_links
                    ? _.find(this.tableHeader._links, {icon: 'Underlined'})
                    : null;
            },
            isAvail() {
                return $.inArray(this.behavior, this.avail_behave_links) > -1
                    && this.tableRow.id
                    && !this.is_td_single;
            },
            specObjImgs() {
                let imgs = [];
                if (this.is_select) {
                    imgs = SpecialFuncs.rcObj(this.tableRow, this.tableHeader.field, this.realValue).img_vals || [];
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
                return this.htmlValue;
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
                return SpecialFuncs.showhtml(this.tableHeader, this.tableRow, this.htmlConvValue, this.tableMeta.unit_conv_is_active);
            },
            //
            canLink(lnk, needed_pos) {
                return this.isAvail
                    && lnk.icon !== 'Underlined'
                    && lnk.link_pos === needed_pos
                    && this.tableHeader.active_links;
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
</style>