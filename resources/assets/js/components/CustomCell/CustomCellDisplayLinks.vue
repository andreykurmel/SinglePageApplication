<template>
    <td :style="getCustomCellStyle"
        class="td-custom"
        ref="td"
        @click="showEdit()"
    >
        <div class="td-wrapper" :style="getTdWrappStyle()">

            <div class="wrapper-inner"
                 :class="[isExportedFlds ? 'more_fields' : '']"
                 :style="!isExportedFlds && tableHeader.field[0] !== '_' ? getWrapperStyle() : {}"
            >
                <div class="inner-content" :style="tableHeader.field[0] !== '_' ? {} : {textAlign: 'center'}">

                    <div v-if="isAvailAdn">
                        <div class="txt-normal">Check to set availability of add-ons. The linked records will be the Data Range.</div>
                        <div v-for="adn in $root.settingsMeta.all_addons" v-if="adn.code !== 'all' && refTable['add_'+adn.code]" class="ml15">
                            <label class="no-margin txt-normal">
                                <input type="checkbox" :disabled="!canCellEdit" :checked="availAdnOn(adn.code)" @change.prevent="updateCheckedDDL(adn.code)">
                                <span>{{ adn.name }}</span>
                            </label>
                        </div>
                    </div>

                    <div v-else-if="isExportedFlds">
                        <div class="txt-normal">
                            <span>Check to set collection of exported fields.</span>
                            <label class="no-margin txt-normal">
                                <input type="checkbox" :disabled="!canCellEdit" :checked="allIsChecked()" @change.prevent="updateAll()">
                                <span>All</span>
                            </label>
                        </div>
                        <div v-for="fld in expoFields" v-if="$root.systemFieldsNoId.indexOf(fld.field) === -1" class="ml15">
                            <label class="no-margin txt-normal">
                                <input type="checkbox" :disabled="!canCellEdit" :checked="availExpFldOn(fld.id)" @change.prevent="updateCheckedDDL(fld.id)">
                                <span>{{ $root.uniqName(fld.name) }}</span>
                            </label>
                        </div>
                    </div>

                    <button v-else-if="tableHeader.field === '_link_reverse' && ! isAddRow"
                            class="btn btn-default btn-sm blue-gradient"
                            @click="addReverseLinkClick()"
                            :disabled="! lnkHasP2SItems() || lnkAlreadyReversed()"
                            :style="$root.themeButtonStyle"
                            style="padding: 2px 10px"
                    >Add</button>

                    <button v-else-if="tableHeader.field === '_list_vars' && ! isAddRow"
                            class="btn btn-default btn-sm blue-gradient"
                            @click="fillEriPartVariables()"
                            :disabled="tableRow && tableRow._part_variables && tableRow._part_variables.length > 0"
                            :style="$root.themeButtonStyle"
                            style="padding: 2px 10px"
                    >List</button>

                    <button v-else-if="tableHeader.field === '_conversion_button' && ! isAddRow"
                            class="btn btn-default btn-sm blue-gradient"
                            @click="showEriFieldConversions()"
                            :disabled="!canCellEdit"
                            :style="$root.themeButtonStyle"
                            style="padding: 2px 10px"
                    >List ({{ tableRow._conversions ? tableRow._conversions.length : 0 }})</button>

                    <label class="switch_t" v-else-if="tableHeader.field === 'show_sum' || tableHeader.field === 'floating_action'">
                        <input type="checkbox"
                               v-model="editValue"
                               :disabled="!inArray(fld_link_type, ['Record'])"
                               @change="updateValue()">
                        <span class="toggler round"></span>
                    </label>

                    <label class="switch_t" v-else-if="tableHeader.f_type === 'Boolean'" :style="{height: Math.min(maxCellHGT, 17)+'px'}">
                        <input type="checkbox" :disabled="!canCellEdit" :checked="checkBoxOn" @change.prevent="updateCheckBox()">
                        <span class="toggler round" :class="[!canCellEdit ? 'disabled' : '']"></span>
                    </label>

                    <template v-else-if="tableHeader.field === 'table_ref_condition_id'">
                        <a title="Open ref condition in popup." @click.stop="showAddRefCond(tableRow.table_ref_condition_id)">{{ showField() }}</a>
                        <a title="Open field display popup." class="pull-right" @click.stop="showFieldDisplay()">Field Display</a>
                    </template>

                    <a v-else-if="tableHeader.field === 'smart_select_data_range'"
                       title="Open data range in popup."
                       @click.stop="showRG(tableRow[tableHeader.field], globalMeta)"
                    >{{ showField() }}</a>

                    <a v-else-if="['lnk_dcr_permission_id', 'lnk_srv_permission_id', 'lnk_mrv_permission_id'].indexOf(tableHeader.field) > -1"
                       title="Open permissions in popup."
                       @click.stop="showPermis()"
                    >{{ showField() }}</a>

                    <a v-else-if="['payment_paypal_keys_id','payment_stripe_keys_id'].indexOf(tableHeader.field) > -1"
                       title="Open user settings in popup."
                       @click.stop="showUserSettings(tableRow[tableHeader.field])"
                    >{{ showField() }}</a>

                    <a v-else-if="tableHeader.field === 'popup_display' && tableRow.popup_display === 'Boards'"
                       title="Open general settings in popup."
                       @click.stop="showBoardSett()"
                    >{{ showField() }}</a>

                    <div v-else-if="is_multisel" :class="{vert_stack: tableHeader.field === 'link_preview_fields'}">
                        <span v-for="str in editValue" v-if="str" :class="{'is_select': is_sel, 'm_sel__wrap': is_multisel}">
                            <span v-html="showMselPart(str)"></span>
                            <span v-if="is_sel && is_multisel" class="m_sel__remove" @click.prevent.stop="updateCheckedDDL(str)">&times;</span>
                        </span>
                    </div>

                    <div v-else="">{{ showField() }}</div>

                </div>

                <div v-if="tableHeader.field === 'max_height_in_vert_table' && tableRow.max_height_in_vert_table < 200"
                     style="position: absolute; top: 50%; right: 3px; color: red; transform: translateY(-50%);"
                >(Table, Listing, Boards views will have auto-height)</div>
            </div>

        </div>



        <!-- ABSOLUTE EDITINGS -->
        <div v-if="isEditing()" class="cell-editing">

            <tablda-select-simple
                    v-if="tableHeader.field === 'table_field_link_id'"
                    :options="allFieldLinks()"
                    :table-row="tableRow"
                    :hdr_field="tableHeader.field"
                    :can_empty="true"
                    :fixed_pos="true"
                    :style="getEditStyle"
                    @selected-item="updateCheckedDDL"
                    @hide-select="hideEdit"
            ></tablda-select-simple>

            <tablda-select-simple
                v-else-if="tableHeader.field === 'smart_select_data_range'"
                :options="getRGr(globalMeta)"
                :table-row="tableRow"
                :hdr_field="tableHeader.field"
                :fixed_pos="true"
                :embed_func_txt="'Add New'"
                :style="getEditStyle"
                @selected-item="updateCheckedDDL"
                @hide-select="hideEdit"
                @embed-func="showRG(null, globalMeta)"
            ></tablda-select-simple>

            <tablda-select-simple
                    v-else-if="inArray(tableHeader.field, ['listing_field_id','link_preview_fields','email_addon_fields']) && tableRow.table_ref_condition_id"
                    :options="listingsFields()"
                    :table-row="tableRow"
                    :hdr_field="tableHeader.field"
                    :can_empty="tableHeader.field === 'listing_field_id'"
                    :fixed_pos="true"
                    :fld_input_type="tableHeader.input_type"
                    :style="getEditStyle"
                    @selected-item="updateCheckedDDL"
                    @hide-select="hideEdit"
            ></tablda-select-simple>

            <tablda-select-simple
                    v-else-if="inArray(tableHeader.field, ['json_import_field_id','json_export_field_id','eri_parser_file_id',
                        'eri_writer_file_id','da_loading_image_field_id','mto_dal_pdf_doc_field_id','mto_geom_doc_field_id',
                        'ai_extract_doc_field_id'])"
                    :options="allFields('Attachment')"
                    :table-row="tableRow"
                    :hdr_field="tableHeader.field"
                    :fixed_pos="true"
                    :fld_input_type="tableHeader.input_type"
                    :style="getEditStyle"
                    @selected-item="updateCheckedDDL"
                    @hide-select="hideEdit"
            ></tablda-select-simple>

            <tablda-select-simple
                    v-else-if="inArray(tableHeader.field, ['json_export_filename_fields','eri_writer_filename_fields','eri_field_id','da_field_id','smart_select_source_field_id','smart_select_target_field_id'])"
                    :options="allFields()"
                    :table-row="tableRow"
                    :hdr_field="tableHeader.field"
                    :fixed_pos="true"
                    :fld_input_type="tableHeader.input_type"
                    :style="getEditStyle"
                    @selected-item="updateCheckedDDL"
                    @hide-select="hideEdit"
            ></tablda-select-simple>

            <tablda-select-simple
                    v-else-if="inArray(tableHeader.field, ['lnk_dcr_permission_id', 'lnk_srv_permission_id', 'lnk_mrv_permission_id']) && tableRow.table_ref_condition_id"
                    :options="refPermissions()"
                    :table-row="tableRow"
                    :hdr_field="tableHeader.field"
                    :fixed_pos="true"
                    :fld_input_type="tableHeader.input_type"
                    :style="getEditStyle"
                    @selected-item="updateCheckedDDL"
                    @hide-select="hideEdit"
            ></tablda-select-simple>

            <tablda-select-simple
                    v-else-if="inArray(tableHeader.field, ['share_record_link_id','eri_parser_link_id'])"
                    :options="recordLinks()"
                    :table-row="tableRow"
                    :hdr_field="tableHeader.field"
                    :can_empty="true"
                    :fixed_pos="true"
                    :fld_input_type="tableHeader.input_type"
                    :style="getEditStyle"
                    @selected-item="updateCheckedDDL"
                    @hide-select="hideEdit"
            ></tablda-select-simple>

            <tablda-select-simple
                    v-else-if="inArray(tableHeader.field, ['table_def_align'])"
                    :options="alignOptions()"
                    :table-row="tableRow"
                    :hdr_field="tableHeader.field"
                    :fixed_pos="true"
                    :fld_input_type="tableHeader.input_type"
                    :style="getEditStyle"
                    @selected-item="updateCheckedDDL"
                    @hide-select="hideEdit"
            ></tablda-select-simple>

            <tablda-select-simple
                    v-else-if="inArray(tableHeader.field, ['listing_panel_status'])"
                    :options="panelStatuses()"
                    :table-row="tableRow"
                    :hdr_field="tableHeader.field"
                    :fixed_pos="true"
                    :fld_input_type="tableHeader.input_type"
                    :style="getEditStyle"
                    @selected-item="updateCheckedDDL"
                    @hide-select="hideEdit"
            ></tablda-select-simple>

            <tablda-select-simple
                    v-else-if="inArray(tableHeader.field, ['inline_style'])"
                    :options="inlineStyles()"
                    :table-row="tableRow"
                    :hdr_field="tableHeader.field"
                    :fixed_pos="true"
                    :fld_input_type="tableHeader.input_type"
                    :style="getEditStyle"
                    @selected-item="updateCheckedDDL"
                    @hide-select="hideEdit"
            ></tablda-select-simple>

            <tablda-select-simple
                    v-else-if="inArray(tableHeader.field, ['inline_width'])"
                    :options="inlineWidths()"
                    :table-row="tableRow"
                    :hdr_field="tableHeader.field"
                    :fixed_pos="true"
                    :fld_input_type="tableHeader.input_type"
                    :style="getEditStyle"
                    @selected-item="updateCheckedDDL"
                    @hide-select="hideEdit"
            ></tablda-select-simple>

            <tablda-select-simple
                    v-else-if="tableHeader.field === 'table_app_id' && fld_link_type === 'App'"
                    :options="tableApps()"
                    :table-row="tableRow"
                    :hdr_field="tableHeader.field"
                    :can_empty="true"
                    :fixed_pos="true"
                    :style="getEditStyle"
                    @selected-item="updateCheckedDDL"
                    @hide-select="hideEdit"
            ></tablda-select-simple>

            <input
                v-else-if="tableHeader.field === 'column_key' && parentRow && parentRow.table_app_id == $root.settingsMeta.ai_extractm_app_id"
                v-model="editValue"
                @blur="hideEdit();updateValue()"
                ref="inline_input"
                class="form-control full-height"
                :style="getEditStyle">

            <tablda-select-simple
                    v-else-if="tableHeader.field === 'column_key'"
                    :options="availColumnKeys()"
                    :table-row="tableRow"
                    :hdr_field="tableHeader.field"
                    :fixed_pos="true"
                    :can_empty="true"
                    :style="getEditStyle"
                    @selected-item="updateCheckedDDL"
                    @hide-select="hideEdit"
            ></tablda-select-simple>

            <tablda-select-simple
                    v-else-if="tableHeader.field === 'da_loading_type'"
                    :options="availDaTypes()"
                    :table-row="tableRow"
                    :hdr_field="tableHeader.field"
                    :fixed_pos="true"
                    :can_empty="true"
                    :style="getEditStyle"
                    @selected-item="updateCheckedDDL"
                    @hide-select="hideEdit"
            ></tablda-select-simple>

            <tablda-select-simple
                    v-else-if="tableHeader.field === 'type'"
                    :options="[
                        {val: '1D', show: '1D'},
                        {val: '2D', show: '2D'},
                    ]"
                    :table-row="tableRow"
                    :hdr_field="tableHeader.field"
                    :fixed_pos="true"
                    :style="getEditStyle"
                    @selected-item="updateCheckedDDL"
                    @hide-select="hideEdit"
            ></tablda-select-simple>

            <tablda-select-simple
                    v-else-if="tableHeader.field === 'link_type'"
                    :options="[
                        {val: 'Record', show: 'Record', disabled:!$root.checkAvailable($root.user, 'link_type_record')},
                        {val: 'Web', show: 'Web', disabled:!$root.checkAvailable($root.user, 'link_type_web')},
                        {val: 'App', show: 'App', disabled:!$root.checkAvailable($root.user, 'link_type_app')},
                        {val: 'GMap', show: 'GMap'},
                        {val: 'GEarth', show: 'GEarth'},
                        {val: 'History', show: 'History'},
                        {val: 'Add-on (Report)', show: 'Add-on (Report)'},
                    ]"
                    :table-row="tableRow"
                    :hdr_field="tableHeader.field"
                    :fixed_pos="true"
                    :style="getEditStyle"
                    @selected-item="updateCheckedDDL"
                    @hide-select="hideEdit"
            ></tablda-select-simple>

            <tablda-select-simple
                    v-else-if="tableHeader.field === 'link_display'"
                    :options="[
                        {val: 'Popup', show: 'Pop-up'},
                        {val: 'Table', show: 'New Tab Table'},
                        {val: 'RorT', show: 'Choose at Opening'},
                    ]"
                    :table-row="tableRow"
                    :hdr_field="tableHeader.field"
                    :fixed_pos="true"
                    :style="getEditStyle"
                    @selected-item="updateCheckedDDL"
                    @hide-select="hideEdit"
            ></tablda-select-simple>

            <tablda-select-simple
                    v-else-if="tableHeader.field === 'link_pos'"
                    :options="[
                        {val: 'before', show: 'Before'},
                        {val: 'after', show: 'After'},
                        {val: 'front', show: 'Front'},
                        {val: 'end', show: 'End'},
                    ]"
                    :table-row="tableRow"
                    :hdr_field="tableHeader.field"
                    :fixed_pos="true"
                    :style="getEditStyle"
                    @selected-item="updateCheckedDDL"
                    @hide-select="hideEdit"
            ></tablda-select-simple>

            <tablda-select-simple
                    v-else-if="tableHeader.field === 'icon'"
                    :options="[
                        {val: 'Arrow', show: 'Arrow'},
                        {val: 'Delete', show: 'Delete'},
                        {val: 'History', show: 'History'},
                        {val: 'PDF', show: 'PDF'},
                        {val: 'Doc', show: 'Doc'},
                        {val: 'Record', show: 'Record'},
                        {val: 'Table', show: 'Table'},
                        {val: 'Web', show: 'Web'},
                        {val: 'Underlined', show: 'Underlined'},
                        {val: 'Button', show: 'Button'},
                    ]"
                    :table-row="tableRow"
                    :hdr_field="tableHeader.field"
                    :can_empty="true"
                    :allowed_tags="true"
                    :fixed_pos="true"
                    :style="getEditStyle"
                    @selected-item="updateCheckedDDL"
                    @hide-select="hideEdit"
            ></tablda-select-simple>

            <tablda-select-simple
                    v-else-if="tableHeader.field === 'popup_display'"
                    :options="[
                        {val: 'Table', show: 'Table', disabled: !tableRow.popup_can_table},
                        {val: 'Listing', show: 'Listing', disabled: !tableRow.popup_can_list},
                        {val: 'Boards', show: 'Boards', disabled: !tableRow.popup_can_board},
                    ]"
                    :table-row="tableRow"
                    :hdr_field="tableHeader.field"
                    :fixed_pos="true"
                    :style="getEditStyle"
                    @selected-item="updateCheckedDDL"
                    @hide-select="hideEdit"
            ></tablda-select-simple>

            <tablda-select-simple
                    v-else-if="tableHeader.field === 'eri_part_id'"
                    :options="eriPartsOpts()"
                    :table-row="tableRow"
                    :hdr_field="tableHeader.field"
                    :fixed_pos="true"
                    :style="getEditStyle"
                    @selected-item="updateCheckedDDL"
                    @hide-select="hideEdit"
            ></tablda-select-simple>

            <tablda-select-simple
                    v-else-if="inArray(tableHeader.field, ['da_loading_gemini_key_id','ai_extract_ai_id'])"
                    :options="availApiKeys()"
                    :table-row="tableRow"
                    :hdr_field="tableHeader.field"
                    :fixed_pos="true"
                    :style="getEditStyle"
                    @selected-item="updateCheckedDDL"
                    @hide-select="hideEdit"
            ></tablda-select-simple>

            <select-with-folder-structure
                v-else-if="inArray(tableHeader.field, ['eri_table_id','da_loading_output_table_id','mto_dal_pdf_output_table_id','mto_geom_output_table_id','ai_extract_output_table_id'])"
                :cur_val="editValue"
                :available_tables="$root.settingsMeta.available_tables"
                :user="user"
                :is_obj_attr="'id'"
                :is_multiple="$root.isMSEL(tableHeader.input_type)"
                :for_single_select="true"
                @sel-changed="(val) => {editValue = val;}"
                @sel-closed="hideEdit();updateValue();"
                class="form-control full-height"
                :style="getEditStyle">
            </select-with-folder-structure>

            <tablda-select-simple
                    v-else-if="tableHeader.field === 'table_ref_condition_id' && inArray(fld_link_type, ['Record', 'App'])"
                    :options="globalRefConds()"
                    :table-row="tableRow"
                    :hdr_field="tableHeader.field"
                    :can_empty="true"
                    :embed_func_txt="isVertTable ? 'Add New' : ''"
                    :fixed_pos="true"
                    :style="getEditStyle"
                    @selected-item="updateCheckedDDL"
                    @hide-select="hideEdit"
                    @embed-func="showAddRefCond(tableRow.table_ref_condition_id)"
            ></tablda-select-simple>

            <tablda-select-simple
                    v-else-if="inArray(tableHeader.field, ['payment_paypal_keys_id','payment_stripe_keys_id'])"
                    :options="userKeys(tableHeader.field)"
                    :table-row="tableRow"
                    :hdr_field="tableHeader.field"
                    :can_empty="true"
                    :fixed_pos="true"
                    :style="getEditStyle"
                    @selected-item="updateCheckedDDL"
                    @hide-select="hideEdit"
            ></tablda-select-simple>

            <tablda-select-simple
                    v-else-if="inArray(tableHeader.field, [
                        'table_field_id','address_field_id','link_field_lat','link_field_lng','link_field_address','payment_amount_fld_id',
                        'payment_history_payee_fld_id','payment_history_amount_fld_id','payment_history_date_fld_id','multiple_web_label_fld_id',
                        'payment_method_fld_id','payment_description_fld_id','payment_customer_fld_id','history_fld_id','eri_master_field_id',
                        'da_master_field_id',
                    ])"
                    :options="nameFields()"
                    :table-row="tableRow"
                    :hdr_field="tableHeader.field"
                    :can_empty="true"
                    :fixed_pos="true"
                    :style="getEditStyle"
                    @selected-item="updateCheckedDDL"
                    @hide-select="hideEdit"
            ></tablda-select-simple>

            <tablda-select-simple
                    v-else-if="inArray(tableHeader.field, ['linked_report_id'])"
                    :options="nameReports()"
                    :table-row="tableRow"
                    :hdr_field="tableHeader.field"
                    :can_empty="true"
                    :fixed_pos="true"
                    :style="getEditStyle"
                    @selected-item="updateCheckedDDL"
                    @hide-select="hideEdit"
            ></tablda-select-simple>

            <tablda-select-simple
                    v-else-if="inArray(tableHeader.field, ['column_id'])"
                    :options="nameFields('id')"
                    :table-row="tableRow"
                    :hdr_field="tableHeader.field"
                    :can_empty="true"
                    :fixed_pos="true"
                    :style="getEditStyle"
                    @selected-item="updateCheckedDDL"
                    @hide-select="hideEdit"
            ></tablda-select-simple>

            <tablda-select-simple
                    v-else-if="eriVariableOptions()"
                    :options="eriVariableOptions()"
                    :table-row="tableRow"
                    :hdr_field="tableHeader.field"
                    :can_empty="true"
                    :fixed_pos="true"
                    :style="getEditStyle"
                    @selected-item="updateCheckedDDL"
                    @hide-select="hideEdit"
            ></tablda-select-simple>

            <input
                    v-else-if="inArray(tableHeader.field, [
                        'name','add_limit','add_record_limit','tooltip','param','value','web_prefix','listing_header_wi',
                        'listing_rows_width','listing_rows_min_width','max_height_in_vert_table','pop_width_px',
                        'pop_width_px_min','pop_width_px_max','pop_height','pop_height_min','pop_height_max',
                        'eri_variable', 'eri_part', 'part', 'section_q_identifier', 'section_r_identifier', 'variable_name', 'var_notes',
                        'eri_convers','tablda_convers',
                    ])"
                    v-model="editValue"
                    @blur="hideEdit();updateValue()"
                    ref="inline_input"
                    class="form-control full-height"
                    :style="getEditStyle">

            <div v-else="">{{ hideEdit() }}</div>

        </div>
        <!-- ABSOLUTE EDITINGS -->

    </td>
</template>

<script>
import {eventBus} from '../../app';

import Select2DDLMixin from './../_Mixins/Select2DDLMixin.vue';
import CellStyleMixin from '../_Mixins/CellStyleMixin.vue';
import DataRangeMixin from '../_Mixins/DataRangeMixin.vue';

import TabldaSelectSimple from "./Selects/TabldaSelectSimple";
import SelectWithFolderStructure from "./InCell/SelectWithFolderStructure.vue";

export default {
        components: {
            SelectWithFolderStructure,
            TabldaSelectSimple,
        },
        name: "CustomCellDisplayLinks",
        mixins: [
            Select2DDLMixin,
            CellStyleMixin,
            DataRangeMixin,
        ],
        data: function () {
            return {
                editValue: null,
                editing: false,
                oldVal: null,
                selectParamIcon: {
                    tags: true,
                    maximumInputLength: 10
                }
            }
        },
        props:{
            globalMeta: {
                type: Object,
                default: function () {
                    return {};
                }
            },
            tableMeta: Object,
            tableHeader: Object,
            tableRow: Object,
            cellValue: String|Number,
            cellHeight: Number,
            maxCellRows: Number,
            user: Object,
            isVertTable: Boolean,
            isAddRow: Boolean,
            no_width: Boolean,
            parentRow: Object,
            foreignSpecial: Object,
            allRows: Object|null,
        },
        watch: {
            cellValue: {
                handler(val) {
                    this.editValue = this.convEditValue(val);
                },
                immediate: true,
            },
        },
        computed: {
            fld_link_type() {
                return this.tableRow.link_type;
            },
            getCustomCellStyle() {
                let obj = this.getCellStyle();
                if (this.no_width) {
                    obj.width = null;
                }
                obj.textAlignt = (this.tableHeader.f_type === 'Boolean' ? 'center' : '');
                if (!this.canCellEdit) {
                    obj.backgroundColor = '#EEE';
                }
                return obj;
            },
            canCellEdit() {
                if (this.tableMeta.db_name === 'table_field_link_to_dcr') {
                    return true;
                }
                return this.globalMeta._is_owner
                    && !this.inArray(this.tableHeader.field, this.$root.systemFields)
                    && !(this.tableHeader.field === 'icon' && this.inArray(this.fld_link_type, ['GMap', 'GEarth']))
                    && !(this.tableHeader.field === 'web_prefix' && this.tableRow.share_record_link_id)
                    && !(this.tableHeader.field === 'address_field_id' && this.tableRow.share_record_link_id)
                    && !(this.tableHeader.field === 'popup_display' && !this.inArray(this.fld_link_type, ['Record']));
            },
            checkBoxOn() {
                return Number(this.editValue);
            },
            is_sel() {
                return this.$root.issel(this.tableHeader.input_type);
            },
            is_multisel() {
                return this.$root.isMSEL(this.tableHeader.input_type);
            },
            payment_keys() {
                switch (this.tableHeader.field) {
                    case 'payment_paypal_keys_id': return this.$root.user._paypal_payment_keys;
                    case 'payment_stripe_keys_id': return this.$root.user._stripe_payment_keys;
                    default: return [];
                }
            },
            refTable() {
                if (this.isAvailAdn || this.isExportedFlds) {
                    let refcond = _.find(this.globalMeta._ref_conditions, {id: Number(this.tableRow.table_ref_condition_id)});
                    return refcond ? refcond._ref_table : {};
                }
                return {};
            },
            isAvailAdn() {
                return this.tableHeader.field === 'avail_addons';
            },
            isExportedFlds() {
                return this.tableHeader.field === 'link_export_drilled_fields';
            },
            expoFields() {
                return this.tableRow.table_app_id == this.$root.settingsMeta.json_export_app_id
                    ? this.globalMeta._fields
                    : this.refTable._fields;
            },
        },
        methods: {
            availColumnKeys() {
                if (this.parentRow.table_app_id == this.$root.settingsMeta.da_loading_app_id) {
                    return [
                        {val: 'Serial', show: 'Serial'},
                        {val: 'Qty', show: 'Qty'},
                        {val: 'Type', show: 'Type'},
                        {val: 'Carrier', show: 'Carrier'},
                        {val: 'Elevation', show: 'Elevation'},
                    ];
                }
                if (this.parentRow.table_app_id == this.$root.settingsMeta.mto_dal_app_id) {
                    return [
                        {val: 'RowId', show: 'ROW ID'},
                        {val: 'Qty', show: 'QTY'},
                        {val: 'TYPE', show: 'TYPE'},
                        {val: 'CARRIER', show: 'CARRIER/NOTES'},
                        {val: 'ELEVATION', show: 'ELEVATION'},
                    ];
                }
                if (this.parentRow.table_app_id == this.$root.settingsMeta.mto_geom_app_id) {
                    return [
                        {val: 'section', show: 'Section #'},
                        {val: 'legs', show: 'Legs'},
                        {val: 'leggrade', show: 'Leg Grade'},
                        {val: 'diagonals', show: 'Diagonals'},
                        {val: 'diagonalgrade', show: 'Diagonal Grade'},
                        {val: 'topgirts', show: 'Top Girts'},
                        {val: 'bottomgirts', show: 'Bottom Girts'},
                        {val: 'horizontals', show: 'Horizontals'},
                        {val: 'sechorizontals', show: 'Sec. Horizontals'},
                        {val: 'redhorizontals', show: 'Red. Horizontals'},
                        {val: 'reddiagonals', show: 'Red. Diagonals'},
                        {val: 'redhips', show: 'Red. Hips'},
                        {val: 'innerbracing', show: 'Inner Bracing'},
                        {val: 'topguypulloffs', show: 'Top Guy Pull-Offs'},
                        {val: 'facewidthft', show: 'Face Width (ft)'},
                        {val: 'weightk', show: 'Weight'},
                        {val: 'panelsheight', show: 'Panels, Height'},
                        {val: 'panelswidth', show: 'Panels, Width'},
                    ];
                }
                return [];
            },
            availDaTypes() {
                return [
                    {val: 'ai', show: 'AI Model'},
                ];
                // return [
                //     {val: 'stim', show: 'Gemini AI(STIM)'},
                //     {val: 'user', show: 'Gemini AI(User)'},
                // ];
            },
            availApiKeys(type) {
                //let keys = _.filter(this.$root.user['_ai_api_keys'] || [], {type: type});
                return _.map(this.$root.user['_ai_api_keys'] || [], (el) => {
                    return {val: el.id, show: el.name};
                });
            },
            eriPartsOpts() {
                return _.map(this.foreignSpecial ? this.foreignSpecial._eri_parts : [], (el) => {
                    return {val: el.id, show: el.part};
                });
            },
            eriVariableOptions() {
                let avail = this.tableHeader.field === 'eri_variable'
                    && this.foreignSpecial
                    && this.foreignSpecial._eri_parts
                    && this.parentRow.eri_part_id;

                let part = avail ? _.find(this.foreignSpecial._eri_parts, {id: Number(this.parentRow.eri_part_id)}) : null;

                if (avail && part && part._part_variables) {
                    let restrictedVars = _.map(this.allRows, 'eri_variable');
                    let partVariables = _.filter(part._part_variables, (el) => {
                        return ! this.$root.inArray(el.variable_name, restrictedVars)
                            || el.variable_name === this.tableRow.eri_variable;
                    });

                    return _.map(partVariables, (el) => {
                        return {val: el.variable_name, show: el.variable_name};
                    });
                }

                return null;
            },
            availAdnOn(item) {
                return this.isAvailAdn && this.inArray(item, this.editValue);
            },
            allIsChecked() {
                return this.isExportedFlds && ! _.find(this.expoFields, (item) => {
                    return ! this.inArray(item.id, this.editValue);
                });
            },
            updateAll() {
                if (this.allIsChecked()) {
                    this.editValue = [];
                } else {
                    this.editValue = _.map(this.expoFields, 'id');
                }
            },
            availExpFldOn(item) {
                return this.isExportedFlds && this.inArray(item, this.editValue);
            },
            convEditValue(res, reverse) {
                if (this.is_multisel) {
                    if (reverse) {
                        return (Array.isArray(res) ? JSON.stringify(res) : res);
                    } else {
                        let arr = this.$root.parseMsel(res);
                        return this.isAvailAdn
                            ? arr
                            : _.map(arr, (id) => { return Number(id); });
                    }
                }
                return res;
            },
            inArray(item, array) {
                return $.inArray(item, array) > -1;
            },
            isEditing() {
                return this.editing && this.canCellEdit && !this.$root.global_no_edit;
            },
            showEdit() {
                if (!this.canCellEdit
                    || this.inArray(this.tableHeader.f_type, ['Boolean'])
                    || this.inArray(this.tableHeader.field, ['show_sum', 'floating_action', '_conversion_button'])) {
                    return;
                }
                //edit cell
                this.editing = true;
                if (this.isEditing()) {
                    this.oldValue = this.editValue;
                    this.$nextTick(function () {
                        if (this.$refs.inline_input) {
                            if (this.tableHeader.field === 'icon') {
                                this.showHideDDLs(this.selectParamIcon);
                            } else
                            if (this.$refs.inline_input && this.$refs.inline_input.nodeName === 'SELECT'){
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
            updateCheckedDDL(item) {
                if (this.is_multisel) {
                    this.editValue = Array.isArray(this.editValue) ? this.editValue : [String(this.editValue)];

                    if (this.editValue.indexOf(item) > -1) {
                        this.editValue.splice( this.editValue.indexOf(item), 1 );
                    } else {
                        this.editValue.push(item);
                    }
                } else {
                    this.editValue = item;
                }
                this.updateValue();
            },
            updateCheckBox() {
                this.editValue = !Boolean(this.editValue);
                this.updateValue();
            },
            updateValue() {
                let newVal = this.convEditValue(this.editValue, true);
                if (newVal !== this.oldValue) {
                    this.tableRow._changed_field = this.tableHeader.field;
                    this.tableRow[this.tableHeader.field] = newVal;
                    if (this.tableHeader.field === 'table_ref_condition_id') {
                        this.tableRow.listing_field_id = null;
                        this.tableRow.link_preview_fields = '';
                        this.tableRow.email_addon_fields = '';
                    }
                    if (this.tableHeader.field === 'link_field_lat' || this.tableHeader.field === 'link_field_lng') {
                        this.tableRow.link_field_address = null;
                    }
                    if (this.tableHeader.field === 'link_field_address') {
                        this.tableRow.link_field_lat = null;
                        this.tableRow.link_field_lng = null;
                    }

                    // if (this.tableHeader.field === 'eri_master_field_id') {
                    //     this.tableRow.eri_variable = null;
                    // }
                    // if (this.tableHeader.field === 'eri_variable') {
                    //     this.tableRow.eri_master_field_id = null;
                    // }

                    if (this.tableHeader.field === 'column_id' && String(this.tableRow['value']).substr(0,2) !== '{$') {
                        this.tableRow.value = null;
                    }
                    if (this.tableHeader.field === 'value' && String(this.tableRow['value']).substr(0,2) !== '{$') {
                        this.tableRow.column_id = null;
                    }
                    if (this.tableHeader.field === 'link_type') {
                        let val = this.tableRow[this.tableHeader.field];
                        this.tableRow.link_display = this.inArray( val, ['Record'] ) ? 'Popup' : null;
                        this.tableRow.popup_display = this.inArray( val, ['Record'] ) ? 'Listing' : null;
                        this.tableRow.show_sum = !!this.inArray( val, ['Record'] );
                        this.tableRow.floating_action = !!this.inArray( val, ['Record'] );
                    }
                    if (this.tableHeader.field === 'add_record_limit') {
                        this.tableRow.already_added_records = 0;
                    }

                    this.$emit('updated-cell', this.tableRow);
                }
            },
            showMselPart(id) {
                if (this.inArray(this.tableHeader.field, ['json_export_filename_fields','eri_writer_filename_fields'])) {
                    let fld = _.find(this.allFields(), {val: Number(id)});
                    return fld ? fld.show : (id || '');
                }

                let ref_cond = _.find(this.globalMeta._ref_conditions, {id: Number(this.tableRow.table_ref_condition_id)});
                let res = id;
                if (ref_cond && ref_cond._ref_table && ref_cond._ref_table._fields) {
                    let field = _.find(ref_cond._ref_table._fields, {id: Number(id)});
                    res = field ? this.$root.uniqName(field.name) : '';
                }
                return res;
            },
            showField() {
                let res = '';
                if (this.tableHeader.field === 'smart_select_data_range') {
                    let val = this.tableRow[this.tableHeader.field];
                    res = val === null ? '' : this.rgName(val, this.globalMeta);
                }
                else
                if (this.tableHeader.field === 'table_ref_condition_id' && this.tableRow.table_ref_condition_id) {
                    let ref_cond = _.find(this.globalMeta._ref_conditions, {id: Number(this.tableRow.table_ref_condition_id)});
                    res = ref_cond ? ref_cond.name : '';
                }
                else
                if (this.inArray(this.tableHeader.field, ['eri_table_id','da_loading_output_table_id','mto_dal_pdf_output_table_id','mto_geom_output_table_id','ai_extract_output_table_id'])) {
                    let refMeta = _.find(this.$root.settingsMeta.available_tables, {id: Number(this.tableRow[this.tableHeader.field])});
                    res = refMeta ? refMeta.name : (this.tableRow[this.tableHeader.field] || '');
                }
                else
                if (this.tableHeader.field === 'eri_part_id') {
                    let rPart = _.find(this.foreignSpecial ? this.foreignSpecial._eri_parts : [], {id: Number(this.tableRow[this.tableHeader.field])});
                    res = rPart ? rPart.part : (this.tableRow[this.tableHeader.field] || '');
                }
                else
                if (this.tableHeader.field === 'column_key') {
                    let rPart = _.find(this.availColumnKeys(), {val: this.tableRow[this.tableHeader.field]});
                    res = rPart ? rPart.show : (this.tableRow[this.tableHeader.field] || '');
                }
                else
                if (this.tableHeader.field === 'da_loading_type') {
                    let rPart = _.find(this.availDaTypes(), {val: this.tableRow[this.tableHeader.field]});
                    res = rPart ? rPart.show : (this.tableRow[this.tableHeader.field] || '');
                }
                else
                if (this.inArray(this.tableHeader.field, ['da_loading_gemini_key_id','ai_extract_ai_id'])) {
                    let rPart = _.find(this.availApiKeys(), {val: Number(this.tableRow[this.tableHeader.field])});
                    res = rPart ? rPart.show : (this.tableRow[this.tableHeader.field] || '');
                }
                else
                if (this.inArray(this.tableHeader.field, ['lnk_dcr_permission_id', 'lnk_srv_permission_id', 'lnk_mrv_permission_id'])) {
                    let permis = _.find(this.refPermissions(), {val: Number(this.tableRow[this.tableHeader.field])});
                    res = permis ? permis.show : 'Visiting';
                }
                else
                if (this.inArray(this.tableHeader.field, ['json_import_field_id','json_export_field_id','eri_parser_file_id',
                    'eri_writer_file_id','da_loading_image_field_id','mto_dal_pdf_doc_field_id','mto_geom_doc_field_id','ai_extract_doc_field_id'])
                ) {
                    let fld = _.find(this.allFields('Attachment'), {val: Number(this.editValue)});
                    res = fld ? fld.show : (this.tableRow[this.tableHeader.field] || '');
                }
                else
                if (this.inArray(this.tableHeader.field, ['eri_field_id','da_field_id','smart_select_source_field_id','smart_select_target_field_id'])) {
                    let fld = _.find(this.allFields(null, 1), {val: Number(this.editValue)});
                    res = fld ? fld.show : (this.tableRow[this.tableHeader.field] || '');
                }
                else
                if ($.inArray(this.tableHeader.field, ['payment_paypal_keys_id','payment_stripe_keys_id']) > -1 && this.tableRow[this.tableHeader.field]) {
                    let keysobj = _.find(this.payment_keys, {id: Number(this.tableRow[this.tableHeader.field])});
                    res = keysobj ? keysobj.name : res;
                }
                else
                if (this.tableHeader.field === 'listing_field_id' && this.tableRow.listing_field_id) {
                    let ref_cond = _.find(this.globalMeta._ref_conditions, {id: Number(this.tableRow.table_ref_condition_id)});
                    if (ref_cond && ref_cond._ref_table && ref_cond._ref_table._fields) {
                        let field = _.find(ref_cond._ref_table._fields, {id: Number(this.tableRow.listing_field_id)});
                        res = field ? this.$root.uniqName(field.name) : '';
                    }
                }
                else
                if (this.tableHeader.field === 'column_id' && this.tableRow[this.tableHeader.field]) {
                    let col = _.find(this.nameFields('id'), {val: Number(this.tableRow[this.tableHeader.field])});
                    res = col ? col.show : this.tableRow[this.tableHeader.field];
                }
                else
                if (this.tableHeader.field === 'share_record_link_id' || this.tableHeader.field === 'eri_parser_link_id') {
                    let rlink = _.find(this.recordLinks(), {val: Number(this.tableRow[this.tableHeader.field])});
                    res = rlink ? rlink.show : (this.tableRow[this.tableHeader.field] || '');
                }
                else
                if (this.tableHeader.field === 'table_def_align') {
                    let st = _.find(this.alignOptions(), {val: this.tableRow[this.tableHeader.field]});
                    res = st ? st.show : (this.tableRow[this.tableHeader.field] || '');
                }
                else
                if (this.tableHeader.field === 'listing_panel_status') {
                    let st = _.find(this.panelStatuses(), {val: this.tableRow[this.tableHeader.field]});
                    res = st ? st.show : (this.tableRow[this.tableHeader.field] || '');
                }
                else
                if (this.tableHeader.field === 'inline_style') {
                    let st = _.find(this.inlineStyles(), {val: this.tableRow[this.tableHeader.field]});
                    res = st ? st.show : (this.tableRow[this.tableHeader.field] || '');
                }
                else
                if (this.tableHeader.field === 'inline_width') {
                    let st = _.find(this.inlineWidths(), {val: this.tableRow[this.tableHeader.field]});
                    res = st ? st.show : (this.tableRow[this.tableHeader.field] || '');
                }
                else
                if (this.tableHeader.field === 'link_display' && this.tableRow.link_display) {
                    switch (this.tableRow.link_display) {
                        case 'Popup': res = 'Pop-up'; break;
                        case 'Table': res = 'New Tab Table'; break;
                        case 'RorT': res = 'Choose at Opening'; break;
                    }
                }
                else
                if (this.tableHeader.field === 'link_pos' && this.tableRow.link_pos) {
                    switch (this.tableRow.link_pos) {
                        case 'before': res = 'Before'; break;
                        case 'after': res = 'After'; break;
                        case 'front': res = 'Front'; break;
                        case 'end': res = 'End'; break;
                    }
                }
                else
                if (this.tableHeader.field === 'table_field_link_id' && this.tableRow.table_field_link_id) {
                    _.each(this.globalMeta._fields, (fld) => {
                        _.each(fld._links, (link,idx) => {
                            if (link.id == this.tableRow.table_field_link_id) {
                                res = fld.name + '/#' + (idx+1);
                            }
                        });
                    });
                }
                else
                if (
                    this.inArray(this.tableHeader.field, [
                        'table_field_id','address_field_id','link_field_lat','link_field_lng','link_field_address','payment_amount_fld_id',
                        'payment_history_payee_fld_id','payment_history_amount_fld_id','payment_history_date_fld_id','multiple_web_label_fld_id',
                        'payment_method_fld_id','payment_description_fld_id','payment_customer_fld_id','history_fld_id',
                    ])
                    &&
                    this.editValue
                ) {
                    let idx = _.findIndex(this.globalMeta._fields, {id: Number(this.editValue)});
                    res = idx > -1 ? this.$root.uniqName( this.globalMeta._fields[idx].name ) : '';
                }
                else
                if (this.inArray(this.tableHeader.field, ['eri_master_field_id','da_master_field_id'])) {
                    let fld = _.find(this.nameFields(), {val: Number(this.editValue)});
                    res = fld ? fld.show : '';
                }
                else
                if (this.inArray(this.tableHeader.field, ['linked_report_id'])) {
                    let rprt = _.find(this.nameReports(), {val: Number(this.editValue)});
                    res = rprt ? rprt.show : '';
                }
                else
                if (this.tableHeader.field === 'table_app_id' && this.tableRow[this.tableHeader.field]) {
                    let app = _.find(this.tableApps(), {val: Number(this.tableRow[this.tableHeader.field])});
                    res = app ? app.show : this.tableRow[this.tableHeader.field];
                }
                else
                if (this.eriVariableOptions()) {
                    let app = _.find(this.eriVariableOptions(), {val: Number(this.tableRow[this.tableHeader.field])});
                    res = app ? app.show : (this.tableRow[this.tableHeader.field] || '');
                }
                else {
                    res = to_standard_val(this.editValue);
                }
                return this.$root.strip_danger_tags( String(res) );
            },
            showAddRefCond(refId) {
                this.$emit('show-add-ref-cond', refId)
            },
            showFieldDisplay() {
                this.$emit('show-def-val-popup', this.tableRow);
            },
            showEriFieldConversionsPopup() {
                this.$emit('show-def-val-popup', this.tableRow, this.parentRow);
            },
            showBoardSett() {
                eventBus.$emit('show-general-settings-popup');
            },
            showUserSettings() {
                eventBus.$emit('open-resource-popup', 'connections', 0, 'payment');
            },
            showPermis() {
                let refcond = _.find(this.globalMeta._ref_conditions, {id: Number(this.tableRow.table_ref_condition_id)}) || {};
                this.$emit('show-add-ddl-option', refcond.ref_table_id);
            },

            //arrays for selects
            nameFields(id) {
                let types = this.$root.inArray(this.tableHeader.field, ['multiple_web_label_fld_id','address_field_id'])
                    ? ['String', 'Text', 'Long Text', 'Auto String']
                    : null;

                let fields = _.filter(this.globalMeta._fields, (hdr) => {
                    return (hdr.field === id || !this.inArray(hdr.field, this.$root.systemFields))
                        && (!types || this.$root.inArray(hdr.f_type, types));
                });

                return _.map(fields, (hdr) => {
                    return { val: hdr.id, show: this.$root.uniqName(hdr.name), }
                });
            },
            nameReports() {
                return _.map(this.globalMeta._reports, (rptr) => {
                    return { val: rptr.id, show: rptr.report_name, }
                });
            },
            userKeys() {
                return _.map(this.payment_keys, (key) => {
                    return { val: key.id, show: key.name, }
                });
            },
            globalRefConds() {
                return _.map(this.globalMeta._ref_conditions, (rc) => {
                    return { val: rc.id, show: rc.name, }
                });
            },
            getLiFi() {
                let refCond = _.find(this.globalMeta._ref_conditions, {id: Number(this.tableRow.table_ref_condition_id)});
                return (refCond && refCond._ref_table && refCond._ref_table._fields ? refCond._ref_table._fields : []);
            },
            listingsFields() {
                let fields = _.filter(this.getLiFi(), (hdr) => {
                    return !this.inArray(hdr.field, this.$root.systemFields)
                });
                return _.map(fields, (hdr) => {
                    return { val: hdr.id, show: this.$root.uniqName(hdr.name), }
                });
            },
            refPermissions() {
                let refcond = _.find(this.globalMeta._ref_conditions, {id: Number(this.tableRow.table_ref_condition_id)}) || {};
                let refTable = _.find(this.$root.settingsMeta.available_tables, {id: Number(refcond.ref_table_id)}) || {};
                return _.map(refTable._table_permissions || [], (prms) => {
                    return { val: prms.id, show: prms.name, }
                });
            },
            recordLinks() {
                let recordLinks = [];
                _.each(this.globalMeta._fields, (fld) => {
                    _.each(fld._links, (lnk) => {
                        if (this.tableHeader.field == 'share_record_link_id' && lnk.link_type === 'Record') {
                            recordLinks.push({ val:lnk.id, show:lnk.name, })
                        }
                        if (this.tableHeader.field == 'eri_parser_link_id' && lnk.table_app_id == this.$root.settingsMeta.eri_parser_app_id) {
                            recordLinks.push({ val:lnk.id, show:lnk.name, })
                        }
                    });
                });
                return recordLinks;
            },
            alignOptions() {
                return [
                    {val: 'start', show: 'Left'},
                    {val: 'center', show: 'Center'},
                    {val: 'end', show: 'Right'},
                ];
            },
            panelStatuses() {
                return [
                    { val:'opened', show:'Open', },
                    { val:'collapsed', show:'Collapsed', },
                    { val:'hidden', show:'None', },
                ];
            },
            inlineStyles() {
                return [
                    { val:'regular', show:'Regular', },
                    { val:'simple', show:'Simple', },
                ];
            },
            inlineWidths() {
                return [
                    { val:'full', show:'Full', },
                    { val:'field', show:'Field', },
                ];
            },
            allFieldLinks() {
                let res = [];
                _.each(this.globalMeta._fields, (fld) => {
                    _.each(fld._links, (link,idx) => {
                        res.push( { val: link.id, show: fld.name + '/#' + (idx+1), } );
                    });
                });
                return res;
            },
            allFields(ftype, noRestriction) {
                let foreign_table_id = null;
                if (this.parentRow && this.tableHeader.field === 'eri_field_id') {
                    foreign_table_id = this.parentRow.eri_table_id;
                }
                if (this.parentRow && this.tableHeader.field === 'da_field_id') {
                    if (this.parentRow.table_app_id == this.$root.settingsMeta.da_loading_app_id) {
                        foreign_table_id = this.parentRow.da_loading_output_table_id;
                    }
                    if (this.parentRow.table_app_id == this.$root.settingsMeta.mto_dal_app_id) {
                        foreign_table_id = this.parentRow.mto_dal_pdf_output_table_id;
                    }
                    if (this.parentRow.table_app_id == this.$root.settingsMeta.mto_geom_app_id) {
                        foreign_table_id = this.parentRow.mto_geom_output_table_id;
                    }
                    if (this.parentRow.table_app_id == this.$root.settingsMeta.ai_extractm_app_id) {
                        foreign_table_id = this.parentRow.ai_extract_output_table_id;
                    }
                }

                let meta = foreign_table_id
                    ? _.find(this.$root.settingsMeta.available_tables, {id: Number(foreign_table_id)})
                    : this.globalMeta;
                if (! meta) {
                    meta = this.globalMeta;
                }

                let fields = meta._fields;
                if (this.$root.inArray(this.tableHeader.field, ['eri_field_id', 'da_field_id']) && !noRestriction) {
                    let restrictedFldIds = _.map(this.allRows, this.tableHeader.field);
                    fields = _.filter(meta._fields, (fld) => ! this.$root.inArray(fld.id, restrictedFldIds));
                }

                let res = [];
                _.each(fields, (fld) => {
                    if (!ftype || fld.f_type === ftype) {
                        res.push({val: fld.id, show: fld.name,});
                    }
                });
                return res;
            },
            tableApps() {
                //return _.map(this.$root.settingsMeta.table_public_apps_data, (app) => {
                return _.map(this.$root.settingsMeta.table_apps_data, (app) => {
                    return { val: app.id, show: app.name, }
                });
            },

            lnkHasP2SItems() {
                let refcond = this.refCond();
                if (! refcond) return;

                return refcond
                    && refcond.ref_table_id != this.globalMeta.id
                    && refcond._items && _.find(refcond._items, {item_type: 'P2S'});
            },
            lnkAlreadyReversed() {
                let refcond = this.refCond();
                if (! refcond) return;

                let refTb = refcond ? _.find(this.$root.settingsMeta.available_tables, {id: Number(refcond.ref_table_id)}) : null;
                return refTb && refTb._ref_conditions && _.find(refTb._ref_conditions, {name: 'R_' + refcond.name});
            },
            addReverseLinkClick() {
                let refcond = this.refCond();
                if (! refcond) return;

                $.LoadingOverlay('show');
                axios.post('/ajax/settings/data/link/add-reverse', {
                    link_id: this.tableRow.id,
                }).then(({ data }) => {
                    let refTb = refcond ? _.find(this.$root.settingsMeta.available_tables, {id: Number(refcond.ref_table_id)}) : null;
                    if (refTb) {
                        refTb._ref_conditions.push({name: 'R_' + refcond.name});
                    }
                    Swal('Info', 'Reversed Link/RC was created!');
                }).catch(errors => {
                    Swal('Info', getErrors(errors));
                }).finally(() => {
                    $.LoadingOverlay('hide');
                });
            },
            fillEriPartVariables() {
                $.LoadingOverlay('show');
                axios.post('/ajax/settings/data/link/eri-part-variable/fill', {
                    link_eri_part_id: this.tableRow.id,
                }).then(({ data }) => {
                    this.tableRow._part_variables = data;
                }).catch(errors => {
                    Swal('Info', getErrors(errors));
                }).finally(() => {
                    $.LoadingOverlay('hide');
                });
            },
            showEriFieldConversions() {
                this.$emit('show-def-val-popup', this.tableRow, this.parentRow);
            },
            refCond() {
                return _.find(this.globalMeta._ref_conditions, {id: Number(this.tableRow.table_ref_condition_id)});
            },
        },
        mounted() {
            this.editValue = this.convEditValue(this.cellValue);
        },
    }
</script>

<style lang="scss" scoped>
    @import "CustomCell.scss";

    .right-cog {
        position: absolute;
        right: 0;
        top: 50%;
        transform: translateY(-50%);
        padding: 5px;
        cursor: pointer;
    }
    .vert_stack {
        display: flex;
        flex-direction: column;
        width: max-content;
        margin: 0 auto;
    }

    .more_fields {
        overflow: auto !important;
    }
</style>