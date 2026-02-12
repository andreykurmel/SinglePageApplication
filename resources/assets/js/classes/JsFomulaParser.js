
import {SpecialFuncs} from './SpecialFuncs';

export class JsFomulaParser {

    /**
     *
     * @param tableMeta
     */
    constructor(tableMeta)
    {
        this.field_name_links = {};
        if (tableMeta) {
            _.each(tableMeta._fields, ($tf) => {
                this.field_name_links[ this.tf_name($tf.name) ] = $tf;
                if ($tf.formula_symbol) {
                    this.field_name_links[ this.tf_name($tf.formula_symbol) ] = $tf;
                }
            });
        } else {
            this.field_name_links['{val}'] = {field: 'val'};
        }
    }

    /**
     *
     * @param $name
     * @param $nolower
     * @returns {*}
     */
    tf_name($name, $nolower = false)
    {
        $name = String($name).replace(newRegexp('[^\\p{L}\\d]'), '');//sanitize
        return '{' + ($nolower ? $name : String($name).toLowerCase()) + '}';
    }

    /**
     *
     * @param $row
     * @param $formula_str
     * @param tableMeta
     * @param suppress
     * @returns {*}
     */
    replaceVars($row, $formula_str, tableMeta, suppress)
    {
        let $active_fields = String($formula_str).match(/\{[^\}]*\}/gi);
        _.each($active_fields, ($act_field, $idx) => { // $act_field = '{FIELD_NAME}'
            let $fld = this.field_name_links[ this.tf_name($act_field) ];
            if ($fld) {
                //parser rules
                let $data = String($row[$fld.field] || '').replace(/['"]/gi, '');//sanitize
                $data = String($data).replace(/(\d),(\d)/i, '$1$2');//prepare for number calcs
                $data = SpecialFuncs.showhtml($fld, $row, $data, tableMeta);
                $data = !String($data) || String($data).match('/[^-\d\.]/i')
                    ? ($data || '') //String if not only digits
                    : ($data || 0); //Number if only digits
                //---
                // if (isNaN($data) || $data === '') {
                //     $data = "'" + $data + "'";
                // }

                $formula_str = String($formula_str).replace($act_field, $data);
            } else {
                $formula_str = suppress === undefined ? "'Field "+$act_field+" not found'" : suppress;
            }
        });
        return $formula_str;
    }

    /**
     *
     * @param $formula_str
     * @param $value
     * @returns {Object}
     */
    justOneVal($formula_str, $value)
    {

        $formula_str = String($formula_str).replace(newRegexp('\\$val([^\\p{L}\\d])'), '{val}$1');//.replace(newRegexp('\\{[^\\}]*\\}'), '{val}');
        $formula_str = this.formulaEval({val: $value}, $formula_str);
        return $formula_str || $value;
    }

    /**
     *
     * @param $row
     * @param $formula_str
     * @param tableMeta
     * @param suppress
     * @returns {*}
     */
    formulaEval($row, $formula_str, tableMeta, suppress)
    {
        let init_Formula = $formula_str || '';
        $formula_str = init_Formula;

        $formula_str = String($formula_str).replaceAll('~', '+');

        $formula_str = this.replaceVars($row, $formula_str, tableMeta, suppress);

        if (!String(init_Formula).match('~')) {
            $formula_str = String($formula_str).replace(/'\$([-\d\.]+)'/gi, "$1");
        }

        $formula_str = String($formula_str).replaceAll('"', "'");

        try {
            $formula_str = eval($formula_str);
        } catch (e) {
            $formula_str = suppress === undefined ? $formula_str : suppress;
        }
        return $formula_str;
    }

    /**
     *
     * @param tableMeta
     * @param tableRow
     */
    static checkRowAndCalculate(tableMeta, tableRow)
    {
        if ( _.find(tableMeta._fields, {input_type: 'Formula'}) ) {
            let changedFld = _.find(tableMeta._fields, {field: tableRow['_changed_field']});
            let calculator = new JsFomulaParser(tableMeta);
            _.each(tableMeta._fields, (fld) => {
                if (fld.input_type === 'Formula') {
                    let key = fld.field + '_formula';

                    let suppres = undefined;
                    if (changedFld && String(tableRow[key]).indexOf(changedFld.name) !== -1) {
                        suppres = 'Calc...';
                    }

                    tableRow[fld.field] = calculator.formulaEval(tableRow, tableRow[key], tableMeta, suppres);
                }
            });
        }
    }

    /**
     *
     * @param {Object} tableMeta
     * @param {Array} allRows
     */
    static checkEmptyRows(tableMeta, allRows)
    {
        if ( _.find(tableMeta._fields, {input_type: 'Formula'}) ) {
            let calculator = new JsFomulaParser(tableMeta);
            _.each(allRows, (row) => {
                _.each(tableMeta._fields, (fld) => {
                    let key = fld.field + '_formula';
                    if (fld.input_type === 'Formula' && !row[fld.field]) {
                        row[fld.field] = calculator.formulaEval(row, row[key], tableMeta, '');
                    }
                });
            });
        }
    }
}