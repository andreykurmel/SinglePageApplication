
export class VertListSettings {
    /**
     * constructor
     * @param obj
     */
    constructor(obj) {
        this.td_cell = obj.td_cell;
        this.listing_field = obj.listing_field;
        this.show_type = obj.show_type;

        this.cur_page = obj.cur_page || 1;
        this.rows_count = obj.rows_count || 0;
        this.sel_idx = obj.sel_idx || -1;

        this.link = obj.link || {};
        this.link_rules = obj.link_rules;

        this.open_history = obj.open_history || 0;
        this.history_header = obj.history_header || null;
    }
}