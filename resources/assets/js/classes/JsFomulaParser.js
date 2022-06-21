
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
        $name = String($name).replace(/\W/gi, '');//sanitize
        return '{' + ($nolower ? $name : String($name).toLowerCase()) + '}';
    }

    /**
     *
     * @param $row
     * @param $formula_str
     * @param unitConv
     * @returns {*}
     */
    replaceVars($row, $formula_str, unitConv)
    {
        let $active_fields = String($formula_str).match(/\{[^\}]*\}/gi);
        _.each($active_fields, ($act_field, $idx) => { // $act_field = '{FIELD_NAME}'
            let $fld = this.field_name_links[ this.tf_name($act_field) ];
            if ($fld) {
                //parser rules
                let $data = String($row[$fld.field] || '').replace(/["]/gi, '');//sanitize
                $data = String($data).replace(/(\d),(\d)/i, '$1$2');//prepare for number calcs
                $data = SpecialFuncs.showhtml($fld, $row, $data, unitConv);
                $data = !String($data) || String($data).match('/[^\d\.]/i')
                    ? ($data || '') //String if not only digits
                    : ($data || 0); //Number if only digits
                //---

                $formula_str = String($formula_str).replace($act_field, $data);
            } else {
                $formula_str = '"Field '+$act_field+' not found"';
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
        $formula_str = String($formula_str).replace(/\$val([^\d\w])/gi, '{val}$1');//.replace(/\{[^\}]*\}/gi, '{val}');
        $formula_str = this.replaceVars({val: $value}, $formula_str);
        try {
            $formula_str = eval(String($formula_str));//.replace(/[^-()\d/*+.]/g, '')
        } catch (e) {
            $formula_str = undefined;
        }
        return $formula_str || $value;
    }
}