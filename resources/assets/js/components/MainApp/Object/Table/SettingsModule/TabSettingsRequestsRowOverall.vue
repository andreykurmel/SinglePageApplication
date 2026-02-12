<template>
    <table class="spaced-table bg-inherit">
        <tbody v-if="requestFields">
        <tr>
            <td :style="getTdStyle">
                <div class="flex flex--center-v td td--25 h-32" :style="getTdStyle">
                    <label>Style:&nbsp;</label>
                    <select class="form-control"
                            :style="textSysStyle"
                            :disabled="!with_edit"
                            v-model="requestRow['dcr_sec_scroll_style']"
                            @change="updatedCell">
                        <option value="scroll">Scroll</option>
                        <option value="flow">Flow</option>
                        <option value="conversational">Conversational</option>
                        <option value="accordion">Accordion</option>
                        <option value="horizontal_tabs">HTabs</option>
                    </select>
                </div>
                <div v-if="requestRow.dcr_sec_scroll_style === 'flow'" class="flex flex--center-v td td--25 h-32" :style="getTdStyle">
                    <label>&nbsp;Sticky Header and Top Message:&nbsp;</label>
                    <label class="switch_t">
                        <input type="checkbox" :disabled="!with_edit" v-model="requestRow['dcr_flow_header_stick']" @change="updatedCell">
                        <span class="toggler round"  :class="[!with_edit ? 'disabled' : '']"></span>
                    </label>
                </div>
                <template v-if="requestRow.dcr_sec_scroll_style === 'conversational'">
                    <div class="flex flex--center-v td td--50 h-32" :style="getTdStyle">
                        <label class="switch_t">
                            <input type="checkbox" :disabled="!with_edit" v-model="requestRow['dcr_sec_slide_top_header']" @change="updatedCell">
                            <span class="toggler round"  :class="[!with_edit ? 'disabled' : '']"></span>
                        </label>
                        <label>&nbsp;:&nbsp;Title Top Message and Notes as the First Slide</label>
                    </div>
                    <div class="flex flex--center-v td td--25 h-32" :style="getTdStyle">
                        <label class="switch_t">
                            <input type="checkbox" :disabled="!with_edit" v-model="requestRow['dcr_sec_slide_progresbar']" @change="updatedCell">
                            <span class="toggler round"  :class="[!with_edit ? 'disabled' : '']"></span>
                        </label>
                        <label>&nbsp;:&nbsp;Progress Bar</label>
                    </div>
                </template>
                <template v-if="requestRow.dcr_sec_scroll_style === 'accordion' || requestRow.dcr_sec_scroll_style === 'horizontal_tabs'">
                    <div v-if="requestRow.dcr_sec_scroll_style === 'accordion'" class="flex flex--center-v td td--25 h-32" :style="getTdStyle">
                        <label class="switch_t">
                            <input type="checkbox" :disabled="!with_edit" v-model="requestRow['dcr_accordion_single_open']" @change="updatedCell">
                            <span class="toggler round"  :class="[!with_edit ? 'disabled' : '']"></span>
                        </label>
                        <label>&nbsp;:&nbsp;Open one panel at a time</label>
                    </div>
                    <div class="flex flex--center-v td td--25 h-32" :style="getTdStyle" style="padding-right: 52px">
                        <label>{{ requestRow.dcr_sec_scroll_style === 'accordion' ? 'Panel' : 'Tab' }} Color&nbsp;:&nbsp;</label>
                        <div class="color-wrapper">
                            <tablda-colopicker
                                :init_color="requestRow['dcr_tab_bg_color']"
                                :fixed_pos="true"
                                :can_edit="with_edit"
                                :avail_null="true"
                                class="h-32"
                                @set-color="updateTabBgColor"
                            ></tablda-colopicker>
                        </div>
                    </div>
                    <div class="flex flex--center-v td td--25 h-32" :style="getTdStyle">
                        <label style="width: 68px;flex-shrink: 0;">Height:&nbsp;</label>
                        <input type="number"
                               :style="textSysStyle"
                               v-model="requestRow['dcr_tab_height']"
                               :disabled="!with_edit"
                               @change="updatedCell"
                               class="form-control"/>
                        <label>px</label>
                    </div>
                    <div v-if="requestRow.dcr_sec_scroll_style === 'horizontal_tabs'" class="flex flex--center-v td td--25 h-32" :style="getTdStyle">
                    </div>
                </template>
            </td>
        </tr>
        <tr v-show="requestRow.dcr_sec_scroll_style === 'accordion' || requestRow.dcr_sec_scroll_style === 'horizontal_tabs'">
            <td :style="getTdStyle">
                <div class="flex flex--center-v td td--25 h-32" :style="getTdStyle">
                    <label>Font:&nbsp;&nbsp;</label>
                    <select v-model="requestRow['dcr_tab_font_type']"
                            :style="textSysStyle"
                            :disabled="!with_edit"
                            @change="updatedCell"
                            class="form-control"
                    >
                        <option v-for="fnt in avail_fonts">{{ fnt }}</option>
                    </select>
                </div>
                <div class="flex flex--center-v td td--25 h-32" :style="getTdStyle" style="padding-right: 35px">
                    <label>Size:&nbsp;</label>
                    <input type="number"
                           :style="textSysStyle"
                           v-model="requestRow['dcr_tab_font_size']"
                           :disabled="!with_edit"
                           @change="updatedCell"
                           class="form-control"/>
                    <label>pt</label>
                </div>
                <div class="flex flex--center-v td td--25 h-32" :style="getTdStyle" style="padding-right: 26px;">
                    <label style="width: 68px;flex-shrink: 0;">Color:&nbsp;</label>
                    <div class="color-wrapper">
                        <tablda-colopicker
                            :init_color="requestRow['dcr_tab_font_color']"
                            :fixed_pos="true"
                            :can_edit="with_edit"
                            :avail_null="true"
                            @set-color="updateColorTab"
                        ></tablda-colopicker>
                    </div>
                </div>
                <div class="flex flex--center-v td td--25 h-32" :style="getTdStyle" style="padding-right: 17px;">
                    <label style="width: 93px; flex-shrink: 0;">Style:&nbsp;</label>
                    <div class="full-height full-width" style="position:relative;">
                        <tablda-select-simple
                            :options="[
                                {val: 'Normal', show: 'Normal'},
                                {val: 'Italic', show: 'Italic'},
                                {val: 'Bold', show: 'Bold'},
                                {val: 'Strikethrough', show: 'Strikethrough'},
                                {val: 'Overline', show: 'Overline'},
                                {val: 'Underline', show: 'Underline'},
                            ]"
                            :table-row="requestRow"
                            :hdr_field="'dcr_tab_font_style'"
                            :fld_input_type="'M-Select'"
                            :style="textSysStyle"
                            :is_disabled="!with_edit"
                            :init_no_open="true"
                            @selected-item="(item) => {updateMSelect(item, 'dcr_tab_font_style')}"
                        ></tablda-select-simple>
                    </div>
                </div>
            </td>
        </tr>
        <tr>
            <td :style="getTdStyle">
                <div class="flex flex--center-v td td--25 h-32" :style="getTdStyle">
                    <label>Division Line:&nbsp;&nbsp;&nbsp;</label>
                </div>
                <div class="flex flex--center-v td td--25 h-32" :style="getTdStyle">
                    <label>Top&nbsp;:&nbsp;</label>
                    <label class="switch_t">
                        <input type="checkbox" :disabled="!with_edit" v-model="requestRow['dcr_sec_line_top']" @change="updatedCell">
                        <span class="toggler round"  :class="[!with_edit ? 'disabled' : '']"></span>
                    </label>
                    <label>&nbsp;Bot&nbsp;:&nbsp;</label>
                    <label class="switch_t">
                        <input type="checkbox" :disabled="!with_edit" v-model="requestRow['dcr_sec_line_bot']" @change="updatedCell">
                        <span class="toggler round"  :class="[!with_edit ? 'disabled' : '']"></span>
                    </label>
                </div>
                <div class="flex flex--center-v td td--25 h-32" :style="getTdStyle" style="padding-right: 26px;">
                    <label style="width: 68px;flex-shrink: 0;">Color:&nbsp;</label>
                    <div class="color-wrapper">
                        <tablda-colopicker
                            :init_color="requestRow['dcr_sec_line_color']"
                            :fixed_pos="true"
                            :can_edit="with_edit"
                            :avail_null="true"
                            class="h-32"
                            @set-color="updateSecColorLine"
                        ></tablda-colopicker>
                    </div>
                </div>
                <div class="flex flex--center-v td td--25 h-32" :style="getTdStyle">
                    <label style="width: 93px; flex-shrink: 0;">Thickness:&nbsp;</label>
                    <input type="number"
                           :style="textSysStyle"
                           v-model="requestRow['dcr_sec_line_thick']"
                           :disabled="!with_edit"
                           @change="updatedCell"
                           class="form-control"/>
                    <label>px</label>
                </div>
            </td>
        </tr>

        <tr>
            <td :style="getTdStyle">
                <div class="flex flex--center-v td td--25 h-32" :style="getTdStyle">
                    <label ref="sec_tooltip_bgc" @mouseover="showSecBGC">Background by:&nbsp;&nbsp;</label>
                    <select class="form-control"
                            :style="textSysStyle"
                            :disabled="!with_edit"
                            v-model="requestRow['dcr_sec_background_by']"
                            @change="updatedCell"
                    >
                        <option value="color">Color</option>
                        <option value="image">Image</option>
                    </select>
                </div>
                <template v-if="requestRow['dcr_sec_background_by'] == 'color'">
                    <div class="flex flex--center-v td td--25 h-32" :style="getTdStyle">
                        <hover-block v-if="sec_tooltip_bgc && requestFields['dcr_sec_bg_top'].tooltip"
                                     :html_str="requestFields['dcr_sec_bg_top'].tooltip"
                                     :p_left="help_left"
                                     :p_top="help_top"
                                     :c_offset="help_offset"
                                     @another-click="sec_tooltip_bgc = false"
                                     @tooltip-blur="sec_tooltip_bgc = false"
                        ></hover-block>
                        <label>Top:&nbsp;</label>
                        <div class="color-wrapper">
                            <tablda-colopicker
                                :init_color="requestRow['dcr_sec_bg_top']"
                                :fixed_pos="true"
                                :can_edit="with_edit"
                                :avail_null="true"
                                class="h-32"
                                @set-color="updateSecTopColor"
                            ></tablda-colopicker>
                        </div>
                        <div class="flex flex--center-v td td--25 h-32" :style="getTdStyle">
                        </div>
                        <label>Bot:&nbsp;</label>
                        <div class="color-wrapper">
                            <tablda-colopicker
                                :init_color="requestRow['dcr_sec_bg_bot']"
                                :fixed_pos="true"
                                :can_edit="with_edit"
                                :avail_null="true"
                                class="h-32"
                                @set-color="updateSecBotColor"
                            ></tablda-colopicker>
                        </div>
                    </div>
                    <div class="flex flex--center-v td td--25 h-32" :style="getTdStyle">
                    </div>
                </template>

                <template v-if="requestRow['dcr_sec_background_by'] == 'image'">
                    <div class="flex flex--center-v td td--25 h-32" :style="getTdStyle" style="padding-right: 26px;">
                        <hover-block v-if="sec_bgi_tooltip && requestFields['dcr_sec_bg_img'].tooltip"
                                     :html_str="requestFields['dcr_sec_bg_img'].tooltip"
                                     :p_left="help_left"
                                     :p_top="help_top"
                                     :c_offset="help_offset"
                                     @another-click="sec_bgi_tooltip = false"
                                     @tooltip-blur="sec_bgi_tooltip = false"
                        ></hover-block>
                        <label ref="sec_bgi_tooltip" @mouseover="showBGISec">BGI:&nbsp;</label>
                        <img v-if="requestRow['dcr_sec_bg_img']"
                             :src="$root.fileUrl({url:requestRow['dcr_sec_bg_img']}, 'sm')"
                             class="img-preview h-32"
                        />
                        <input type="file" :style="textSysStyle" ref="bg_img_sec" :disabled="!with_edit" @change="uploadSecFile" class="form-control"/>
                        <button
                            v-if="requestRow['dcr_sec_bg_img']"
                            class="btn flex flex--center btn-danger btn-del-bg"
                            :disabled="!with_edit"
                            @click="delSecFile"
                        >&times;</button>
                    </div>
                    <div class="flex flex--center-v td td--25 h-32" :style="getTdStyle" style="padding-right: 26px;">
                        <label style="width: 68px;flex-shrink: 0;">Fit:&nbsp;</label>
                        <select v-model="requestRow['dcr_sec_bg_img_fit']" :style="textSysStyle" :disabled="!with_edit" @change="updatedCell" class="form-control">
                            <option>Height</option>
                            <option>Width</option>
                            <option>Fill</option>
                        </select>
                    </div>
                    <div class="flex flex--center-v td td--25 h-32" :style="getTdStyle">
                    </div>
                </template>
            </td>
        </tr>
        </tbody>
    </table>
</template>

<script>
    import {SpecialFuncs} from '../../../../../classes/SpecialFuncs';

    import CellStyleMixin from "../../../../_Mixins/CellStyleMixin.vue";
    import ReqRowMixin from "./ReqRowMixin.vue";

    import TabldaColopicker from "../../../../CustomCell/InCell/TabldaColopicker.vue";
    import HoverBlock from "../../../../CommonBlocks/HoverBlock";
    import TabldaSelectSimple from "../../../../CustomCell/Selects/TabldaSelectSimple";

    export default {
        components: {
            TabldaSelectSimple,
            HoverBlock,
            TabldaColopicker,
        },
        mixins: [
            CellStyleMixin,
            ReqRowMixin,
        ],
        name: "TabSettingsRequestsRowOverall",
        data: function () {
            return {
            };
        },
        props: {
            table_id: Number,
            cellHeight: Number,
            maxCellRows: Number,
            tableRequest: Object,
            requestRow: Object,
            tableMeta: Object,
            with_edit: Boolean,
        },
        computed: {
            getTdStyle() {
                return {
                    height: this.tdCellHGT+'px',
                    ...this.textSysStyle,
                };
            },
        },
        watch: {
            table_id(val) {
                this.setAvailFields();
            },
        },
        methods: {
        },
        mounted() {
            this.setAvailFields();
        },
        beforeDestroy() {
        }
    }
</script>

<style lang="scss" scoped>
    @import "./TabSettingsPermissions";
    @import "./ReqRowStyle";

    .ck_textarea {
        margin-top: 5px;
        height: 300px;
    }

    .add_link {
        white-space: nowrap;
        margin-bottom: 10px;

        label {
            margin: 0;
        }
        select {
            max-width: 250px;
            height: 30px;
            padding: 3px 6px;
        }
    }

    .max-sm {
        max-width: 75px;
    }
    .btn-default {
        height: 30px;
    }
</style>