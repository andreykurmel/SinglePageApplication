<template>
    <div class="navbar navbar-default no-border"
         v-if="(dcrObject.dcr_title || dcrObject.dcr_title_bg_img) && !is_embed"
         :style="{ backgroundColor: getBgCol('dcr_sec_bg_top'), }"
    >
        <!--TITLE-->
        <div class="flex flex--center-h"
             style="overflow: hidden;"
             :style="{
                height: (show_header ? titleHeight : 0),
                transition: transition_time_ms+'ms',
             }"
        >
            <div class="dcr-title"
                 ref="title_wrapper"
                 :draw="saveTitleHeight()"
                 :style="{
                    maxWidth: '100%',
                    width: titleWi(),
                    height: ((dcrObject.dcr_title_height * (sm_text ? 0.8 : 1))+'px' || null),
                    backgroundColor: getBgCol('dcr_title_bg_color', true),
                    boxShadow: getBgCol('dcr_title_bg_color', true) ? getBoxShad : null,
                    borderTopLeftRadius: (dcrObject.dcr_form_line_type == 'space' ? dcrObject.dcr_form_line_radius+'px' : ''),
                    borderTopRightRadius: (dcrObject.dcr_form_line_type == 'space' ? dcrObject.dcr_form_line_radius+'px' : ''),
                    borderBottomLeftRadius: (dcrObject.dcr_form_line_type == 'space' ? dcrObject.dcr_form_line_radius+'px' : ''),
                    borderBottomRightRadius: (dcrObject.dcr_form_line_type == 'space' ? dcrObject.dcr_form_line_radius+'px' : ''),
                }"
            >
                <h1 class="hid item__h1" :style="fontStyleObj('dcr_title_font')" v-html="dcrObject.dcr_title"></h1>
                <h1 class="dcr-title--item item__h1" :style="fontStyleObj('dcr_title_font')" v-html="dcrObject.dcr_title"></h1>
                <img v-if="dcrObject.dcr_title_bg_img && dcrObject.dcr_title_background_by === 'image'"
                     class="dcr-title--item item__img"
                     :src="$root.fileUrl({url:dcrObject.dcr_title_bg_img})"
                     :style="{
                        height: (['Height','Fill'].indexOf(dcrObject.dcr_title_bg_fit) > -1 ? '100%' : null),
                        width: (['Width','Fill'].indexOf(dcrObject.dcr_title_bg_fit) > -1 ? '100%' : null),
                        objectFit: (dcrObject.dcr_title_bg_fit === 'Fill' ? 'cover' : null),
                     }"
                />

                <header-resizer
                    v-if="dcrEditMode"
                    :table-header="dcrObject"
                    :hdr_key="'dcr_title_width'"
                    :resize-only="true"
                    :step="10"
                    style="z-index: 150;"
                ></header-resizer>
                <header-resizer
                    v-if="dcrEditMode"
                    :table-header="dcrObject"
                    :hdr_key="'dcr_title_height'"
                    :vertical="true"
                    :resize-only="true"
                    :step="10"
                    style="z-index: 150;"
                ></header-resizer>
                <i v-if="dcrEditMode" class="fas fa-edit" @click="$emit('title-popup')"></i>
            </div>
        </div>
        <!--TITLE-->

        <div v-if="dcrObject.dcr_form_line_top" :style="{
            borderBottom: (dcrObject.dcr_form_line_type == 'line' ? (dcrObject.dcr_form_line_thick || 1)+'px solid '+(dcrObject.dcr_form_line_color || '#d3e0e9') : null),
            marginBottom: (dcrObject.dcr_form_line_type == 'space' ? (dcrObject.dcr_form_line_thick || 1)+'px' : null),
        }"></div>

        <!--TOP MESSAGE-->
        <div class="dcr-top-msg" :style="{
                maxWidth: '100%',
                width: formWi(),
                margin: 'auto',
                boxShadow: getBoxShad,
                borderTopLeftRadius: dcrObject.dcr_form_line_radius+'px',
                borderTopRightRadius: dcrObject.dcr_form_line_radius+'px',
                borderBottomLeftRadius: (dcrObject.dcr_form_line_type == 'space' ? dcrObject.dcr_form_line_radius+'px' : ''),
                borderBottomRightRadius: (dcrObject.dcr_form_line_type == 'space' ? dcrObject.dcr_form_line_radius+'px' : ''),
        }">
            <div v-if="dcr_form_msg"
                 class="top-message-wrap"
                 ref="message_wrapper"
                 :draw="saveMessageHeight()"
                 :style="{
                        height: (show_header ? messageHeight : 0),
                        transition: transition_time_ms+'ms',
                        backgroundColor: formBgTransp(),
                        borderTopLeftRadius: dcrObject.dcr_form_line_radius+'px',
                        borderTopRightRadius: dcrObject.dcr_form_line_radius+'px',
                        borderBottomLeftRadius: (dcrObject.dcr_form_line_type == 'space' ? dcrObject.dcr_form_line_radius+'px' : ''),
                        borderBottomRightRadius: (dcrObject.dcr_form_line_type == 'space' ? dcrObject.dcr_form_line_radius+'px' : ''),
                 }"
            >
                <div style="padding: 10px" v-html="dcr_form_msg"></div>

                <header-resizer
                    v-if="dcrEditMode"
                    :table-header="dcrObject"
                    :hdr_key="'dcr_form_width'"
                    :resize-only="true"
                    :step="10"
                    style="z-index: 150;"
                ></header-resizer>
                <i v-if="dcrEditMode" class="fas fa-edit" @click="$emit('title-popup')"></i>
            </div>
            <div class="flex navbar navbar-default fit-content" :class="flexCenterClass" v-if="dcrObject.one_per_submission != 1">
                <div v-if="loaded && $root.tableMeta" class="nav flex flex--center flex--automargin">
                    <div style="height: 40px;"></div>
                    <div class="active">
                        <a @click.prevent="changeViewT"><span class="glyphicon glyphicon-list"></span> {{ viewTable ? 'Form' : 'Grid View' }}</a>
                    </div>
                    <div v-if="canAddRow" v-show="viewTable" class="flex">
                        <a v-if="canAllSave()">
                            <button type="button" class="btn btn-success" :style="$root.themeButtonStyle" @click="storeRows('Saved')">Save</button>
                        </a>
                        <a v-if="canAllSubmit()">
                            <button type="button" class="btn btn-success" :style="$root.themeButtonStyle" @click="storeRows('Submitted')">Submit</button>
                        </a>
                        <a v-if="canAllUpdate()">
                            <button type="button" class="btn btn-success" :style="$root.themeButtonStyle" @click="storeRows('Updated')">Update</button>
                        </a>
                    </div>
                    <div v-show="viewTable" v-if="$root.tableMeta">
                        <a><cell-height-button
                            :table_meta="$root.tableMeta"
                            :cell-height="$root.cellHeight"
                            :max-cell-rows="$root.maxCellRows"
                            @change-cell-height="$root.changeCellHeight"
                            @change-max-cell-rows="$root.changeMaxCellRows"
                        ></cell-height-button></a>
                    </div>
                    <div v-show="viewTable">
                        <a @click.prevent="$root.fullWidthCellToggle()"><full-width-button :full-width-cell="$root.fullWidthCell"></full-width-button></a>
                    </div>
                </div>
            </div>
        </div>
        <!--TOP MESSAGE-->

        <div class="header--toggler"
             @click="toggleHeader()"
             :style="{width: titleWi()}"
        >
            <img src="/assets/img/DCR_Hide_Show.png"
                 :style="{
                    transform: show_header ? '' : 'rotate(180deg)',
                    top: show_header ? '-20px' : ''
                 }"/>
        </div>
<!--        <div v-if="dcr_form_msg" class="header&#45;&#45;bg" :style="{-->
<!--            backgroundColor: formBgTransp(),-->
<!--            left: titleCenterLeft(),-->
<!--            width: titleWi(),-->
<!--        }"></div>-->
    </div>
</template>

<script>
    import {SpecialFuncs} from "../../classes/SpecialFuncs";

    import {eventBus} from '../../app';

    import RequestMixin from "./RequestMixin.vue";

    import FullWidthButton from './../Buttons/FullWidthButton';
    import CellHeightButton from './../Buttons/CellHeightButton';
    import HeaderResizer from "../CustomTable/Header/HeaderResizer.vue";

    export default {
        name: "MainRequestTitle",
        mixins: [
            RequestMixin,
        ],
        components: {
            HeaderResizer,
            FullWidthButton,
            CellHeightButton,
        },
        data: function () {
            return {
                htmlMessageHeight: 0,
                htmlTitleHeight: 0,
                show_header: true,
                viewTable: false,
            }
        },
        props: {
            dcrObject: Object,
            dcrRow: Object,
            is_embed: Boolean|Number,
            loaded: Boolean,
            dcr_form_msg: String,
            sm_text: Boolean|Number,
            allRows: Object|null,
            canAddRow: Boolean|Number,
            transition_time_ms: Number,
            dcrEditMode: Boolean,
        },
        computed: {
            getBottom() {
                if (this.dcrObject.one_per_submission != 1) {
                    return {
                        bottom: this.show_header ? '57px' : '52px',
                    }
                } else {
                    return {
                        bottom: this.show_header ? '15px' : '10px',
                    }
                }
            },
            flexCenterClass() {
                return [window.innerWidth > 767 ? 'flex--center' : ''];
            },
            getWidth() {
                return {
                    maxWidth: '100%',
                    width: this.formWi(),
                };
            },
            getBoxShad() {
                return this.dcrObject.dcr_form_shadow
                    ? (this.dcrObject.dcr_form_shadow_dir == 'BL' ? '-' : '')+'5px 5px 12px '+(this.dcrObject.dcr_form_shadow_color || '#777')
                    : null;
            },
            titleHeight() {
                let dcrHeight = this.dcrObject.dcr_title_height * (this.sm_text ? 0.8 : 1);
                return (dcrHeight || this.htmlTitleHeight) + 'px';
            },
            messageHeight() {
                return this.htmlMessageHeight ? this.htmlMessageHeight+'px' : 'auto';
            },
        },
        watch: {
            "dcrObject.dcr_title": () => {
                this.htmlTitleHeight = 0;
            },
            dcr_form_msg() {
                this.htmlMessageHeight = 0;
            },
        },
        methods: {
            saveTitleHeight() {
                this.$nextTick(() => {
                    if (!this.htmlTitleHeight && this.$refs.title_wrapper) {
                        this.htmlTitleHeight = this.$refs.title_wrapper.clientHeight;
                    }
                });
            },
            saveMessageHeight() {
                this.$nextTick(() => {
                    if (!this.htmlMessageHeight && this.$refs.message_wrapper) {
                        this.htmlMessageHeight = this.$refs.message_wrapper.clientHeight;
                    }
                });
            },
            toggleHeader() {
                this.show_header = !this.show_header;
            },
            changeViewT() {
                this.viewTable = !this.viewTable;
                this.$emit('set-view-table', this.viewTable);
            },
            titleWi() {
                let fw = this.dcrObject.dcr_title_width;
                let width = '100%';
                if (fw) {
                    width = fw <= 1 ? (fw*100)+'%' : fw+'px';
                }
                return width;
            },
            titleCenterLeft() {
                let fw = this.dcrObject.dcr_title_width;
                let left = '0%';
                if (fw) {
                    left = fw <= 1 ? (50 - fw*100/2)+'%' : 'calc(50% - '+fw/2+'px)';
                }
                return left;
            },
            //getters
            getBgCol(key, force) {
                return this.dcrObject.dcr_sec_bg_img && !force ? 'transparent' : (this.dcrObject[key] || 'transparent');
            },
            fontStyleObj(type) {
                let stl = SpecialFuncs.fontStyleObj(type, this.dcrObject);
                if (this.sm_text) {
                    stl.fontSize = ((parseInt(stl.fontSize) || 14) * 0.8) + 'px';
                    stl.lineHeight = ((parseInt(stl.lineHeight) || 16) * 0.8) + 'px';
                }
                return stl;
            },
            formBgTransp() {
                let clr = this.dcrObject.dcr_form_bg_color || 'transparent';
                if (clr !== 'transparent') {
                    let transp = to_float(this.dcrObject.dcr_form_transparency || 0) / 100 * 255;
                    transp = Math.ceil(transp);
                    transp = Math.max(Math.min(transp, 255), 0);
                    clr += Number(255 - transp).toString(16);
                }
                return clr;
            },
            formWi() {
                let fw = this.dcrObject.dcr_form_width;
                let width = '100%';
                if (fw) {
                    width = fw <= 1 ? (fw*100)+'%' : fw+'px';
                }
                return width;
            },
            storeRows(status) {
                this.$emit('store-rows-click', status);
            },
            canAllSave() {
                let can = false;
                _.each(this.allRows, (row) => {
                    can = can || this.availSave(row, true);
                });
                return can;
            },
            canAllSubmit() {
                let can = false;
                _.each(this.allRows, (row) => {
                    can = can || this.availSubmit(row, true);
                });
                return can;
            },
            canAllUpdate() {
                let can = false;
                _.each(this.allRows, (row) => {
                    can = can || this.availUpdate(row, true);
                });
                return can;
            },
        },
        mounted() {
        },
        beforeDestroy() {
        }
    }
</script>

<style lang="scss">
    .top-message-wrap {
        overflow: hidden;
        text-align: initial;
        position: relative;

        p, label {
            margin: 0 !important;
        }
    }
</style>

<style lang="scss" scoped>
    .dcr-top-msg {
        max-width: 100%;
    }

    .dcr-title {
        max-width: 100%;
        position: relative;
        overflow: hidden;
        transition: all 0.3s linear;
    }

    .hid {
        visibility: hidden;
    }
    .dcr-title--item {
        max-width: 100%;
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
    }
    .item__h1 {
        z-index: 20;
        margin: 0;
        width: 100%;
    }
    .item__img {
        z-index: 10;
    }

    .navbar-default {
        text-align: center;
        position: relative;
    }

    .fit-content {
        width: fit-content;
        margin: 0 auto;
    }

    .header--toggler {
        text-align: center;
        position: relative;
        height: 1px;
        cursor: pointer;
        margin: 0 auto;
        transition: 1s;
        opacity: 0;

        img {
            position: absolute;
            left: 0;
            width: 100%;
        }

        &:hover {
            opacity: 1;
        }
    }
    .header--bg {
        position: absolute;
        height: 15px;
        left: 0;
        right: 0;
        bottom: 0;
        z-index: -1;
    }
    .fa-edit {
        position: absolute;
        top: 3px;
        right: 2px;
        z-index: 250;
        cursor: pointer;
        color: #777;
    }
</style>