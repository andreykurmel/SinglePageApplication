<template>
    <div class="container-fluid full-height">
        <div class="row full-height permissions-tab">
            <!--LEFT SIDE-->
            <div class="col-xs-12 full-height">

                <div class="top-text top-text--height" :style="textSysStyle">
                    <span>Basic Settings</span>
                </div>
                <div class="permissions-panel no-padding" style="height: calc(70% - 35px);">
                    <custom-table
                            :cell_component_name="'custom-cell-settings-display'"
                            :global-meta="tableMeta"
                            :table-meta="$root.settingsMeta['table_fields']"
                            :settings-meta="$root.settingsMeta"
                            :all-rows="tableMeta._fields"
                            :rows-count="tableMeta._fields.length"
                            :cell-height="1"
                            :user="$root.user"
                            :is-full-width="true"
                            :behavior="'settings_display'"
                            :available-columns="$root.availableCalendarColumns"
                            @updated-row="updateRow"
                            @row-index-clicked="rowIndexClicked"
                    ></custom-table>
                    <for-settings-pop-up
                            v-if="$root.settingsMeta['table_fields'] && editDisplayPopUpRow"
                            :global-meta="tableMeta"
                            :table-meta="$root.settingsMeta['table_fields']"
                            :settings-meta="$root.settingsMeta"
                            :table-row="editDisplayPopUpRow"
                            :ext-avail-tabs="['calendar_tab']"
                            :user="$root.user"
                            :cell-height="1"
                            @popup-update="updateRow"
                            @popup-close="closePopUp"
                            @another-row="anotherRowPopup"
                    ></for-settings-pop-up>
                </div>
                <div class="top-text top-text--height" :style="textSysStyle">
                    <span>Additional Settings</span>
                </div>
                <div class="permissions-panel no-padding" style="height: calc(30% - 30px);">
                    <div class="additionals_sett" :style="textSysStyle">
                        <div class="form-group flex flex--center-v">
                            <label>Locale:&nbsp;</label>
                            <select class="form-control" v-model="tableMeta.calendar_locale" @change="emitSave()" style="width: 100px;" :style="textSysStyle">
                                <option :value="null">Default</option>
                                <option v-for="cnt in $root.settingsMeta.countries_all" :value="cnt.iso_3166_2">{{ cnt.name }}</option>
                            </select>
                        </div>
                        <div class="form-group flex flex--center-v">
                            <label>Timezone:&nbsp;</label>
                            <moment-timezones
                                    :cur_tz="tableMeta.calendar_timezone || $root.user.timezone"
                                    :name="'Timezone'"
                                    style="width: 200px;"
                                    :style="textSysStyle"
                                    @changed-tz="updateTZ"
                            ></moment-timezones>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</template>

<script>
    import CustomTable from "../../../../CustomTable/CustomTable";
    import ForSettingsPopUp from "../../../../CustomPopup/ForSettingsPopUp";
    import MomentTimezones from "../../../../MomentTimezones";

    import CellStyleMixin from "../../../../_Mixins/CellStyleMixin";

    export default {
        name: "CalendarSettings",
        components: {
            MomentTimezones,
            ForSettingsPopUp,
            CustomTable,
        },
        mixins: [
            CellStyleMixin,
        ],
        data: function () {
            return {
                editDisplayPopUpRow: null,
            }
        },
        props:{
            tableMeta: Object,
        },
        computed: {
        },
        watch: {
        },
        methods: {
            updateTZ(tz) {
                this.tableMeta.calendar_timezone = tz;
                this.emitSave();
            },
            updateRow(tableRow) {
                this.$root.updateSettingsColumn(this.tableMeta, tableRow);
            },
            emitSave() {
                this.$emit('save-backend');
            },

            //popup for headers
            rowIndexClicked(index) {
                this.editDisplayPopUpRow = this.tableMeta._fields[index];
            },
            closePopUp() {
                this.editDisplayPopUpRow = null;
            },
            anotherRowPopup(is_next) {
                let row_id = (this.editDisplayPopUpRow ? this.editDisplayPopUpRow.id : null);
                this.$root.anotherPopup(this.tableMeta._fields, row_id, is_next, this.rowIndexClicked);
            },
        },
        mounted() {
        },
        beforeDestroy() {
        }
    }
</script>

<style lang="scss" scoped>
    @import "./../SettingsModule/TabSettingsPermissions";

    .additionals_sett {
        padding: 15px;
        width: 400px;
        white-space: nowrap;

        label {
            margin: 0;
        }
        .form-group {
            margin-right: 30px;
        }
    }
</style>