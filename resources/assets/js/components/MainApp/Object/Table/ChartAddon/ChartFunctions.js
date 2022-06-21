
export class ChartFunctions {

    /**
     * constructor
     */
    constructor(tableMeta) {
        let setts = tableMeta._collaborator_bi_settings || {};
        this.settings = {
            table_id: tableMeta.id || null,
            is_owner: tableMeta._is_owner,

            fix_layout: tableMeta.bi_fix_layout,
            can_add: tableMeta.bi_can_add,
            hide_settings: tableMeta.bi_can_settings,
            cell_height: tableMeta.bi_cell_height || 50,
            cell_spacing: tableMeta.bi_cell_spacing || 25,

            avail_fix_layout: tableMeta._is_owner || !!setts.avail_fix_layout,
            avail_can_add: tableMeta._is_owner || !!setts.avail_can_add,
            avail_hide_settings: tableMeta._is_owner || !!setts.avail_hide_settings,
            avail_cell_height: tableMeta._is_owner || !!setts.avail_cell_height,
            avail_cell_spacing: tableMeta._is_owner || !!setts.avail_cell_spacing,
        };
    }

    /**
     * 
     * @returns {{table_id, is_owner: (*|boolean), bi_fix_layout: *, bi_can_add: *, bi_can_settings: *, bi_cell_height: *, bi_cell_spacing: *}|*}
     */
    getSett() {
        return this.settings;
    }
    
    /* HELPERS */

    /**
     *
     * @param $root
     */
    static saveSett(tableMeta, $root) {
        $root = $root || {};
        let settings = tableMeta._global_bi_settings.getSett();
        if (settings.is_owner && settings.table_id) {
            $root.sm_msg_type = 1;
            axios.put('/ajax/table', {
                table_id: settings.table_id,
                bi_fix_layout: settings.fix_layout,
                bi_can_add: settings.can_add,
                bi_can_settings: settings.hide_settings,
                bi_cell_height: settings.cell_height,
                bi_cell_spacing: settings.cell_spacing,
            }).finally(() => {
                $root.sm_msg_type = 0;
            });
        }
    }

    static settsFromMeta(tableMeta, vue) {
        if (!tableMeta._global_bi_settings) {
            let cf = new ChartFunctions(tableMeta);
            vue.$set(tableMeta, '_global_bi_settings', cf);
        }
        return tableMeta._global_bi_settings.getSett();
    }
    
    /**
     *
     * @param table_id
     * @param user_id
     * @param row_idx
     * @param col_idx
     * @returns {*}
     */
    static newChart(table_id, user_id, row_idx, col_idx) {
        return {
            id: null,
            col_idx: col_idx,
            row_idx: row_idx,
            user_id: user_id,
            table_id: table_id,
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
    static maxLVL() {
        return Math.max(this.maxHor(), this.maxVert());
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
            dataset: {
                type: 'list_view',
                rowgr_id: 0,
            },
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
                about: {
                    field: null,
                    calc_val: 1,
                    group_function: 'sum',
                    show_zeros: false,
                    abo_type: 'field',
                    formula_string: '',
                },
                about_2: {
                    field: null,
                    calc_val: 1,
                    group_function: 'sum',
                    show_zeros: false,
                    abo_type: 'field',
                    formula_string: '',
                },
                about_3: {
                    field: null,
                    calc_val: 1,
                    group_function: 'sum',
                    show_zeros: false,
                    abo_type: 'field',
                    formula_string: '',
                },
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
            obj.pivot_table.horizontal['l'+i+'_hide_empty'] = false;
            obj.pivot_table.horizontal['l'+i+'_show_links'] = false;
            obj.pivot_table.horizontal['l'+i+'_sub_total'] = false;
            obj.pivot_table.horizontal['l'+i+'_split'] = false;
            obj.pivot_table.vertical['l'+i+'_reference'] = null;//'field' -> the same table, 'id' -> another table
            obj.pivot_table.vertical['l'+i+'_ref_link'] = null;//'id' just for '_reference'
            obj.pivot_table.vertical['l'+i+'_field'] = null;
            obj.pivot_table.vertical['l'+i+'_hide_empty'] = false;
            obj.pivot_table.vertical['l'+i+'_show_links'] = false;
            obj.pivot_table.vertical['l'+i+'_sub_total'] = false;
            obj.pivot_table.vertical['l'+i+'_split'] = false;
        }
        return obj;
    }
}