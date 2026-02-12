
import {SpecialFuncs} from "./SpecialFuncs";
import {JsFomulaParser} from "./JsFomulaParser";

export class UnitConversion {


    /**
     * Do Unit Conversion for Value.
     *
     * @param tableHeader
     * @param value
     * @param is_reverse
     * @returns {*}
     */
    static doConv(tableHeader, value, is_reverse) {
        let conversions = tableHeader.__selected_unit_convs || [];
        //let can_convert = !window.vueRootApp || window.vueRootApp.checkAvailable(window.vueRootApp.user, 'unit_conversions');
        if (!tableHeader.unit || !tableHeader.unit_display || !isNumber(value) || !conversions.length /*|| !can_convert*/) {
            return this.formatVal(value, tableHeader);
        }

        //convert by "convert-units" npm.
        if (conversions[0].is_npm_unit_conv) {
            if (is_reverse) {
                return ConvertUnit(value).from(conversions[0].conv_to).to(conversions[0].conv_from);
            } else {
                return ConvertUnit(value).from(conversions[0].conv_from).to(conversions[0].conv_to);
            }
        }

        //convert with UnitConversion table.
        _.each(conversions, (conv) => {
            let operator = this.ucm_get_operator(conv.operator);
            if (is_reverse) {
                operator = this.ucm_operator_reverse(operator);
            }
            switch(operator) {
                case 'Multiply': value *= conv.factor;
                    break;
                case 'Divide': value /= conv.factor;
                    break;
                case 'Add': value += conv.factor;
                    break;
                case 'Substract': value -= conv.factor;
                    break;
                case 'Square': value = Math.pow(value, 2);
                    break;
                case 'Square Root': value = Math.sqrt(value);
                    break;
                case 'Formula':
                    let pars = new JsFomulaParser();
                    value = pars.justOneVal(conv.formula, value);
                    break;
                case 'Formula Reverse':
                    let parsrev = new JsFomulaParser();
                    value = parsrev.justOneVal(conv.formula_reverse, value);
                    break;
            }
        });
        return this.formatVal(value, tableHeader);
    }


    /**
     * Find conversions.
     *
     * @param tableMeta
     * @param tableHeader
     * @returns {Array}
     */
    static findConvs(tableMeta, tableHeader) {
        let user_conversions = tableMeta.__unit_convers || [];
        let sys_conversions = window.vueRootApp ? (window.vueRootApp.settingsMeta.unit_conversion || []) : [];

        let unit_display = SpecialFuncs.rcObj(tableHeader, 'unit_display', tableHeader.unit_display).show_val || tableHeader.unit_display;
        let unit = SpecialFuncs.rcObj(tableHeader, 'unit', tableHeader.unit).show_val || tableHeader.unit;

        let result = [];
        if (!result.length && tableMeta.unit_conv_by_user) {
            result = this.ucm_recursive_search(user_conversions, unit, unit_display, []);
        }
        if (!result.length && tableMeta.unit_conv_by_system) {
            result = this.ucm_recursive_search(sys_conversions, unit, unit_display, []);
        }
        if (!result.length && tableMeta.unit_conv_by_lib) {
            result = this.ucm_find_npmUnitConv(unit, unit_display);
        }

        return result;
    }

    /**
     *
     * @param tableHeader
     * @param tableMeta
     * @returns {string}
     */
    static showUnit(tableHeader, tableMeta) {
        let res = '';
        if (tableHeader.unit_display && tableMeta.unit_conv_is_active) {
            res = SpecialFuncs.rcObj(tableHeader, 'unit_display', tableHeader.unit_display).show_val || tableHeader.unit_display;
        }
        if (!res && tableHeader.unit) {
            res = SpecialFuncs.rcObj(tableHeader, 'unit', tableHeader.unit).show_val || tableHeader.unit;
        }
        return res;
    }

    /**
     * Format Value output after conversion.
     *
     * @param value
     * @param tableHeader
     * @returns {*}
     */
    static formatVal(value, tableHeader) {
        //is string -> return as is
        if (value && String(value).match(/[^\d-.]/gi)) {
            return value;
        }

        if (value === null || value === undefined) {
            value = '';
        }

        return this.applyShowZero(value, tableHeader);
    }

    /**
     *
     * @param value
     * @param tableHeader
     * @returns {*}
     */
    static applyShowZero(value, tableHeader) {
        value = isNumber(value) ? to_float(value) : value;
        if (String(value) === '0') {
            value = tableHeader.show_zeros ? 0 : '';
        } else {
            value = value || '';
        }
        return value;
    }


    /** Helpers **/

    /**
     *
     * @param fromU
     * @param toU
     * @returns {*}
     */
    static ucm_find_npmUnitConv(fromU, toU) {
        fromU = String(fromU).toLowerCase();
        toU = String(toU).toLowerCase();

        let conv_from = null;
        let conv_to = null;

        let unit_list = ConvertUnit().list();
        _.each(unit_list, (ul) => {
            if ((ul.abbr.toLowerCase() === fromU) || (ul.singular.toLowerCase() === fromU) || (ul.plural.toLowerCase() === fromU)) {
                conv_from = ul.abbr;
            }
            if ((ul.abbr.toLowerCase() === toU) || (ul.singular.toLowerCase() === toU) || (ul.plural.toLowerCase() === toU)) {
                conv_to = ul.abbr;
            }
        });

        return conv_from && conv_to
            ? [{
                is_npm_unit_conv: true,
                conv_from: conv_from,
                conv_to: conv_to,
            }]
            : [];
    }

    /**
     *
     * @param unit
     */
    static ucm_get_unit(unit) {
        let arr = unit.split(',');
        _.each(arr, (el, i) => {
            arr[i] = String(el).trim().toLowerCase();
        });
        return arr;
    }

    /**
     *
     * @param conversion_rules
     * @param fromU
     * @param toU
     * @param res_array
     * @returns {*}
     */
    static ucm_recursive_search(conversion_rules, fromU, toU, res_array) {
        fromU = String(fromU).trim().toLowerCase();
        toU = String(toU).trim().toLowerCase();
        //the same
        if (fromU == toU) {
            return res_array;
        }
        //deep of the search
        if (res_array.length >= 5) {
            return [];
        }
        //on level search
        for (let i=0; i < conversion_rules.length; i++)
        {
            let conv = conversion_rules[i];
            //direct convert
            if (this.ucm_unit_fits(conv, fromU, toU)) {
                res_array.push({
                    operator: this.ucm_get_operator(conv.operator),
                    factor: to_float(conv.factor),
                    formula: String(conv.formula),
                    formula_reverse: String(conv.formula_reverse),
                });
                return res_array;
            }
            //reverse convert
            if (this.ucm_unit_fits(conv, toU, fromU)) {
                res_array.push({
                    operator: this.ucm_operator_reverse( this.ucm_get_operator(conv.operator) ),
                    factor: to_float(conv.factor),
                    formula: String(conv.formula),
                    formula_reverse: String(conv.formula_reverse),
                });
                return res_array;
            }
        }
        //deep search
        for (let i=0; i < conversion_rules.length; i++)
        {
            let conv = conversion_rules[i];
            let from_unit = this.ucm_get_unit(conv.from_unit);
            let to_unit = this.ucm_get_unit(conv.to_unit);
            if (this.ucm_unit_fits(conv, fromU, undefined)) {
                let tmp_res_array = _.cloneDeep(res_array);
                tmp_res_array.push({
                    operator: this.ucm_get_operator(conv.operator),
                    factor: to_float(conv.factor),
                    formula: String(conv.formula),
                    formula_reverse: String(conv.formula_reverse),
                });
                for (let j=0; j < to_unit.length; j++) {
                    let arr = this.ucm_recursive_search(conversion_rules, to_unit[j], toU, tmp_res_array);
                    if (arr.length) {
                        return arr;
                    }
                }
            }
            if (this.ucm_unit_fits(conv, undefined, toU)) {
                let tmp_res_array = _.cloneDeep(res_array);
                tmp_res_array.push({
                    operator: this.ucm_operator_reverse( this.ucm_get_operator(conv.operator) ),
                    factor: to_float(conv.factor),
                    formula: String(conv.formula),
                    formula_reverse: String(conv.formula_reverse),
                });
                for (let j=0; j < from_unit.length; j++) {
                    let arr = this.ucm_recursive_search(conversion_rules, from_unit[j], toU, tmp_res_array);
                    if (arr.length) {
                        return arr;
                    }
                }
            }
        }
        return [];
    }

    /**
     *
     * @param conv
     * @param fromU
     * @param toU
     * @returns {boolean}
     */
    static ucm_unit_fits(conv, fromU, toU) {
        let from_unit = this.ucm_get_unit(conv.from_unit);
        let to_unit = this.ucm_get_unit(conv.to_unit);
        let operator = String(conv.operator).trim().toLowerCase();
        return (!fromU || from_unit.indexOf(fromU) > -1)
            && (!toU || to_unit.indexOf(toU) > -1)
            && (
                operator !== 'formula'
                ||
                (String(conv.formula).match(newRegexp('\\$val[^\\p{L}\\d]')) && String(conv.formula_reverse).match(newRegexp('\\$val[^\\p{L}\\d]')))
            );
    }

    /**
     *
     * @param operator
     * @returns {string|*}
     */
    static ucm_get_operator(operator) {
        operator = String(operator).trim().toLowerCase();
        if (operator.indexOf('*') > -1 || operator.indexOf('mult') > -1) {
            operator = 'Multiply';
        }
        if (operator.indexOf('/') > -1 || operator.indexOf('div') > -1) {
            operator = 'Divide';
        }
        if (operator.indexOf('+') > -1 || operator.indexOf('add') > -1) {
            operator = 'Add';
        }
        if (operator.indexOf('-') > -1 || operator.indexOf('sub') > -1) {
            operator = 'Substract';
        }
        if (operator.indexOf('root') > -1) {
            operator = 'Square Root';
        }
        if (operator.indexOf('^') > -1 || operator.indexOf('sq') > -1) {
            operator = 'Square';
        }
        if (operator.indexOf('reverse') > -1) {
            operator = 'Formula Reverse';
        }
        if (operator.indexOf('formula') > -1) {
            operator = 'Formula';
        }
        return operator;
    }

    /**
     *
     * @param operator
     * @returns {*}
     */
    static ucm_operator_reverse(operator) {
        switch(operator) {
            case 'Multiply': operator = 'Divide';
                break;
            case 'Divide': operator = 'Multiply';
                break;
            case 'Add': operator = 'Substract';
                break;
            case 'Substract': operator = 'Add';
                break;
            case 'Square': operator = 'Square Root';
                break;
            case 'Square Root': operator = 'Square';
                break;
            case 'Formula': operator = 'Formula Reverse';
                break;
            case 'Formula Reverse': operator = 'Formula';
                break;
        }
        return operator;
    }
}