
export class ChartFunctions {
    /* HELPERS */

    /**
     *
     * @returns {{bi_cell_spacing: number, bi_can_add: number, bi_corner_radius: number, bi_fix_layout: number, bi_can_settings: number, bi_cell_height: number}}
     */
    static emptyBiSett(table_id) {
        return {
            id: null,
            table_id: table_id,
            bi_fix_layout: 1,
            bi_can_add: 1,
            bi_can_settings: 1,
            bi_cell_height: 50,
            bi_cell_spacing: 25,
            bi_corner_radius: 5,
        };
    }

    /**
     *
     * @param table_chart_tab_id
     * @param table_id
     * @param user_id
     * @param row_idx
     * @param col_idx
     * @returns {{table_chart_tab_id, row_idx, chart_settings: *, user_id, id: null, table_id, _can_edit: boolean, cached_data: *[], __gs_hash: *, col_idx}}
     */
    static newChart(table_chart_tab_id, table_id, user_id, row_idx, col_idx) {
        return {
            id: null,
            col_idx: col_idx,
            row_idx: row_idx,
            user_id: user_id,
            table_id: table_id,
            table_chart_tab_id: table_chart_tab_id,
            chart_settings: this.emptySettings(),
            cached_data: [],
            _can_edit: true,
            __gs_hash: uuidv4(),
        };
    }

    /**
     *
     * @returns {*}
     */
    static empTableVar() {
        return {
            name: '',
            chart_id: null,
            title: '',
            hor_1: '',
            hor_2: '',
            hor_3: '',
            vart_1: '',
            vart_2: '',
            vart_3: '',
        };
    }

    /**
     *
     * @returns {*}
     */
    static empVar(type) {
        if (type === 'chart') {
            return {
                name: '',
                chart_id: null,
                group_1: '',
                group_2: '',
                group_3: '',
            };
        } else {
            return {
                name: '',
                chart_id: null,
                hor_1: '',
                hor_2: '',
                hor_3: '',
                vert_1: '',
                vert_2: '',
                vert_3: '',
                about: 1,
            };
        }
    }

    /**
     *
     * @returns {number}
     */
    static maxHor() {
        return 3;
    }

    /**
     *
     * @returns {number}
     */
    static maxVert() {
        return 5;
    }

    /**
     *
     * @returns {number}
     */
    static maxAbout() {
        return 5;
    }

    /**
     *
     * @returns {number}
     */
    static maxLVL() {
        return Math.max(this.maxHor(), this.maxVert(), this.maxAbout());
    }

    /**
     *
     * @param {Object} chart_settings
     * @param {Boolean} uc_first
     * @returns {string}
     */
    static chElType(chart_settings, uc_first) {
        let str = '';
        switch (chart_settings.elem_type) {
            case 'bi_chart': str = 'chart'; break;
            case 'pivot_table': str = 'table'; break;
            case 'text_data': str = 'text'; break;
            default: str = 'text'; break;
        }
        if (uc_first) {
            str = str[0].toUpperCase() + str.slice(1);
        }
        return str;
    }

    /**
     *
     * @param chart_settings
     * @param type
     * @param fromIndex
     * @param toIndex
     * @returns {*}
     */
    static reorderSettings(chart_settings, type, fromIndex, toIndex) {
        if (type === 'horizontal' || type === 'vertical') {
            let clone = _.cloneDeep(chart_settings.pivot_table);
            let suffixes = ['_reference','_ref_link','_field','_lvl_fname','_hide_empty','_show_links','_sub_total','_split'];
            for (let i = Math.min(fromIndex, toIndex); i <= Math.max(fromIndex, toIndex); i++) {
                _.each(suffixes, (suffix) => {
                    let getI = i == toIndex
                        ? fromIndex
                        : (fromIndex < toIndex ? i+1 : i-1);
                    chart_settings.pivot_table[type]['l'+i+suffix] = clone[type]['l'+getI+suffix];
                });
            }
        }
        if (type === 'about') {
            let clone = _.cloneDeep(chart_settings.pivot_table);
            let suffixes = ['field','label_field','calc_val','group_function','show_zeros','abo_type','formula_string','lvl_fname'];
            for (let i = Math.min(fromIndex, toIndex); i <= Math.max(fromIndex, toIndex); i++) {
                _.each(suffixes, (suffix) => {
                    let getI = i == toIndex
                        ? fromIndex
                        : (fromIndex < toIndex ? i+1 : i-1);
                    let iKey = i > 1 ? 'about_'+i : 'about';
                    let getIkey = getI > 1 ? 'about_'+getI : 'about';
                    chart_settings.pivot_table[iKey][suffix] = clone[getIkey][suffix];
                });
            }
        }
        return chart_settings;
    }

    /**
     *
     * @returns {*}
     */
    static emptySettings() {
        let obj = {
            id: null,
            name: '',
            elem_type: 'pivot_table',
            active_type: 'settings',
            vert_align: 'start',
            hor_align: 'start',
            no_auto_update: false,
            wait_for_update: false,
            table_to_export: null,
            excluded_hors: {
                // db_field: [v1,v2,v3,v4,v5,...]
            },
            excluded_verts: {
                // db_field: [v1,v2,v3,v4,v5,...]
            },
            dimensions: {
                gs_wi: 2,
                gs_he: 4,
                back_color: null,
            },
            data_range: null,
            bi_chart: {
                show_legend: true,
                chart_type: 'basic',
                chart_sub_type: 'line',
                labels: {
                    general: '',
                    x_label: '',
                    y_label: '',
                },
                x_axis: {
                    field: null,
                    stack: 'Array',
                    l1_group_fld: null,
                    l2_group_fld: null,
                },
                y_axis: {
                    field: null,
                    calc_val: 1,
                    group_function: 'sum',
                },
                tslh: {
                    top: null,
                    bottom: null,
                    start: null,
                    end: null,
                    tooltip1: null,
                    tooltip2: null,
                    front_fld: null,
                }
            },
            pivot_table: {
                data_widths: {}, //key: {width: 80}
                vert_widths: {
                    1: {width: 80},
                    2: {width: 80},
                    3: {width: 80},
                    4: {width: 80},
                    5: {width: 80},
                },
                labels: {
                    general: '',
                    x_label: '',
                    y_label: '',
                },
                hor_l: 1,
                vert_l: 1,
                horizontal: {
                    hor_fields_width: 'uniform',
                    sub_tot_pos: 'back',
                },
                vertical: {
                    sub_tot_pos: 'bot',
                },
                len_about: 1,
                stack_about: false,
                referenced_tables: false,
                activness_visible: true,
            },
            text_data: {
                content: '',
                vars_table: [],
                vars_chart: [],
            },
        };
        for (let i = 1; i <= this.maxLVL(); i++) {
            obj.pivot_table.horizontal['l'+i+'_reference'] = null;//'field' -> the same table, 'id' -> another table
            obj.pivot_table.horizontal['l'+i+'_ref_link'] = null;//'id' just for '_reference'
            obj.pivot_table.horizontal['l'+i+'_field'] = null;
            obj.pivot_table.horizontal['l'+i+'_lvl_fname'] = null;
            obj.pivot_table.horizontal['l'+i+'_hide_empty'] = false;
            obj.pivot_table.horizontal['l'+i+'_show_links'] = false;
            obj.pivot_table.horizontal['l'+i+'_sub_total'] = false;
            obj.pivot_table.horizontal['l'+i+'_split'] = false;

            obj.pivot_table.vertical['l'+i+'_reference'] = null;//'field' -> the same table, 'id' -> another table
            obj.pivot_table.vertical['l'+i+'_ref_link'] = null;//'id' just for '_reference'
            obj.pivot_table.vertical['l'+i+'_field'] = null;
            obj.pivot_table.vertical['l'+i+'_lvl_fname'] = null;
            obj.pivot_table.vertical['l'+i+'_hide_empty'] = false;
            obj.pivot_table.vertical['l'+i+'_show_links'] = false;
            obj.pivot_table.vertical['l'+i+'_sub_total'] = false;
            obj.pivot_table.vertical['l'+i+'_split'] = false;

            let akey = i > 1 ? 'about_'+i : 'about';
            obj.pivot_table[akey] = {};
            obj.pivot_table[akey].field = null;
            obj.pivot_table[akey].label_field = null;
            obj.pivot_table[akey].calc_val = 1;
            obj.pivot_table[akey].group_function = 'sum';
            obj.pivot_table[akey].show_zeros = false;
            obj.pivot_table[akey].abo_type = 'field';
            obj.pivot_table[akey].formula_string = '';
            obj.pivot_table[akey].lvl_fname = '';
        }
        return obj;
    }
}