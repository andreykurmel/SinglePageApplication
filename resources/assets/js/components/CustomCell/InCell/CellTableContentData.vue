<template>
    <span>
        <template v-for="lnk in tableHeader._links" v-if="isAvail && lnk.icon !== 'Underlined' && tableHeader.active_links">
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
                <img v-if="is_select && specObjImg"
                     :src="$root.fileUrl({url:specObjImg})"
                     class="item-image"
                     :height="lineHeight"/>
                <span :class="{'is_select': is_select, 'm_sel__wrap': $root.isMSEL(tableHeader.input_type)}">
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
    </span>
</template>

<script>
    import {SpecialFuncs} from "./../../../classes/SpecialFuncs";

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
        inject: {
            reactive_provider: {
                from: 'reactive_provider',
                default: () => { return {} }
            }
        },
        data: function () {
            return {
                avail_behave_links: ['list_view','favorite','link_popup','bi_module'],
            }
        },
        props:{
            user: Object,
            globalMeta: Object,
            tableMeta: Object,
            tableHeader: Object,
            tableRow: Object,
            htmlValue: String|Number,
            realValue: String|Number,
            is_select: Boolean,
            isVertTable: Boolean,
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
                return $.inArray(this.reactive_provider.behavior, this.avail_behave_links) > -1
                    && this.tableRow.id;
            },
            specObjImg() {
                let img = '';
                if (this.is_select) {
                    img = this.$root.rcObj(this.tableRow, this.tableHeader.field, this.realValue).img_val;
                    if (!img) {
                        try {
                            let obj = JSON.parse(this.tableRow[this.tableHeader.field+'_sys']);
                            img = obj ? obj.s_img : '';
                        } catch (e) {}
                    }
                }
                return img;
            },
            htmlConvValue() {
                if (this.reactive_provider.is_def_fields && !this.htmlValue) {
                    return '{$user}';
                }
                if (
                    !this.reactive_provider.is_def_fields
                    &&
                    $.inArray(this.htmlValue, ['{$first_name}','{$last_name}','{$email}','{$user}']) > -1
                ) {
                    return '';
                }
                return this.htmlValue;
            },
        },
        methods: {
            show_html() {
                return SpecialFuncs.showhtml(this.tableHeader, this.tableRow, this.htmlConvValue, this.tableMeta.unit_conv_is_active);
            },
            //
            canShowUser() {
                return !this.$root.isMSEL(this.tableHeader.input_type) //no mselect
                    || this.tableMeta._is_owner //owner
                    || this.tableMeta._current_right._user_is_manager //manager
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
        },
        mounted() {
        }
    }
</script>

<style lang="scss" scoped="">
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