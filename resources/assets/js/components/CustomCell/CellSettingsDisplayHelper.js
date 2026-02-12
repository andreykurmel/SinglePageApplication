import {SpecialFuncs} from "../../classes/SpecialFuncs";
import {VerticalTableFldObject} from "../CustomTable/VerticalTableFldObject";

export class CellSettingsDisplayHelper {
    
    static inArray(needle, array) {
        return array.indexOf(needle) > -1;
    }

    static disabledCheckBox(globalMeta, tableHeader, tableRow, behavior) {
        let inputType = tableRow.input_type === 'Mirror' && tableRow.mirror_rc_id
            ? tableRow.mirror_edit_component
            : tableRow.input_type;

        return !this.canCellEdit(globalMeta, tableHeader, tableRow, true, behavior)
            ||
            ( //autofill and autocomplete are not accessible for not DDL Input types
                this.inArray(tableHeader.field, ['ddl_add_option', 'ddl_auto_fill'])
                &&
                !this.inArray(inputType, window.vueRootApp.ddlInputTypes)
            )
            ||
            ( //is_search_autocomplete_display is not accessible for 'User','Attachment'
                tableHeader.field === 'is_search_autocomplete_display'
                &&
                this.inArray(tableRow.f_type, ['Attachment'])
            )
            ||
            ( //'is_uniform_formula','f_formula' is not accessible for not 'Formula' input
                this.inArray(tableHeader.field, ['is_uniform_formula', 'f_formula'])
                &&
                inputType !== 'Formula'
            )
            ||
            ( //'markerjs_annotations','markerjs_cropro' are not accessible for not 'Attachment' field
                this.inArray(tableHeader.field, ['markerjs_annotations', 'markerjs_cropro', 'image_fitting'])
                &&
                !this.inArray(tableRow.f_type, ['Attachment'])
            )
            ||
            ( //'filter_search' only for 'filter_type=value'
                this.inArray(tableHeader.field, ['filter_search'])
                &&
                (tableRow.filter_type !== 'value' || !tableRow.filter)
            );
    }

    static canCellEdit(globalMeta, tableHeader, tableRow, isAddRow = true, behavior = '') {
        if (tableHeader.field === 'f_default') {
            return false;
        }
        if (tableHeader.field === 'input_type' && window.vueRootApp.systemFields.indexOf(tableRow.field) !== -1) {
            return false;
        }

        /*let has_inheritance = false;
        if (SpecialFuncs.issel(tableRow.input_type)) {
            let ddl = _.find(globalMeta._ddls, {id: Number(tableRow.ddl_id)}) || {};//NOTE: shared ddls cannot be used here.
            _.each(ddl._references || [], (ref) => {
                let rc = _.find(globalMeta._ref_conditions, {id: Number(ref.table_ref_condition_id)});
                if (rc && rc.table_id != rc.ref_table_id) {
                    has_inheritance = true;
                }
            });
        }*/
        let strType = this.inArray(tableRow.f_type, ['String', 'Text', 'Long Text', 'Auto String']);
        let has_unit = tableRow.unit && tableRow.unit_ddl_id && globalMeta.unit_conv_is_active;// && globalMeta.__unit_convers && globalMeta.__unit_convers.length;
        let isFldDis = this.inArray(tableHeader.field, ['fld_display_name', 'fld_display_value', 'fld_display_border', 'fld_display_header_type', 'is_topbot_in_popup']);

        return (tableHeader.field !== 'name' || isAddRow)
            && (globalMeta._is_owner || this.inArray(tableHeader.field, window.vueRootApp.availableNotOwnerDisplayColumns))
            && !this.inArray(tableHeader.field, window.vueRootApp.systemFields)
            && (!this.inArray(tableHeader.field, ['header_unit_ddl', 'unit_display']) || has_unit)
            && (!isFldDis || VerticalTableFldObject.fieldSetting('fld_popup_shown', tableRow, null, behavior))
            && (tableHeader.field !== 'f_formula' || tableRow.input_type === 'Formula')
            && (tableHeader.field !== 'is_default_show_in_popup' || window.vueRootApp.checkAvailable(window.vueRootApp.user, 'form_visibility'))
            && (tableHeader.field !== 'verttb_row_height' || !tableRow.verttb_he_auto)
            && (tableHeader.field !== 'twilio_google_acc_id' || tableRow.f_type === 'Email')
            && (tableHeader.field !== 'twilio_sendgrid_acc_id' || tableRow.f_type === 'Email')
            && (tableHeader.field !== 'twilio_sms_acc_id' || tableRow.f_type === 'Phone Number')
            && (tableHeader.field !== 'twilio_voice_acc_id' || tableRow.f_type === 'Phone Number')
            && (tableHeader.field !== 'twilio_sender_name' || tableRow.twilio_google_acc_id || tableRow.twilio_sendgrid_acc_id)
            && (tableHeader.field !== 'twilio_sender_email' || tableRow.twilio_sendgrid_acc_id)
            && (tableHeader.field !== 'mirror_rc_id' || tableRow.input_type === 'Mirror')
            && (tableHeader.field !== 'mirror_field_id' || (tableRow.input_type === 'Mirror' && tableRow.mirror_rc_id))
            && (tableHeader.field !== 'mirror_part' || (tableRow.input_type === 'Mirror' && tableRow.mirror_rc_id))
            && (tableHeader.field !== 'mirror_one_value' || (tableRow.input_type === 'Mirror' && tableRow.mirror_rc_id))
            && (tableHeader.field !== 'mirror_editable' || (tableRow.input_type === 'Mirror' && tableRow.mirror_rc_id))
            && (tableHeader.field !== 'mirror_edit_component' || (tableRow.input_type === 'Mirror' && tableRow.mirror_rc_id && tableRow.mirror_editable))
            && (tableHeader.field !== 'fetch_source_id' || (tableRow.input_type === 'Fetch' && tableRow.f_type === 'Attachment'))
            && (tableHeader.field !== 'fetch_by_row_cloud_id' || (tableRow.input_type === 'Fetch' && tableRow.f_type === 'Attachment'))
            && (tableHeader.field !== 'fetch_one_cloud_id' || (tableRow.input_type === 'Fetch' && tableRow.f_type === 'Attachment'))
            && (tableHeader.field !== 'fetch_uploading' || (tableRow.input_type === 'Fetch' && tableRow.f_type === 'Attachment'))
            && (tableHeader.field !== 'is_inherited_tree' || (tableRow.ddl_id && SpecialFuncs.issel(tableRow.input_type)))
            && (tableHeader.field !== 'has_copy_prefix' || strType)
            && (tableHeader.field !== 'has_copy_suffix' || strType)
            && (tableHeader.field !== 'has_datetime_suffix' || strType)
            && (tableHeader.field !== 'copy_prefix_value' || (strType && tableRow.has_copy_prefix))
            && (tableHeader.field !== 'copy_suffix_value' || (strType && tableRow.has_copy_suffix))
            && (tableHeader.field !== 'pop_tab_order' || !tableRow._poptaborder_disabled);
    }
}