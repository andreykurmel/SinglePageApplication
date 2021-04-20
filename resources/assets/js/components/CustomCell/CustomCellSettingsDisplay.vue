    <template>
        <td :style="getCustomCellStyle"
            class="td-custom"
            ref="td"
            @click="showEdit()"
        >
            <div class="td-wrapper" :style="getTdWrappStyle">

                <div class="wrapper-inner" :style="getWrapperStyle">
                    <div class="inner-content">

                        <div v-if="tableHeader.f_type === 'Boolean' && tableHeader.field === 'header_unit_ddl'">
                            <label class="switch_t" :style="{height: Math.min(maxCellHGT, 17)+'px'}">
                                <input type="checkbox"
                                       v-model="tableRow[tableHeader.field]"
                                       @click="header_unit_ddl_click"
                                       @change="disabledCheckBox ? '' : updateValue()">
                                <span class="toggler round" :class="[disabledCheckBox ? 'disabled' : '']"></span>
                            </label>
                            <info-popup v-if="radio_help"
                                        :title_html="'Info'"
                                        :content_html="'User needs to “Activate Unit Conversion” in Settings/Basics/General prior to turning on “DDL in Header” for unit conversion'"
                                        :extra_style="{top:'calc(50% - 75px)'}"
                                        @hide="radio_help = false"
                            ></info-popup>
                            <!--<hover-block v-if="radio_help"-->
                                         <!--:html_str="'User needs to “Activate Unit Conversion” in Settings/Basics/General prior to turning on “DDL in Header” for unit conversion'"-->
                                         <!--:p_left="radio_left"-->
                                         <!--:bg_color="'#777'"-->
                                         <!--:extra_title="'Edit'"-->
                                         <!--:p_top="radio_top"-->
                                         <!--@another-click="radio_help = false"-->
                            <!--&gt;</hover-block>-->
                        </div>

                        <div v-else-if="tableHeader.f_type === 'Boolean'">
                            <label class="switch_t" :style="{height: Math.min(maxCellHGT, 17)+'px'}">
                                <input type="checkbox" :disabled="disabledCheckBox" v-model="tableRow[tableHeader.field]" @change="updateValue()">
                                <span class="toggler round" :class="[disabledCheckBox ? 'disabled' : '']"></span>
                            </label>
                        </div>

                        <input
                                v-else-if="tableHeader.f_type === 'Radio' && !isAddRow"
                                :checked="tableRow[tableHeader.field]"
                                :disabled="disabledCheckBox"
                                @click="updateRadio()"
                                type="radio"
                                ref="inline_input"
                                class="checkbox-input"/>

                        <a v-else-if="tableHeader.field === 'ddl_id' || tableHeader.field === 'unit_ddl_id'"
                           @click.stop="showDdlSettingsPopup()"
                        >{{ showField() }}</a>

                        <div v-else="" :title="getTitle" ref="sett_content_elem">{{ showField() }}</div>

                    </div>
                </div>

                <cell-table-data-expand
                        v-if="cont_height > maxCellHGT+cell_top_padding"
                        style="background-color: #FFF;"
                        :cont_height="cont_height"
                        :cont_width="cont_width"
                        :table-meta="globalMeta"
                        :table-row="tableRow"
                        :table-header="tableHeader"
                        :html="cont_html"
                        :uniqid="getuniqid()"
                        :can-edit="canCellEdit"
                        :user="user"
                ></cell-table-data-expand>

            </div>



            <!-- ABSOLUTE EDITINGS -->
            <div v-if="tableHeader.f_type === 'Color'" class="cell-editing">
                <tablda-colopicker
                        :init_color="tableRow[tableHeader.field]"
                        :saved_colors="$root.color_palette"
                        :avail_null="true"
                        @set-color="(clr, save) => {updateColor(tableHeader, clr, save)}"
                ></tablda-colopicker>
            </div>

            <div v-else-if="tableHeader.field === 'col_align'" class="cell-editing">
                <align-of-column
                        :table-row="tableRow"
                        @set-align="updateValue()"
                ></align-of-column>
            </div>

            <div v-else-if="isEditing()" class="cell-editing">

                <formula-helper
                        v-if="tableHeader.input_type === 'Formula'"
                        :user="user"
                        :table-meta="globalMeta"
                        :table-row="tableRow"
                        :table-header="tableRow"
                        :header-key="'f_formula'"
                        :can-edit="canCellEdit"
                        :pop_width="'100%'"
                        @set-formula="updateRow"
                ></formula-helper>

                <tablda-select-simple
                        v-else-if="tableHeader.field === 'name' && isAddRow"
                        :options="nameFields()"
                        :table-row="tableRow"
                        :hdr_field="'active_links'"
                        :fixed_pos="reactive_provider.fixed_ddl_pos"
                        :style="getEditStyle"
                        @selected-item="updateCheckedDDL"
                        @hide-select="hideEdit"
                ></tablda-select-simple>

                <tablda-select-simple
                        v-else-if="tableHeader.field === 'unit_ddl_id'"
                        :options="globalMetaDdls()"
                        :table-row="tableRow"
                        :hdr_field="tableHeader.field"
                        :can_empty="true"
                        :fixed_pos="reactive_provider.fixed_ddl_pos"
                        :embed_func_txt="'Add New'"
                        :style="getEditStyle"
                        @selected-item="updateCheckedDDL"
                        @hide-select="hideEdit"
                        @embed-func="showDdlSettingsPopup()"
                ></tablda-select-simple>

                <tablda-select-simple
                        v-else-if="tableHeader.field === 'ddl_id' && inArray(tableRow.input_type, $root.ddlInputTypes)"
                        :options="globalMetaDdls()"
                        :table-row="tableRow"
                        :hdr_field="tableHeader.field"
                        :can_empty="true"
                        :embed_func_txt="'Add New'"
                        :fixed_pos="reactive_provider.fixed_ddl_pos"
                        :style="getEditStyle"
                        @selected-item="updateCheckedDDL"
                        @hide-select="hideEdit"
                        @embed-func="showDdlSettingsPopup()"
                ></tablda-select-simple>

                <tablda-select-simple
                        v-else-if="tableHeader.field === 'default_stats'"
                        :options="availRowSumFormulas()"
                        :table-row="tableRow"
                        :hdr_field="tableHeader.field"
                        :can_empty="true"
                        :fixed_pos="reactive_provider.fixed_ddl_pos"
                        :style="getEditStyle"
                        @selected-item="updateCheckedDDL"
                        @hide-select="hideEdit"
                ></tablda-select-simple>

                <tablda-select-simple
                        v-else-if="tableHeader.field === 'input_type'"
                        :options="[
                            {show: 'Auto', val: 'Auto'},
                            {show: 'Input', val: 'Input'},
                            {show: 'S-Select', val: 'S-Select'},
                            {show: 'S-Search', val: 'S-Search'},
                            {show: 'S-SS', val: 'S-SS'},
                            {show: 'M-Select', val: 'M-Select'},
                            {show: 'M-Search', val: 'M-Search'},
                            {show: 'M-SS', val: 'M-SS'},
                            {show: 'Formula', val: 'Formula'},
                        ]"
                        :table-row="tableRow"
                        :hdr_field="tableHeader.field"
                        :fixed_pos="reactive_provider.fixed_ddl_pos"
                        :style="getEditStyle"
                        @selected-item="updateCheckedDDL"
                        @hide-select="hideEdit"
                ></tablda-select-simple>

                <tablda-select-simple
                        v-else-if="tableHeader.field === 'verttb_cell_height'"
                        :options="[
                            {show: 'Small', val: 1},
                            {show: 'Medium', val: 2},
                            {show: 'Large', val: 3},
                            {show: 'Ex-Large', val: 5},
                        ]"
                        :table-row="tableRow"
                        :hdr_field="tableHeader.field"
                        :fixed_pos="reactive_provider.fixed_ddl_pos"
                        :style="getEditStyle"
                        @selected-item="updateCheckedDDL"
                        @hide-select="hideEdit"
                ></tablda-select-simple>

                <tablda-select-simple
                        v-else-if="tableHeader.field === 'filter_type'
                                && tableRow.filter
                                && inArray(tableRow.f_type, availRangeTypes)"
                        :options="[
                            {val: 'value', show: 'Value'},
                            {val: 'range', show: 'Range'},
                        ]"
                        :table-row="tableRow"
                        :hdr_field="tableHeader.field"
                        :fixed_pos="reactive_provider.fixed_ddl_pos"
                        :style="getEditStyle"
                        @selected-item="updateCheckedDDL"
                        @hide-select="hideEdit"
                ></tablda-select-simple>

                <tablda-select-simple
                        v-else-if="tableHeader.field === 'ddl_style'
                            && tableRow.ddl_id
                            && inArray(tableRow.input_type, $root.ddlInputTypes)"
                        :options="[
                            {val: 'ddl', show: 'DDL'},
                            {val: 'panel', show: 'Panel'},
                        ]"
                        :table-row="tableRow"
                        :hdr_field="tableHeader.field"
                        :fixed_pos="reactive_provider.fixed_ddl_pos"
                        :style="getEditStyle"
                        @selected-item="updateCheckedDDL"
                        @hide-select="hideEdit"
                ></tablda-select-simple>

                <tablda-select-ddl
                        v-else-if="tableHeader.field === 'unit_display' || (tableHeader.field === 'unit' && tableRow.unit_ddl_id)"
                        :ddl_id="tableRow.unit_ddl_id"
                        :can_empty="true"
                        :table-row="tableRow"
                        :hdr_field="tableHeader.field"
                        :fixed_pos="reactive_provider.fixed_ddl_pos"
                        :style="getEditStyle"
                        @selected-item="updateCheckedDDL"
                        @hide-select="hideEdit"
                ></tablda-select-ddl>

                <input
                        v-else-if="inArray(tableHeader.field, ownerInputs) && !inArray(tableHeader.field, fieldsWithSpecialRules)"
                        v-model="tableRow[tableHeader.field]"
                        @blur="updateRow()"
                        ref="inline_input"
                        class="form-control full-height"
                        :style="getEditStyle">

                <input
                        v-else-if="inArray(tableHeader.field, fieldsForUser) && !inArray(tableHeader.field, fieldsWithSpecialRules)"
                        v-model="tableRow[tableHeader.field]"
                        @blur="updateRow()"
                        ref="inline_input"
                        class="form-control full-height"
                        :style="getEditStyle">

                <div v-else="">{{ hideEdit() }}</div>

            </div>
            <!-- ABSOLUTE EDITINGS -->

        </td>
    </template>

    <script>
        import {SelectedCells} from './../../classes/SelectedCells';

        import {eventBus} from './../../app';

        import Select2DDLMixin from './../_Mixins/Select2DDLMixin.vue';
        import CellStyleMixin from '../_Mixins/CellStyleMixin.vue';
        import CellMoveKeyHandlerMixin from './../_Mixins/CellMoveKeyHandlerMixin.vue';
        import ExpandIconMixin from './../_Mixins/ExpandIconMixin.vue';

        import TabldaColopicker from "./InCell/TabldaColopicker.vue";
        import AlignOfColumn from "./InCell/AlignOfColumn.vue";
        import TabldaSelectSimple from "./Selects/TabldaSelectSimple.vue";
        import TabldaSelectDdl from "./Selects/TabldaSelectDdl.vue";
        import HoverBlock from "../CommonBlocks/HoverBlock.vue";
        import InfoPopup from "../CustomPopup/InfoPopup.vue";
        import FormulaHelper from "./InCell/FormulaHelper";
        import CellTableDataExpand from "./InCell/CellTableDataExpand";

        export default {
            name: "CustomCellSettingsDisplay",
            components: {
                CellTableDataExpand,
                FormulaHelper,
                InfoPopup,
                HoverBlock,
                TabldaSelectDdl,
                TabldaSelectSimple,
                AlignOfColumn,
                TabldaColopicker,
            },
            mixins: [
                Select2DDLMixin,
                CellStyleMixin,
                CellMoveKeyHandlerMixin,
                ExpandIconMixin,
            ],
            inject: {
                reactive_provider: {
                    from: 'reactive_provider',
                    default: () => { return {} }
                }
            },
            data: function () {
                return {
                    radio_top: 0,
                    radio_left: 0,
                    radio_help: false,
                    editing: false,
                    oldVal: null,
                    ownerInputs: ['formula_symbol','tooltip','verttb_he_auto','verttb_cell_height','verttb_row_height','notes','placeholder_content'],
                    fieldsWithSpecialRules: [
                        'filter_type',
                    ],
                    fieldsForUser: this.$root.availableNotOwnerDisplayColumns,
                    selectParam: {
                        tags: true,
                        maximumInputLength: 5
                    },
                    availRangeTypes: ['String','Integer','Decimal','Currency','Percentage','Date','Date Time'],
                    table_header_field: (this.tableHeader.field === 'name' ? 'id' : this.tableHeader.field),
                    no_key_handler: false,
                }
            },
            props:{
                globalMeta: {
                    type: Object,
                    default: function () {
                        return {};
                    }
                },
                selectedCell: SelectedCells,
                tableHeader: Object,
                tableRow: Object,
                rowIndex: Number,
                cellHeight: Number,
                maxCellRows: Number,
                user: Object,
                isAddRow: Boolean,
                isVertTable: Boolean,
                isSelected: Boolean,
                behavior: String,
                is_visible: Boolean,
            },
            computed: {
                getCustomCellStyle() {
                    let obj = this.getCellStyle;
                    obj.textAlign = (this.inArray(this.tableHeader.f_type, ['Boolean', 'Radio']) ? 'center' : '');
                    obj.backgroundColor = this.isSelected ? '#CFC' : 'inherit';
                    let has_unit = this.tableRow.unit && this.tableRow.unit_ddl_id && this.globalMeta.unit_conv_is_active;// && this.globalMeta.__unit_convers && this.globalMeta.__unit_convers.length;
                    if (
                        //In Settings/Links
                        this.tableRow.link_type === 'Web' && this.inArray(this.tableHeader.field, ['table_ref_condition_id', 'listing_field_id'])
                        ||
                        this.tableRow.link_type === 'Table' && this.inArray(this.tableHeader.field, ['listing_field_id'])
                        ||
                        //In Settings/Basics
                        (!this.tableRow.ddl_id || !this.inArray(this.tableRow.input_type, this.$root.ddlInputTypes)) && this.inArray(this.tableHeader.field, ['ddl_style'])
                        ||
                        !this.inArray(this.tableRow.input_type, this.$root.ddlInputTypes) && this.inArray(this.tableHeader.field, ['ddl_id'])
                        ||
                        !this.tableRow.unit_ddl_id && this.inArray(this.tableHeader.field, ['unit'])
//                        ||
//                        (this.tableRow.f_type === 'User' && this.inArray(this.tableHeader.field, ['ddl_id','ddl_add_option','ddl_auto_fill']))
                        ||
                        (this.inArray(this.tableHeader.field, ['header_unit_ddl','unit_display']) && !has_unit)
                        ||
                        (this.tableHeader.field === 'is_default_show_in_popup' && !this.$root.checkAvailable(this.$root.user, 'form_visibility'))
                        ||
                        (this.tableHeader.field === 'f_formula' && this.tableRow.input_type !== 'Formula')
                        ||
                        (this.tableHeader.field === 'is_topbot_in_popup' && this.tableRow.is_table_field_in_popup)
                        ||
                        (this.tableHeader.field === 'is_gantt_left_header' && this.tableRow.is_gantt_group)
                        ||
                        (this.tableHeader.field === 'verttb_row_height' && this.tableRow.verttb_he_auto)
                        ||
                        this.disabledCheckBox
                    ) {
                        obj.backgroundColor = '#EEE';
                    }
                    return obj;
                },
                disabledCheckBox() {
                    return !this.canCellEdit
                        ||
                        ( //autofill and autocomplete are not accessible for not DDL Input types
                            this.inArray(this.tableHeader.field, ['ddl_add_option','ddl_auto_fill'])
                            &&
                            !this.inArray(this.tableRow.input_type, this.$root.ddlInputTypes)
                        )
                        ||
                        ( //is_search_autocomplete_display is not accessible for 'User','Attachment'
                            this.tableHeader.field === 'is_search_autocomplete_display'
                            &&
                            this.inArray(this.tableRow.f_type, ['Attachment'])
                        )
                        ||
                        ( //'is_uniform_formula','f_formula' is not accessible for not 'Formula' input
                            this.inArray(this.tableHeader.field, ['is_uniform_formula','f_formula'])
                            &&
                            this.tableRow.input_type !== 'Formula'
                        )
                        ||
                        ( //'is_uniform_formula','f_formula' is not accessible for not 'Formula' input
                            this.inArray(this.tableHeader.field, ['is_gantt_left_header'])
                            &&
                            ( _.find(this.globalMeta._fields, {is_gantt_main_group: 1}) || _.find(this.globalMeta._fields, {is_gantt_parent_group: 1}) )
                        )
                        ||
                        ( //'is_gantt_main_group' is not accessible for not 'Formula' input
                            this.inArray(this.tableHeader.field, ['is_gantt_main_group'])
                            &&
                            !_.find(this.globalMeta._fields, {is_gantt_parent_group: 1})
                        )
                },
                getTitle() {
                    let title = '';
                    switch (this.tableHeader.field) {
                        case 'filter_type': title = 'Available column types: '+this.availRangeTypes.join(',');
                            break;
                        case 'ddl_style': title = 'Not recommended for long DDL';
                            break;
                    }
                    return title;
                },
                canCellEdit() {
                    let has_unit = this.tableRow.unit && this.tableRow.unit_ddl_id && this.globalMeta.unit_conv_is_active;// && this.globalMeta.__unit_convers && this.globalMeta.__unit_convers.length;
                    return (this.tableHeader.field !== 'name' || this.isAddRow)
                        && (this.globalMeta._is_owner || this.inArray(this.tableHeader.field, this.fieldsForUser))
                        && !this.inArray(this.tableHeader.field, this.$root.systemFields)
                        && (!this.inArray(this.tableHeader.field, ['header_unit_ddl','unit_display']) || has_unit)
                        && (this.tableHeader.field !== 'f_formula' || this.tableRow.input_type === 'Formula')
                        && (this.tableHeader.field !== 'is_default_show_in_popup' || this.$root.checkAvailable(this.$root.user, 'form_visibility'))
                        && (this.tableHeader.field !== 'is_topbot_in_popup' || !this.tableRow.is_table_field_in_popup)
                        && (this.tableHeader.field !== 'is_gantt_left_header' || !this.tableRow.is_gantt_group)
                        && (this.tableHeader.field !== 'verttb_row_height' || !this.tableRow.verttb_he_auto)
                },
            },
            methods: {
                header_unit_ddl_click(e) {
                    if (this.disabledCheckBox) {
                        this.show_radio_help(e);
                        e.preventDefault();
                    }
                },
                show_radio_help(e) {
                    if (!this.globalMeta.unit_conv_is_active && this.tableHeader.field === 'header_unit_ddl') {
                        this.radio_help = true;
                        this.radio_left = e.clientX;
                        this.radio_top = e.clientY;
                    }
                },
                inArray(item, array) {
                    return $.inArray(item, array) > -1;
                },
                isEditing() {
                    return this.editing && this.canCellEdit && !this.$root.global_no_edit;
                },
                showEdit() {
                    //edit cell
                    if (
                        window.screen.width >= 768
                        && this.selectedCell
                        && !this.selectedCell.is_selected(this.globalMeta, this.tableHeader, this.rowIndex)
                    ) {
                        this.selectedCell.single_select(this.tableHeader, this.rowIndex);
                    } else {
                        if (!this.canCellEdit || this.inArray(this.tableHeader.f_type, ['Boolean'])) {
                            return;
                        }
                        this.editing = true;
                        if (this.isEditing()) {
                            this.oldValue = this.tableRow[this.tableHeader.field];
                            this.$nextTick(function () {
                                if (this.$refs.inline_input) {
                                    if (this.$refs.inline_input && this.$refs.inline_input.nodeName === 'SELECT') {
                                        this.showHideDDLs(this.$root.selectParam);
                                        this.ddl_cached = false;
                                    } else {
                                        this.$refs.inline_input.focus();
                                    }
                                }
                            });
                        } else {
                            this.no_key_handler = true;
                            this.editing = false;
                        }
                    }
                },
                hideEdit() {
                    this.editing = false;
                },
                updateCheckedDDL(item) {
                    if (this.tableHeader.field === 'name') {
                        this.tableRow.id = item;
                    } else {
                        this.tableRow[this.tableHeader.field] = item;
                    }
                    this.updateValue();
                },
                updateValue() {
                    if (this.tableHeader.field === 'name' && !this.tableRow.name) {
                        let fld = _.find(this.globalMeta._fields, {id: Number(this.tableRow.id)});
                        this.tableRow.name = fld ? fld.name : this.tableRow.id;
                    }
                    if (this.tableHeader.field === 'unit_ddl_id') {
                        this.tableRow.unit = null;
                        this.tableRow.unit_display = null;
                    }

                    if (this.tableRow[this.tableHeader.field] !== this.oldValue) {
                        if (this.tableHeader.f_type === 'Boolean') {
                            this.tableRow[this.tableHeader.field] = this.tableRow[this.tableHeader.field] ? 1 : 0;
                        }

                        if (this.tableHeader.field === 'verttb_row_height') {
                            this.tableRow.verttb_row_height = Number(this.tableRow.verttb_row_height);
                            this.tableRow.verttb_row_height = Math.max(1, this.tableRow.verttb_row_height);
                            this.tableRow.verttb_row_height = Math.min(20, this.tableRow.verttb_row_height);//max_he_for_auto_rows: 20
                        }

                        this.tableRow._changed_field = this.tableHeader.field;
                        this.$emit('updated-cell', this.tableRow);
                        this.$nextTick(() => {
                            this.changedContSize();
                        });
                    }
                },
                updateRow() {
                    this.hideEdit();
                    this.updateValue();
                },
                updateRadio() {
                    this.tableRow[this.tableHeader.field] = this.tableRow[this.tableHeader.field] ? 0 : 1;
                    this.updateValue();
                },
                updateColor(header, clr, save) {
                    if (save) {
                        this.$root.color_palette.unshift(clr);
                        localStorage.setItem('color_palette', this.$root.color_palette.join(','));
                    }
                    this.tableRow[header.field] = clr;
                    this.updateValue();
                },
                showField() {
                    let res = '';
                    if (this.tableHeader.f_type === 'User') {
                        res = this.$root.smallUserStr(this.tableRow, this.tableHeader, this.tableRow[this.tableHeader.field]);
                    }
                    else
                    if (this.tableHeader.field === 'name') {
                        res = this.$root.uniqName( this.tableRow[this.tableHeader.field] );
                    }
                    else
                    if (this.tableHeader.field === 'filter_type') {
                        switch (this.tableRow[this.tableHeader.field]) {
                            case 'value': res = 'Values'; break;
                            case 'range': res = 'Range'; break;
                        }
                    }
                    else
                    if (this.tableHeader.field === 'ddl_style') {
                        switch (this.tableRow[this.tableHeader.field]) {
                            case 'ddl': res = 'DDL'; break;
                            case 'panel': res = 'Panel'; break;
                        }
                    }
                    else
                    if (this.tableHeader.field === 'verttb_cell_height') {
                        switch (this.tableRow[this.tableHeader.field]) {
                            case 1: res = 'Small'; break;
                            case 2: res = 'Medium'; break;
                            case 3: res = 'Large'; break;
                            case 5: res = 'Ex-Large'; break;
                        }
                    }
                    else
                    if (this.tableHeader.field === 'verttb_row_height') {
                        res = this.tableRow.verttb_he_auto ? '' : this.tableRow.verttb_row_height;
                    }
                    else
                    if (this.tableHeader.field === 'default_stats') {
                        res = this.tableRow[this.tableHeader.field]
                            ? String(this.tableRow[this.tableHeader.field]).toUpperCase()
                            : '';
                    }
                    else
                    if (this.tableHeader.field === 'unit_ddl_id' && this.tableRow.unit_ddl_id) {
                        let idx = _.findIndex(this.globalMeta._ddls, {id: Number(this.tableRow.unit_ddl_id)});
                        res = idx > -1 ? this.globalMeta._ddls[idx].name : '';
                    }
                    else
                    if (this.tableHeader.field === 'unit' || this.tableHeader.field === 'unit_display') {
                        res = this.$root.rcShow(this.tableRow, this.tableHeader.field);
                    }
                    else
                    if (this.tableHeader.field === 'ddl_id' && this.tableRow.ddl_id) {
                        let idx = _.findIndex(this.globalMeta._ddls, {id: Number(this.tableRow.ddl_id)});
                        res = idx > -1 ? this.globalMeta._ddls[idx].name : '';
                    }
                    else
                    if (this.inArray(this.tableHeader.field, ['is_lat_field','is_long_field','is_addr_field','is_info_header_field'])) {
                        let field = _.find(this.globalMeta._fields, {id: Number(this.tableRow[this.tableHeader.field])});
                        res = field ? field.name : '';
                    }
                    else {
                        res = this.tableRow[this.tableHeader.field];
                    }
                    return this.$root.strip_tags(res);
                },
                radioSettingsCheckedHandler(column, checkedFieldId) {
                    if (
                        this.tableRow.id !== checkedFieldId
                        &&
                        this.tableHeader.field === column
                        &&
                        this.tableRow[this.tableHeader.field] === 1
                    ) {
                        this.tableRow[this.tableHeader.field] = 0;
                        this.updateValue();
                    }
                },

                //select arrays
                nameFields() {
                    let fltr = this.behavior === 'settings_kanban_add' ? 'kanban_group' : 'active_links';
                    let fields = _.filter(this.globalMeta._fields, (hdr) => { return !hdr[fltr] });
                    return _.map(fields, (hdr) => {
                        return { val: hdr.id, show: this.$root.uniqName(hdr.name), }
                    });
                },
                globalMetaDdls() {
                    return _.map(this.globalMeta._ddls, (hdr) => {
                        return { val: hdr.id, show: hdr.name, }
                    });
                },
                availRowSumFormulas() {
                    return _.map(this.$root.availRowSumFormulas, (frm) => {
                        return { val: frm, show: String(frm).toUpperCase(), }
                    });
                },

                //Emits
                showDdlSettingsPopup() {
                    eventBus.$emit('show-ddl-settings-popup', this.globalMeta.db_name, this.tableRow[this.tableHeader.field]);
                },

                //KEYBOARD
                changeCol(is_next) {
                    if (this.editing) {
                        this.hideEdit();
                        this.updateValue();
                        if (this.$refs.inline_input && $(this.$refs.inline_input).hasClass('select2-hidden-accessible')) {
                            $(this.$refs.inline_input).select2('destroy');
                        }
                    }
                    this.$nextTick(() => {
                        this.selectedCell.next_col(this.globalMeta, is_next, this.isVertTable);
                    });
                },
            },
            mounted() {
                eventBus.$on('global-keydown', this.globalKeydownHandler);
                eventBus.$on('table-data-string-popup__update', this.tableDataStringUpdateHandler);
            },
            beforeDestroy() {
                eventBus.$off('global-keydown', this.globalKeydownHandler);
                eventBus.$off('table-data-string-popup__update', this.tableDataStringUpdateHandler);
            }
        }
    </script>

    <style scoped>
        @import "CustomCell.scss";
    </style>