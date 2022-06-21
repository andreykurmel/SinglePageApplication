
export class VerticalTableFldObject {
    /**
     * constructor
     */
     constructor() {
        this.sub_header_name = '';

        this.single = null; // {TableField}->php
        //OR
        this.group = null; // {Array} of {TableField}->php

        this.level = 0;
        this.base_subs_lvl = 0;
        this.sub_headers = []; // {Array} of {string}
        this.dcr_linked = null; // {DcrLinkedTable}->php
    }

    /**
     * setSingle
     * @param header
     */
     setSingle(header) {
        this.sub_header_name = this._getHeadName(header.name);
        this.single = header;
        this.group = null;
    }

    /**
     * setGroup
     * @param header
     */
     setGroup(header) {
        this.sub_header_name = this._getHeadName(header.name);
        this.group = [header];
        this.single = null;
    }

    /**
     * setGroup
     * @param header
     */
     addToGroup(header) {
        if (this.group && this.group.length) {
            this.group.push(header);
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
     * @returns {Array} of {VerticalTableFldObject}
     */
     static buildSubHeaders(fields, all_is_single) {
        let fld_objects = this._groupTableFields(fields, all_is_single);
        let sub_headers_obj = {
            global_subs: [],
            header_subs: [],
        };
        _.each(fld_objects, (vertTableFieldObject) => {
            sub_headers_obj = this._getSubHeaders(sub_headers_obj, vertTableFieldObject.sub_header_name);
            vertTableFieldObject.level = sub_headers_obj.global_subs.length;
            vertTableFieldObject.base_subs_lvl = sub_headers_obj.global_subs.length - sub_headers_obj.header_subs.length;
            vertTableFieldObject.sub_headers = sub_headers_obj.header_subs;
        });
        return fld_objects;
    }

    /**
     * _groupTableFields
     * @param fields
     * @param all_is_single
     * @returns {Array} of {VerticalTableFldObject}
     */
     static _groupTableFields(fields, all_is_single) {
        //group Fields which are 'Tables in Form'
        let fld_objects = [];
        let idx = 0;
        _.each(fields, (header) => {
            if (header.is_table_field_in_popup && !all_is_single) {
                //add
                if (fld_objects[idx]) {
                    if (
                        !header.is_start_table_popup
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
}