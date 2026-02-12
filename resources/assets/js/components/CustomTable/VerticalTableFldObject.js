
export class VerticalTableFldObject {
    /**
     * constructor
     */
     constructor() {
        this.sub_header_name = '';

        this.single = null; // {TableField}->php
        //OR
        this.group = null; // {Array} of {TableField}->php
        this.group_lnk = null; // {TableField}->php

        this.level = 0;
        this.base_subs_lvl = 0;
        this.sub_headers = []; // {Array} of {string}
        this.global_subs = []; // {Array} of {string}
        this.dcr_linked = null; // {DcrLinkedTable}->php
        this.is_hlior = false;
        this.table_full_wi = true;
    }

    /**
     * setSingle
     * @param header
     */
     setSingle(header) {
        this.sub_header_name = this._getHeadName(header.name);
        this.single = header;
        this.group = null;
        this.group_lnk = null;
    }

    /**
     * setGroup
     * @param header
     */
     setGroup(header) {
        this.sub_header_name = this._getHeadName(header.name);
        this.group = [header];
        this.group_lnk = header.__inlined_link ? header : null;
        this.single = null;
    }

    /**
     * setGroup
     * @param header
     */
     addToGroup(header) {
        if (this.group && this.group.length) {
            this.group.push(header);
            this.group_lnk = header.__inlined_link ? header : this.group_lnk;
            this.sub_header_name = this._minGroupName();
        } else {
            this.setGroup(header);
        }
    }

    /**
     * _getHeadName
     * @param name
     * @returns {string}
     * @private
     */
     _getHeadName(name) {
        let arr = String(name).split(',');
        return arr.slice(0, arr.length-1).join(',');
    }

    /**
     * getFirstSubheader
     * @param name
     * @returns {string}
     * @private
     */
     static _getFirstSubheader(name) {
        let arr = String(name).split(',');
        return arr[0] || name;
    }

    /**
     * noSubs
     * @param name1
     * @param name2
     * @returns {boolean}
     * @private
     */
     static _noSubs(name1, name2) {
        let arr1 = String(name1).split(',');
        let arr2 = String(name2).split(',');
        return arr1.length <= 1 && arr2.length <= 1;
    }

    /**
     * _minGroupName
     * @returns {string}
     * @private
     */
     _minGroupName() {
        let res = '';
        if (this.group && this.group.length) {
            res = String(this.group[0].name).split(',');
            _.each(this.group, (header) => {
                let name_arr = String(header.name).split(',');
                for (let i in res) {
                    if (res[i] !== name_arr[i]) {
                        res = res.slice(0, i);
                        break;
                    }
                }
            });
            res = res.join(',');
        }
        return res;
    }

    /**
     * buildSubHeaders
     * @param fields {Array} of {TableField}
     * @param all_is_single {Boolean}
     * @param extraPivotFields {Array|null}
     * @param behavior {String|null}
     * @returns {Array} of {VerticalTableFldObject}
     */
     static buildSubHeaders(fields, all_is_single, extraPivotFields, behavior) {
        let fld_objects = this._groupTableFields(fields, all_is_single, extraPivotFields, behavior);
        let sub_headers_obj = {
            global_subs: [],
            header_subs: [],
        };
        _.each(fld_objects, (vertTableFieldObject) => {
            sub_headers_obj = this._getSubHeaders(sub_headers_obj, vertTableFieldObject.sub_header_name);
            vertTableFieldObject.level = sub_headers_obj.global_subs.length;
            vertTableFieldObject.base_subs_lvl = sub_headers_obj.global_subs.length - sub_headers_obj.header_subs.length;
            vertTableFieldObject.sub_headers = sub_headers_obj.header_subs;
            vertTableFieldObject.global_subs = sub_headers_obj.global_subs;
            if (vertTableFieldObject.single) {
                vertTableFieldObject.is_hlior = !!this.fieldSetting('is_hdr_lvl_one_row', vertTableFieldObject.single, extraPivotFields, behavior);
            }
            if (vertTableFieldObject.group && vertTableFieldObject.group.length) {
                let first = _.first(vertTableFieldObject.group);
                vertTableFieldObject.table_full_wi = this.fieldSetting('width_of_table_popup', first, extraPivotFields, behavior) === 'full';
                vertTableFieldObject.is_hlior = true;
                _.each(vertTableFieldObject.group, (hdr) => {
                    vertTableFieldObject.is_hlior = vertTableFieldObject.is_hlior
                        && !!this.fieldSetting('is_hdr_lvl_one_row', hdr, extraPivotFields, behavior);
                });
            }
        });
        return fld_objects;
    }

    /**
     * _groupTableFields
     * @param fields {Array} of {TableField}
     * @param all_is_single {Boolean}
     * @param extraPivotFields {Array|null}
     * @param behavior {String|null}
     * @returns {Array} of {VerticalTableFldObject}
     */
     static _groupTableFields(fields, all_is_single, extraPivotFields, behavior) {
        //group Fields which are 'Tables in Form'
        let fld_objects = [];
        let idx = 0;
        _.each(fields, (header) => {
            if (this.fieldSetting('is_table_field_in_popup', header, extraPivotFields, behavior) && !all_is_single) {
                //add
                if (fld_objects[idx]) {
                    if (
                        !this.fieldSetting('is_start_table_popup', header, extraPivotFields, behavior)
                        &&
                        (
                            this._noSubs(header.name, fld_objects[idx].sub_header_name)
                            ||
                            this._getFirstSubheader(header.name) == this._getFirstSubheader(fld_objects[idx].sub_header_name)
                        )
                    ) {
                        fld_objects[idx].addToGroup(header);
                    } else {
                        idx++;
                        fld_objects[idx] = new VerticalTableFldObject();
                        fld_objects[idx].setGroup(header);
                    }
                }
                //create
                else {
                    fld_objects[idx] = new VerticalTableFldObject();
                    fld_objects[idx].setGroup(header);
                }
            } else {
                //if present 'group'
                if (fld_objects[idx]) { idx++; }
                //create
                fld_objects[idx] = new VerticalTableFldObject();
                fld_objects[idx].setSingle(header);
                idx++;
            }
        });
        return fld_objects;
    }

    /**
     * _getSubHeaders
     * @param sho
     * @param sub_header_name
     * @returns {*}
     * @private
     */
     static _getSubHeaders(sho, sub_header_name) {
        let words = _.uniq( sub_header_name.split(',') );
        sho.header_subs = [];
        if (sub_header_name && words.length) {
            _.each(words, (word, idx) => {
                let trim = String(word).trim();
                //not present in 'Globals'
                if (trim && (!sho.global_subs[idx] || sho.global_subs[idx] !== trim)) {
                    sho.global_subs = sho.global_subs.slice(0, idx);
                    sho.global_subs[idx] = trim;
                    sho.header_subs.push(trim);
                }
            });
            sho.global_subs = sho.global_subs.slice(0, words.length);
        } else {
            sho.global_subs = [];
        }
        return sho;
    }

    /**
     *
     * @param key
     * @param tableHeader
     * @param extraPivotFields
     * @param behavior
     * @returns {*}
     */
    static fieldSetting(key, tableHeader, extraPivotFields, behavior)
    {
        let pivotKeys = ['table_show_name','table_show_value','cell_border','picture_style','picture_fit','width_of_table_popup',
            'is_start_table_popup','is_table_field_in_popup','is_hdr_lvl_one_row','is_dcr_section','dcr_section_name'];

        let ignoredBehavior = ['link_popup', 'dcr_linked_tb'].indexOf(behavior) > -1;
        if (window.vueRootApp.is_dcr_page && ! ignoredBehavior) { //Is DCR
            pivotKeys = _.concat(
                pivotKeys,
                ['fld_popup_shown','fld_display_name','fld_display_value','fld_display_border','fld_display_header_type','is_topbot_in_popup']
            );
            if (! extraPivotFields) {
                extraPivotFields = window.vueRootApp.dcrPivotFields;
            }
        }

        if (extraPivotFields && pivotKeys.indexOf(key) > -1) {
            let pivotHeader = _.find(extraPivotFields || [], {table_field_id: Number(tableHeader.id)}) || {};
            return pivotHeader[key];
        }

        return tableHeader[key];
    }
}