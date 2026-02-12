<template>
    <div
        ref="formula_button"
        class="formula-helper"
        :style="wrapStyle"
        @click.self="hide()"
        @contextmenu.stop=""
    >
        <div :class="[show_big_popup ? 'popup' : '']" :style="getPopupStyle()">

            <div v-if="selectionStart > -1" class="formula-examples" :style="{width: edit_example ? '100%' : null}">
                <label>Examples:</label>
                <div v-if="! edit_example"
                     :style="{color: 'green', cursor: $root.user.is_admin ? 'pointer' : null}"
                     @click="$root.user.is_admin ? edit_example = true : null"
                     v-html="getExamples(false)"
                ></div>
                <textarea v-if="edit_example"
                          class="form-control"
                          rows="5"
                          :value="getExamples(true)"
                          @keyup.stop=""
                          @keydown.stop=""
                          @keypress.stop=""
                          @change="(e) => updateExample(e)"
                ></textarea>
            </div>

            <div class="flex flex--col">
                <div v-if="show_big_popup" class="popup-header">
                    <div class="drag-bkg" draggable="true" @dragstart="dragPopSt()" @drag="dragPopup()"></div>
                    <div class="flex">
                        <div class="flex__elem-remain txt--left">Field Editing</div>
                        <div class="" style="padding-bottom: 4px;">
                            <span class="glyphicon glyphicon-remove pull-right header-btn" @click="hide()"></span>
                        </div>
                    </div>
                </div>

                <div :class="[show_big_popup ? 'popup-main' : '']" class="popup-content">
                    <div v-if="formulaElemets !== null"
                         contenteditable="true"
                         ref="inline_input"
                         class="form-control content_input"
                         :style="{minHeight: show_big_popup ? '116px' : '58px', maxHeight: show_big_popup ? '246px' : '123px'}"
                         @blur="contentChanged"
                         @keydown.stop="contentPress"
                         @click="contentClick"
                    >
                        <template v-for="el in formulaElemets">

                            <!--FORMULA PART-->
                            <span v-if="el.type == 'f'">
                                <template v-for="inn in el.inners">
                                    <select v-if="!clear_text && inn.type == 'c'"
                                            class="form-control"
                                            style="padding: 0px;height: auto;width: 52px;display: inline-block;"
                                            :style="{width: (inn.data.length*7.7+25)+'px'}"
                                            @change="(sel) => selInFormulaChange(el, inn, sel)"
                                    >
                                        <option v-for="fld in el.ref_fields"
                                                :selected="isSelData(inn.data, fld)"
                                                :value="prepName(fld.formula_symbol || fld.name)"
                                        >{{ fld.name + (fld.formula_symbol ? '('+fld.formula_symbol+')' : '') }}</option>
                                    </select>

                                    <span v-else-if="clear_text && inn.type == 'c'"
                                          style="color: #00f;cursor: pointer;"
                                          @click.stop="fieldClick(inn)"
                                          v-html="'{'+inn.data+'}'"></span>

                                    <span v-else-if="inn.type == 's'"
                                          style="color: #f0f;font-size: 120%;font-weight: bold;"
                                          v-html="inn.data"></span>

                                    <span v-else-if="inn.type == 'fp'"
                                          style="color: #000; font-weight: bold; cursor: pointer;"
                                          @click.stop="formulaClick(el)"
                                          v-html="inn.data"></span>

                                    <span v-else=""
                                          style="color: #f00; cursor: pointer;"
                                          @click.stop="formulaClick(el)"
                                          v-html="inn.data"></span>
                                </template>
                            </span>

                            <!--FIELD AS TEXT-->
                            <span v-else-if="clear_text && el.type == 'c'"
                                  style="color: #00f;cursor: pointer;"
                                  @click.stop="fieldClick(el)"
                                  v-html="'{'+el.data+'}'"></span>

                            <!--FIELD AS SELECT-->
                            <select v-else-if="!clear_text && el.type == 'c'"
                                    class="form-control"
                                    style="padding: 0px;height: auto;width: 52px;display: inline-block;"
                                    :style="{width: (el.data.length*7.7+25)+'px'}"
                                    @change="(sel) => selFldChange(el, sel)"
                            >
                                <option v-for="fld in tableMeta._fields"
                                        :selected="isSelData(el.data, fld)"
                                        :value="prepName(fld.formula_symbol || fld.name)"
                                >{{ fld.name + (fld.formula_symbol ? '('+fld.formula_symbol+')' : '') }}</option>
                            </select>

                            <!--MATH SIGN-->
                            <span v-else-if="el.type == 's'"
                                  style="color: #f0f;font-size: 120%;font-weight: bold;"
                                  v-html="el.data"></span>

                            <!--JUST TEXT-->
                            <span v-else="" v-html="el.data"></span>
                        </template>
                    </div>
                    <!--<textarea-autosize-->
                            <!--v-model="formulaAllString"-->
                            <!--ref="inline_input"-->
                            <!--class="form-control"-->
                            <!--:rows="1"-->
                            <!--:style="{minHeight: show_big_popup ? '116px' : '58px', maxHeight: show_big_popup ? '246px' : '123px'}"-->
                    <!--&gt;</textarea-autosize>-->

                    <div class="flex flex--col">
                        <div class="formula__btns">
                            <button class="btn btn-default btn-sm"
                                    :class="add_type === 'field' ? 'blue-gradient' : ''"
                                    :style="add_type === 'field' ? $root.themeButtonStyle : null"
                                    @click="add_type = 'field';curSelectedFormula='';filter_str = '';formulaFunc=null;"
                            >Field</button>
                            <button v-if="!noFunction"
                                    class="btn btn-default btn-sm"
                                    :class="add_type === 'function' ? 'blue-gradient' : ''"
                                    :style="add_type === 'function' ? $root.themeButtonStyle : null"
                                    @click="add_type = 'function';curField='';filter_str = '';formulaFunc=null;"
                            >Function</button>

                            <div v-if="canUniform"
                                 class="flex"
                                 style="position: absolute; top: 7px; right: 80px;"
                                 :style="{right: show_big_popup ? '80px' : '110px'}"
                            >
                                <span v-if="show_big_popup" class="mr3">Uniform</span>
                                <label title="Uniform" class="switch_t no-margin">
                                    <input type="checkbox"
                                           v-model="tableHeader.is_uniform_formula"
                                           @change="updateHeaderSettings()">
                                    <span class="toggler round"></span>
                                </label>
                            </div>
                            <button class="clear_txt btn btn-default btn-sm blue-gradient"
                                    :style="t_style"
                                    @click="clear_text = !clear_text"
                            >{{ clear_text ? 'B' : 'T' }}</button>
                            <i v-show="!show_big_popup" class="glyphicon glyphicon-resize-full" @click="showPopupBig()"></i>
                            <info-sign-link :app_sett_key="'help_link_formula_helper'" :txt="'for Formula Helper'"></info-sign-link>
                        </div>

                        <div v-show="add_type === 'field'" class="formula__tab">
                            <div class="field__selector" :style="{height: show_big_popup ? '120px' : '75px'}">
                                <div v-for="fld in tableMeta._fields"
                                     v-if="filterFld(fld)"
                                     class="field__item txt--left"
                                     :ref="'scrl_'+prepName(fld.name)"
                                     :style="{color: curField === prepName(fld.name) ? '#f00' : null}"
                                     @click="addField(fld.formula_symbol || fld.name)"
                                >{{ fld.name + (fld.formula_symbol ? '('+fld.formula_symbol+')' : '') }}</div>
                            </div>
                        </div>

                        <div v-show="add_type === 'function'" class="formula__tab">
                            <div class="flex">
                                <div class="field__selector flex__elem-remain" :style="{height: show_big_popup ? '120px' : '75px'}">
                                    <div v-for="(display, method) in available_math_methods"
                                         v-if="filterFunc(method)"
                                         class="field__item txt--left"
                                         :ref="'scrl_'+method"
                                         :style="{color: formulaFunc === method ? '#f00' : null}"
                                         @click="formulaFunc = method;changedFunction();"
                                    >{{ display }}</div>
                                    <div v-for="(display, method) in avail_reference_methods"
                                         v-if="filterFunc(method)"
                                         class="field__item txt--left"
                                         :ref="'scrl_'+method"
                                         :style="{color: formulaFunc === method ? '#f00' : null}"
                                         @click="formulaFunc = method;changedFunction();"
                                    >{{ display }}</div>
                                </div>
                                <button class="btn btn-sm btn-success blue-gradient"
                                        :disabled="!availAdd"
                                        @click="addToFormula()"
                                        :style="$root.themeButtonStyle"
                                >{{ curSelectedFormula ? 'Update' : 'Insert' }}</button>
                            </div>

                            <div v-show="isRefFunction">
                                <label>SELECT A REF. COND.(RC):</label>
                                <select style="margin-bottom: 2px;"
                                        class="form-control"
                                        @change="curSelectedFormula ? addToFormula(true) : null"
                                        v-model="formulaRefCond"
                                >
                                    <!--<option :value="{name: '$this', _ref_table: tableMeta}">$THIS</option>-->
                                    <option v-for="ref_cond in tableMeta._ref_conditions" :value="ref_cond">
                                        {{ ref_cond.name }}
                                    </option>
                                </select>

                                <label>SELECT A FIELD:</label>
                                <select style="margin-bottom: 2px;"
                                        class="form-control"
                                        @change="addToFormula(!!curSelectedFormula)"
                                        v-model="formulaField"
                                >
                                    <template v-if="formulaRefCond && formulaRefCond._ref_table">
                                        <option
                                            v-for="fld in formulaRefCond._ref_table._fields"
                                            :value="'{' + prepName(fld.formula_symbol || fld.name) + '}'"
                                        >
                                            {{ fld.name + (fld.formula_symbol ? '('+fld.formula_symbol+')' : '') }}
                                        </option>
                                    </template>
                                </select>
                            </div>
                            <div v-show="!isRefFunction">
                                <template v-for="param in formulaParams">
                                    <div class="flex flex--space">
                                        <label>{{ param.name }}:</label>
                                        <div v-if="param.type == 'fld'">
                                            <span class="indeterm_check__wrap">
                                                <span class="indeterm_check" @click="useColumnNameChecked(param)">
                                                    <i v-if="param.use_column_name" class="glyphicon glyphicon-ok group__icon"></i>
                                                </span>
                                            </span>
                                            <label>Use column name</label>
                                        </div>
                                    </div>
                                    <div class="flex flex--center" style="max-width: 350px;">
                                        <select v-if="param.type.indexOf('fld') > -1"
                                                class="form-control"
                                                v-model="param.fld_name"
                                                @change="fldValueOption(param)"
                                        >
                                            <option v-for="fld in getTableFields(param.type)" :value="prepName(fld.name)">
                                                {{ fld.name + (fld.formula_symbol ? '('+fld.formula_symbol+')' : '') }}
                                            </option>
                                        </select>

                                        <label style="margin: 0" v-if="param.type.indexOf(',') > -1"> OR </label>

                                        <input v-if="param.type.indexOf('input') > -1"
                                               class="form-control"
                                               v-model="param.input_val"
                                               @change="inputValueOption(param)"
                                        />

                                        <div v-if="param.type.indexOf('inp_with_formula') > -1" class="full-width" style="position: relative">
                                            <input class="form-control"
                                                   v-model="param.input_val"
                                                   @focus="param._helper = true"
                                                   @change="inputValueOption(param)"
                                            />
                                            <formula-helper
                                                v-if="param._helper"
                                                :user="$root.user"
                                                :table-meta="tableMeta"
                                                :table-row="param"
                                                :header-key="'input_val'"
                                                :can-edit="true"
                                                :no_prevent="true"
                                                :pop_width="'100%'"
                                                @close-formula="param._helper = false"
                                                @set-formula="param._helper = false;inputValueOption(param);"
                                            ></formula-helper>
                                        </div>

                                        <select v-if="param.type.indexOf('select') > -1"
                                                class="form-control"
                                                v-model="param.input_val"
                                                @change="inputValueOption(param)"
                                        >
                                            <option v-for="opt in getParamOpts(param)" :value="opt">{{ opt }}</option>
                                        </select>

                                        <div v-if="param.type.indexOf('multi-sel') > -1" class="msel-wrapper">
                                            <tablda-select-simple
                                                    :options="mselFields"
                                                    :table-row="param"
                                                    :hdr_field="'input_val'"
                                                    :init_no_open="true"
                                                    :fld_input_type="'M-Select'"
                                                    @selected-item="(item) => { mselectChanged(item, param) }"
                                            ></tablda-select-simple>
                                        </div>
                                    </div>
                                </template>
                            </div>

                            <div v-if="availInfoPanel" class="info-div">
                                <textarea class="form-control"
                                          rows="2"
                                          :disabled="!user.is_admin"
                                          @change="saveHelperTooltip()"
                                          v-model="helperFuncInfo.val"
                                ></textarea>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</template>

<script>
    import {SpecialFuncs} from '../../../classes/SpecialFuncs';

    import {eventBus} from '../../../app';

    import PopupAnimationMixin from './../../_Mixins/PopupAnimationMixin';
    import MixinSmartPosition from '../../_Mixins/MixinSmartPosition';

    import SelectWithFolderStructure from "./SelectWithFolderStructure";
    import TextareaAutosize from "./TextareaAutosize";
    import CellTableDataContent from "./CellTableContent";
    import InfoSignLink from "../../CustomTable/Specials/InfoSignLink";
    import TabldaSelectSimple from "../Selects/TabldaSelectSimple";

    export default {
        name: "FormulaHelper",
        components: {
            TabldaSelectSimple,
            InfoSignLink,
            CellTableDataContent,
            TextareaAutosize,
            SelectWithFolderStructure,
        },
        mixins: [
            PopupAnimationMixin,
            MixinSmartPosition,
        ],
        data: function () {
            return {
                edit_example: false,
                clear_text: true,
                available_math_methods: {
                    'sqrt': 'Sqrt($Number)',
                    'pow': 'Pow($Number, $Pow)',
                    'yrday': 'YrDay($Date)',
                    'moday': 'MoDay($Date)',
                    'wkday': 'WkDay($Date, $ARG)',
                    'week': 'Week($Date)',
                    'month': 'Month($Date, $ARG)',
                    'year': 'Year($Date)',
                    'date': 'Date($DateTime, $Format)',
                    'time': 'Time($DateTime, $Format)',
                    'today': 'Today($Timezone, $Format)',
                    'tomorrow': 'Tomorrow($Timezone, $Format)',
                    'yesterday': 'Yesterday($Timezone, $Format)',
                    'timechange': 'TimeChange($Timestart, $Change, $Amount)',
                    'duration': 'Duration($Timefrom, $Timeto)',
                    'andx': 'ANDX($Condition1, $Condition2, ...)',
                    'orx': 'ORX($Condition1, $Condition2, ...)',
                    'if': 'If($Condition, $Do_if_true, $Do_if_false)',
                    'switch': 'Switch($Condition, [$Case1, $Case2, ...], [$Do1, $Do2, ...], $Default)',
                    'isempty': 'Isempty($Field1, $Value1, $Value2)',
                    'asum': 'ASUM($EXP1, $EXP2, ...)',
                    'amin': 'AMIN($EXP1, $EXP2, ...)',
                    'amax': 'AMAX($EXP1, $EXP2, ...)',
                    'amean': 'AMEAN($EXP1, $EXP2, ...)',
                    'aavg': 'AAVG($EXP1, $EXP2, ...)',
                    'avar': 'AVAR($EXP1, $EXP2, ...)',
                    'astd': 'ASTD($EXP1, $EXP2, ...)',
                    'ddloption': 'DDLOption ($Field, $Type)',
                    'ai_create': 'AI_CREATE ($Question)',
                    'ai_extract': 'AI_EXTRACT ($PromptField, $DocField)',
                },
                //#app_avail_formulas
                avail_reference_methods: {
                    'count': 'COUNT ($RC, $Field)',
                    'countunique': 'COUNTUNIQUE ($RC, $Field)',
                    'sum': 'SUM($RC, $Field)',
                    'min': 'MIN($RC, $Field)',
                    'max': 'MAX($RC, $Field)',
                    'mean': 'MEAN($RC, $Field)',
                    'avg': 'AVG($RC, $Field)',
                    'var': 'VAR($RC, $Field)',
                    'std': 'STD($RC, $Field)',
                },

                curField: '',
                curSelectedFormula: '',
                formulaElemets: [],
                formulaAllString: this.tableRow[this.headerKey] || '',
                formulaFunc: null,
                formulaRefCond: null,
                formulaField: null,
                formulaParams: [],
                selectionStart: -1,

                oldFuncValue: null,
                add_type: 'field',
                filter_str: '',
                filter_elem_val: '',

                show_big_popup: false,
                prevent_one: !this.no_prevent,

                //PopupAnimationMixin
                getPopupHeight: 'auto',
                getPopupWidth: this.pop_width || 500,
                anim_opac: 1,
                idx: 0,
            }
        },
        props:{
            user: Object,
            tableMeta: Object,
            tableRow: Object,
            tableHeader: Object,
            headerKey: String,
            canEdit: Boolean,
            fixed_pos: Boolean,
            foreign_element: HTMLElement,
            noFunction: Boolean,
            pop_width: String,
            no_prevent: Boolean,
            no_uniform: Boolean,
            is_td_single: Object,
        },
        watch: {
            tableRow(val) {
                this.formulaAllString = this.tableRow[this.headerKey] || '';
                this.parseFormulaString();
            },
        },
        computed: {
            wrapStyle() {
                let style = {
                    ...this.ItemsListStyle(),
                    ...this.smartLeftRight(),
                };
                style.position = (this.fixed_pos || this.show_big_popup ? 'fixed' : 'absolute');
                return style;
            },
            funcString() {
                return String(this.formulaFunc).replace(newRegexp('[^\\p{L}]'), '').toLowerCase();
            },
            showFuncString() {
                return this.available_math_methods[this.funcString]
                    || this.avail_reference_methods[this.funcString];
            },
            helperFuncInfo() {
                return this.$root.settingsMeta.app_settings['formula_helper_function_'+this.funcString] || {};
            },
            isRefFunction() {
                return this.avail_reference_methods[this.funcString];
            },
            availAdd() {
                let can = true;
                _.each(this.formulaParams, (param) => {
                    can = can && param.val !== undefined;
                });
                return this.isRefFunction || can;
            },
            availInfoPanel() {
                return this.formulaFunc // selected func
                    && ( // user is admin or present info
                        this.user.is_admin
                        ||
                        this.helperFuncInfo.val
                    );
            },
            t_style() {
                let tsy = {right: !this.show_big_popup ? '70px' : '40px'};
                return {
                    ...this.$root.themeButtonStyle,
                    ...tsy,
                };
            },
            mselFields() {
                return _.map(this.tableMeta._fields, (fld) => {
                    return {
                        show: fld.name + (fld.formula_symbol ? '('+fld.formula_symbol+')' : ''),
                        val: '{' + this.prepName(fld.name) + '}',
                    };
                });
            },
            canUniform() {
                return this.tableHeader && this.tableHeader.id && this.tableHeader.table_id
                    && this.tableMeta && this.tableMeta._is_owner
                    && !this.openedFromSettings
                    && !this.no_uniform;
            },
            openedFromSettings() {
                return this.headerKey === 'f_formula' || this.is_td_single;
            },
        },
        methods: {
            getExamples(no_html) {
                let obj = this.$root.settingsMeta.formula_examples['formula_example_' + (this.formulaFunc || '')]
                    || this.$root.settingsMeta.formula_examples['formula_example_'];

                let result = obj ? obj.notes : '';
                return no_html ? br2nl(result) : result;
            },
            updateExample(e) {
                let obj = this.$root.settingsMeta.formula_examples['formula_example_' + (this.formulaFunc || '')]
                    || this.$root.settingsMeta.formula_examples['formula_example_'];

                if (obj) {
                    obj.notes = this.$root.strip_danger_tags(nl2br(e.target.value || ''));

                    axios.put('/ajax/app/formula-helpers', {
                        formula: obj.formula,
                        notes: obj.notes
                    }).catch(errors => {
                        Swal('Info', getErrors(errors));
                    });

                    this.edit_example = false;
                }
            },
            jsonPrepared(json, reverse) {
                if (reverse) {
                    return String(json).replace(/([\[\{])/gi, '"$1').replace(/([\]\}])/gi, '$1"');
                } else {
                    return String(json).replace(/"/gi, '');
                }
            },
            prepName(name) {
                return String(name || '').replace(newRegexp('[^\\p{L}\\d]'),'');
            },
            isSelData(data, fld) {
                return this.prepName(data) == this.prepName(fld.formula_symbol)
                    || this.prepName(data) == this.prepName(fld.name);
            },
            showPopupBig() {
                this.show_big_popup = true;
                this.getPopupWidth = 700;
                this.transition_ms = 0;
                this.anim_opac = 0;
                setTimeout(() => {
                    this.transition_ms = 800;
                    this.runAnimation();
                }, 1);
            },

            //Filters
            filterFld(fld) {
                return !this.$root.in_array(fld.field, this.$root.systemFieldsNoId)
                    && (
                        !this.filter_str
                        || this.prepName(fld.formula_symbol).toLowerCase().indexOf(this.filter_str) === 0
                        || this.prepName(fld.name).toLowerCase().indexOf(this.filter_str) === 0
                    );
            },
            filterFunc(func) {
                return !this.filter_str
                    || String(func).toLowerCase().indexOf(this.filter_str) === 0;
            },

            //Preparers
            useColumnNameChecked(param) {
                param.use_column_name = !param.use_column_name;
                this.fldValueOption(param);
            },
            fldValueOption(param, externalSign) {
                _.each(this.getTableFields(), (fld) => {
                    if (
                        param.fld_name == this.prepName(fld.formula_symbol)
                        || param.fld_name == this.prepName(fld.name)
                    ) {
                        param.fld_name = this.prepName(fld.name);
                        param.fld_symb = this.prepName(fld.formula_symbol);
                    }
                });

                let sign = '';
                if (externalSign === undefined) {
                    sign = (param.fld_symb && !param.use_column_name ? param.fld_symb : param.fld_name);
                } else {
                    sign = externalSign;
                }

                if (['flde_attach','flde_text','select','input'].indexOf(param.type) > -1
                    || param.type.indexOf('inp_with_formula') > -1
                ) {
                    let needWrap = ['flde_attach','flde_text','input','"inp_with_formula"'].indexOf(param.type) > -1;
                    if (needWrap && sign[0] != '"' && sign[0] != '{' && sign[0] != '[') {
                        sign = '"' + sign + '"';
                    }
                    param.val = sign;
                    param.input_val = sign;
                } else {
                    param.val = '{'+sign+'}';
                    param.input_val = '{'+sign+'}';
                }
            },
            inputValueOption(param) {
                param.fld_name = null;
                param.val = String(param.input_val).match(/["\{\(]/gi)
                    ? param.input_val
                    : '"'+(param.input_val)+'"';
            },
            mselectChanged(item, param) {
                let arrays = param.input_val ? JSON.parse(param.input_val) : [];
                if (arrays.indexOf(item) > -1) {
                    arrays.splice( arrays.indexOf(item), 1 );
                } else {
                    arrays.push(item);
                }
                param.input_val = JSON.stringify(arrays);
                param.fld_name = null;
                param.val = this.jsonPrepared(param.input_val);
            },
            getParamOpts(param) {
                if (param.related) {
                    let related = _.find(this.formulaParams, {name: param.related});
                    return param.sel_options[related.input_val];
                } else {
                    return param.sel_options;
                }
            },

            //Formula Helper Functions
            getParam(name, val, type, additionals) {
                additionals = additionals || {};
                return {
                    name: name,
                    fld_name: null,
                    fld_symb: null,
                    input_val: val || '',
                    val: additionals.force_val
                        ? '"'+val+'"'
                        : (val ? '"'+val+'"' : undefined),
                    type: type || 'fld', //avail: 'fld','input','inp_with_formula','"inp_with_formula"','select','multi-sel'
                    use_column_name: false,
                    sel_options: additionals.options || [],
                    related: additionals.related || '',
                    _helper: false,
                };
            },
            changedFunction() {
                switch (this.funcString) {
                    case 'sqrt':
                        this.formulaParams = [ this.getParam('Number') ];
                        break;
                    case 'pow':
                        this.formulaParams = [
                            this.getParam('Number'),
                            this.getParam('Pow',2,'input')
                        ];
                        break;
                    case 'yrday':
                    case 'moday':
                    case 'week':
                    case 'year':
                        this.formulaParams = [ this.getParam('Date') ];
                        break;
                    case 'month':
                    case 'wkday':
                        this.formulaParams = [
                            this.getParam('Date'),
                            this.getParam('Argument','Number','select',{options: ['Number','Name']})
                        ];
                        break;
                    case 'today':
                    case 'tomorrow':
                    case 'yesterday':
                        this.formulaParams = [
                            this.getParam('Timezone',this.user.timezone,'input'),
                            this.getParam('Format','Y-m-d','input')
                        ];
                        break;
                    case 'date':
                        this.formulaParams = [
                            this.getParam('DateTime'),
                            this.getParam('Format','Y-m-d','input')
                        ];
                        break;
                    case 'time':
                        this.formulaParams = [
                            this.getParam('DateTime'),
                            this.getParam('Format','H:i:s','input')
                        ];
                        break;
                    case 'timechange':
                        this.formulaParams = [
                            this.getParam('Timestart', '', 'input'),
                            this.getParam('Change','Add','select',{options: ['Add','Substract']}),
                            this.getParam('Amount', '', 'input')
                        ];
                        break;
                    case 'duration':
                        this.formulaParams = [
                            this.getParam('Timefrom'),
                            this.getParam('Timeto')
                        ];
                        break;
                    case 'ddloption':
                        this.formulaParams = [
                            this.getParam('Field'),
                            this.getParam('Change','Value','select',{options: ['Value','Show']})
                        ];
                        break;
                    case 'ai_create':
                        this.formulaParams = [
                            this.getParam('Question', '', '"inp_with_formula"')
                        ];
                        break;
                    case 'ai_extract':
                        this.formulaParams = [
                            this.getParam('Prompt Field'),
                            this.getParam('Doc Field', '', 'flde_attach')
                        ];
                        break;
                    case 'isempty':
                        this.formulaParams = [
                            this.getParam('Field'),
                            this.getParam('Value1','','input', {force_val: true}),
                            this.getParam('Value2','','input', {force_val: true})
                        ];
                        break;
                    case 'if':
                        this.formulaParams = [
                            this.getParam('Condition','','input'),
                            this.getParam('If True','','input'),
                            this.getParam('If False','','input')
                        ];
                        break;
                    case 'switch':
                        this.formulaParams = [
                            this.getParam('Condition','','inp_with_formula'),
                            this.getParam('Cases','["first"]','input'),
                            this.getParam('Methods','[Today()]','input'),
                            this.getParam('Default','','input')
                        ];
                        break;
                    case 'andx':
                    case 'orx':
                        this.formulaParams = [ this.getParam('Conditions','','multi-sel') ];
                        break;
                    case 'asum':
                    case 'amin':
                    case 'amax':
                    case 'amean':
                    case 'aavg':
                    case 'astd':
                    case 'avar':
                        this.formulaParams = [ this.getParam('Fields','','multi-sel') ];
                        break;
                    //REF FUNCTIONS STAB
                    case 'countunique':
                    case 'count':
                    case 'sum':
                    case 'min':
                    case 'max':
                    case 'mean':
                    case 'avg':
                    case 'std':
                    case 'var':
                        this.formulaParams = [ this.getParam('RC'), this.getParam('Field') ];
                        break;
                    default: this.formulaParams = [];
                }
            },
            getTableFields(paramType) {
                if (!this.formulaFunc) {
                    return [];
                }
                let filter = null;
                if (paramType === 'flde_attach') {
                    filter = ['Attachment'];
                }
                if (paramType === 'flde_text') {
                    filter = ['String', 'Text', 'Long Text'];
                }
                return filter
                    ? _.filter(this.tableMeta._fields, (fld) => { return filter.indexOf(fld.f_type) > -1; })
                    : this.tableMeta._fields;
            },
            addField(fld) {
                fld = this.prepName(fld);
                if (this.filter_str) {
                    let replacer = this.filter_elem_val.replace(new RegExp(this.filter_str+'$', 'i'), '{' + fld + '}');
                    this.formulaAllString = this.formulaAllString.replace(this.filter_elem_val, replacer);
                } else
                if (this.curField) {
                    let nocomma = this.formulaAllString.replace(/{[^}]+}/gi, (m) => {
                        return m.replace(/,/gi, '');
                    });
                    let start = nocomma.indexOf('{' + this.prepName(this.curField) + '}') + 1;
                    let end = this.formulaAllString.substring(start).indexOf('}') + start;
                    this.formulaAllString = this.formulaAllString.substring(0, start)
                        + fld + this.formulaAllString.substring(end);
                } else
                if (this.selectionStart > -1) {
                    this.formulaAllString = this.formulaAllString.substring(0, this.selectionStart)
                        + '{' + fld + '}' + this.formulaAllString.substring(this.selectionStart);
                } else {
                    this.formulaAllString += '{' + fld + '}';
                }
                this.parseFormulaString();
                this.curField = '';
                this.filter_str = '';
                this.selectionStart = -1;
            },
            addToFormula(save_selection) {
                if (this.formulaFunc)
                {
                    let param_key = this.formulaFunc === 'switch' ? 'input_val' : 'val';
                    let formula_elem = this.showFuncString.replace(/[\s]*\(.*\)/gi, '');
                    if (this.isRefFunction) {
                        let arr = [];
                        arr.push( this.formulaRefCond ? '"'+this.formulaRefCond.name+'"' : '' );
                        arr.push( this.formulaField || '' );
                        formula_elem += '(' + arr.join(',') + ')';
                    } else {
                        let arr = [];
                        _.each(this.formulaParams, (param) => {
                            arr.push( param[param_key] );
                        });
                        formula_elem += '(' + arr.join(',') + ')';
                    }

                    if (this.curSelectedFormula) {
                        formula_elem = formula_elem.replaceAll('{{', '{').replaceAll('}}', '}');
                        this.formulaAllString = this.formulaAllString.replace(this.curSelectedFormula, formula_elem);
                        this.curSelectedFormula = save_selection ? formula_elem : '';
                        this.formulaFunc = save_selection ? this.formulaFunc : null;
                    } else
                    if (this.filter_str) {
                        let replacer = this.filter_elem_val.replace(new RegExp(this.filter_str+'$', 'i'), formula_elem);
                        this.formulaAllString = this.formulaAllString.replace(this.filter_elem_val, replacer);
                    } else {
                        this.formulaAllString += formula_elem;
                    }
                    this.parseFormulaString();
                    this.filter_str = '';
                }
            },
            hide() {
                setTimeout(() => {//timeout - fix: when formula is closed by clicking in another place -> last change will not be saved
                    if (this.tableRow[this.headerKey] === this.formulaAllString) {
                        this.$emit('close-formula');
                        return;
                    }

                    this.contentChanged();
                    this.tableRow[this.headerKey] = this.formulaAllString;
                    if (
                        !this.openedFromSettings
                        && !this.no_uniform
                        && this.tableMeta._is_owner
                        && this.tableHeader
                        && this.tableHeader.is_uniform_formula
                        && this.tableHeader.f_formula != this.formulaAllString
                    ) {
                        this.tableHeader.f_formula = this.formulaAllString;
                        this.tableHeader._changed_field = 'f_formula';
                        this.$root.updateSettingsColumn(this.tableMeta, this.tableHeader);
                        this.$emit('close-formula');
                    } else {
                        this.$emit('set-formula');
                    }
                }, 1);
            },

            //Show/Hide Helper Popup
            hideFormulaHelper(e) {
                if (this.prevent_one) {
                    this.prevent_one = false;
                    return;
                }
                let container = $(this.$refs.formula_button);
                if (container.has(e.target).length === 0) {
                    if (this.user.is_admin && this.formulaFunc) {
                        this.saveHelperTooltip();
                    }
                    this.hide();
                }
            },

            //helper tooltip
            saveHelperTooltip() {
                if (this.user.is_admin && this.helperFuncInfo.val) {
                    $.LoadingOverlay('show');
                    axios.put('/ajax/app/settings', {
                        app_key: 'formula_helper_function_'+this.funcString,
                        app_val: this.helperFuncInfo.val
                    }).then(({ data }) => {
                        if (!this.$root.settingsMeta.app_settings['formula_helper_function_'+this.funcString]) {
                            this.$set(this.$root.settingsMeta.app_settings, 'formula_helper_function_'+this.funcString, {
                                key: 'formula_helper_function_'+this.funcString,
                                val: this.helperFuncInfo.val,
                            });
                        }
                    }).catch(errors => {
                        Swal('Info', getErrors(errors));
                    }).finally(() => {
                        $.LoadingOverlay('hide');
                    });
                }
            },

            //Input Parser
            getSubelems(string) {
                let resu = [], quote = String(string).match(/"[^"]+"/gi);
                if (quote) {
                    _.each(quote, (q) => { string = String(string).replace(q, '<#>'); });
                    string = String(string).replace(/([\+\*\/-])/gi, '</><s>$1</>');
                    string = string.replace(/<#>/gi, () => { return quote.shift(); });
                } else {
                    string = String(string).replace(/([\+\*\/-])/gi, '</><s>$1</>');
                }
                string = String(string).replace(/(\{[^\}]*\})/gi, '</><c>$1</>');
                let subelems = String(string).replace(newRegexp('([\\p{L}]+\\()|(,)|(\\[)|(\\])|(\\))'), '</><fp>$1$2$3$4$5</>');
                subelems = String(subelems).replace(/(<\/>)+/gi, '</>');
                _.each(subelems.split('</>'), (sub) => {
                    if (String(sub).match(/^<c>/)) {
                        resu.push({
                            type: 'c', // Column
                            data: String(sub).replace(/^<c>/, '').replace(/[\{\}]/gi, '')
                        });
                    } else
                    if (String(sub).match(/^<s>/)) {
                        resu.push({
                            type: 's', // Sign [+-*/]
                            data: String(sub).replace(/^<s>/, '')
                        });
                    } else
                    if (String(sub).match(/^<fp>/)) {
                        resu.push({
                            type: 'fp', // Formula part: sum(,,[])
                            data: String(sub).replace(/^<fp>/, '')
                        });
                    } else {
                        resu.push({
                            type: 't', // Text
                            data: String(sub)
                        });
                    }
                });
                return resu;
            },
            parseFormulaString() {
                let html = String(this.formulaAllString).replace(newRegexp('([\\p{L}]+\\([^\\)]*\\))'), '</><f>$1</>');
                let elements = [];
                _.each(html.split('</>'), (elem) => {
                    if (String(elem).match(/^<f>/)) {
                        let newel = {
                            type: 'f', // Formula
                            data: String(elem).replace(/^<f>/, '')
                        };
                        newel.inners = this.getSubelems(newel.data);
                        newel.ref_fields = this.tableMeta._fields;
                        let formula_func = String(newel.data).replace(/[\s]*\(.*\)/gi, '').toLowerCase();
                        if (this.avail_reference_methods[formula_func]) {
                            let refname = String(newel.data).match(/"([^"]*)"/, '');
                            let reftb = refname && refname[1] ? _.find(this.tableMeta._ref_conditions, {name: refname[1]}) : null;
                            newel.ref_fields = reftb && reftb._ref_table ? reftb._ref_table._fields : newel.ref_fields;
                        }

                        elements.push(newel);
                    } else {
                        elements = elements.concat(this.getSubelems(elem));
                    }
                });
                this.formulaElemets = null;
                this.$nextTick(() => {
                    this.formulaElemets = _.filter(elements, (elem) => { return elem.data; });
                });
            },
            fieldClick(eve) {//Field is clicked from the "Formula String"
                this.add_type = 'field';
                this.curField = this.prepName(eve.data);

                this.$nextTick(() => {
                    //scroll to selected Field
                    let refs = this.$refs['scrl_'+this.prepName(eve.data)];
                    let rrow = _.first(refs);
                    if (!!window.chrome && rrow) {
                        rrow.scrollIntoView({block: 'center'});
                    }
                    this.saveSelection();
                });
            },
            formulaClick(eve) {//Formula is clicked from the "Formula String"
                this.curField = '';
                this.add_type = 'function';
                this.formulaFunc = String(eve.data).replace(/[\s]*\(.*\)/gi, '').toLowerCase();
                this.changedFunction();
                let argums = String(eve.data)
                    .replace(/,(?=[^\{]*\})/gi, '') //remove ',' in {field,Nested}
                    .replace(/[^\(]*\(/, '')
                    .replace(')', '')
                    .replace(/\[[^\]]+\]/gi, (match) => {
                        return String(match).replace(/,/gi, '`');
                    }); //wrap ',' in arrays []
                _.each(this.formulaParams, (prm, i) => {
                    let vVal = this.formulaParams.length == 1
                        ? _.trim( argums )
                        : _.trim( argums.split(',')[i] );
                    if (vVal) {
                        vVal = String(vVal).replace(/`/gi, ','); //unwrap ',' in arrays []
                        if (this.isRefFunction) {
                            vVal = this.prepName(vVal);
                            if (i == 0) {
                                this.formulaRefCond = _.find(this.tableMeta._ref_conditions, (rc) => {
                                    return this.prepName(rc.name) == vVal;
                                });
                            }
                            if (this.formulaRefCond && i == 1) {
                                let fnd = _.find(this.formulaRefCond._ref_table._fields, (fld) => {
                                    return this.prepName(fld.formula_symbol || fld.name) === vVal;
                                });
                                this.formulaField = fnd
                                    ? '{' + this.prepName(fnd.formula_symbol || fnd.name) + '}'
                                    : null;
                            }
                        } else {
                            if (prm.type === 'multi-sel') {
                                vVal = this.jsonPrepared(vVal, true);
                            }
                            prm.fld_name = vVal[0] === '{' || vVal[0] === '"'
                                ? vVal.substr(1, vVal.length-2)
                                : vVal;
                            this.fldValueOption(prm, vVal);
                        }
                    }
                });
                this.curSelectedFormula = eve.data;

                this.$nextTick(() => {
                    //scroll to selected Func
                    let refs = this.$refs['scrl_'+this.formulaFunc];
                    let rrow = _.first(refs);
                    if (!!window.chrome && rrow) {
                        rrow.scrollIntoView({block: 'center'});
                    }
                    this.saveSelection();
                });
            },
            selInFormulaChange(el, inn, sel) {
                el.data = String(el.data).replace(inn.data, sel.target.value);
                this.contentChanged();
            },
            selFldChange(el, sel) {
                el.data = sel.target.value;
                this.contentChanged();
            },
            contentChanged() {
                this.formulaAllString = this.getNodesValue();
                this.parseFormulaString();
            },
            getNodesValue() {
                let nodes = this.$refs.inline_input ? this.$refs.inline_input.childNodes || [] : [];
                return this.recursSearch(nodes);
            },
            recursSearch(nodes) {
                let html = '';
                _.each(nodes, (nod) => {
                    if (nod.nodeName.toLowerCase() !== 'select' && nod.childNodes && nod.childNodes.length) {
                        html += this.recursSearch(nod.childNodes);
                    } else {
                        html += nod.nodeValue || (nod.value ? '{' + nod.value + '}' : nod.innerText);
                    }
                });
                return html;
            },
            contentPress(eve) {
                this.curField = '';
                if ([16].indexOf(eve.keyCode) > -1) {
                    return; // ignore: 'shift'.
                }
                let cmdOrCtrl = eve.metaKey || eve.ctrlKey;
                if (cmdOrCtrl) {
                    if (eve.key === 'c') {
                        SpecialFuncs.strToClipboard( this.getNodesValue() );
                    }
                    return;
                }
                let sel = window.getSelection();
                let bnode = sel ? sel.baseNode : {};
                let key = eve.key.length == 1 ? eve.key : '';
                let node_val = String( (bnode.nodeValue || bnode.value || bnode.innerText) ).substr( 0, sel.baseOffset ) + key;
                let match = String(node_val).match(/[^\{a-zA-Z]*([\{a-zA-Z]*)$/i);
                if (match && match[1]) {
                    if (match[1][0] == '{') {
                        //column
                        this.add_type = 'field';
                        this.filter_str = this.getFilterString(match[1], eve, true);
                    } else if( String(match[1][0] || '').match(/[a-z]/i) ) {
                        //funcs
                        this.add_type = 'function';
                        this.filter_str = this.getFilterString(match[1], eve, false);
                    }
                    this.filter_elem_val = String(node_val);
                } else {
                    this.filter_str = '';
                }
                setTimeout(() => {
                    this.saveSelection();
                }, 100);
            },
            getFilterString(match, event, sub) {
                let result = String(match).toLowerCase();
                if (sub) {
                    result = result.substring(1);
                }
                if (event && event.keyCode == 8) {
                    result = result.substring(0, result.length - 1);
                }
                return result;
            },
            contentClick() {
                this.curSelectedFormula = '';
                this.curField = '';
                this.filter_str = '';
                this.formulaFunc = null;
                this.saveSelection();
            },
            saveSelection() {
                if (document.getSelection) {
                    let sel = document.getSelection();
                    let offset = sel.focusOffset;
                    let node = sel.focusNode;
                    while (!node.className || node.className.indexOf('content_input') < 0) {
                        if (node.nodeName.toLowerCase() == 'span') {
                            if (node.previousSibling) {
                                offset += node.previousSibling.innerText.length;
                                node = node.previousSibling;
                            } else {
                                node = node.parentNode;
                            }
                        } else {
                            node = node.parentNode;
                        }
                    }
                    this.selectionStart = offset;
                }
            },
            updateHeaderSettings() {
                if (this.tableHeader.is_uniform_formula) {
                    //save present formula
                    this.tableHeader.f_formula = this.formulaAllString;
                    this.tableHeader._changed_field = 'f_formula';
                    this.$root.updateSettingsColumn(this.tableMeta, this.tableHeader).then(() => {
                        //enable uniform
                        this.tableHeader.is_uniform_formula = 1;
                        this.tableHeader._changed_field = 'is_uniform_formula';
                        this.$root.updateSettingsColumn(this.tableMeta, this.tableHeader);
                    });
                } else {
                    //disable uniform
                    this.tableHeader.is_uniform_formula = 0;
                    this.tableHeader._changed_field = 'is_uniform_formula';
                    this.$root.updateSettingsColumn(this.tableMeta, this.tableHeader);
                }
            },
        },
        created() {
            this.parseFormulaString();
            eventBus.$on('global-click', this.hideFormulaHelper);
        },
        mounted() {
            this.smart_wrapper = 'formula_button';
            this.smart_limit = this.foreign_element ? 190 : 60;
            this.smart_horizontal = this.tableHeader
                ? Math.max(to_float(this.tableHeader.width), 300)
                : 300;
            this.showItemsList();
        },
        beforeDestroy() {
            eventBus.$off('global-click', this.hideFormulaHelper);
        }
    }
</script>

<style lang="scss" scoped>
    @import "./../../CustomPopup/CustomEditPopUp";

    .formula-helper {
        position: absolute;
        width: 100%;
        min-width: 300px;
        top: 0;
        right: 0;
        border: 1px solid #CCC;
        padding: 5px;
        background-color: #FFF;
        color: #444;
        border-radius: 5px;
        z-index: 1500;

        .formula-examples {
            position: absolute;
            max-height: 100%;
            max-width: 100%;
            height: auto;
            width: auto;
            right: 100%;
            top: 0;
            border: 1px solid #CCC;
            padding: 5px;
            background-color: #FFF;
            color: #444;
            border-radius: 5px;
            overflow: auto;
            white-space: nowrap;

            label {
                margin: 0;
            }
        }

        .content_input {
            overflow: auto;
            text-align: left;
            font-family: monospace !important;

             select {
                 font-family: monospace;
                 font-size: 14px;
             }
        }

        label {
            margin-bottom: 0;
            margin-top: 5px;
            white-space: nowrap;
        }

        .info-div {
            margin-top: 10px;
        }

        .formula__btns {
            display: flex;
            height: 34px;
            position: relative;
             div {
                 height: 40px;
                 position: absolute;
                 right: 5px;
                 top: -5px;
             }
        }
        .formula__tab {
            position: relative;
            border: 1px solid #ccc;
            padding: 5px;
            top: -3px;
            background-color: #FFF;
            color: #444;
        }

        .field__selector {
            overflow: auto;
            border: 1px solid #ccd0d2;
            border-radius: 4px;

            .field__item {
                cursor: pointer;
                background-color: #FFF;
                color: #444;

                &:hover {
                    background-color: #DDD;
                }
            }
        }

        .clear_txt {
            height: 26px;
            line-height: 1em;
            position: absolute;
            right: 70px;
            top: 2px;
        }
        .glyphicon-resize-full {
            position: absolute;
            right: 40px;
            font-size: 2rem;
            top: 5px;
        }

        .msel-wrapper {
            position: relative;
            width: 100%;
            height: 36px;
        }
    }

    .popup-wrapper {
        z-index: 2500;
        background: rgba(0, 0, 0, 0.45);

        .popup {
            height: auto;

            .popup-main {
            }
        }
    }
</style>