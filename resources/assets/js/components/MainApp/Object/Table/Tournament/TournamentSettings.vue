<template>
    <div class="full-height">
        <div class="full-height permissions-tab p5" :style="$root.themeMainBgStyle">

            <div class="permissions-panel full-height" :style="$root.themeMainBgStyle">

                <div class="permissions-menu-header">
                    <button class="btn btn-default btn-sm" :style="textSysStyle" :class="{active : activeTab === 'list'}" @click="changeTab('list')">
                        List
                    </button>
                    <button class="btn btn-default btn-sm" :style="textSysStyle" :class="{active : activeTab === 'general'}" @click="changeTab('general')">
                        General
                    </button>

                    <div v-if="selTournmt" class="flex flex--center-v flex--end" style="position: absolute; top: 0; right: 0; height: 32px; width: 50%;">
                        <label class="flex flex--center ml15 mr0" style="margin: 0 0 0 5px;white-space: nowrap;" :style="textSysStyleSmart">
                            Loaded Settings for Bracket:&nbsp;
                            <select-block
                                :options="toursOpts()"
                                :sel_value="selTournmt.id"
                                :style="{ maxWidth:'500px', height:'32px', }"
                                @option-select="toursChange"
                            ></select-block>
                        </label>
                    </div>
                </div>
                <div class="permissions-menu-body" style="border: 1px solid #CCC;">

                    <div class="full-height permissions-panel no-padding" v-show="activeTab === 'list'" :style="$root.themeMainBgStyle">
                        <custom-table
                            :cell_component_name="'custom-cell-col-row-group'"
                            :global-meta="tableMeta"
                            :table-meta="$root.settingsMeta['table_tournaments']"
                            :settings-meta="$root.settingsMeta"
                            :all-rows="tableMeta._tournaments"
                            :rows-count="tableMeta._tournaments.length"
                            :adding-row="{active: true, position: 'bottom'}"
                            :cell-height="1"
                            :max-cell-rows="0"
                            :is-full-width="true"
                            :with_edit="canAddonEdit()"
                            :selected-row="selectedCol"
                            :user="$root.user"
                            :behavior="'data_sets'"
                            :available-columns="['name','description','tb_tour_data_range','tour_active']"
                            :use_theme="true"
                            @added-row="addTour"
                            @updated-row="updateTour"
                            @delete-row="deleteTour"
                            @row-index-clicked="rowIndexTour"
                        ></custom-table>
                    </div>

                    <div class="full-height permissions-panel no-padding overflow-auto" v-show="activeTab === 'general'" :style="$root.themeMainBgStyle">
                        <div class="additionals_sett" v-if="selTournmt" :style="textSysStyleSmart">

                            <div class="form-group flex flex--center-v">
                                <label class="w-align">Team A:&nbsp;</label>
                                <select class="form-control"
                                        v-model="selTournmt.teamhomename_fld_id"
                                        @change="updateTour(selTournmt)"
                                        :disabled="!canPermisEdit()"
                                        style="width: 250px;"
                                        :style="textSysStyle"
                                >
                                    <option :value="null"></option>
                                    <option v-for="fld in tableMeta._fields" v-if="fld.id != selTournmt.teamguestname_fld_id" :value="fld.id">{{ fld.name }}</option>
                                </select>
                                <label>&nbsp;&nbsp;&nbsp;Score:&nbsp;</label>
                                <select class="form-control"
                                        v-model="selTournmt.teamhomegoals_fld_id"
                                        @change="updateTour(selTournmt)"
                                        :disabled="!canPermisEdit()"
                                        style="width: 250px;"
                                        :style="textSysStyle"
                                >
                                    <option :value="null"></option>
                                    <option v-for="fld in tableMeta._fields" v-if="fld.id != selTournmt.teamguestgoals_fld_id" :value="fld.id">{{ fld.name }}</option>
                                </select>
                            </div>
                            <div class="form-group flex flex--center-v">
                                <label class="w-align">Team B:&nbsp;</label>
                                <select class="form-control"
                                        v-model="selTournmt.teamguestname_fld_id"
                                        @change="updateTour(selTournmt)"
                                        :disabled="!canPermisEdit()"
                                        style="width: 250px;"
                                        :style="textSysStyle"
                                >
                                    <option :value="null"></option>
                                    <option v-for="fld in tableMeta._fields" v-if="fld.id != selTournmt.teamhomename_fld_id" :value="fld.id">{{ fld.name }}</option>
                                </select>
                                <label>&nbsp;&nbsp;&nbsp;Score:&nbsp;</label>
                                <select class="form-control"
                                        v-model="selTournmt.teamguestgoals_fld_id"
                                        @change="updateTour(selTournmt)"
                                        :disabled="!canPermisEdit()"
                                        style="width: 250px;"
                                        :style="textSysStyle"
                                >
                                    <option :value="null"></option>
                                    <option v-for="fld in tableMeta._fields" v-if="fld.id != selTournmt.teamhomegoals_fld_id" :value="fld.id">{{ fld.name }}</option>
                                </select>
                            </div>
                            <div class="form-group flex flex--center-v">
                                <label class="w-align">Stages:&nbsp;</label>
                                <select class="form-control"
                                        v-model="selTournmt.stage_fld_id"
                                        @change="updateTour(selTournmt)"
                                        :disabled="!canPermisEdit()"
                                        style="width: 250px;"
                                        :style="textSysStyle"
                                >
                                    <option :value="null"></option>
                                    <option v-for="fld in tableMeta._fields" :value="fld.id">{{ fld.name }}</option>
                                </select>
                            </div>
                            <div class="form-group flex flex--center-v">
                                <label class="w-align">Date:&nbsp;</label>
                                <select class="form-control"
                                        v-model="selTournmt.date_fld_id"
                                        @change="updateTour(selTournmt)"
                                        :disabled="!canPermisEdit()"
                                        style="width: 250px;"
                                        :style="textSysStyle"
                                >
                                    <option :value="null"></option>
                                    <option v-for="fld in tableMeta._fields"
                                            v-if="fld.f_type === 'Date' || fld.f_type === 'Date Time'"
                                            :value="fld.id"
                                    >{{ fld.name }}</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import CellStyleMixin from "../../../../_Mixins/CellStyleMixin";

    import CustomTable from "../../../../CustomTable/CustomTable";
    import SelectBlock from "../../../../CommonBlocks/SelectBlock";
    import {eventBus} from "../../../../../app";

    export default {
        name: "TournamentSettings",
        components: {
            SelectBlock,
            CustomTable
        },
        mixins: [
            CellStyleMixin,
        ],
        data: function () {
            return {
                activeTab: 'list',
                selectedCol: 0,
            }
        },
        props:{
            table_id: Number,
            tableMeta: Object,
        },
        computed: {
            selTournmt() {
                return this.tableMeta._tournaments[this.selectedCol]
                    ? this.tableMeta._tournaments[this.selectedCol]
                    : null;
            },
        },
        watch: {
            table_id(val) {
                this.selectedCol = 0;
            }
        },
        methods: {
            canPermisEdit() {
                return this.$root.addonCanPermisEdit(this.tableMeta, this.selTournmt, '_tournament_rights');
            },
            canAddonEdit() {
                return this.$root.addonCanEditGeneral(this.tableMeta, 'tournament');
            },
            changeTab(key) {
                this.activeTab = key;
                this.$emit('set-sub-tab', key);
            },
            toursOpts() {
                return _.map(this.tableMeta._tournaments, (tour) => {
                    return { val:tour.id, show:tour.name, };
                });
            },
            toursChange(opt) {
                this.selectedCol = _.findIndex(this.toursOpts(), {id: Number(opt.val)});
            },
            
            addTour(tournament) {
                axios.post('/ajax/table/tournament', {
                    table_id: this.tableMeta.id,
                    fields: tournament,
                }).then(({data}) => {
                    this.tableMeta._tournaments = data;
                }).catch(errors => {
                    Swal('Info', getErrors(errors));
                });
            },
            updateTour(tournament) {
                axios.put('/ajax/table/tournament', {
                    table_tournament_id: tournament.id,
                    fields: tournament,
                }).then(({data}) => {
                    this.tableMeta._tournaments = data;
                }).catch(errors => {
                    Swal('Info', getErrors(errors));
                });
            },
            deleteTour(tournament) {
                axios.delete('/ajax/table/tournament', {
                    params: {
                        table_tournament_id: tournament.id,
                    }
                }).then(({data}) => {
                    this.tableMeta._tournaments = data;
                }).catch(errors => {
                    Swal('Info', getErrors(errors));
                });
            },
            rowIndexTour(index) {
                this.selectedCol = index;
            },
            //select functions
            optRgGr() {
                let rrows = _.map(this.tableMeta._row_groups, (rg) => {
                    return { val:rg.id, show:rg.name };
                });
                rrows.unshift({ val:null, show:"" });
                return rrows;
            },
            showRGRP(id) {
                eventBus.$emit('show-grouping-settings-popup', this.tableMeta.db_name, 'row', id);
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
        white-space: nowrap;

        label {
            margin: 0;
        }
        .form-group {
            margin-right: 30px;
        }
        .w-align {
            width: 125px;
        }
    }
</style>