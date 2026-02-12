<template>
    <div v-show="visibleLink && loadedLinks[visibleLink]" class="flex flex--col inline-linked-tabs">
        <div
            v-if="!currentLinkSimple"
            class="header-btn"
            :style="{height: !noTabs() ? 'auto' : '16px'}"
            @mouseenter="show_vtype = true"
            @mouseleave="show_vtype = false"
        >
            <i v-if="inlined_full === false" class="fas fa-arrow-right left-bottom" @click="redrawWidth"></i>
            <i v-if="inlined_full === true" class="fas fa-arrow-left left-bottom" @click="redrawWidth"></i>

            <i v-if="currentTableAlign === 'start' && popupViewType === viewTypeTable" class="fas fa-align-left left-bottom l15" @click="setAlignm('center')"></i>
            <i v-if="currentTableAlign === 'center' && popupViewType === viewTypeTable" class="fas fa-align-center left-bottom l15" @click="setAlignm('end')"></i>
            <i v-if="currentTableAlign === 'end' && popupViewType === viewTypeTable" class="fas fa-align-right left-bottom l15" @click="setAlignm('start')"></i>

            <div v-if="!noTabs()" style="position: relative; top: 2px;" :style="{marginLeft: popupViewType === viewTypeTable ? '35px' : '20px'}">
                <button v-for="link in inlinedLinks"
                        class="btn btn-default btn-sm"
                        :class="{active : visibleLink === link.id}"
                        :style="textSysStyle"
                        style="margin-right: 3px;"
                        @click="visibleChange(link)"
                >{{ link.name }}</button>
            </div>

            <template v-if="viewsAreAvail(currentLink)">
                <i class="fas"
                   :title="viewTypeTitle()"
                   :class="viewTypeIcon()"
                   style="position: absolute; right: 0; bottom: -5px;"
                ></i>
                <div v-if="show_vtype" class="view-type-wrapper">
                    <i v-for="type in allTypes(currentLink)"
                       v-if="popupViewType != type"
                       class="fas pull-right"
                       :class="viewTypeIcon(type)"
                       :title="viewTypeTooltip(type)"
                       @click="setView(type)"
                    ></i>
                </div>
            </template>
        </div>

        <div v-for="(link,idx) in inlinedLinks"
             v-if="!currentLinkSimple || idx === 0"
             v-show="visibleLink == link.id"
             class="link_wrapper"
             :style="{
                 height: heightParams[link.id]+'px',
                 border: noBorder() ? 'none' : null,
             }"
        >
            <inline-link-block
                :link="link"
                :tb-row="tableRow"
                :tb-header="tableHeader"
                :tb-meta="tableMeta"
                :view_type="popupViewType"
                :redraw_history="redraw_history"
                :with_edit="with_edit"
                :external_align="currentTableAlign"
                @loaded-link="loadedLink(link)"
                @hist-updated="histUpdated"
                @show-src-record="showSrcRecord"
                @max-height-changed="(height) => { maxHeightChanged(link, height); }"
            ></inline-link-block>
        </div>
    </div>
</template>

<script>
    import CellStyleMixin from "../../_Mixins/CellStyleMixin.vue";
    import ViewTypeLinkMixin from './ViewTypeLinkMixin';

    import InlineLinkBlock from "./InlineLinkBlock";
    import CellTableSysContent from "../../CustomCell/InCell/CellTableSysContent.vue";

    export default {
        name: 'InlineLinkedTabs',
        mixins: [
            CellStyleMixin,
            ViewTypeLinkMixin,
        ],
        components: {
            CellTableSysContent,
            InlineLinkBlock,
        },
        data() {
            return {
                visibleLink: null,
                heightParams: {},
                loadedLinks: {},
                currentTableAlign: this.currentLink ? this.currentLink.table_def_align : 'start',
            }
        },
        computed: {
            inlinedLinks() {
                return _.filter(this.tableHeader._links, (link) => {
                    return !!link.inline_in_vert_table;
                });
            },
            currentLink() {
                return _.find(this.inlinedLinks, {id: Number(this.visibleLink)})
                    || _.first(this.inlinedLinks);
            },
            currentLinkSimple() {
                return this.currentLink && this.currentLink.inline_style === 'simple';
            },
        },
        props: {
            tableRow: Object,
            tableHeader: Object,
            tableMeta: Object,
            redraw_history: Number,
            with_edit: {
                type: Boolean,
                default: true
            },
            inlined_full: Boolean,
        },
        methods: {
            setAlignm(align) {
                this.currentTableAlign = align;
                console.log('inline - linked - tabs', align);
            },
            noTabs() {
                return this.inlinedLinks.length == 1 && _.first(this.inlinedLinks).inline_hide_tab;
            },
            noBorder() {
                return this.inlinedLinks.length == 1 && _.first(this.inlinedLinks).inline_hide_boundary;
            },
            noPadding() {
                return this.inlinedLinks.length == 1 && _.first(this.inlinedLinks).inline_hide_padding;
            },
            visibleChange(link) {
                this.visibleLink = link.id;
                this.popupViewType = link.popup_display;
                this.$nextTick(() => {
                    this.currentTableAlign = this.currentLink ? this.currentLink.table_def_align : 'start';
                });
            },
            setView(type) {
                this.popupViewType = type;
            },
            histUpdated() {
                this.$emit('hist-updated');
            },
            showSrcRecord(lnk, header, tableRow) {
                this.$emit('show-src-record', lnk, header, tableRow, 'link');
            },
            maxHeightChanged(lnk, height) {
                this.heightParams[lnk.id] = Number(height);
            },
            loadedLink(link) {
                this.loadedLinks[link.id] = true;
            },
            redrawWidth() {
                this.$emit('redraw-inlined-width');
            },
        },
        mounted() {
            _.each(this.inlinedLinks, (link) => {
                this.$set(this.heightParams, link.id, Number(link.max_height_in_vert_table || 400));
                this.$set(this.loadedLinks, link.id, false);
            });

            this.visibleChange( _.first(this.inlinedLinks) );
        },
        beforeDestroy() {
        }
    }
</script>

<style lang="scss" scoped>
    .link_wrapper {
        min-height: 50px;
        overflow: auto;
        border: 1px #ccc solid;
        border-radius: 5px;
        position: relative;
        background: white;
        z-index: 90;
    }
    .header-btn {
        cursor: pointer;
        color: #555;
        position: relative;
        z-index: 100;
        font-size: 14px;

        .view-type-wrapper {
            right: 18px;
            position: absolute;
            background-color: transparent;
            width: max-content;
            white-space: nowrap;
            bottom: -5px;
        }

        .fas {
            color: #555 !important;
            margin: 3px;
        }
    }
    .left-bottom {
        position: absolute;
        left: 0;
        bottom: -5px;
    }
    .l15 {
        left: 15px;
    }
</style>