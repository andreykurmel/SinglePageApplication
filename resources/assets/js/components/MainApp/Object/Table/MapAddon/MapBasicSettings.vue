<template>
    <div class="full-height" v-if="tableMeta && tableMeta._fields">
        <custom-table
                :cell_component_name="'custom-cell-settings-display'"
                :global-meta="tableMeta"
                :table-meta="$root.settingsMeta['table_fields']"
                :settings-meta="$root.settingsMeta"
                :all-rows="tableMeta._fields"
                :rows-count="tableMeta._fields.length"
                :cell-height="1"
                :is-full-width="false"
                :with_edit="canEdit"
                :user="$root.user"
                :behavior="'settings_display'"
                :parent-row="selectedMap"
                :redraw_table="redraw_table"
                :available-columns="limitColumns"
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
                @direct-row="rowIndexClicked"
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
            }
        },
        computed: {
        },
        props:{
            tableMeta: Object,
            limitColumns: Array,
            canEdit: Boolean,
            selectedMap: Object,
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
                this.editPopUpRow = this.canEdit ? this.tableMeta._fields[index] : null;
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