<template>
    <div class="full-height">
        <custom-table
                :cell_component_name="'custom-cell-settings-display'"
                :global-meta="tableMeta"
                :table-meta="$root.settingsMeta['table_fields']"
                :settings-meta="$root.settingsMeta"
                :all-rows="tableMeta._fields"
                :rows-count="tableMeta._fields.length"
                :cell-height="1"
                :is-full-width="false"
                :user="$root.user"
                :behavior="'settings_display'"
                :redraw_table="redraw_table"
                :available-columns="$root.availableMapColumns"
                :forbidden-columns="limitColumns ? frbCols : []"
                @updated-row="updateRow"
                @row-index-clicked="rowIndexClicked"
        ></custom-table>
        <for-settings-pop-up
                v-if="$root.settingsMeta['table_fields'] && editPopUpRow"
                :global-meta="tableMeta"
                :table-meta="$root.settingsMeta['table_fields']"
                :settings-meta="$root.settingsMeta"
                :table-row="editPopUpRow"
                :ext-avail-tabs="['map_tab']"
                :user="$root.user"
                :cell-height="1"
                @popup-update="updateRow"
                @popup-close="closePopUp"
                @another-row="anotherRowPopup"
        ></for-settings-pop-up>
    </div>
</template>

<script>
    import {eventBus} from '../../../../../app';

    import ForSettingsPopUp from "../../../../CustomPopup/ForSettingsPopUp";
    import CustomTable from "../../../../CustomTable/CustomTable";

    export default {
        name: "MapBasicSettings",
        components: {
            CustomTable,
            ForSettingsPopUp,
        },
        mixins: [
        ],
        provide() {
            return {
            };
        },
        data: function () {
            return {
                editPopUpRow: null,
                redraw_table: 0,
                frbCols: [
                    'is_lat_field',
                    'is_long_field',
                    'is_info_header_field',
                    'map_find_street_field',
                    'map_find_city_field',
                    'map_find_state_field',
                    'map_find_county_field',
                    'map_find_zip_field',
                ],
            }
        },
        computed: {
        },
        props:{
            tableMeta: Object,
            limitColumns: Boolean,
        },
        methods: {
            updateRow(tableRow) {
                this.$emit('upd-settings-row', this.tableMeta, tableRow);
            },
            //change rows in popup
            anotherRowPopup(is_next) {
                let row_id = (this.editPopUpRow ? this.editPopUpRow.id : null);
                this.$root.anotherPopup(this.tableMeta._fields, row_id, is_next, this.rowIndexClicked);
            },
            rowIndexClicked(index) {
                this.editPopUpRow = this.tableMeta._fields[index];
            },
            closePopUp() {
                this.editPopUpRow = null;
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