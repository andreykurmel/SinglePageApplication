<template>
    <div class="full-height" style="padding: 10px;" :style="textSysStyle">
        <div class="form-group flex flex--center-v">
            <label class="switch_t" style="display: inline-block;margin-right: 5px;">
                <input type="checkbox" v-model="tableMeta.single_view_active" :disabled="!canEditView" @change="updatedCell">
                <span class="toggler round" :class="[!canEditView ? 'disabled' : '']"></span>
            </label>
            <label>&nbsp;View status</label>
        </div>
        <div class="form-group flex flex--center-v">
            <label class="f-w">Permission to be applied:&nbsp;</label>
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
            <label class="f-w">Field saving status for ea. record:&nbsp;</label>
            <select-block
                :options="statusOpt()"
                :sel_value="tableMeta.single_view_status_id"
                :style="{ maxWidth:'200px', height:'32px', ...textSysStyle, }"
                :is_disabled="!canEditView"
                @option-select="statusUpdate"
            ></select-block>
        </div>
        <div class="form-group flex flex--center-v">
            <label class="f-w">Field saving password for ea. record:&nbsp;</label>
            <select-block
                :options="passOpt()"
                :sel_value="tableMeta.single_view_password_id"
                :style="{ maxWidth:'200px', height:'32px', ...textSysStyle, }"
                :is_disabled="!canEditView"
                @option-select="passUpdate"
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
                     :src="$root.fileUrl({url:tableMeta.single_view_bg_img})"
                     class="img-preview"
                />
                <input type="file" ref="bg_img_sec" :style="textSysStyle" :disabled="!canEditView" @change="uploadBgiFile" class="form-control view-select"/>
                <button
                    v-if="tableMeta.single_view_bg_img"
                    class="btn flex flex--center btn-danger btn-del-bg"
                    :disabled="!canEditView"
                    @click="delBgiFile"
                >&times;</button>

                <label>&nbsp;&nbsp;&nbsp;Fit:&nbsp;</label>
                <select v-model="tableMeta.single_view_bg_fit" :style="textSysStyle" :disabled="!canEditView" @change="updatedCell" class="form-control view-select--md">
                    <option>Height</option>
                    <option>Width</option>
                    <option>Fill</option>
                </select>
            </template>
        </div>

        <div class="form-group flex flex--center-v">
            <label>Width:&nbsp;</label>
            <input type="number" v-model="tableMeta.single_view_form_width" :style="textSysStyle" :disabled="!canEditView" @change="updatedCell" class="form-control view-select--sm"/>
            <label>&nbsp;px&nbsp;</label>

            <label>&nbsp;&nbsp;&nbsp;BGC&nbsp;:&nbsp;</label>
            <div class="color-wrapper clr-min">
                <tablda-colopicker
                    :init_color="tableMeta.single_view_form_color"
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

            <label>&nbsp;&nbsp;&nbsp;Font size:&nbsp;</label>
            <input type="number" v-model="tableMeta.single_view_form_font_size" :style="textSysStyle" :disabled="!canEditView" @change="updatedCell" class="form-control view-select--sm"/>
            <label>&nbsp;px&nbsp;</label>
        </div>
    </div>
</template>

<script>
    import {eventBus} from '../../../../../app';

    import SelectBlock from "../../../../CommonBlocks/SelectBlock";
    import TabldaColopicker from "../../../../CustomCell/InCell/TabldaColopicker";

    import CellStyleMixin from "../../../../_Mixins/CellStyleMixin";

    export default {
        name: "SingleViewModule",
        components: {
            TabldaColopicker,
            SelectBlock
        },
        mixins: [
            CellStyleMixin,
        ],
        data: function () {
            return {
            }
        },
        props:{
            tableMeta: Object,
        },
        computed: {
            canEditView() {
                return this.tableMeta._is_owner
                    || // OR user with available rights for add View
                    (this.tableMeta._current_right && this.tableMeta._current_right.can_create_view);
            }
        },
        methods: {
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
            statusUpdate(opt) {
                this.tableMeta.single_view_status_id = opt.val;
                this.updatedCell();
            },

            passOpt() {
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
            
            //form style
            updateBgColor(clr, save) {
                this.updateColor('single_view_bg_color', clr, save);
            },
            updateFormBgClr(clr, save) {
                this.updateColor('single_view_form_color', clr, save);
            },
            updateColor(hdr, clr, save) {
                if (save) {
                    this.$root.saveColorToPalette(clr);
                }
                this.tableMeta[hdr] = clr;
                this.updatedCell();
            },

            //server calls
            updatedCell() {
                this.$root.sm_msg_type = 1;
                axios.put('/ajax/table', {
                    table_id: this.tableMeta.id,
                    ...this.tableMeta,
                }).catch(errors => {
                    Swal('', getErrors(errors));
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
                        Swal('', getErrors(errors));
                    }).finally(() => {
                        this.$root.sm_msg_type = 0;
                    });
                } else {
                    Swal('File not found!');
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
                    Swal('', getErrors(errors));
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
    .f-w {
        width: 250px;
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