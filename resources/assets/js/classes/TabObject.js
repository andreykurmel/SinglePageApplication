
export class TabObject {
    /**
     * constructor
     * @param obj
     */
    constructor(obj) {
        this.tables = obj.tables;
        this.master_id = obj.master_id;
        this.master_table = obj.master_table;
        this.master_obj = obj.master_obj;
        this.type_3d = obj.type_3d;
        this.init_top = obj.init_top;
        this.init_select = obj.init_select;
        this.tab_style = obj.tab_style;
        this.del_child_tbls = obj.del_child_tbls;
        this.copy_child_tbls = obj.copy_child_tbls;
    }
}