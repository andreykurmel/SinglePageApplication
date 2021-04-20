
<script>
    /**
     * Must be present:
     *
     * import SpecialFuncs class
     *
     * spanned: Boolean,
     * all_settings: Object,
     * table_meta: Object,
     * all_variants: Object,
     * show_matrix: Object,
     * sorted_values: Object,
     * about_1: Array,
     * about_2: Array,
     * about_3: Array,
     */
    import {SpecialFuncs} from '../../../../../classes/SpecialFuncs';

    export default {
        data: function () {
            return {};
        },
        computed: {
            pivot_spanned() {
                return this.spanned;
            },
            pos_subtot_is_top() {
                return this.pivot_vert.sub_tot_pos === 'top';
            },
            pos_subtot_bottom() {
                return this.pivot_vert.sub_tot_pos === 'bot';
            },
            front_subtotal() {
                return this.pivot_hor.sub_tot_pos === 'front';
            },
            subtotal_back() {
                return this.pivot_hor.sub_tot_pos === 'back';
            },

            pivot_labels() {
                return this.all_settings.pivot_table.labels;
            },
            vert_widths() {
                return this.all_settings.pivot_table.vert_widths;
            },
            pivot_vert() {
                return this.all_settings.pivot_table.vertical;
            },
            pivot_hor() {
                return this.all_settings.pivot_table.horizontal;
            },
            //always ON
            pivot_hor_l1_sub_total() {
                return true;//this.pivot_hor.l1_sub_total;
            },
            pivot_vert_l1_sub_total() {
                return true;//this.pivot_vert.l1_sub_total;
            },
            //always ON ^^^^^^^
            colWidth() {
                let w = null;
                switch (this.pivot_hor.hor_fields_width) {
                    case 'min': w = 5;
                            break;
                    case 'uniform': w = this.max_cell_len ? (this.max_cell_len*8 + 10) : 30;
                            break;
                    default: w = 5;
                        break;
                }
                return {
                    width: w+'px'
                };
            },
            h_l1_fld() {
                return !!this.pivot_hor.l1_field;
            },
            h_l2_fld() {
                return !!this.pivot_hor.l2_field;
            },
            h_l3_fld() {
                return !!this.pivot_hor.l3_field;
            },
            l1_fld_v() {
                return !!this.pivot_vert.l1_field;
            },
            l2_fld_v() {
                return !!this.pivot_vert.l2_field;
            },
            l3_fld_v() {
                return !!this.pivot_vert.l3_field;
            },
            l4_fld_v() {
                return !!this.pivot_vert.l4_field;
            },
            l5_fld_v() {
                return !!this.pivot_vert.l5_field;
            },
            r_s() {
                let rs = (this.pivot_hor.l1_field ? 2 : 1);
                rs += (this.pivot_hor.l2_field ? 1 : 0);
                rs += (this.pivot_hor.l3_field ? 1 : 0);
                return rs + (this.multi_about > 1 ? 1 : 0);
            },
            c_s() {
                let cs = 1;
                cs += (this.pivot_vert.l2_field ? 1 : 0);
                cs += (this.pivot_vert.l3_field ? 1 : 0);
                cs += (this.pivot_vert.l4_field ? 1 : 0);
                cs += (this.pivot_vert.l5_field ? 1 : 0);
                return cs;
            },
            multi_about() {
                let multi_about = 1;
                if (!this.all_settings.pivot_table.stack_about) {
                    multi_about += (this.about_2 ? 1 : 0);
                    multi_about += (this.about_3 ? 1 : 0);
                }
                return multi_about;
            },
        },
        methods: {
            //SELECT AND SHOW DATA
            filterVals(sorted_vals, level_object, filter_show) {
                let keys = this.dataKeys(level_object);
                if (level_object['__sub_total'] && level_object['__sub_total']['__show']) {
                    keys.push('__sub_total');
                }
                return _.filter(sorted_vals, (k) => {
                    return keys.indexOf(k) > -1;
                });
            },
            dataKeys(resu) {
                return _.filter(Object.keys(resu), (key) => {
                    return this.syskeys.indexOf(key) === -1;
                });
            },
            isSubTot(val) {
                return val === '__sub_total';
            },
            getmatrix(type, l1, l2, l3, l4, l5) {
                let matrix = type === 'hor' ? this.pivot.columns : this.pivot.rows;
                return l1 === undefined ? matrix
                    : l2 === undefined ? matrix[l1]
                        : l3 === undefined ? matrix[l1] && matrix[l1][l2]
                            : l4 === undefined ? matrix[l1] && matrix[l1][l2] && matrix[l1][l2][l3]
                                : l5 === undefined ? matrix[l1] && matrix[l1][l2] && matrix[l1][l2][l3] && matrix[l1][l2][l3][l4]
                                    : matrix[l1] && matrix[l1][l2] && matrix[l1][l2][l3] && matrix[l1][l2][l3][l4] && matrix[l1][l2][l3][l4][l5];
            },
            showVal(type, l1, l2, l3, l4, l5) {
                let el = this.getmatrix(type, l1, l2, l3, l4, l5);
                return el && el.__show;
            },

            //SPAN ROW HEADS
            isFirstCell(i1, i2, i3, i4, i5, lvl) {
                let chk2 = 0; let chk3 = 0; let chk4 = 0; let chk5 = 0;
                switch (lvl) {
                    case 1: return i2 === chk2 && i3 === chk3 && i4 === chk4 && i5 === chk5;
                    case 2: return i3 === chk3 && i4 === chk4 && i5 === chk5;
                    case 3: return i4 === chk4 && i5 === chk5;
                    case 4: return i5 === chk5;
                }
            },

            //SPANS
            lenForSpan(type, l1, l2, l3, l4, l5) {
                let el = this.getmatrix(type, l1, l2, l3, l4, l5);
                return el && el.__span_len;
            },
            spanLen(l1, l2) {
                let sys_len = l1 === undefined ? this.lenForSpan('hor')
                    : l2 === undefined ? this.lenForSpan('hor', l1)
                        : this.lenForSpan('hor', l1, l2);
                return this.spanned ? sys_len*this.multi_about : 1;
            },
            noSpanLen(l1, l2) {
                let sys_len = l1 === undefined ? this.lenForSpan('hor')
                    : l2 === undefined ? this.lenForSpan('hor', l1)
                        : this.lenForSpan('hor', l1, l2);
                return this.spanned ? 1 : sys_len*this.multi_about;
            },

            //VALUES DRAWING
            findValue(about, v_l1, v_l2, v_l3, v_l4, v_l5, l1_h, l2_h, l3_h) {
                return this.subTotalCell(about, 'vert_l1', v_l1, 'vert_l2', v_l2, 'vert_l3', v_l3, 'vert_l4', v_l4, 'vert_l5', v_l5, 'hor_l1', l1_h, 'hor_l2', l2_h, 'hor_l3', l3_h);
            },
            convertCell(about, val) {
                about = about > 1 ? 'about_'+about : 'about';
                let abo = this.all_settings.pivot_table[about] || {};
                let y_axis_col = _.find(this.table_meta._fields, {field: abo.field});
                return SpecialFuncs.showhtml(y_axis_col, {}, val);
            },

            //SUM CALCS
            subTotalApp(about, type1, key1, type2, key2, type3, key3, type4, key4, type5, key5, type6, key6, type7, key7, type8, key8) {
                return to_float( this.justSubTotal(about, type1, key1, type2, key2, type3, key3, type4, key4, type5, key5, type6, key6, type7, key7, type8, key8) );
            },
            subTotalCell(about, type1, key1, type2, key2, type3, key3, type4, key4, type5, key5, type6, key6, type7, key7, type8, key8) {
                return this.convertCell( about, this.justSubTotal(about, type1, key1, type2, key2, type3, key3, type4, key4, type5, key5, type6, key6, type7, key7, type8, key8) );
            },
            justSubTotal(about, type1, key1, type2, key2, type3, key3, type4, key4, type5, key5, type6, key6, type7, key7, type8, key8) {
                let sum = 0;
                let data = about ? this['about_'+about] || this.about_1 : this.about_1;
                _.each(data, (el) => {
                    if (
                        this.cellIsActive(el)
                        && (!type1 || key1 === undefined || this.isSubTot(key1) || el[type1] == key1)
                        && (!type2 || key2 === undefined || this.isSubTot(key2) || el[type2] == key2)
                        && (!type3 || key3 === undefined || this.isSubTot(key3) || el[type3] == key3)
                        && (!type4 || key4 === undefined || this.isSubTot(key4) || el[type4] == key4)
                        && (!type5 || key5 === undefined || this.isSubTot(key5) || el[type5] == key5)
                        && (!type6 || key6 === undefined || this.isSubTot(key6) || el[type6] == key6)
                        && (!type7 || key7 === undefined || this.isSubTot(key7) || el[type7] == key7)
                        && (!type8 || key8 === undefined || this.isSubTot(key8) || el[type8] == key8)
                    ) {
                        sum += to_float(el.y);
                    }
                });
                return sum;
            },

            //EXCLUDE FUNCTIONS
            cellIsActive(cell) {
                return !this.isExcluded('row', cell.vert_l1, cell.vert_l2)
                    && !this.isExcluded('col', cell.hor_l1, cell.hor_l2);
            },
            isExcluded(type, l1_h, l2_h) {
                let obj = this.getType(type)[0];
                let ex = this.getType(type)[1];
                if (!obj || !ex) {
                    return 0;
                }

                let l1, l2;
                if (ex[obj.l1_field] && ex[obj.l2_field]) {
                    let i = -1;
                    _.each(ex[obj.l1_field], (l1,k1) => {
                        if (l1==l1_h && ex[obj.l2_field][k1]==l2_h) {
                            i = k1;
                        }
                    });
                    return i+1;
                } else {
                    if (ex[obj.l1_field]) {
                        return ex[obj.l1_field].indexOf(l1_h) + 1;
                    } else
                    if (ex[obj.l2_field]) {
                        return ex[obj.l2_field].indexOf(l1_h) + 1;
                    } else {
                        return 0;
                    }
                }
            },
            toggleActive(type, l1_h, l2_h) {
                let obj = this.getType(type)[0];
                let ex = this.getType(type)[1];
                if (!obj || !ex) {
                    return;
                }
                let index = this.isExcluded(type, l1_h, l2_h);
                if (index) {
                    if (obj.l1_field) {
                        ex[obj.l1_field].splice( index-1, 1 );
                    }
                    if (obj.l2_field) {
                        ex[obj.l2_field].splice( index-1, 1 );
                    }
                } else {
                    if (obj.l1_field) {
                        ex[obj.l1_field].push(l1_h);
                    }
                    if (obj.l2_field) {
                        ex[obj.l2_field].push(l2_h);
                    }
                }
                this.$emit('active-change', type === 'col' ? 'excluded_hors' : 'excluded_verts');
            },
            getType(type) {
                if (type === 'col') {
                    return [this.pivot_hor, this.all_settings.excluded_hors];
                } else {
                    return [this.pivot_vert, this.all_settings.excluded_verts];
                }
            },

            //CELLS STYLE
            getStyleCell(v_l1, v_l2, l1_h, l2_h)
            {
                let disabled = this.isExcluded('row', v_l1, v_l2) || this.isExcluded('col', l1_h, l2_h);
                return {
                    backgroundColor: disabled ? '#CCC' : null,
                };
            },
            showHead(type, l1, l2, l3, l4, l5) {
                let key = type +
                    (l5 !== undefined ? '_l5'
                        : (l4 !== undefined ? '_l4'
                            : (l3 !== undefined ? '_l3'
                                : (l2 !== undefined ? '_l2'
                                    : '_l1'))));
                let str = (l5 !== undefined ? l5
                    : (l4 !== undefined ? l4
                        : (l3 !== undefined ? l3
                            : (l2 !== undefined ? l2
                                : l1))));
                str = String(str);

                return _.map(this.$root.parseMsel(str), (el) => {
                    return this.headElConv(key, _.trim(el));
                });
            },
            headElConv(key, val) {
                let str_val = String(val);
                let rc = this.all_variants.rcs[key][str_val] || {};
                let usr = this.$root.getUserSimple(this.all_variants.usrs[key][str_val], {
                    user_fld_show_first: true,
                    user_fld_show_last: true,
                });
                return usr || rc.show_val || val;
            },

            //show link
            showSrcRecord(lnk, header, tableRow) {
                this.$emit('show-src-record', lnk, header, tableRow, 'bi_module');
            },
        },
    }
</script>