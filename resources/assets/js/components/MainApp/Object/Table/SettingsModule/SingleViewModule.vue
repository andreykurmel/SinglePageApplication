<template>
    <div class="permissions-panel full-height">
        <div class="permissions-menu-header">
            <button class="btn btn-default btn-sm" :style="textSysStyle" :class="{active : activeTab === 'srv_general'}" @click="activeTab = 'srv_general'">
                General
            </button>
            <button class="btn btn-default btn-sm" :style="textSysStyle" :class="{active : activeTab === 'srv_fields'}" @click="activeTab = 'srv_fields'">
                Field Specific
            </button>
        </div>
        <div class="permissions-menu-body">
            <div v-show="activeTab === 'srv_general'" class="full-frame" style="padding: 10px;" :style="textSysStyle">
                <div class="form-group flex flex--center-v">
                    <label class="switch_t" style="display: inline-block;margin-right: 5px;">
                        <input type="checkbox" v-model="tableMeta.single_view_active" :disabled="!canEditView" @change="updatedCell">
                        <span class="toggler round" :class="[!canEditView ? 'disabled' : '']"></span>
                    </label>
                    <label>&nbsp;Status</label>

                    <label class="">&nbsp;&nbsp;&nbsp;Permission to be applied:&nbsp;</label>
                    <select-block
                        :options="permisOpt()"
                        :sel_value="tableMeta.single_view_permission_id"
                        :style="{ maxWidth:'200px', height:'32px', ...textSysStyle, }"
                        :with_links="true"
                        :is_disabled="!canEditView"
                        @option-select="permisUpdate"
                        @link-click="permisShow"
                    ></select-block>
                </div>
                <div class="form-group flex flex--center-v">
                    <label class="">Select a field for saving:</label>
                </div>
                <div class="form-group flex flex--center-v">
                    <label>URL,&nbsp;Prefix:&nbsp;</label>
                    <button class="btn btn-primary btn-sm blue-gradient"
                            :title="srvUrl"
                            :style="$root.themeButtonStyle"
                            @click="copyUrl()"
                    >{{ copyText }}</button>
                    <label class="">&nbsp;Record specific hash:&nbsp;</label>
                    <select-block
                        :options="stringFlds()"
                        :sel_value="tableMeta.single_view_url_id"
                        :style="{ maxWidth:'200px', height:'32px', ...textSysStyle, }"
                        :is_disabled="!canEditView"
                        @option-select="(opt) => { statusUpdate('single_view_url_id', opt.val) }"
                    ></select-block>
                    <label>&nbsp;Data Range:&nbsp;</label>
                    <select-block
                        :is_disabled="!tableMeta.single_view_regenerate"
                        :options="getRGr(tableMeta, true)"
                        :sel_value="tableMeta.single_view_regenerate_datarange"
                        :style="{ maxWidth:'150px', height:'32px', ...textSysStyle, }"
                        class="h-snp"
                        @option-select="(opt) => { statusUpdate('single_view_regenerate_datarange', opt.val) }"
                    ></select-block>
                    <label class="">&nbsp;</label>
                    <label class="switch_t" style="display: inline-block;margin-right: 5px;">
                        <input type="checkbox" v-model="tableMeta.single_view_regenerate" :disabled="!canEditView" @change="updatedCell">
                        <span class="toggler round" :class="[!canEditView ? 'disabled' : '']"></span>
                    </label>
                    <label>&nbsp;Regenerate all upon selecting.&nbsp;</label>
                </div>
                <div class="form-group flex flex--center-v">
                    <label class="">Password:&nbsp;</label>
                    <select-block
                        :options="stringFlds()"
                        :sel_value="tableMeta.single_view_password_id"
                        :style="{ maxWidth:'200px', height:'32px', ...textSysStyle, }"
                        :is_disabled="!canEditView"
                        @option-select="passUpdate"
                    ></select-block>

                    <label class="">&nbsp;&nbsp;&nbsp;Visibility:&nbsp;</label>
                    <select-block
                        :options="statusOpt()"
                        :sel_value="tableMeta.single_view_status_id"
                        :style="{ maxWidth:'200px', height:'32px', ...textSysStyle, }"
                        :is_disabled="!canEditView"
                        @option-select="(opt) => { statusUpdate('single_view_status_id', opt.val) }"
                    ></select-block>

                    <label class="">&nbsp;&nbsp;&nbsp;Editability:&nbsp;</label>
                    <select-block
                        :options="statusOpt()"
                        :sel_value="tableMeta.single_view_edit_id"
                        :style="{ maxWidth:'200px', height:'32px', ...textSysStyle, }"
                        :is_disabled="!canEditView"
                        @option-select="(opt) => { statusUpdate('single_view_edit_id', opt.val) }"
                    ></select-block>
                </div>

                <div class="form-group flex flex--center-v">
                    <label>Background by:&nbsp;</label>
                    <select class="form-control view-select--md" :style="textSysStyle" :disabled="!canEditView" v-model="tableMeta.single_view_background_by" @change="updatedCell">
                        <option value="color">Color</option>
                        <option value="image">Image</option>
                    </select>

                    <template v-if="tableMeta.single_view_background_by == 'color'">
                        <label>&nbsp;&nbsp;&nbsp;BGC:&nbsp;</label>
                        <div class="color-wrapper clr-min">
                            <tablda-colopicker
                                :init_color="tableMeta.single_view_bg_color"
                                :fixed_pos="true"
                                :can_edit="canEditView"
                                :avail_null="true"
                                @set-color="updateBgColor"
                            ></tablda-colopicker>
                        </div>
                    </template>

                    <template v-if="tableMeta.single_view_background_by == 'image'">
                        <label>&nbsp;&nbsp;&nbsp;BGI:&nbsp;</label>
                        <img v-if="tableMeta.single_view_bg_img"
                             :src="$root.fileUrl({url:tableMeta.single_view_bg_img}, 'sm')"
                             class="img-preview"
                        />
                        <input type="file" ref="bg_img_sec" :style="textSysStyle" :disabled="!canEditView" @change="uploadBgiFile" class="form-control view-select"/>
                        <button
                            v-if="tableMeta.single_view_bg_img"
                            class="btn flex flex--center btn-danger btn-del-bg"
                            :disabled="!canEditView"
                            @click="delBgiFile"
                        >&times;</button>

                        <label>&nbsp;&nbsp;&nbsp;Fitting:&nbsp;</label>
                        <select-block
                            :options="[
                                { show: 'Height', val: 'Height' },
                                { show: 'Width', val: 'Width' },
                                { show: 'Fill', val: 'Fill' },
                            ]"
                            :sel_value="tableMeta.single_view_bg_fit"
                            :style="{ maxWidth:'100px', height:'32px', ...textSysStyle, }"
                            :is_disabled="!canEditView"
                            @option-select="(opt) => { seleUpdate(opt, 'single_view_bg_fit'); }"
                        ></select-block>
                    </template>
                </div>

                <div class="form-group flex flex--center-v">
                    <h3>Header</h3>
                </div>

                <div class="form-group flex flex--center-v">
                    <label>Content:&nbsp;</label>
                    <div style="position: relative;">
                        <input type="text"
                               style="width: 400px;"
                               :style="textSysStyle"
                               v-model="tableMeta.single_view_header"
                               :disabled="!canEditView"
                               @keyup="recreaFormula('formula_view_header')"
                               @focus="formula_view_header = true"
                               @change="updatedCell"
                               class="form-control"/>
                        <formula-helper
                            v-if="formula_view_header"
                            :user="$root.user"
                            :table-meta="tableMeta"
                            :table-row="tableMeta"
                            :header-key="'single_view_header'"
                            :can-edit="canEditView"
                            :no-function="true"
                            :no_prevent="true"
                            :pop_width="'100%'"
                            style="padding: 0; color: #333;"
                            @close-formula="formula_view_header = false"
                            @set-formula="updatedCell"
                        ></formula-helper>
                    </div>

                    <label>&nbsp;&nbsp;&nbsp;BGC:&nbsp;</label>
                    <div class="color-wrapper clr-min">
                        <tablda-colopicker
                            :init_color="tableMeta.single_view_header_background"
                            :fixed_pos="true"
                            :can_edit="canEditView"
                            :avail_null="true"
                            @set-color="(clr, save) => { updateColor('single_view_header_background', clr, save); }"
                        ></tablda-colopicker>
                    </div>
                </div>

                <div class="form-group flex flex--center-v">
                    <label>Font style:&nbsp;</label>
                    <select-block
                        :options="[
                            {val: 'Normal', show: 'Normal'},
                            {val: 'Italic', show: 'Italic'},
                            {val: 'Bold', show: 'Bold'},
                            {val: 'Strikethrough', show: 'Strikethrough'},
                            {val: 'Overline', show: 'Overline'},
                            {val: 'Underline', show: 'Underline'},
                        ]"
                        :is_multiselect="true"
                        :sel_value="tableMeta.single_view_header_font"
                        :style="{ maxWidth:'200px', height:'32px', ...textSysStyle, }"
                        :is_disabled="!canEditView"
                        @option-select="mselUpdate"
                    ></select-block>

                    <label>&nbsp;&nbsp;&nbsp;Size:&nbsp;</label>
                    <input type="number" v-model="tableMeta.single_view_header_font_size" :style="textSysStyle" :disabled="!canEditView" @change="updatedCell" class="form-control view-select--sm"/>
                    <label>&nbsp;px&nbsp;</label>

                    <label>&nbsp;&nbsp;&nbsp;Color:&nbsp;</label>
                    <div class="color-wrapper clr-min">
                        <tablda-colopicker
                            :init_color="tableMeta.single_view_header_color"
                            :fixed_pos="true"
                            :can_edit="canEditView"
                            :avail_null="true"
                            @set-color="(clr, save) => { updateColor('single_view_header_color', clr, save); }"
                        ></tablda-colopicker>
                    </div>
                </div>

                <div class="form-group flex flex--center-v">
                    <label>Placement&nbsp;&nbsp;AlignH:&nbsp;</label>
                    <select-block
                        :options="[
                                { show: 'Left', val: 'Left' },
                                { show: 'Center', val: 'Center' },
                                { show: 'Right', val: 'Right' },
                            ]"
                        :sel_value="tableMeta.single_view_header_align_h"
                        :style="{ maxWidth:'100px', height:'32px', ...textSysStyle, }"
                        :is_disabled="!canEditView"
                        @option-select="(opt) => { seleUpdate(opt, 'single_view_header_align_h'); }"
                    ></select-block>

                    <label>&nbsp;&nbsp;&nbsp;AlignV:&nbsp;</label>
                    <select-block
                        :options="[
                                { show: 'Top', val: 'Top' },
                                { show: 'Middle', val: 'Middle' },
                                { show: 'Bottom', val: 'Bottom' },
                            ]"
                        :sel_value="tableMeta.single_view_header_align_v"
                        :style="{ maxWidth:'100px', height:'32px', ...textSysStyle, }"
                        :is_disabled="!canEditView"
                        @option-select="(opt) => { seleUpdate(opt, 'single_view_header_align_v'); }"
                    ></select-block>

                    <label>&nbsp;&nbsp;&nbsp;Height:&nbsp;</label>
                    <input type="number" v-model="tableMeta.single_view_header_height" :style="textSysStyle" :disabled="!canEditView" @change="updatedCell" class="form-control view-select--sm"/>
                    <label>&nbsp;px&nbsp;</label>
                </div>

                <div class="form-group flex flex--center-v">
                    <h3>Table</h3>
                </div>

                <div class="form-group flex flex--center-v">
                    <label>Placement&nbsp;</label>

                    <label>&nbsp;AlignH:&nbsp;</label>
                    <select-block
                        :options="[
                                { show: 'Left', val: 'Left' },
                                { show: 'Center', val: 'Center' },
                                { show: 'Right', val: 'Right' },
                            ]"
                        :sel_value="tableMeta.single_view_form_align_h"
                        :style="{ maxWidth:'100px', height:'32px', ...textSysStyle, }"
                        :is_disabled="!canEditView"
                        @option-select="(opt) => { seleUpdate(opt, 'single_view_form_align_h'); }"
                    ></select-block>

                    <label>&nbsp;&nbsp;&nbsp;AlignV:&nbsp;</label>
                    <select-block
                        :options="[
                                { show: 'Top', val: 'Top' },
                                { show: 'Middle', val: 'Middle' },
                                { show: 'Bottom', val: 'Bottom' },
                            ]"
                        :sel_value="tableMeta.single_view_form_align_v"
                        :style="{ maxWidth:'100px', height:'32px', ...textSysStyle, }"
                        :is_disabled="!canEditView"
                        @option-select="(opt) => { seleUpdate(opt, 'single_view_form_align_v'); }"
                    ></select-block>
                </div>

                <div class="form-group flex flex--center-v">
                    <label>Width:&nbsp;</label>
                    <input type="number" v-model="tableMeta.single_view_form_width" :style="textSysStyle" :disabled="!canEditView" @change="updatedCell" class="form-control view-select--sm"/>
                    <label>&nbsp;px&nbsp;</label>

                    <label>&nbsp;&nbsp;&nbsp;BGC&nbsp;:&nbsp;</label>
                    <div class="color-wrapper clr-min">
                        <tablda-colopicker
                            :init_color="tableMeta.single_view_form_background"
                            :fixed_pos="true"
                            :can_edit="canEditView"
                            :avail_null="true"
                            @set-color="updateFormBgClr"
                        ></tablda-colopicker>
                    </div>

                    <label>&nbsp;&nbsp;&nbsp;Transparency:&nbsp;</label>
                    <input type="number" v-model="tableMeta.single_view_form_transparency" :style="textSysStyle" :disabled="!canEditView" @change="updatedCell" class="form-control view-select--sm"/>
                    <label>&nbsp;%</label>
                </div>

                <div class="form-group flex flex--center-v">
                    <label>Row height:&nbsp;</label>
                    <input type="number" v-model="tableMeta.single_view_form_line_height" :style="textSysStyle" :disabled="!canEditView" @change="updatedCell" class="form-control view-select--sm"/>
                    <label>&nbsp;px&nbsp;</label>

                    <label>&nbsp;&nbsp;Font size:&nbsp;</label>
                    <input type="number" v-model="tableMeta.single_view_form_font_size" :style="textSysStyle" :disabled="!canEditView" @change="updatedCell" class="form-control view-select--sm"/>
                    <label>&nbsp;px&nbsp;</label>
                </div>
            </div>

            <div v-show="activeTab === 'srv_fields'" class="full-frame">
                <custom-table
                    :cell_component_name="'custom-cell-pivot-field'"
                    :global-meta="tableMeta"
                    :table-meta="$root.settingsMeta['table_srv_2_table_fields']"
                    :all-rows="allFields"
                    :rows-count="allFields.length"
                    :cell-height="1"
                    :max-cell-rows="0"
                    :is-full-width="true"
                    :use_theme="true"
                    :with_edit="tableMeta._is_owner && withEdit"
                    :user="$root.user"
                    :behavior="'pivot'"
                    :parent-row="tableMeta"
                    :available-columns="['_name','width_of_table_popup','is_start_table_popup','is_table_field_in_popup','is_hdr_lvl_one_row','is_dcr_section']"
                    @check-row="srvSettCheck"
                ></custom-table>
            </div>
        </div>
    </div>
</template>

<script>
    import {SpecialFuncs} from "../../../../../classes/SpecialFuncs";

    import {eventBus} from '../../../../../app';

    import SelectBlock from "../../../../CommonBlocks/SelectBlock";
    import TabldaColopicker from "../../../../CustomCell/InCell/TabldaColopicker";
    import CustomTable from "../../../../CustomTable/CustomTable";

    import DataRangeMixin from "../../../../_Mixins/DataRangeMixin.vue";
    import CellStyleMixin from "../../../../_Mixins/CellStyleMixin";

    export default {
        name: "SingleViewModule",
        components: {
            CustomTable,
            TabldaColopicker,
            SelectBlock
        },
        mixins: [
            DataRangeMixin,
            CellStyleMixin,
        ],
        data: function () {
            return {
                copyText: 'Copy',
                withEdit: true,
                activeTab: 'srv_general',
                formula_view_header: false,
            }
        },
        props: {
            tableMeta: Object,
        },
        computed: {
            canEditView() {
                return this.tableMeta._is_owner
                    || // OR user with available rights for add View
                    (this.tableMeta._current_right && this.tableMeta._current_right.can_create_view);
            },
            selPermis() {
                return _.find(this.tableMeta._table_permissions, {id: Number(this.tableMeta.single_view_permission_id)});
            },
            allFields() {
                if (!this.selPermis) {
                    return [];
                }
                let colgr = _.map(this.selPermis._permission_columns, 'table_column_group_id');
                let avaFields = [];
                _.each(this.tableMeta._column_groups, (colGroup) => {
                    if (this.$root.inArray(colGroup.id, colgr)) {
                        avaFields = avaFields.concat( _.map(colGroup._fields, 'field') );
                    }
                });

                return _.filter(this.tableMeta._fields, (fld) => {
                    return this.$root.systemFieldsNoId.indexOf(fld.field) === -1
                        && avaFields.indexOf(fld.field) > -1;
                });
            },
            srvUrl() {
                return this.$root.clear_url + '/srv/' + this.tableMeta.hash;
            },
        },
        methods: {
            copyUrl() {
                SpecialFuncs.strToClipboard(this.srvUrl);
                this.copyText = 'Copied!';
                setTimeout(() => {
                    this.copyText = 'Copy';
                }, 1500);
            },
            //main
            permisOpt() {
                let permis = _.map(this.tableMeta._table_permissions, (permis) => {
                    return { val:permis.id, show:permis.name };
                });
                permis.unshift({val:null, show:''});
                return permis;
            },
            permisUpdate(opt) {
                this.tableMeta.single_view_permission_id = opt.val;
                this.updatedCell();
            },
            permisShow() {
                eventBus.$emit('show-permission-settings-popup', this.tableMeta.db_name, this.tableMeta.single_view_permission_id);
            },

            statusOpt() {
                let flds = _.filter(this.tableMeta._fields, (fld) => { return this.$root.inArray(fld.f_type, ['Boolean']); });
                flds = _.map(flds, (fld) => {
                    return { val:fld.id, show:this.$root.uniqName(fld.name) };
                });
                flds.unshift({val:null, show:''});
                return flds;
            },
            statusUpdate(key, val) {
                this.tableMeta[key] = val;
                this.updatedCell(key);
            },

            stringFlds() {
                let flds = _.filter(this.tableMeta._fields, (fld) => { return !this.$root.inArraySys(fld.f_type, ['Attachment']); });
                flds = _.map(flds, (fld) => {
                    return { val:fld.id, show:this.$root.uniqName(fld.name) };
                });
                flds.unshift({val:null, show:''});
                return flds;
            },
            passUpdate(opt) {
                this.tableMeta.single_view_password_id = opt.val;
                this.updatedCell();
            },

            seleUpdate(opt, key) {
                this.tableMeta[key] = opt.val;
                this.updatedCell();
            },
            recreaFormula(param) {
                this[param] = false;
                this.$nextTick(() => {
                    this[param] = true;
                });
            },
            
            //form style
            updateBgColor(clr, save) {
                this.updateColor('single_view_bg_color', clr, save);
            },
            updateFormBgClr(clr, save) {
                this.updateColor('single_view_form_background', clr, save);
            },
            updateColor(hdr, clr, save) {
                if (save) {
                    this.$root.saveColorToPalette(clr);
                }
                this.tableMeta[hdr] = clr;
                this.updatedCell();
            },
            mselUpdate(opt) {
                let optVal = opt.val;
                let arr = SpecialFuncs.parseMsel(this.tableMeta.single_view_header_font);
                if (arr.indexOf(optVal) > -1) {
                    arr.splice( arr.indexOf(optVal), 1 );
                } else {
                    arr.push(optVal);
                }
                this.tableMeta.single_view_header_font = JSON.stringify(arr);
                this.updatedCell();
            },

            //server calls
            updatedCell(key) {
                this.formula_view_header = false;
                this.$root.sm_msg_type = 1;
                axios.put('/ajax/table', {
                    table_id: this.tableMeta.id,
                    ...this.tableMeta,
                }).catch(errors => {
                    Swal('Info', getErrors(errors));
                    if (key === 'single_view_url_id') {
                        eventBus.$emit('reload-page');
                    }
                }).finally(() => {
                    this.$root.sm_msg_type = 0;
                });
            },
            uploadBgiFile() {
                if (this.$refs.bg_img_sec.files[0]) {
                    this.$root.sm_msg_type = 1;
                    let formData = new FormData();
                    formData.append('table_id', this.tableMeta.id);
                    formData.append('bgi_file', this.$refs.bg_img_sec.files[0]);
                    axios.post('/ajax/srv/bgi-file', formData, {
                        headers: {
                            'Content-Type': 'multipart/form-data'
                        }
                    }).then(({data}) => {
                        this.tableMeta.single_view_bg_img = data.filepath;
                        this.$refs.bg_img_sec.value = null;
                    }).catch(errors => {
                        Swal('Info', getErrors(errors));
                    }).finally(() => {
                        this.$root.sm_msg_type = 0;
                    });
                } else {
                    Swal('Info','File not found!');
                }
            },
            delBgiFile() {
                this.$root.sm_msg_type = 1;
                axios.delete('/ajax/srv/bgi-file', {
                    params: {
                        table_id: this.tableMeta.id,
                        bgi_file: 'delete',
                    }
                }).then(({ data }) => {
                    this.tableMeta.single_view_bg_img = null;
                    this.$refs.bg_img_sec.value = null;
                }).catch(errors => {
                    Swal('Info', getErrors(errors));
                }).finally(() => {
                    this.$root.sm_msg_type = 0;
                });
            },
            //Fields settings
            srvSettCheck(field, val) {
                this.$root.sm_msg_type = 1;
                this.withEdit = false;
                axios.post('/ajax/srv/field-specific', {
                    table_id: this.tableMeta.id,
                    field_id: field,
                    setting: val.setting,
                    val: val.val,
                }).then(({ data }) => {
                    this.withEdit = true;
                    this.tableMeta._fields_pivot = data._fields_pivot;
                }).catch(errors => {
                    Swal('Info', getErrors(errors));
                }).finally(() => {
                    this.$root.sm_msg_type = 0;
                });
            },
        },
        mounted() {
        },
        beforeDestroy() {
        }
    }
</script>

<style lang="scss" scoped>
    .permissions-menu-body {
        border: 1px solid #CCC;
        position: relative;
        height: calc(100% - 25px);
        top: -3px;
        background: #FFF;
    }
    .btn-default {
        outline: none;
        background-color: #CCC;

        &:focus {
            outline: none;
            background-color: #FFF;
        }
    }
    .active {
        background-color: #FFF;
    }

    label {
        margin: 0;
    }
    .view-select {
        max-width: 200px;
    }
    .view-select--sm {
        max-width: 75px;
    }
    .view-select--md {
        max-width: 100px;
    }
    .img-preview {
        max-width: 200px;
        max-height: 100px;
    }
    .color-wrapper {
        height: 32px;
        position: relative;
        border: 1px solid #ccd0d2 !important;
        border-radius: 5px;
    }
    .clr-min {
        width: 60px;
        min-width: 60px;
    }
</style>