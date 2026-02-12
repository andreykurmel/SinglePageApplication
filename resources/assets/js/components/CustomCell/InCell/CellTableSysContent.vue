<template>
    <div ref="content_elem">
        <template v-if="Array.isArray(editValue)">
            <span v-for="str in editValue">
                <cell-table-content-data
                        :global-meta="globalMeta"
                        :table-meta="tableMeta"
                        :table-row="tableRow"
                        :table-header="tableHeader"
                        :html-value="showField(str)"
                        :real-value="str"
                        :is_select="true"
                        :user="user"
                        :behavior="behavior"
                        @unselect-val="unselectVal(str)"
                        @show-src-record="showSrcRecord"
                ></cell-table-content-data>
            </span>
        </template>
        <template v-else="">
            <cell-table-content-data
                    :global-meta="globalMeta"
                    :table-meta="tableMeta"
                    :table-row="tableRow"
                    :table-header="tableHeader"
                    :html-value="showField(editValue)"
                    :real-value="editValue"
                    :is_select="fldTypeSelect"
                    :user="user"
                    :behavior="behavior"
                    @show-src-record="showSrcRecord"
            ></cell-table-content-data>
        </template>
    </div>
</template>

<script>
    import CellTableContentData from "./CellTableContentData.vue";

    export default {
        name: "CellTableSysContent",
        mixins: [
        ],
        components: {
            CellTableContentData
        },
        data: function () {
            return {
                cont_height: 0,
                cont_width: 0,
                //STIM 3D
                styleVariants: [
                    { val: 'vh_tabs', show: 'VH Tabs', },
                    { val: 'accordion', show: 'Accordion', },
                ],
                model3dVariants: [
                    { val: '3d:ma', show: '3D:MA', },
                    { val: '3d:structure', show: '3D:Structure', },
                    { val: '3d:equipment', show: '3D:Equipment', },
                    { val: '2d:canvas', show: '2D:Canvas', },
                ],
                tabldaTypeVariants: [
                    { val: 'vertical', show: 'Vertical Table (Form)', },
                    { val: 'table', show: 'Table', },
                    { val: 'attachment', show: '`Attachment` Fields', },
                    { val: 'chart', show: 'BI Chart', },
                    { val: 'map', show: 'GSI', },
                    { val: 'configurator', show: 'Configurator', },
                ],
            }
        },
        props:{
            globalMeta: Object,
            tableMeta: Object,
            tableHeader: Object,
            tableRow: Object,
            editValue: String|Number,
            user: Object,
            behavior: String,
        },
        watch: {
        },
        computed: {
            fldTypeSelect() {
                return $.inArray(this.tableHeader.input_type, this.$root.ddlInputTypes) > -1;
            },
        },
        methods: {
            inArray(item, array) {
                return $.inArray(item, array) > -1;
            },
            unselectVal(m_select_part) {
                this.$emit('unselect-val', m_select_part);
            },
            showField(editValue) {
                let res = editValue;

                //for field types == User
                if (this.inArray(this.tableHeader.f_type, ['User'])) {
                    if (editValue === '{$visitor}') {
                        res = editValue;
                    } else {
                        let usr = this.$root.smallUserStr(this.tableRow, this.tableHeader, editValue, true);
                        res = this.$root.getUserSimple(usr, this.tableMeta._owner_settings);
                    }
                }
                else
                if (this.tableHeader.field === 'plan_id') {
                    if (this.tableRow._plan) {
                        res = this.tableRow._plan.name || this.tableRow.plan_id;
                    } else {
                        let idx = _.findIndex(this.$root.settingsMeta.all_plans, {code: this.tableRow.plan_id});
                        res = idx > -1 ? this.$root.settingsMeta.all_plans[idx].name : this.tableRow.plan_id;
                    }
                }
                else
                if (this.tableHeader.field === 'on_change_app_id') {
                    let app = _.find(this.$root.settingsMeta.table_apps_data, {id: Number(this.tableRow.on_change_app_id)});
                    res = app ? app.name : this.tableRow.on_change_app_id;
                }
                else
                if (this.tableHeader.field === 'correspondence_app_id') {
                    res = this.tableRow._app ? this.tableRow._app.name : this.tableRow.correspondence_app_id;
                }
                else
                if (this.tableHeader.field === 'correspondence_table_id') {
                    res = this.tableRow._table ? this.tableRow._table.app_table : this.tableRow.correspondence_table_id;
                }
                else
                if (this.tableHeader.field === 'style') {
                    let $mod = _.find(this.styleVariants, {val: this.tableRow.style});
                    res = $mod ? $mod.show : this.tableRow.style;
                }
                else
                if (this.tableHeader.field === 'model_3d') {
                    let $mod = _.find(this.model3dVariants, {val: this.tableRow.model_3d});
                    res = $mod ? $mod.show : this.tableRow.model_3d;
                }
                else
                if (this.tableHeader.field === 'type_tablda') {
                    let $mod = _.find(this.tabldaTypeVariants, {val: this.tableRow.type_tablda});
                    res = $mod ? $mod.show : this.tableRow.type_tablda;
                }
                else
                //User Activity
                if (['description_time','ending_time'].indexOf(this.tableHeader.field) > -1 && this.tableRow[this.tableHeader.field]) {
                    res = moment.unix( this.tableRow[this.tableHeader.field] ).format('YYYY-MM-DD HH:mm:ss');
                }
                else
                if (this.inArray(this.tableHeader.field, ['link_table_db','_data_table_id', 'data_table'])) {
                    res = this.tableRow['__'+this.tableHeader.field];
                    if (!res && this.tableRow._table && this.tableHeader.field != 'link_table_db') {
                        let tb = _.find(this.$root.settingsMeta.available_tables, {db_name: this.tableRow._table.data_table}) || {};
                        res = tb.name || this.tableRow._table.data_table;
                    }
                    if (!res) {
                        let tb = _.find(this.$root.settingsMeta.available_tables, {db_name: this.editValue}) || {};
                        return tb.name || this.editValue;
                    }
                }
                else
                if (this.inArray(this.tableHeader.field, ['data_field','link_field_db'])) {
                    res = this.tableRow['__'+this.tableHeader.field];
                    if (!res) {
                        let dbName = this.tableHeader.field == 'data_field' ? '_table_data_id' : 'link_table_db';
                        let tb = _.find(this.$root.settingsMeta.available_tables, {db_name: this.tableRow[dbName]}) || {};
                        let fld = _.find(tb._fields || [], {field: this.editValue}) || {};
                        return fld.name || this.editValue;
                    }
                    res = this.$root.uniqName(res);
                }
                else
                if (this.tableMeta.db_name === 'table_fields__for_tooltips' && this.tableHeader.field === 'table_id') {
                    res = this.tableRow.tb_name || this.tableRow.table_id;
                }
                else
                if (this.tableHeader.f_type === 'RefTable') {
                    let tb = _.find(this.$root.settingsMeta.available_tables, {id: Number(this.editValue)}) || {};
                    res = tb.name || this.editValue;
                }
                else
                if (this.tableHeader.f_type === 'RefField') {
                    let tbCol = _.find(this.tableMeta._fields, {f_type: 'RefTable'}) || {};
                    let tb = _.find(this.$root.settingsMeta.available_tables, {id: Number(this.tableRow[tbCol.field])}) || {};
                    let opt = _.find(tb._fields || [], {id: Number(this.editValue)}) || {};
                    res = this.$root.uniqName(opt.name || this.editValue);
                }
                else
                if (editValue == '[]') {
                    res = '';
                }

                return res;
            },
            //show link popup
            showSrcRecord(link, header, tableRow) {
                this.$emit('show-src-record', link, header, tableRow);
            },
        },
        mounted() {
        }
    }
</script>

<style lang="scss" scoped>
    span {
        display: inline-block;
    }
    /*.content_elem {
        overflow-wrap: break-word;
    }*/
</style>