<template>
    <div class="full-frame flex flex--col">
        <table class="spaced-table bg-inherit">
            <tbody v-if="requestFields">
            <tr>
                <td :style="getTdStyle">
                    <div class="flex flex--center-v td td--100 h-32" :style="getTdStyle">
                        <!--<label>{{ $root.uniqName(requestFields['dcr_title'].name) }}:&nbsp;</label>-->
                        <input type="text" :style="textSysStyle" ref="dcr_title_input" v-model="requestRow['dcr_title']" :disabled="!with_edit" @change="updatedCell" class="form-control"/>
                        <cell-table-data-expand
                            v-if="dcr_title_expand"
                            style="background-color: #FFF; bottom: 5px; right: 3px;"
                            :table-meta="$root.settingsMeta['table_permissions']"
                            :table-row="requestRow"
                            :table-header="requestFields['dcr_title']"
                            :uniqid="titleuniqid"
                            :can-edit="with_edit"
                            :user="$root.user"
                        ></cell-table-data-expand>
                    </div>
                </td>
            </tr>

            <tr>
                <td :style="getTdStyle">
                    <div class="flex flex--center-v td td--25 h-32" :style="getTdStyle" style="padding-right: 27px;">
                        <label>Font:&nbsp;&nbsp;</label>
                        <select v-model="requestRow['dcr_title_font_type']" :style="textSysStyle" :disabled="!with_edit" @change="updatedCell" class="form-control">
                            <option v-for="fnt in avail_fonts">{{ fnt }}</option>
                        </select>
                    </div>
                    <div class="flex flex--center-v td td--25 h-32" :style="getTdStyle" style="padding-right: 19px;">
                        <label>Size:&nbsp;</label>
                        <input type="number" :style="textSysStyle" v-model="requestRow['dcr_title_font_size']" :disabled="!with_edit" @change="updatedCell" class="form-control"/>
                        <label>pt</label>
                    </div>
                    <div class="flex flex--center-v td td--25 h-32" :style="getTdStyle">
                        <label>Color:&nbsp;</label>
                        <div class="color-wrapper">
                            <tablda-colopicker
                                :init_color="requestRow['dcr_title_font_color']"
                                :fixed_pos="true"
                                :can_edit="with_edit"
                                :avail_null="true"
                                @set-color="updateColorFont"
                            ></tablda-colopicker>
                        </div>
                    </div>
                    <div class="flex flex--center-v td td--25 h-32" :style="getTdStyle">
                        <label>Style:&nbsp;</label>
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
                                :hdr_field="'dcr_title_font_style'"
                                :fld_input_type="'M-Select'"
                                :style="textSysStyle"
                                :is_disabled="!with_edit"
                                :init_no_open="true"
                                @selected-item="(item) => {updateMSelect(item, 'dcr_title_font_style')}"
                            ></tablda-select-simple>
                        </div>
                    </div>
                </td>
            </tr>

            <tr>
                <td :style="getTdStyle">
                    <div class="flex flex--center-v td td--25 h-32" :style="getTdStyle">
                        <label>Width:&nbsp;</label>
                        <input type="number" :style="textSysStyle" v-model="requestRow['dcr_title_width']" :disabled="!with_edit" @change="updatedCell" class="form-control"/>
                        <label>px</label>
                    </div>
                    <div class="flex flex--center-v td td--25 h-32" :style="getTdStyle" style="padding-right: 19px;">
                        <label>Height:&nbsp;</label>
                        <input type="number" :style="textSysStyle" v-model="requestRow['dcr_title_height']" :disabled="!with_edit" @change="updatedCell" class="form-control"/>
                        <label>px</label>
                    </div>
                    <div class="flex flex--center-v td td--25 h-32" :style="getTdStyle">
                    </div>
                </td>
            </tr>

            <tr>
                <td :style="getTdStyle">
                    <div class="flex flex--center-v td td--25 h-32" :style="getTdStyle" style="padding-right: 27px;">
                        <label ref="sec_tooltip_bgc" @mouseover="showSecBGC">Background by:&nbsp;&nbsp;</label>
                        <select class="form-control" :style="textSysStyle" :disabled="!with_edit" v-model="requestRow['dcr_title_background_by']" @change="updatedCell">
                            <option value="color">Color</option>
                            <option value="image">Image</option>
                        </select>
                    </div>
                    <template v-if="requestRow['dcr_title_background_by'] == 'color'">
                        <div class="flex flex--center-v td td--25 h-32" :style="getTdStyle" style="padding-right: 34px;">
                            <hover-block v-if="tit_tooltip_bgc && requestFields['dcr_title_bg_color'].tooltip"
                                         :html_str="requestFields['dcr_title_bg_color'].tooltip"
                                         :p_left="help_left"
                                         :p_top="help_top"
                                         :c_offset="help_offset"
                                         @another-click="tit_tooltip_bgc = false"
                                         @tooltip-blur="tit_tooltip_bgc = false"
                            ></hover-block>
                            <label ref="tit_tooltip_bgc" @mouseover="showTitBGC">BGC:&nbsp;</label>
                            <div class="color-wrapper">
                                <tablda-colopicker
                                    :init_color="requestRow['dcr_title_bg_color']"
                                    :fixed_pos="true"
                                    :can_edit="with_edit"
                                    :avail_null="true"
                                    @set-color="updateColorBg"
                                ></tablda-colopicker>
                            </div>
                        </div>
                        <div class="flex flex--center-v td td--25 h-32" :style="getTdStyle">
                        </div>
                    </template>

                    <template v-if="requestRow['dcr_title_background_by'] == 'image'">
                        <div class="flex flex--center-v td td--25 h-32" :style="getTdStyle">
                            <hover-block v-if="tit_bgi_tooltip && requestFields['dcr_title_bg_img'].tooltip"
                                         :html_str="requestFields['dcr_title_bg_img'].tooltip"
                                         :p_left="help_left"
                                         :p_top="help_top"
                                         :c_offset="help_offset"
                                         @another-click="tit_bgi_tooltip = false"
                                         @tooltip-blur="tit_bgi_tooltip = false"
                            ></hover-block>
                            <label ref="tit_bgi_tooltip" @mouseover="showBGITit">BGI:&nbsp;</label>
                            <img v-if="requestRow['dcr_title_bg_img']"
                                 :src="$root.fileUrl({url:requestRow['dcr_title_bg_img']}, 'sm')"
                                 class="img-preview h-32"
                            />
                            <input type="file" :style="textSysStyle" ref="bg_img" :disabled="!with_edit" @change="uploadFile" class="form-control"/>
                            <button
                                v-if="requestRow['dcr_title_bg_img']"
                                class="btn flex flex--center btn-danger btn-del-bg"
                                :disabled="!with_edit"
                                @click="delFile"
                            >&times;</button>
                        </div>
                        <div class="flex flex--center-v td td--25 h-32" :style="getTdStyle">
                            <label>Fit:&nbsp;</label>
                            <select v-model="requestRow['dcr_title_bg_fit']" :style="textSysStyle" :disabled="!with_edit" @change="updatedCell" class="form-control">
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
            <tr>
                <td :style="getTdStyle">
                    <label class="mt20">Top Message</label>
                </td>
            </tr>
            </tbody>
        </table>
        <div class="flex__elem-remain">
            <tab-ckeditor
                v-if="canCK"
                :table-meta="tableMeta"
                :target-row="requestRow"
                :field-name="'dcr_form_message'"
                @save-row="updatedCell"
            ></tab-ckeditor>
        </div>
    </div>
</template>

<script>
    import {SpecialFuncs} from '../../../../../classes/SpecialFuncs';

    import CellStyleMixin from "../../../../_Mixins/CellStyleMixin.vue";
    import ReqRowMixin from "./ReqRowMixin.vue";

    import TabldaColopicker from "../../../../CustomCell/InCell/TabldaColopicker.vue";
    import HoverBlock from "../../../../CommonBlocks/HoverBlock";
    import TabldaSelectSimple from "../../../../CustomCell/Selects/TabldaSelectSimple";
    import CellTableDataExpand from "../../../../CustomCell/InCell/CellTableDataExpand";
    import TabCkeditor from "../../../../CommonBlocks/TabCkeditor";

    export default {
        components: {
            TabCkeditor,
            CellTableDataExpand,
            TabldaSelectSimple,
            HoverBlock,
            TabldaColopicker,
        },
        mixins: [
            CellStyleMixin,
            ReqRowMixin,
        ],
        name: "TabSettingsRequestsRowTitle",
        data: function () {
            return {
                canCK: false,
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
            titleuniqid: String,
        },
        computed: {
            getTdStyle() {
                return {
                    height: this.tdCellHGT+'px',
                    ...this.textSysStyle,
                };
            },
            dcr_title_expand() {
                return this.$refs.dcr_title_input
                    ? String(this.requestRow['dcr_title']).length * 8 > this.$refs.dcr_title_input.clientWidth
                    : false;
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
            window.setTimeout(() => {
                this.canCK = true;
            }, 1);
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