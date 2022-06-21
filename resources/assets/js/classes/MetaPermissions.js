
export class MetaPermissions {
    constructor(obj) {
        obj = obj || {};
        this.can_add = !!obj.can_add;
        this.can_edit = !!obj.can_edit;
        this.can_delete = !!obj.can_delete;
        this.has_checked = !!obj.has_checked;
        this.has_parse_paste = !!obj.has_parse_paste;
        this.has_section_parse = !!obj.has_section_parse;
        this.has_search_block = !!obj.has_search_block;
        this.has_fill_attachments = !!obj.has_fill_attachments;
        this.has_rl_calculator = !!obj.has_rl_calculator;
        this.has_rts = !!obj.has_rts;
        this.has_download = !!obj.has_download;
        this.has_halfmoon = !!obj.has_halfmoon;
        this.has_condformat = !!obj.has_condformat;
        this.has_cellheight_btn = !!obj.has_cellheight_btn;
        this.has_string_replace = !!obj.has_string_replace;
        this.has_copy_childrene = !!obj.has_copy_childrene;
        this.has_viewpop = !!obj.has_viewpop;
        this.cur_page = !!obj.cur_page;
    }
}