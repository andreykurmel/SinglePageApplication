<template>
    <custom-table
        :cell_component_name="'custom-cell-settings-dcr'"
        :global-meta="tableMeta"
        :table-meta="$root.settingsMeta['table_data_requests_2_table_fields']"
        :all-rows="allFields"
        :rows-count="allFields.length"
        :cell-height="1"
        :max-cell-rows="0"
        :is-full-width="true"
        :use_theme="true"
        :with_edit="withEdit"
        :user="$root.user"
        :behavior="'dcr_group'"
        :parent-row="selectedDcr"
        :available-columns="['_name','is_start_table_popup','is_table_field_in_popup','is_hdr_lvl_one_row','is_dcr_section','dcr_section_name',
            'fld_popup_shown','fld_display_name','fld_display_value','fld_display_border','fld_display_header_type','is_topbot_in_popup',
            'width_of_table_popup']"
        @check-row="dcrSettCheck"
    ></custom-table>
</template>

<script>
import CustomTable from '../../../../CustomTable/CustomTable';

export default {
    name: "TabSettingsRequestsDisplayWrap",
    components: {
        CustomTable,
    },
    data: function () {
        return {
        }
    },
    props:{
        tableMeta: Object,
        selectedDcr: Object,
        withEdit: Boolean,
    },
    computed: {
        allFields() {
            let colgr = _.map(
                _.filter(this.selectedDcr._data_request_columns, {view: 1}),
                'table_column_group_id'
            );
            let metaColGroups = this.tableMeta._column_groups && this.tableMeta._column_groups.length > 0
                ? this.tableMeta._column_groups
                : this.tableMeta._gen_col_groups;

            let avaFields = [];
            _.each(metaColGroups, (colGroup) => {
                if (this.$root.inArray(colGroup.id, colgr)) {
                    avaFields = avaFields.concat( _.map(colGroup._fields, 'field') );
                }
            });

            return _.filter(this.tableMeta._fields, (fld) => {
                return this.$root.systemFieldsNoId.indexOf(fld.field) === -1
                    && avaFields.indexOf(fld.field) > -1;
            });
        },
        availDefaults() {
        },
    },
    methods: {
        dcrSettCheck(field, val) {
            this.$emit('check-row', field, val);
        },
    },
    mounted() {
    },
    beforeDestroy() {
    }
}
</script>

<style lang="scss" scoped>
</style>