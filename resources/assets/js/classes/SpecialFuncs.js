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
        el.value = '';
        el.focus();
        document.execCommand("paste");
    }

    /**
     *
     * @returns {string}
     */
    static clipboardGetStr() {
        let el = document.getElementById('for_paste_get');
        return el.value || '';
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
            let new_fl = { ...old, ...fltr };
            filters_new.push(new_fl);
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
        if (Array.isArray(val)) {
            return val;
        }

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
     * @param color
     * @returns {string}
     */
    static wrap_span(string, nohtml, color = '#00F') {
        return nohtml ? string : '<span style="color: '+color+';font-weight: bold;">' + string + '</span>';
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
        return to_standard_val(val, true)
            ? (obj[val] || obj[to_float(val)] || {})
            : obj;
    }

    /**
     *
     * @param tablemeta
     * @param additionalDefaultFields
     * @returns {{}}
     */
    static emptyRow(tablemeta, additionalDefaultFields) {
        let objectForAdd = {};
        for (let i in tablemeta._fields) {
            if (tablemeta._fields[i].f_type === 'Boolean') {
                objectForAdd[tablemeta._fields[i].field] = tablemeta._fields[i].f_default == '1' ? 1 : 0;
            } else {
                objectForAdd[tablemeta._fields[i].field] = tablemeta._fields[i].f_default || null;
            }
        }
        _.each((additionalDefaultFields || []), (adFld) => {
            let tbf = _.find(tablemeta._fields, {id: Number(adFld.table_field_id)});
            if (tbf && to_standard_val(adFld.default, true).length) {
                objectForAdd[tbf.field] = adFld.default;
            }
        });
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
     * @param tableMeta
     * @returns {string}
     */
    static currencySign(tableHeader, showValue, tableMeta) {
        let unit_dis = this.rcObj(tableHeader, 'unit_display', tableHeader.unit_display).show_val || tableHeader.unit_display;
        unit_dis = tableMeta && tableMeta.unit_conv_is_active ? unit_dis : '';
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
     * @param tableMeta
     * @param asArray
     * @returns {string}
     */
    static showFullHtml(tableHeader, tableRow, tableMeta, asArray = false) {
        let cellVal = tableRow[tableHeader.field];
        let fullHtml = asArray ? [] : '';
        if (['M-Select', 'M-Search', 'M-SS'].indexOf(tableHeader.input_type) > -1) {
            let arr = [];
            _.each(this.parseMsel(cellVal), (el) => {
                arr.push(this.showhtml(tableHeader, tableRow, el, tableMeta));
            });
            fullHtml = asArray ? arr : arr.join(', ');
        } else {
            let html = this.showhtml(tableHeader, tableRow, cellVal, tableMeta);
            fullHtml = asArray ? [html] : html;
        }
        return fullHtml;
    }

    /**
     *
     * @param tableHeader
     * @param tableRow
     * @param htmlValue
     * @param tableMeta
     * @returns {*}
     */
    static showhtml(tableHeader, tableRow, htmlValue, tableMeta) {
        let val = '';
        let ddl = this.rcObj(tableRow, tableHeader.field, '_is_ddlid');

        if (Object.keys(ddl).length) {
            ddl = this.rcObj(tableRow, tableHeader.field, htmlValue);
            val = ddl.show_val !== 'null' ? ddl.show_val || htmlValue : '';
            val = tableMeta && tableMeta.unit_conv_is_active
                ? UnitConversion.doConv(tableHeader, val, false)
                : val;
        } else {
            val = htmlValue;
        }

        //show zeros for input types
        if (tableRow._is_def_cell) {
            val = to_standard_val(val);
        } else {
            val = UnitConversion.applyShowZero( to_standard_val(val), tableHeader );
        }

        //format value
        val = this.formatWithSigns(tableHeader, val, tableRow.__no_html, tableMeta, tableRow);
        val = this.strip_tags( String(val) );
        val = unicodeToChar(val);

        //asterisks if needed
        if (tableHeader.fill_by_asterisk) {
            val = _.pad('', String(val).length, '*');
        }

        return val;
    }

    /**
     *
     * @param tableHeader
     * @param val
     * @param no_html
     * @param tableMeta
     * @param tableRow
     * @returns {string}
     */
    static formatWithSigns(tableHeader, val, no_html, tableMeta, tableRow) {
        //format value
        val = this.format(tableHeader, val, no_html, tableRow, tableMeta);

        //for DDL is/val on 'currency'
        if (tableHeader.f_type == 'Currency' && val) {
            val = this.currencySign(tableHeader, val, tableMeta);
        }
        return val;
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
            ? this.wrap_span('-', nohtml, '#F00') + ' (' + result.join(' ') + ')'
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
     * @param str
     * @returns {*}
     */
    static space2nbsp(str) {
        return String(str).replace(/  /g, ' &nbsp;');
    }

    /**
     *
     * @param tableHeader
     * @param value
     * @param no_html
     * @param tableRow
     * @param tableMeta
     * @returns {*}
     */
    static format(tableHeader, value, no_html, tableRow, tableMeta) {
        tableMeta = tableMeta || {};

        //remove '0000-00-00'
        if (value === '0000-00-00' || value === '0000-00-00 00:00:00') {
            value = '';
        }

        //remove extra numbers
        if (isValue(value)) {

            //DECIMALS
            if (['Decimal', 'Currency', 'Percentage', 'Progress Bar'].indexOf(tableHeader.f_type) > -1) {

                value = to_float(value);
                if (tableHeader.f_type === 'Percentage') {
                    value *= 100;
                }

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
            if (tableHeader.f_type === 'Date' && !this.isSpecialVar(value)) {
                let fformat = String(tableHeader.f_format).toLowerCase();
                switch (fformat) {
                    case 'mm-dd-yyyy':
                    case 'mm-dd-yyyy-':
                    case 'mm-dd-yyyy/':
                        value = String(fformat).slice(-1) == '/' ? moment(value).format('MM/DD/YYYY') : moment(value).format('MM-DD-YYYY');
                        break;
                    case 'm-d-yyy':
                    case 'm-d-yyy-':
                    case 'm-d-yyy/':
                        value = String(fformat).slice(-1) == '/' ? moment(value).format('M/D/Y') : moment(value).format('M-D-Y');
                        break;
                    case 'yyyy-mm-dd':
                    case 'yyyy-mm-dd-':
                    case 'yyyy-mm-dd/':
                        value = String(fformat).slice(-1) == '/' ? moment(value).format('YYYY/MM/DD') : moment(value).format('YYYY-MM-DD');
                        break;
                    case 'yyy-m-d':
                    case 'yyy-m-d-':
                    case 'yyy-m-d/':
                        value = String(fformat).slice(-1) == '/' ? moment(value).format('Y/M/D') : moment(value).format('Y-M-D');
                        break;
                    case 'dd-mm-yyyy':
                    case 'dd-mm-yyyy-':
                    case 'dd-mm-yyyy/':
                        value = String(fformat).slice(-1) == '/' ? moment(value).format('DD/MM/YYYY') : moment(value).format('DD-MM-YYYY');
                        break;
                    case 'd-m-yyy':
                    case 'd-m-yyy-':
                    case 'd-m-yyy/':
                        value = String(fformat).slice(-1) == '/' ? moment(value).format('M/D/Y') : moment(value).format('M-D-Y');
                        break;
                    case 'month d, yr':
                    case 'month d, yr-':
                    case 'month d, yr/':
                        value = moment(value).format('MMMM D, Y');
                        break;
                    case 'mon. d, yr':
                    case 'mon. d, yr-':
                    case 'mon. d, yr/':
                        value = moment(value).format('MMM. D, Y');
                        break;
                }
            }
            //DATE-TIME
            if (tableHeader.f_type === 'Date Time') {
                value = this.convertToLocal(value, window.vueRootApp.user.timezone);
            }

            //DURATION
            if (tableHeader.f_type === 'Duration') {
                value = this.second2duration(value, tableHeader, no_html);
            }

            //STRING
            if (tableHeader.f_type === 'String') {
                value = this.nl2br(value);
            }

            //USER
            if (tableHeader.f_type === 'User' && !no_html && tableRow) {
                value = window.vueRootApp.getUserOneStr(value, tableRow, tableHeader, tableMeta._owner_settings);
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
            value = value / 100;
        }
        if (['Decimal','Currency','Percentage'].indexOf(tableHeader.f_type) > -1 && value) {
            value = String(value).replace(/,/gi, '');
        }
        if (['Gradient Color'].indexOf(tableHeader.f_type) > -1 && value) {
            value = value > 100 ? parseFloat(value) / 100 : parseFloat(value);
            value = value > 1 ? 1 : value;
            value = value < 0 ? 0 : value;
        }

        //convert data to UTC
        if (['Date Time'].indexOf(tableHeader.f_type) > -1 && value) {
            if (this.isSpecialVar(value)) {
                //special variables: {{Today}} etc.
            } else {
                value = this.convertToUTC(value, window.vueRootApp.user.timezone, tableHeader.f_type);
            }
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
     * @param date
     * @returns {boolean}
     */
    static isSpecialVar(date)
    {
        return String(date).substr(0,2) === '{{';
    }

    /**
     *
     * @param datetime
     * @param roundup
     * @param timeformat
     * @returns {*}
     */
    static dateTimeasDate(datetime, roundup, timeformat) {
        return moment.utc(datetime)
            .add(roundup || 0, 'day')
            .format( timeformat ? this.dateTimeFormat() : this.dateFormat() );
    }

    /**
     *
     * @param date
     * @param timezone
     * @param f_type
     * @returns {string}
     */
    static convertToLocal(date, timezone, f_type = 'Date Time') {
        if (this.isSpecialVar(date)) {
            return date;
        }
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
        if (this.isSpecialVar(date)) {
            return date;
        }
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

    /**
     *
     * @param input_type
     * @returns {boolean}
     */
    static isInputOrSel(input_type) {
        return ['Input','S-Select','S-Search','S-SS','M-Select','M-Search','M-SS'].indexOf(input_type) > -1;
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
        let user = window.vueRootApp ? window.vueRootApp.user : window.rootUser;
        return {
            table_id: table_id,
            ref_cond_id: ref_cond_id,
            user_id: !user.see_view ? user.id : null,
            special_params: this.specialParams(for_list_view),
        };
    }

    /**
     *
     * @param for_list_view
     * @param dcr_linked_id
     * @param dcr_parent_row
     * @returns {{view_hash: (*|string), view_filtering_row: string, dcr_linked_id, dcr_uid: (*|string), dcr_parent_row, dcr_hash: (*|string), _user_id: (*|null), is_folder_view: (*|string), for_list_view: boolean}}
     */
    static specialParams(for_list_view, dcr_linked_id, dcr_parent_row) {
        let user = window.vueRootApp ? window.vueRootApp.user : window.rootUser;
        return {
            _user_id: !user.see_view ? user.id : null,
            view_filtering_row: user.view_filtering_row || '',
            view_hash: user.view_hash || '',
            is_folder_view: user._is_folder_view || '',
            dcr_hash: user._dcr_hash || '',
            dcr_uid: user._dcr_uid || '',
            for_list_view: !!for_list_view,
            dcr_linked_id: dcr_linked_id,
            dcr_parent_row: dcr_parent_row,
        };
    }

    /**
     *
     * @param {string|number} data_range // Examples: -2, 0, 5, '-1', 'rg:2', 'sf:4'
     * @param table_id
     * @param request_params
     * @param additionals
     * @returns {*}
     */
    static dataRangeRequestParams(data_range, table_id, request_params, additionals) {
        let grp = '';
        let rangeVal = data_range;
        if (String(data_range).indexOf(':') > -1) {
            grp = _.first( String(data_range).split(':') );
            rangeVal = _.last( String(data_range).split(':') );
        }
        rangeVal = parseInt(rangeVal);

        let params = {};
        if (rangeVal > 0) { //RowGroup or SavedFilter
            params = this.tableMetaRequest(table_id);
            if (grp === 'sf') {
                params.selected_saved_filter_id = rangeVal;
            } else {
                params.selected_row_group_id = rangeVal;
            }
            params.page = 1;
            params.rows_per_page = 0;
        }
        else if (!rangeVal) { //Current Page
            params = _.cloneDeep(request_params);
            params.special_params.for_list_view = false;
        }
        else if (rangeVal === -1) { //All Pages (with filters)
            params = _.cloneDeep(request_params);
            params.special_params.for_list_view = false;
            params.page = 1;
            params.rows_per_page = 0;
        }
        else { //Full Table
            params = this.tableMetaRequest(table_id);
            params.page = 1;
            params.rows_per_page = 0;
        }
        params = this._applyAdditionals(params, additionals);
        return params;
    }

    /**
     *
     * @param params
     * @param additionals
     * @returns {*}
     * @private
     */
    static _applyAdditionals(params, additionals) {
        if (additionals && additionals['sort']) {
            params['sort'] = additionals['sort'];
        }
        return params;
    }

    /**
     *
     * @param tableMeta
     * @returns {Array}
     */
    static forbiddenCustomizables(tableMeta) {
        let forbid = [];
        if (tableMeta._current_right && !tableMeta._is_owner) {
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
        let r = parseInt(bgclr.substring(1, 3), 16);
        let g = parseInt(bgclr.substring(3, 5), 16);
        let b = parseInt(bgclr.substring(5, 7), 16);
        const brightness = Math.round(((parseInt(r) * 299) +
            (parseInt(g) * 587) +
            (parseInt(b) * 114)) / 1000);
        return (brightness > 125) ? 'black' : 'white';
    }

    /**
     *
     * @param tableField
     * @param delta
     * @returns {string}
     */
    static getGradientColor(tableField, delta) {
        let gradients = String(tableField.f_format).split('-');
        if (!delta) {
            return gradients[0];
        }

        let r1 = parseInt(gradients[0].substring(1, 3), 16);
        let g1 = parseInt(gradients[0].substring(3, 5), 16);
        let b1 = parseInt(gradients[0].substring(5, 7), 16);

        let r2 = parseInt(gradients[1].substring(1, 3), 16);
        let g2 = parseInt(gradients[1].substring(3, 5), 16);
        let b2 = parseInt(gradients[1].substring(5, 7), 16);

        let r = parseInt(r1 + ((r2 - r1) * delta));
        let g = parseInt(g1 + ((g2 - g1) * delta));
        let b = parseInt(b1 + ((b2 - b1) * delta));

        return '#' + ((1 << 24) + (r << 16) + (g << 8) + b).toString(16).slice(1);
    }

    /**
     *
     * @param name
     * @returns {string}
     */
    static safeTableName(name)
    {
        return String(name || '').replace(newRegexp('[^\\p{L}\\d\\(\\)\\-\\.,_ ]'), '');
    }

    /**
     *
     * @param obj
     * @param key
     * @param datas
     */
    static setRowsSaveNewstatus(obj, key, datas) {
        let new_row_ids = _.map(
            _.filter(obj[key], {_is_new: 1}),
            (row) => { return row.id; }
        );
        obj[key] = datas;
        _.each(obj[key], (row) => {
            if ($.inArray(row.id, new_row_ids) > -1) {
                row._is_new = 1;
            }
        });
    }

    /**
     *
     * @param tableMeta
     * @param user
     * @param needPos
     * @returns {string|string|null|*}
     */
    static filterOnTop(tableMeta, user, needPos) {
        if (user && user.view_all) {
            return needPos ? user.view_all.srv_fltrs_ontop_pos : user.view_all.srv_fltrs_on_top;
        }
        if (tableMeta) {
            return needPos ? tableMeta.filters_ontop_pos : tableMeta.filters_on_top;
        }
        return '';
    }

    /**
     *
     * @param type
     * @param dcrObject
     * @returns {{fontFamily: (*|string), color: (*|null), fontSize: string, lineHeight: string}}
     */
    static fontStyleObj (type, dcrObject) {
        let stl = {
            fontFamily: dcrObject[type+'_font'] || dcrObject[type+'_type'] || 'Raleway, sans-serif',
            fontSize: (dcrObject[type+'_size'] || 14)+'px',
            lineHeight: (to_float(dcrObject[type+'_size'] || 14)*1.1)+'px',
            color: dcrObject[type+'_color'] || null,
        };
        let fonts = window.vueRootApp.parseMsel(dcrObject[type+'_style']);
        _.each(fonts, (f) => {
            (f === 'Italic' ? stl.fontStyle = 'italic' : stl.fontStyle = stl.fontStyle || null);
            (f === 'Bold' ? stl.fontWeight = 'bold' : stl.fontWeight = stl.fontWeight || null);
            (f === 'Strikethrough' ? stl.textDecoration = 'line-through' : stl.textDecoration = stl.textDecoration || null);
            (f === 'Overline' ? stl.textDecoration = 'overline' : stl.textDecoration = stl.textDecoration || null);
            (f === 'Underline' ? stl.textDecoration = 'underline' : stl.textDecoration = stl.textDecoration || null);
        });
        return stl;
    }

    /**
     *
     * @param tableHeader
     * @returns {boolean}
     */
    static defaultVisibility(tableHeader) {
        if (!!tableHeader['is_default_show_in_popup']) {
            return true;
        }

        let obj = window.vueRootApp.is_dcr_page
            ? _.find(window.vueRootApp.dcrPivotFields, {table_field_id: Number(tableHeader.id)}) || {}
            : {};

        return !!obj['fld_popup_shown'];
    }

    /**
     *
     * @param string
     * @param searchArray
     * @param key
     * @returns {*}
     */
    static nameWithIndex(string, searchArray, key) {
        let idx = 1;
        while (
            _.find(searchArray, (item) => { return item[key] == string + (idx > 1 ? idx : ''); })
        ) {
            idx += 1;
        }
        return string + (idx > 1 ? idx : '');
    }

    /**
     *
     * @param fields
     * @param dcrLinkedTables
     * @returns {Dictionary<unknown[]>}
     */
    static getFieldTabs(fields, dcrLinkedTables) {
        let prepared = _.map(fields, (fld) => {
            return {
                group: fld.pop_tab_name || 'Fields',
                field: fld.field,
                pop_tab_order: fld.pop_tab_order,
            };
        });

        let individualTabs = _.filter(dcrLinkedTables || [], (dcrLink) => {
            return dcrLink.placement_tab_name && !dcrLink.position_field_id;
        });
        if (individualTabs && individualTabs.length) {
            let preparedDcrs = _.map(individualTabs, (dcr) => {
                return {
                    group: dcr.placement_tab_name,
                    dcr: dcr,
                    pop_tab_order: dcr.placement_tab_order,
                };
            });
            prepared = _.concat(prepared, preparedDcrs);
        }

        let ordered = _.sortBy(prepared, 'pop_tab_order');
        let tabs = _.groupBy(ordered, 'group');
        _.each(tabs, (tab, key) => {
            tabs[key] = {
                fields: _.filter(_.map(tab, 'field')),
                dcr_lnks: _.filter(_.map(tab, 'dcr')),
            };
        });

        return tabs;
    }
}