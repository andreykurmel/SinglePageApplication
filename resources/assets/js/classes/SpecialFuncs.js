
import {UnitConversion} from "./UnitConversion";

export class SpecialFuncs {

    static dateFormat() { return 'YYYY-MM-DD'; }
    static dateTimeFormat() { return 'YYYY-MM-DD HH:mm:ss'; }
    static timeFormat() { return 'HH:mm:ss'; }

    //COOKIES
    /**
     *
     * @param name
     * @param val
     */
    static set_cookie(name, val) {
        this.delete_cookie(name);
        let expires = 'expires='+ (new Date(Date.now()+30*24*60*60*1000)).toUTCString();
        let domain = 'domain='+window.vue_app_domain;
        document.cookie = name+'='+val+'; '+domain+'; '+expires+'; path=/';
    }

    /**
     *
     * @param name
     */
    static delete_cookie(name) {
        let expires = 'expires='+ (new Date(0)).toUTCString();
        let domain = 'domain='+window.vue_app_domain;
        document.cookie = name+'='+'; '+expires;
        document.cookie = name+'='+'; '+domain+'; '+expires+'; path=/';
    }



    //CLIPBOARD
    /**
     *
     */
    static clipFillPaste() {
        let el = document.getElementById('for_paste_get');
        el.focus();
        document.execCommand("paste");
    }

    /**
     *
     * @returns {string}
     */
    static clipboardGetStr() {
        let el = document.getElementById('for_paste_get');
        return el.value;
    }

    /**
     *
     * @param Str
     */
    static strToClipboard(Str) {
        if (Str) {
            const el = document.createElement('textarea');
            el.value = Str;
            document.body.appendChild(el);
            el.select();
            document.execCommand('copy');
            document.body.removeChild(el);
        }
    }

    /**
     *
     * @param Dom
     */
    static domToClipboard(Dom) {
        if (Dom) {
            let body = document.body, range, sel;
            if (document.createRange && window.getSelection) {
                range = document.createRange();
                sel = window.getSelection();
                sel.removeAllRanges();
                try {
                    range.selectNodeContents(Dom);
                    sel.addRange(range);
                } catch (e) {
                    range.selectNode(Dom);
                    sel.addRange(range);
                }
                document.execCommand('copy');

            } else if (body.createTextRange) {
                range = body.createTextRange();
                range.moveToElementText(Dom);
                range.select();
                range.execCommand("Copy");
            }
        }
    }



    //HELPERS
    /**
     *
     * @param str
     * @returns {string}
     */
    static capitalizeFirst(str) {
        return String(str).charAt(0).toUpperCase() + String(str).slice(1);
    }

    /**
     *
     * @param str
     * @returns {string}
     */
    static lowerCase(str) {
        return String(str).toLowerCase();
    }

    /**
     * prepareFilters
     * @param initial_filters
     * @param updated_filters
     * @returns {Array}
     */
    static prepareFilters(initial_filters, updated_filters) {
        //save special params (_is_single, _single_val, etc...)
        let filters_new = [];
        _.each(updated_filters, (fltr) => {
            let old = _.find(initial_filters, {id: Number(fltr.id)});
            filters_new.push({
                ...old,
                ...fltr
            });
        });
        //-------
        return filters_new;
    }

    /**
     *
     * @param val
     * @returns {[*]}
     */
    static parseMsel(val) {
        let result = [];
        val = String(val || '');
        try {
            if (in_array(val.charAt(0), ['[', '{'])) {
                result = JSON.parse(val);
            } else {
                result = [val];
            }
        } catch (e) {
            result = [val];
        }
        return _.filter(result);
    }

    /**
     *
     * @param str
     * @returns {*}
     */
    static tryJson(str) {
        let result = null;
        try {
            result = JSON.parse( String(str || '') );
        } catch (e) {
            result = null;
        }
        return result;
    }

    /**
     *
     * @param string
     * @param nohtml
     * @returns {string}
     */
    static wrap_span(string, nohtml) {
        return nohtml ? string : '<span style="color: #00F;font-weight: bold;">' + string + '</span>';
    }

    /**
     *
     * @param input
     * @param allowed
     * @returns {*}
     */
    static strip_tags(input, allowed) {
        if (!input) {
            return '';
        }

        allowed = allowed ? allowed : '<br><a><img><span><p><ul><ol><li><b><i><s><sub><sup><u><span><h1><h2><h3><h4><h5><h6>';
        // making sure the allowed arg is a string containing only tags in lowercase (<a><b><c>)
        allowed = (((allowed || "") + "").toLowerCase().match(/<[a-z][a-z0-9]*>/g) || []).join('');

        let tags = /<\/?([a-z][a-z0-9]*)\b[^>]*>/gi,
            commentsAndPhpTags = /<!--[\s\S]*?-->|<\?(?:php)?[\s\S]*?\?>/gi;

        return String(input).replace(commentsAndPhpTags, '').replace(tags, function ($0, $1) {
            return allowed.indexOf('<' + $1.toLowerCase() + '>') > -1 ? $0 : '';
        });
    }

    /**
     *
     * @param input
     * @returns {*}
     */
    static strip_danger_tags(input) {
        if (!input) {
            return '';
        }
        let dangerTags = /<!--[\s\S]*?-->|<\?(?:php)?[\s\S]*?\?>|<\/?script\b[^>]*>/gi;
        return String(input).replace(dangerTags, '');
    }

    /**
     *
     * @param tableRow
     * @param field
     * @param val
     * @returns {*|{}}
     */
    static rcObj(tableRow, field, val) {
        let obj = tableRow['_rc_' + field] || {};
        return val ? (obj[val] || obj[to_float(val)] || {}) : obj;
    }

    /**
     *
     * @param tablemeta
     * @returns {{}}
     */
    static emptyRow(tablemeta) {
        let objectForAdd = {};
        for (let i in tablemeta._fields) {
            if (tablemeta._fields[i].f_type === 'Boolean') {
                objectForAdd[tablemeta._fields[i].field] = tablemeta._fields[i].f_default == '1';
            } else {
                objectForAdd[tablemeta._fields[i].field] = null;
            }
        }
        objectForAdd._temp_id = uuidv4();
        objectForAdd.row_hash = uuidv4();
        return objectForAdd;
    }

    /**
     *
     * @param {object} metaTable
     * @param {object} tableRow
     * @returns {boolean}
     */
    static managerOfRow(metaTable, tableRow) {
        let ugroups = metaTable._current_right ? (metaTable._current_right._manager_of_ugroups || []) : [];
        if (ugroups && ugroups.length) {
            let found = false;
            _.each(metaTable._fields, (header) => {
                if (header.f_type === 'User' && tableRow[header.field]) {
                    let single = ugroups.indexOf(tableRow[header.field]) > -1;
                    let multiple = false;
                    _.each(ugroups, (ug) => {
                        multiple = multiple || String(tableRow[header.field]).indexOf('"'+ug+'"') > -1;
                    });
                    found = found || single || multiple;
                }
            });
            return found;
        } else {
            return false;
        }
    }

    /**
     *
     * @param tableHeader
     * @param showValue
     * @param unitConv
     * @returns {string}
     */
    static currencySign(tableHeader, showValue, unitConv) {
        let unit_dis = this.rcObj(tableHeader, 'unit_display', tableHeader.unit_display).show_val || tableHeader.unit_display;
        unit_dis = unitConv ? unit_dis : '';
        let unit = this.rcObj(tableHeader, 'unit', tableHeader.unit).show_val || tableHeader.unit;

        let cur = String(unit_dis || unit).toUpperCase();
        let cur_data = _.find(window.vueRootApp.settingsMeta.country_data, {currency_code: cur});
        cur = cur_data ? cur_data.currency_symbol : '';

        //let res = String(showValue).replace(/[^\d\.]/gi, '');
        return cur + showValue;//res;
    }

    /**
     *
     * @param tableHeader
     * @param tableRow
     * @param unitConv
     * @returns {string}
     */
    static showFullHtml(tableHeader, tableRow, unitConv) {
        let cellVal = tableRow[tableHeader.field];
        let fullHtml = '';
        if (['M-Select', 'M-Search', 'M-SS'].indexOf(tableHeader.input_type) > -1) {
            let arr = [];
            _.each(this.parseMsel(cellVal), (el) => {
                arr.push(this.showhtml(tableHeader, tableRow, el, unitConv));
            });
            fullHtml = arr.join(', ');
        } else {
            fullHtml = this.showhtml(tableHeader, tableRow, cellVal, unitConv);
        }
        return fullHtml;
    }

    /**
     *
     * @param tableHeader
     * @param tableRow
     * @param htmlValue
     * @param unitConv
     * @returns {*}
     */
    static showhtml(tableHeader, tableRow, htmlValue, unitConv) {
        let val = '';
        let ddl = this.rcObj(tableRow, tableHeader.field, '_is_ddlid');

        if (Object.keys(ddl).length) {
            ddl = this.rcObj(tableRow, tableHeader.field, htmlValue);
            val = ddl.show_val !== 'null' ? ddl.show_val || htmlValue : '';
            val = unitConv ? UnitConversion.doConv(tableHeader, val, false) : val;
        } else {
            val = htmlValue;
        }

        //show zeros for input types
        if (tableRow._is_def_cell) {
            val = to_standard_val(val);
        } else {
            val = to_standard_val(val) || (tableHeader.show_zeros ? '0' : '');
        }

        //format value
        val = this.format(tableHeader, val, tableRow.__no_html);

        //for DDL is/val on 'currency'
        if (tableHeader.f_type == 'Currency' && val) {
            val = this.currencySign(tableHeader, val, unitConv);
        }

        return this.strip_tags( String(val) );
    }

    /**
     *
     * @param seconds
     * @param header
     * @param nohtml
     * @returns {string}
     */
    static second2duration(seconds, header, nohtml) {
        seconds = to_float(seconds);
        let minus = seconds < 0;
        seconds = Math.abs(seconds);
        let fformat = header.f_format ? String(header.f_format).toLowerCase() : 's';
        let result = [];
        let variants = {wk: 604800, w: 604800, d: 86400, h: 3600, m: 60, s: 1};
        _.each(variants, (val, key) => {
            if (fformat.indexOf(key) > -1) {
                let tmp = parseInt(seconds / val);
                let wrapkey = this.wrap_span(key, nohtml);
                tmp ? result.push(tmp + wrapkey) : '';
                seconds -= tmp * val;
            }
        });
        return minus
            ? this.wrap_span('-', nohtml) + ' (' + result.join(' ') + ')'
            : result.join(' ');
    }

    /**
     *
     * @param string
     * @returns {number}
     */
    static duration2second(string) {
        let variants = {wk: 604800, w: 604800, d: 86400, h: 3600, m: 60, s: 1};
        let result = 0;
        let parts = String(string).replace(/<[^>]*>/gi, ''); //remove tags

        //change fractions: 3/5 => 0.6
        parts = String(parts).replaceAll(/([\.\d]+)\/([\.\d]+)/gi, (string, first, second) => {
            return Number(first / second);
        });

        let minus = String(parts).indexOf('-') > -1;

        parts = parts.replace(/[^\.\dwkdhms]/gi, '') //remove not available symbols
            .replace(/(wk|d|h|m|s|w)/gi, '$1,') //prepare for splitting
            .split(',');

        _.each(parts, (el) => {
            let val = to_float(_.trim(el));
            let key = _.trim(el).replace(/[^wkdhms]/gi, '').toLowerCase();
            result += to_float(val) * to_float(variants[key]);
        });
        return minus ? -result : +result;
    }

    /**
     *
     * @param str
     * @returns {*}
     */
    static nl2br(str) {
        return String(str).replace(/([^>])\n/g, '$1<br/>');
    }

    /**
     *
     * @param tableHeader
     * @param value
     * @param no_html
     * @returns {*}
     */
    static format(tableHeader, value, no_html) {

        //remove '0000-00-00'
        if (value === '0000-00-00' || value === '0000-00-00 00:00:00') {
            value = '';
        }

        //remove extra numbers
        if (isValue(value)) {

            //DECIMALS
            if (['Decimal', 'Currency', 'Percentage', 'Progress Bar'].indexOf(tableHeader.f_type) > -1) {

                value = to_float(value);
                if (tableHeader.f_format) {

                    //apply F-Format
                    let fformat = String(tableHeader.f_format).toLowerCase();
                    fformat = fformat.split('-');
                    let decim = isNaN(fformat[1]) ? 2 : to_float(fformat[1]);
                    switch (fformat[0]) {
                        case 'float':
                            value = Number(value).toFixed(decim);
                            break;
                        case 'comma':
                            value = Number( Number(value).toFixed(decim) ).toLocaleString();
                            break;
                        case 'scientific':
                            value = this._get_scient_val(value, decim);
                            break;
                    }

                } else {

                    let deci_part = _.last(String(value).split('.'));
                    let precis0 = deci_part ? deci_part.indexOf('00') : -1;
                    let precis9 = deci_part ? deci_part.indexOf('99') : -1;
                    //get min found precision
                    let decimal = precis0 > -1
                        ? (precis9 > -1 ? Math.min(precis0, precis9) : precis0)
                        : (precis9 > -1 ? precis9 : -1);
                    //apply if can
                    if (decimal > -1) {
                        value = Number(value).toFixed(decimal);
                    }

                }

            }
            if (tableHeader.f_type === 'Percentage') {
                let l = String(value).length;
                value = (String(value).charAt(l) !== '%' ? value + '%' : value);
            }

            //DATE
            if (tableHeader.f_type === 'Date') {
                let fformat = String(tableHeader.f_format).toLowerCase();
                switch (fformat) {
                    case 'mm-dd-yyyy':
                        value = moment(value).format('MM-DD-YYYY');
                        break;
                    case 'm-d-yyy':
                        value = moment(value).format('M-D-Y');
                        break;
                    case 'yyyy-mm-dd':
                        value = moment(value).format('YYYY-MM-DD');
                        break;
                    case 'yyy-m-d':
                        value = moment(value).format('Y-M-D');
                        break;
                    case 'dd-mm-yyyy':
                        value = moment(value).format('DD-MM-YYYY');
                        break;
                    case 'd-m-yyy':
                        value = moment(value).format('M-D-Y');
                        break;
                    case 'month d, yr':
                        value = moment(value).format('MMMM D, Y');
                        break;
                    case 'mon. d, yr':
                        value = moment(value).format('MMM. D, Y');
                        break;
                }
            }
            //DATE-TIME
            if (['Date Time'].indexOf(tableHeader.f_type) > -1) {
                value = this.convertToLocal(value, window.vueRootApp.user.timezone);
            }
            //TIME
            if (['Time'].indexOf(tableHeader.f_type) > -1) {
                value = this.timeToLocal(value, window.vueRootApp.user.timezone, 'HH:mm:ss');
            }

            //DURATION
            if (tableHeader.f_type === 'Duration') {
                value = this.second2duration(value, tableHeader, no_html);
            }

            //STRING
            if (tableHeader.f_type === 'String') {
                value = this.nl2br(value);
            }
        }

        //remove 'null' and 'undefined'
        if (value === null || value === undefined || value === 'null') {
            value = '';
        }

        return value;
    }

    /**
     *
     * @param val
     * @param precis
     * @returns {string}
     * @private
     */
    static _get_scient_val(val, precis) {
        if (-1 < val && val < 1) {
            let len = String(val).replace('-', '').length - 2;
            let vl = Number( Math.pow(10, len) * val ).toPrecision(precis+1);
            return vl.replace(/\+/gi, '-');
        } else {
            if (Number(val).toPrecision(precis).match(/e/gi)) {
                return Number(val).toPrecision(precis+1);
            } else {
                let add = String(val).indexOf('.');
                return Number(val).toPrecision(precis+add);
            }
        }
    }

    /**
     *
     * @param tableHeader
     * @param value
     * @param no_convert
     * @returns {*}
     */
    static getEditValue(tableHeader, value, no_convert) {
        //activated MultiSelect
        if (this.isMSELorArray(tableHeader.input_type)) {
            return this.parseMsel(value);
        }

        return UnitConversion.doConv(tableHeader, value);
    }

    /**
     *
     * @param tableHeader
     * @param value
     * @returns string
     */
    static applySetMutator(tableHeader, value) {
        //Numbers
        if (['Currency'].indexOf(tableHeader.f_type) > -1 && value) {
            value = String(value).replace(/[^\d\.]/gi, '');
        }
        if (['Percentage'].indexOf(tableHeader.f_type) > -1 && value) {
            value = String(value).replace(/%/gi, '');
        }
        if (['Decimal','Currency','Percentage'].indexOf(tableHeader.f_type) > -1 && value) {
            value = String(value).replace(/,/gi, '');
        }

        //convert data to UTC
        if (['Date Time'].indexOf(tableHeader.f_type) > -1 && value) {
            value = this.convertToUTC(value, window.vueRootApp.user.timezone, tableHeader.f_type);
        }
        if (['Time'].indexOf(tableHeader.f_type) > -1 && value) {
            value = this.timeToUTC(value, window.vueRootApp.user.timezone, 'HH:mm:ss');
        }

        //Activated MultiSelect
        if (this.isMSEL(tableHeader.input_type)) {
            return (Array.isArray(value) ? JSON.stringify(value) : value);
        }

        return UnitConversion.doConv(tableHeader, value, true);
    }



    //DATE CONVERTERS

    /**
     *
     * @param datetime
     * @param roundup
     */
    static dateTimeasDate(datetime, roundup) {
        return moment(datetime).add(roundup || 0, 'day').format( this.dateFormat() );
    }

    /**
     *
     * @param date
     * @param timezone
     * @param f_type
     * @returns {string}
     */
    static convertToLocal(date, timezone, f_type = 'Date Time') {
        let tz = f_type === 'Date Time' ? timezone : null;
        let d_format = f_type === 'Date Time' ? this.dateTimeFormat() : this.dateFormat();

        let testDateUtc = moment.utc(date);
        let localDate = (tz ? testDateUtc.tz(tz) : testDateUtc);
        return localDate.isValid() ? localDate.format(d_format) : '';
    }

    /**
     *
     * @param date
     * @param timezone
     * @param f_type
     * @returns {string}
     */
    static convertToUTC(date, timezone, f_type = 'Date Time') {
        let tz = f_type === 'Date Time' ? timezone : null;
        let d_format = f_type === 'Date Time' ? this.dateTimeFormat() : this.dateFormat();

        let localDate = (tz ? moment.tz(date, tz) : moment.utc(date));
        let testDateUtc = localDate.utc();
        return testDateUtc.isValid() ? testDateUtc.format(d_format) : '';
    }

    /**
     *
     * @param time
     * @param timezone
     * @param format
     * @returns {string}
     */
    static timeToLocal(time, timezone, format) {
        format = format || 'HH:mm';
        let date_str = moment().format('YYYY-MM-DD')+' '+time;
        let testDateUtc = moment.utc(date_str);
        let localDate = (timezone ? testDateUtc.tz(timezone) : testDateUtc);
        return localDate.isValid() ? localDate.format(format) : '';
    }

    /**
     *
     * @param time
     * @param timezone
     * @param format
     * @returns {string}
     */
    static timeToUTC(time, timezone, format) {
        format = format || 'HH:mm';
        let date_str = moment().format('YYYY-MM-DD')+' '+time;
        let localDate = (timezone ? moment.tz(date_str, timezone) : moment.utc(date_str));
        let testDateUtc = localDate.utc();
        return testDateUtc.isValid() ? testDateUtc.format(format) : '';
    }



    //select and m-select

    /**
     *
     * @param input_type
     * @returns {boolean}
     */
    static hasStype(input_type) {
        return ['S-Search','S-SS','M-Search','M-SS'].indexOf(input_type) > -1;
    }

    /**
     *
     * @param input_type
     * @returns {boolean}
     */
    static hasDsearch(input_type) {
        return ['S-Search','M-Search'].indexOf(input_type) > -1;
    }

    /**
     *
     * @param input_type
     * @returns {boolean}
     */
    static isMSEL(input_type) {
        return ['M-Select','M-Search','M-SS'].indexOf(input_type) > -1;
    }

    /**
     *
     * @param input_type
     * @returns {boolean}
     */
    static isMSELorArray(input_type) {
        return ['M-Select','M-Search','M-SS','Mirror'].indexOf(input_type) > -1;
    }

    /**
     *
     * @param input_type
     * @returns {boolean}
     */
    static issel(input_type) {
        return ['S-Select','S-Search','S-SS','M-Select','M-Search','M-SS'].indexOf(input_type) > -1;
    }



    //OTHERS

    /**
     *
     * @param obj
     * @returns {number}
     */
    static lastKey(obj) {
        let keys = Object.keys(obj).sort();
        let last = keys.length ? _.last(keys) : 0;
        return parseInt( last ) + 1;
    }


    /**
     *
     * @param $menutree
     * @param $id
     * @param $type
     * @returns {*}
     */
    static findInTree($menutree, $id, $type) {
        let $res = null;
        _.each($menutree, ($node) => {
            if ($node['children'] && $node['children'].length) {
                $res = $res || this.findInTree($node['children'], $id, $type);
            }

            if ($node['li_attr']['data-id'] == $id && $node['li_attr']['data-type'] == $type) {
                $res = $res || $node;
            }
        });
        return $res;
    }

    /**
     *
     * @param target
     * @param source
     */
    static assignProps(target, source) {
        _.each(source, (val,prop) => {
            target[prop] = val;
        });
    }

    /**
     *
     * @param table_id
     * @param ref_cond_id
     * @param for_list_view
     * @returns {{user_id: (*|null), ref_cond_id: *, table_id: *, special_params: {_user_id: null, view_hash: string, edited_view_hash: (*|string), is_folder_view: (*|string), dcr_hash: (*|string), for_list_view: boolean}}}
     */
    static tableMetaRequest(table_id, ref_cond_id, for_list_view) {
        return {
            table_id: table_id,
            ref_cond_id: ref_cond_id,
            user_id: !window.vueRootApp.user.see_view ? window.vueRootApp.user.id : null,
            special_params: this.specialParams(for_list_view),
        };
    }

    /**
     *
     * @param for_list_view
     * @param dcr_linked_id
     * @returns {{view_hash: (*|string), dcr_linked_id, dcr_hash: string, _user_id: (*|null), is_folder_view: (*|string), for_list_view: boolean}}
     */
    static specialParams(for_list_view, dcr_linked_id) {
        return {
            _user_id: !window.vueRootApp.user.see_view ? window.vueRootApp.user.id : null,
            view_hash: window.vueRootApp.user.view_hash || '',
            is_folder_view: window.vueRootApp.user._is_folder_view || '',
            dcr_hash: window.vueRootApp.user._dcr_hash || '',
            dcr_uid: window.vueRootApp.user._dcr_uid || '',
            for_list_view: !!for_list_view,
            dcr_linked_id: dcr_linked_id,
        };
    }

    /**
     *
     * @param tableMeta
     * @returns {Array}
     */
    static forbiddenCustomizables(tableMeta) {
        let forbid = [];
        if (tableMeta._current_right) {
            _.each(tableMeta._current_right.forbidden_col_settings, (db_name) => {
                forbid.push(db_name);
            });
        }
        return forbid;
    }

    /**
     *
     * @param obj
     */
    static clearObject(obj) {
        _.each(obj, (val,key) => {
            if (key[0] === '_') {
                obj[key] = [];
            } else {
                obj[key] = '';
            }
        });
    }

    /**
     *
     * @param background
     * @returns {string}
     */
    static smartTextColorOnBg(background) {
        // http://www.w3.org/TR/AERT#color-contrast
        let bgclr = String(background) && String(background)[0] === '#' && String(background).length === 7 ? String(background) : '#FFFFFF';
        let r = parseInt(bgclr.substr(1, 2), 16);
        let g = parseInt(bgclr.substr(3, 2), 16);
        let b = parseInt(bgclr.substr(5, 2), 16);
        const brightness = Math.round(((parseInt(r) * 299) +
            (parseInt(g) * 587) +
            (parseInt(b) * 114)) / 1000);
        return (brightness > 125) ? 'black' : 'white';
    }

    /**
     *
     * @param name
     * @returns {string}
     */
    static safeTableName(name)
    {
        return String(name || '').replace(/[^\w\d\(\)\-\.,_ ]/gi, '');
    }
}