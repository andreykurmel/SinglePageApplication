<script>
    /**
     *  Can be used:
     *
     *  */
    import IsShowFieldMixin from './IsShowFieldMixin.vue';
    import TestRowColMixin from './TestRowColMixin.vue';

    import {VerticalTableFldObject} from "../CustomTable/VerticalTableFldObject";
    import {SpecialFuncs} from "../../classes/SpecialFuncs";

    export default {
        mixins: [
            IsShowFieldMixin,
            TestRowColMixin
        ],
        data: function () {
            return {
            }
        },
        methods: {
            getSectionGroups(tableMeta, tableRow, pivotFields, initGroups, multipleGroups, behavior, dcrLinkedTables) {
                let firstPush = true;
                let availGroups = initGroups || [];
                let fld_objects = this.sortAndFilterFields(tableMeta, tableMeta._fields, tableRow, true);

                let ava = [];
                _.each(fld_objects, (fld) => {
                    let isDcrSection = VerticalTableFldObject.fieldSetting('is_dcr_section', fld, pivotFields, behavior);
                    if (isDcrSection && multipleGroups) {
                        let tabs = SpecialFuncs.getFieldTabs(ava, firstPush ? dcrLinkedTables : null);
                        firstPush = false;

                        availGroups.push({
                            fieldTabs: tabs,
                            fields: ava,
                            avail_columns: _.map(ava, 'field'),
                            showthis: 1,
                            showitem: 0,
                            activetab: _.first(Object.keys(tabs)),
                            uuid: uuidv4(),
                        });
                        ava = [];
                    }
                    ava.push(fld);
                });

                if (ava && ava.length) {
                    let tabs = SpecialFuncs.getFieldTabs(ava, firstPush ? dcrLinkedTables : null);
                    firstPush = false;

                    availGroups.push({
                        fieldTabs: tabs,
                        fields: ava,
                        avail_columns: _.map(ava, 'field'),
                        showthis: 1,
                        showitem: 0,
                        activetab: _.first(Object.keys(tabs)),
                        uuid: uuidv4(),
                    });
                }

                return _.filter(availGroups, (avail) => { return avail.isSlideTitle || avail.avail_columns.length > 0; });
            },
            /**
             * return [header, header, header, ...]
             * @returns {Array}
             */
            sortAndFilterFields(tableMeta, fields, tableRow, ignore_format) {
                let flds = [];
                if (tableMeta.vert_tb_floating) {
                    //first 'is_floating'
                    _.each(fields, (fld) => {
                        (fld.is_floating ? flds.push(fld) : null);
                    });
                    //then rest of the Fields
                    _.each(fields, (fld) => {
                        (!fld.is_floating ? flds.push(fld) : null);
                    });
                } else {
                    flds = fields;
                }
                //filter 'only visible'
                flds = _.filter(flds, (header) => {
                    return this.canMainView(tableMeta, header, tableRow, ignore_format);
                });
                return flds;
            },
            //can view
            canMainView(tableMeta, header, tableRow, ignore_format) {
                let noIsShow = this.behavior === 'kanban_view';
                return this.$root.systemFields.indexOf(header.field) === -1
                    && this.isShowField(header, noIsShow)
                    && (ignore_format || this.hiddenByFormat(tableMeta, header, tableRow));
            },
            //hidden by CondFormat 'show in header'
            hiddenByFormat(tableMeta, tableHeader, tableRow) {
                let visible = SpecialFuncs.defaultVisibility(tableHeader);
                let condFormats = tableMeta._cond_formats || [];
                for (let i = condFormats.length-1; i >= 0; i--) {
                    let format = condFormats[i] || {};
                    //check that Format is applied to this Cell
                    if (
                        format.status == 1 //check that Format is Active
                        &&
                        tableRow && this.testRow(tableRow, format.id) //check saved result that current row is active for format
                        &&
                        (!format.table_column_group_id || this.testColumn(tableHeader, format.table_column_group_id, tableMeta)) //check column
                    ) {
                        visible = !!(format.show_form_data);
                    }
                }
                return visible;
            },
        },
    }
</script>