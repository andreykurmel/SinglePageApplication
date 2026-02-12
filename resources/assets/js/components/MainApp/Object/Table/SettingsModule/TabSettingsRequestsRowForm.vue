<template>
    <table class="spaced-table bg-inherit">
        <tbody v-if="requestFields">
        <tr>
            <td :style="getTdStyle">
                <div class="flex flex--center-v td td--25 h-32" :style="getTdStyle" style="padding-right: 27px;">
                    <label>Width:&nbsp;</label>
                    <input type="number" :style="textSysStyle" v-model="requestRow['dcr_form_width']" :disabled="!with_edit" @change="updatedCell" class="form-control"/>
                    <label>px</label>
                </div>
                <div v-if="requestRow['one_per_submission'] != 1" class="flex flex--center-v td td--25 h-32" :style="getTdStyle" style="padding-right: 44px;">
                    <label>Listing Panel Width:&nbsp;</label>
                    <input type="number" :style="textSysStyle" v-model="requestRow['dcr_many_rows_width']" :disabled="!with_edit" @change="updatedCell" class="form-control"/>
                </div>
                <div class="flex flex--center-v td td--25 h-32" :style="getTdStyle" style="padding-right: 27px;">
                    <hover-block v-if="form_tooltip_bgc && requestFields['dcr_form_bg_color'].tooltip"
                                 :html_str="requestFields['dcr_form_bg_color'].tooltip"
                                 :p_left="help_left"
                                 :p_top="help_top"
                                 :c_offset="help_offset"
                                 @another-click="form_tooltip_bgc = false"
                                 @tooltip-blur="form_tooltip_bgc = false"
                    ></hover-block>
                    <label ref="form_tooltip_bgc" @mouseover="showFormBGC" style="width: 70px;flex-shrink: 0;">BGC:&nbsp;</label>
                    <div class="color-wrapper">
                        <tablda-colopicker
                            :init_color="requestRow['dcr_form_bg_color']"
                            :fixed_pos="true"
                            :can_edit="with_edit"
                            :avail_null="true"
                            class="h-32"
                            @set-color="updateColorFormBg"
                        ></tablda-colopicker>
                    </div>
                </div>
                <div class="flex flex--center-v td td--25 h-32" :style="getTdStyle" style="padding-right: 8px;">
                    <label style="width: 120px;flex-shrink: 0;">Transparency:&nbsp;</label>
                    <input type="number" :style="textSysStyle" v-model="requestRow['dcr_form_transparency']" :disabled="!with_edit" @change="updatedCell" class="form-control"/>
                    <label>%</label>
                </div>
            </td>
        </tr>

        <tr>
            <td :style="getTdStyle">
                <div class="flex flex--center-v td td--25 h-32" :style="getTdStyle" style="padding-right: 44px;">
                    <label>DIV Style:&nbsp;</label>
                    <select v-model="requestRow['dcr_form_line_type']" :style="textSysStyle" :disabled="!with_edit" @change="updatedCell" class="form-control">
                        <option value="line">Line</option>
                        <option value="space">Space</option>
                    </select>
                </div>
                <div class="flex flex--center-v td td--25 h-32" :style="getTdStyle">
                    <label>Top&nbsp;:&nbsp;</label>
                    <label class="switch_t">
                        <input type="checkbox" :disabled="!with_edit" v-model="requestRow['dcr_form_line_top']" @change="updatedCell">
                        <span class="toggler round"  :class="[!with_edit ? 'disabled' : '']"></span>
                    </label>
                    <label>&nbsp;Bot&nbsp;:&nbsp;</label>
                    <label class="switch_t">
                        <input type="checkbox" :disabled="!with_edit" v-model="requestRow['dcr_form_line_bot']" @change="updatedCell">
                        <span class="toggler round"  :class="[!with_edit ? 'disabled' : '']"></span>
                    </label>
                </div>
                <div v-if="requestRow['dcr_form_line_type'] === 'line'" class="flex flex--center-v td td--25 h-32" :style="getTdStyle" style="padding-right: 27px;">
                    <label style="width: 70px;flex-shrink: 0;">Color:&nbsp;</label>
                    <div class="color-wrapper">
                        <tablda-colopicker
                            :init_color="requestRow['dcr_form_line_color']"
                            :fixed_pos="true"
                            :can_edit="with_edit"
                            :avail_null="true"
                            class="h-32"
                            @set-color="updateColorLine"
                        ></tablda-colopicker>
                    </div>
                </div>
                <div v-if="requestRow['dcr_form_line_type'] === 'space'" class="flex flex--center-v td td--25 h-32" :style="getTdStyle">
                    <label style="width: 70px;flex-shrink: 0;">Radius:&nbsp;</label>
                    <input type="number" :style="textSysStyle" v-model="requestRow['dcr_form_line_radius']" :disabled="!with_edit" @change="updatedCell" class="form-control"/>
                    <label>px</label>
                </div>
                <div class="flex flex--center-v td td--25 h-32" :style="getTdStyle">
                    <label style="width: 120px;flex-shrink: 0;">Thickness:&nbsp;</label>
                    <input type="number" :style="textSysStyle" v-model="requestRow['dcr_form_line_thick']" :disabled="!with_edit" @change="updatedCell" class="form-control"/>
                    <label>px</label>
                </div>
            </td>
        </tr>

        <tr>
            <td :style="getTdStyle">
                <div class="flex flex--center-v td td--25 h-32" :style="getTdStyle">
                    <label>Shadow:&nbsp;</label>
                    <label class="switch_t">
                        <input type="checkbox" :disabled="!with_edit" v-model="requestRow['dcr_form_shadow']" @change="updatedCell">
                        <span class="toggler round" :class="[!with_edit ? 'disabled' : '']"></span>
                    </label>
                </div>
                <div class="flex flex--center-v td td--25 h-32" :style="getTdStyle" style="padding-right: 44px;">
                    <label>Color:&nbsp;</label>
                    <div class="color-wrapper">
                        <tablda-colopicker
                            :init_color="requestRow['dcr_form_shadow_color']"
                            :fixed_pos="true"
                            :can_edit="with_edit"
                            :avail_null="true"
                            @set-color="updateShadowColor"
                        ></tablda-colopicker>
                    </div>
                </div>
                <div class="flex flex--center-v td td--25 h-32" :style="getTdStyle" style="padding-right: 26px;">
                    <label style="width: 70px;flex-shrink: 0;">Style:&nbsp;</label>
                    <select v-model="requestRow['dcr_form_shadow_dir']" :style="textSysStyle" :disabled="!with_edit" @change="updatedCell" class="form-control">
                        <option value="BR">BR</option>
                        <option value="BL">BL</option>
                    </select>
                </div>
            </td>
        </tr>

        <tr>
            <td :style="getTdStyle">
                <div class="flex flex--center-v td td--25 h-32" :style="getTdStyle">
                    <label>Row height:&nbsp;</label>
                    <input type="number" :style="textSysStyle" v-model="requestRow['dcr_form_line_height']" :disabled="!with_edit" @change="updatedCell" class="form-control"/>
                    <label>&nbsp;px&nbsp;</label>
                </div>
                <div class="flex flex--center-v td td--25 h-32" :style="getTdStyle">
                    <label>Font size:&nbsp;</label>
                    <input type="number" :style="textSysStyle" v-model="requestRow['dcr_form_font_size']" :disabled="!with_edit" @change="updatedCell" class="form-control"/>
                    <label>&nbsp;px&nbsp;</label>
                </div>
                <div class="flex flex--center-v td td--25 h-32" :style="getTdStyle">
                </div>
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

    export default {
        components: {
            HoverBlock,
            TabldaColopicker,
        },
        mixins: [
            CellStyleMixin,
            ReqRowMixin,
        ],
        name: "TabSettingsRequestsRowForm",
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