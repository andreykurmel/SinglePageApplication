<template>
    <td :style="custCellStyle"
        class="td-custom"
        ref="td"
        @dragenter="ddlEnter"
        @dragover.prevent=""
        @dragleave="ddlLeave"
        @drop.prevent.stop="ddlDrop"
        @click="showEdit()"
    >
        <div class="td-wrapper" :style="getTdWrappStyle">

            <div class="wrapper-inner" :style="getWrapperStyle">
                <div class="inner-content">

                    <span v-if="tableHeader.f_type === 'Boolean'" class="indeterm_check__wrap">
                        <span class="indeterm_check checkbox-input"
                              ref="inline_input"
                              @click="updateCheckBox()"
                              :style="$root.checkBoxStyle"
                        >
                            <i v-if="tableRow[tableHeader.field]" class="glyphicon glyphicon-ok group__icon"></i>
                        </span>
                    </span>

                    <tablda-colopicker
                        v-else-if="tableHeader.f_type === 'Color'"
                        :init_color="tableRow[tableHeader.field]"
                        :avail_null="true"
                        :fixed_pos="true"
                        @set-color="updateCheckedDDL"
                    ></tablda-colopicker>

                    <a v-else-if="is_attach && tableRow[tableHeader.field]"
                       title="Open file in a new tab."
                       target="_blank"
                       class="has-deleter"
                       :href="$root.fileUrl({url:tableRow[tableHeader.field]})"
                    >
                        <img class="_img_preview"
                             :src="$root.fileUrl({url:tableRow[tableHeader.field]})"
                             :style="{maxHeight: (maxCellHGT ? maxCellHGT+'px' : tdCellHGT+'px')}"
                             @click="imgClick([{url:tableRow[tableHeader.field]}], 0)">
                        <span class="delete-icon-btn" @click.stop.prevent="uploadItemFile(null)">&times;</span>
                    </a>

                    <button v-if="tableHeader.field === '_ref_colors' && !isAddRow"
                            class="btn btn-primary btn-sm blue-gradient btn-detail"
                            @click.stop.prevent="showRefValueColors()"
                            :style="$root.themeButtonStyle"
                    >
                        <span>Detail</span>
                    </button>

                    <a v-else-if="tableHeader.field === 'apply_target_row_group_id'"
                       title="Open row group in popup."
                       @click.stop="showGroupsPopup('row', tableRow.apply_target_row_group_id)"
                    >{{ showField() }}</a>

                    <a v-else-if="tableHeader.field === 'table_ref_condition_id'"
                       title="Open ref condition in popup."
                       @click.stop="showAddRefCond(tableRow.table_ref_condition_id)"
                    >{{ showField() }}</a>

                    <div v-else-if="tableHeader.field === 'ref_value'">
                        <span class="is_select" :style="selectBG">{{ showField() }}</span>
                    </div>

                    <div v-else="">{{ showField() }}</div>

                </div>
            </div>

        </div>



        <!-- ABSOLUTE EDITINGS -->
        <div v-if="isEditing()" class="cell-editing">

            <tablda-select-simple
                v-if="'datas_sort' === tableHeader.field"
                :options="[
                        {val: 'asc', show: 'A->Z(0->9)'},
                        {val: 'desc', show: 'Z->A(9->0)'},
                    ]"
                :table-row="tableRow"
                :hdr_field="tableHeader.field"
                :fixed_pos="true"
                :style="getEditStyle"
                @selected-item="updateCheckedDDL"
                @hide-select="hideEdit"
            ></tablda-select-simple>

            <!--DDL References-->
            <tablda-select-simple
                    v-else-if="tableHeader.field === 'apply_target_row_group_id'"
                    :options="globalRowGroups()"
                    :table-row="tableRow"
                    :hdr_field="tableHeader.field"
                    :fixed_pos="true"
                    :embed_func_txt="'Add New'"
                    :style="getEditStyle"
                    @selected-item="updateCheckedDDL"
                    @hide-select="hideEdit"
                    @embed-func="showGroupsPopup('row')"
            ></tablda-select-simple>

            <tablda-select-simple
                    v-else-if="tableHeader.field === 'table_ref_condition_id'"
                    :options="globalRefConds()"
                    :table-row="tableRow"
                    :hdr_field="tableHeader.field"
                    :fixed_pos="true"
                    :embed_func_txt="'Add New'"
                    :style="getEditStyle"
                    @selected-item="updateCheckedDDL"
                    @hide-select="hideEdit"
                    @embed-func="showRCSettingsPopup(tableRow)"
            ></tablda-select-simple>

            <tablda-select-simple
                    v-else-if="['target_field_id','show_field_id'].indexOf(tableHeader.field) > -1 && tableRow.table_ref_condition_id"
                    :options="nameFieldsFromConds()"
                    :table-row="tableRow"
                    :hdr_field="tableHeader.field"
                    :can_empty="tableHeader.field !== 'target_field_id'"
                    :fixed_pos="true"
                    :style="getEditStyle"
                    @selected-item="updateCheckedDDL"
                    @hide-select="hideEdit"
            ></tablda-select-simple>

            <tablda-select-simple
                    v-else-if="['image_field_id'].indexOf(tableHeader.field) > -1  && tableRow.table_ref_condition_id"
                    :options="nameAttachsFromConds()"
                    :table-row="tableRow"
                    :hdr_field="tableHeader.field"
                    :can_empty="true"
                    :fixed_pos="true"
                    :style="getEditStyle"
                    @selected-item="updateCheckedDDL"
                    @hide-select="hideEdit"
            ></tablda-select-simple>

            <!--DDL items-->
            <input
                    v-else-if="is_attach"
                    @change="uploadItemFile()"
                    @blur="hideFileEdit();"
                    type="file"
                    accept="image/*"
                    ref="inline_input"
                    class="form-control full-height"
                    :style="getEditStyle">

            <input
                    v-else-if="inArray(tableHeader.field, ['name', 'notes', 'option', 'show_option', 'ref_value'])"
                    v-model="tableRow[tableHeader.field]"
                    @blur="hideEdit();updateValue()"
                    ref="inline_input"
                    class="form-control full-height"
                    :style="getEditStyle">

            <div v-else="">{{ hideEdit() }}</div>
            
        </div>
        <!-- ABSOLUTE EDITINGS -->

        <!-- Full-size img for attachments -->
        <full-size-img-block
                v-if="overImages && overImages.length"
                :file_arr="overImages"
                :file_idx="overImageIdx"
                @close-full-img="overImages = null"
        ></full-size-img-block>

    </td>
</template>

<script>
import {SpecialFuncs} from "../../classes/SpecialFuncs";
import {Endpoints} from "../../classes/Endpoints";

import {eventBus} from '../../app';

import Select2DDLMixin from './../_Mixins/Select2DDLMixin.vue';
import CellStyleMixin from '../_Mixins/CellStyleMixin.vue';

import TabldaSelectSimple from "./Selects/TabldaSelectSimple";
import FullSizeImgBlock from "../CommonBlocks/FullSizeImgBlock";
import TabldaColopicker from "./InCell/TabldaColopicker";

export default {
        components: {
            TabldaColopicker,
            FullSizeImgBlock,
            TabldaSelectSimple,
        },
        name: "CustomCellSettingsDdl",
        mixins: [
            Select2DDLMixin,
            CellStyleMixin,
        ],
        data: function () {
            return {
                attach_overed: false,
                editing: false,
                compared_values: [],
                source_field_for_values: null,
                overImages: null,
                overImageIdx: null,
            }
        },
        props:{
            globalMeta: {
                type: Object,
                default: function () {
                    return {};
                }
            },
            tableMeta: Object,//style mixin
            tableHeader: Object,
            tableRow: Object,
            cellHeight: Number,
            maxCellRows: Number,
            isAddRow: Boolean,
            user: Object,
            parentRow: Object,
            with_edit: {
                type: Boolean,
                default: true
            },
        },
        computed: {
            is_attach() {
                return this.inArray(this.tableHeader.field, ['image_path']);
            },
            fields_from_condition() {
                let fields = [];
                let ref_cond = _.find(this.globalMeta._ref_conditions, {id: Number(this.tableRow.table_ref_condition_id)});
                if (ref_cond && ref_cond._ref_table && ref_cond._ref_table._fields) {
                    fields = ref_cond._ref_table._fields;
                }
                return fields;
            },
            canCellEdit() {
                return this.with_edit
                    && this.globalMeta._is_owner
                    && !this.inArray(this.tableHeader.field, this.$root.systemFields)
                    && (this.tableHeader.field !== 'target_field_id' || this.tableRow.table_ref_condition_id)
                    && (this.tableHeader.field !== 'show_field_id' || this.tableRow.table_ref_condition_id)
                    && (this.tableHeader.field !== 'image_field_id' || this.tableRow.table_ref_condition_id)
                    && (this.tableHeader.field !== '_ref_colors');
            },
            custCellStyle() {
                let stl = this.getCellStyle;
                if (!this.with_edit) {
                    stl.backgroundColor = '#EEE';
                }
                if (this.tableHeader.field === '_ref_colors') {
                    stl.textAlign = 'center';
                }
                return stl;
            },
            selectBG() {
                let bg = this.tableRow.color || '';
                return bg
                    ? {
                        backgroundColor: bg,
                        color: SpecialFuncs.smartTextColorOnBg(bg),
                    }
                    : {};
            },
        },
        methods: {
            inArray(item, array) {
                return $.inArray(item, array) > -1;
            },
            isEditing() {
                return this.editing && this.canCellEdit && !this.$root.global_no_edit;
            },
            showEdit() {
                if (!this.canCellEdit) {
                    return;
                }
                //edit cell
                this.editing = true;
                if (this.isEditing()) {
                    this.oldValue = this.tableRow[this.tableHeader.field];
                    this.$nextTick(function () {
                        if (this.$refs.inline_input) {
                            if (this.$refs.inline_input.nodeName === 'SELECT'){
                                this.showHideDDLs(this.$root.selectParam);
                                this.ddl_cached = false;
                            } else {
                                this.$refs.inline_input.focus();
                            }
                        }
                    });
                } else {
                    this.editing = false;
                }
            },
            hideEdit() {
                this.editing = false;
            },
            updateValue() {
                if (this.tableRow[this.tableHeader.field] !== this.oldValue) {
                    this.emitUpdateSignal();
                }
            },
            emitUpdateSignal() {
                if (this.tableHeader.field === 'table_ref_condition_id') {
                    this.tableRow.target_field_id = null;
                    this.tableRow.show_field_id = null;
                    this.tableRow.image_field_id = null;
                }
                this.tableRow._changed_field = this.tableHeader.field;
                this.$emit('updated-cell', this.tableRow);
            },
            updateCheckedDDL(item) {
                this.tableRow[this.tableHeader.field] = item;
                this.updateValue();
            },
            updateCheckBox() {
                this.tableRow[this.tableHeader.field] = !Boolean(this.tableRow[this.tableHeader.field]);
                this.emitUpdateSignal();
            },
            showField() {
                let res = '';

                if (this.isAddRow && this.inArray(this.tableHeader.field, this.$root.systemFields)) {
                    res = 'Auto'
                }
                else
                if (this.tableHeader.f_type === 'User') {
                    res = this.$root.smallUserStr(this.tableRow, this.tableHeader, this.tableRow[this.tableHeader.field]);
                }
                else
                if (this.tableHeader.field === 'apply_target_row_group_id' && this.tableRow.apply_target_row_group_id) {
                    let idx = _.findIndex(this.globalMeta._row_groups, {id: Number(this.tableRow.apply_target_row_group_id)});
                    res = idx > -1 ? this.globalMeta._row_groups[idx].name : '';
                }
                else
                if (this.tableHeader.field === 'table_ref_condition_id' && this.tableRow.table_ref_condition_id) {
                    let idx = _.findIndex(this.globalMeta._ref_conditions, {id: Number(this.tableRow.table_ref_condition_id)});
                    res = idx > -1 ? this.globalMeta._ref_conditions[idx].name : '';
                }
                else
                if (this.tableHeader.field === 'datas_sort' && this.tableRow.datas_sort) {
                    switch (this.tableRow.datas_sort) {
                        case 'asc': res = 'A->Z(0->9)'; break;
                        case 'desc': res = 'Z->A(9->0)'; break;
                    }
                }
                else
                if (['target_field_id','show_field_id','image_field_id'].indexOf(this.tableHeader.field) > -1) {
                    let idx = _.findIndex(this.fields_from_condition, {id: Number(this.tableRow[this.tableHeader.field])});
                    res = idx > -1
                        ? this.$root.uniqName( this.fields_from_condition[idx].name )
                        : (this.tableHeader.field === 'target_field_id' ? 'ID' : '');
                }
                else {
                    res = this.tableRow[this.tableHeader.field];
                }

                return this.$root.strip_tags(res);
            },
            showGroupsPopup(type, id) {
                eventBus.$emit('show-grouping-settings-popup', this.globalMeta.db_name, type, id);
            },
            showAddRefCond(refId) {
                this.$emit('show-add-ref-cond', refId)
            },

            //DDL Item File
            hideFileEdit() {
                if (window.event.sourceCapabilities) {
                    this.hideEdit();
                }
            },
            uploadItemFile(fore_file) {
                let file = fore_file || (this.$refs.inline_input ? this.$refs.inline_input.files[0] : null);
                let rw_id = window.uuidv4();
                if (this.tableRow.id) {
                    rw_id = this.tableRow.ddl_id+'_row'+this.tableRow.id
                }

                if (rw_id) {
                    this.$root.sm_msg_type = 1;
                    Endpoints.fileUpload({
                        table_id: this.globalMeta.id,
                        table_field_id: this.tableHeader.id,
                        row_id: 'ddl_'+rw_id,
                        file: file,
                        clear_before: 1,
                    }).then(({ data }) => {
                        this.tableRow[this.tableHeader.field] = data.filepath + data.filename;
                        this.emitUpdateSignal();
                    }).catch(errors => {
                        Swal('', getErrors(errors));
                    }).finally(() => {
                        this.$root.sm_msg_type = 0;
                        this.editing = false;
                    });
                } else {
                    this.editing = false;
                }
            },
            
            //Drag&Drop
            ddlEnter(e) {
                if (!this.is_attach) {
                    return;
                }
                this.attach_overed = true;
            },
            ddlLeave(e) {
                if (!this.is_attach) {
                    return;
                }
                this.attach_overed = false;
            },
            ddlDrop(ev) {
                if (!this.is_attach) {
                    return;
                }
                let file = ev.dataTransfer.items && ev.dataTransfer.items[0] && ev.dataTransfer.items[0].kind === 'file'
                    ? ev.dataTransfer.items[0].getAsFile()
                    : null;
                this.uploadItemFile(file);
                this.attach_overed = false;
            },

            //Emits
            showRCSettingsPopup(row) {
                eventBus.$emit('show-ref-conditions-popup', this.globalMeta.db_name, row.table_ref_condition_id);
            },

            //arrays for selects
            globalRefConds() {
                return _.map(this.globalMeta._ref_conditions, (rc) => {
                    return { val: rc.id, show: rc.name, }
                });
            },
            globalRowGroups() {
                return _.map(this.globalMeta._row_groups, (rc) => {
                    return { val: rc.id, show: rc.name, }
                });
            },
            nameFieldsFromConds() {
                let fields = _.filter(this.fields_from_condition, (hdr) => {
                    return !in_array(hdr.field, this.$root.systemFields);
                });
                if (this.tableHeader.field === 'target_field_id') {
                    fields.splice(0, 0, {id: null, name: 'ID'});
                }
                return _.map(fields, (hdr) => {
                    return { val: hdr.id, show: this.$root.uniqName(hdr.name), }
                });
            },
            nameAttachsFromConds() {
                let fields = _.filter(this.fields_from_condition, (hdr) => {
                    return !this.inArray(hdr.field, this.$root.systemFields)
                        && hdr.f_type === 'Attachment';
                });
                return _.map(fields, (hdr) => {
                    return { val: hdr.id, show: this.$root.uniqName(hdr.name), }
                });
            },
            imgClick(images, idx) {
                if (!window.event.ctrlKey) {
                    this.overImages = images;
                    this.overImageIdx = idx;
                    window.event.stopPropagation();
                    window.event.preventDefault();
                }
            },
            showRefValueColors() {
                eventBus.$emit('show-ref-value-colors', this.parentRow, this.tableRow);
            },
        }
    }
</script>

<style lang="scss" scoped>
    @import "CustomCell.scss";

    .btn-detail {
        font-size: 0.8em;
        padding: 0 7px;
    }
    .has-deleter {
        display: inline-block;
        position: relative;
    }
    .has-deleter > .img--deleter {
        display: none;
        color: #F00;
        font-size: 1.6em;
        font-weight: bold;
        line-height: 0.8em;
        position: absolute;
        top: calc(50% - 8px);
        left: 100%;
    }
    .has-deleter:hover > .img--deleter {
        display: inline-block;
    }
</style>