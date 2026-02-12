<template>
    <div class="full-height permissions-tab p5" :style="$root.themeMainBgStyle">

        <div v-if="!$root.AddonAvailableToUser(tableMeta, 'tournament')" class="permissions-panel full-height flex flex--center" :style="$root.themeMainBgStyle">
            <label>Addon is unavailable!</label>
        </div>
        <div v-else="" class="permissions-panel full-height" :style="$root.themeMainBgStyle">
            <div class="permissions-menu-header">
                <button class="btn btn-default btn-sm left-btn"
                        :class="{active : acttab === 'settings'}"
                        :style="textSysStyle"
                        @click="changeActab('settings')"
                >Settings</button>
                <template v-for="tour in tableMeta._tournaments" v-if="tour.tour_active">
                    <button class="btn btn-default btn-sm left-btn"
                            :class="{active : acttab == tour.id}"
                            :style="textSysStyle"
                            style="margin-right: 3px;"
                            @click="changeActab(tour.id)"
                    >{{ tour.name }}</button>
                </template>

                <tour-settings-button
                    v-if="SelectedTournament"
                    :selected-tournament="SelectedTournament"
                    :with-edit="canPermisEdit()"
                    @updated-tournament="updateTrnmnt(SelectedTournament)"
                    style="position: absolute; top: -3px; right: 40px; height: 32px;"
                ></tour-settings-button>
                <div v-show="acttab === 'settings'" style="position: absolute; right: 5px;">
                    <info-sign-link v-show="trnTab === 'list'" :app_sett_key="'help_link_tournament_tab'" :txt="'for Tournament/List'" :hgt="18"></info-sign-link>
                    <info-sign-link v-show="trnTab === 'general'" :app_sett_key="'help_link_tournament_tab_general'" :txt="'for Tournament/General'" :hgt="18"></info-sign-link>
                </div>
                <div v-show="acttab !== 'settings'" style="position: absolute; right: 5px;">
                    <info-sign-link :app_sett_key="'help_link_tournament_tab_data'" :txt="'for Tournament/Data Tab'" :hgt="18"></info-sign-link>
                </div>
            </div>
            <div class="permissions-menu-body" style="border: 1px solid #CCC;">
                <!--SETTINGS TAB-->
                <div class="full-height" v-show="acttab === 'settings'">
                    <tournament-settings
                        :table-meta="tableMeta"
                        @set-sub-tab="(key) => { trnTab = key; }"
                    ></tournament-settings>
                </div>

                <!--BASICS TAB-->
                <div class="full-height" v-show="acttab !== 'settings'">
                    <tournament-tab
                        v-if="tableMeta && tableRows && SelectedTournament"
                        :table-meta="tableMeta"
                        :table-tournament="SelectedTournament"
                        :table-rows="tableRows"
                        :is-visible="acttab && acttab !== 'settings'"
                        @row-clicked="popupClick"
                    ></tournament-tab>
                </div>
            </div>
        </div>
        <custom-edit-pop-up
            v-if="tableMeta && editPopupRow"
            :global-meta="tableMeta"
            :table-meta="tableMeta"
            :table-row="editPopupRow"
            :settings-meta="$root.settingsMeta"
            :role="'edit'"
            :input_component_name="$root.tdCellComponent(tableMeta.is_system)"
            :behavior="'list_view'"
            :user="$root.user"
            :cell-height="$root.cellHeight"
            :max-cell-rows="$root.maxCellRows"
            @popup-update="updateRow"
            @popup-close="closePopUp"
            @show-src-record="showSrcRecord"
        ></custom-edit-pop-up>
    </div>
</template>

<script>
    import {Endpoints} from "../../../../classes/Endpoints";

    import CellStyleMixin from "./../../../_Mixins/CellStyleMixin.vue";
    import MixinForAddons from "./MixinForAddons";

    import SlotPopup from "../../../CustomPopup/SlotPopup";
    import TournamentSettings from "./Tournament/TournamentSettings";
    import InfoSignLink from "../../../CustomTable/Specials/InfoSignLink";
    import TournamentTab from "./Tournament/TournamentTab";
    import CustomEditPopUp from "../../../CustomPopup/CustomEditPopUp";
    import TourSettingsButton from "../../../Buttons/TourSettingsButton.vue";

    export default {
        name: "TabTournamentView",
        mixins: [
            CellStyleMixin,
            MixinForAddons,
        ],
        components: {
            TourSettingsButton,
            CustomEditPopUp,
            TournamentTab,
            InfoSignLink,
            TournamentSettings,
            SlotPopup,
        },
        data: function () {
            return {
                trnTab: 'list',
                acttab: 'settings',
                can_tour: false,
                add_click: 0,
            }
        },
        props: {
            tableMeta: Object,
            isVisible: Boolean,
            currentPageRows: Array,
            requestParams: Object,
        },
        computed: {
            SelectedTournament() {
                return _.find(this.tableMeta._tournaments, {id: Number(this.acttab)});
            },
        },
        watch: {
            isVisible(val) {
                if (val) {
                    this.changeActab(this.acttab);
                }
            },
        },
        methods: {
            canPermisEdit() {
                return this.$root.addonCanPermisEdit(this.tableMeta, this.SelectedTournament, '_tournament_rights');
            },
            changeActab(val) {
                this.acttab = '';
                this.tableRows = null;
                this.$nextTick(() => {
                    this.acttab = String(val);
                    this.loadRows();
                });
            },
            loadRows() {
                if (this.acttab === 'settings') {
                    return;
                }
                let tour_data_range = this.SelectedTournament ? this.SelectedTournament.tb_tour_data_range : null;
                let tour_id = this.SelectedTournament ? this.SelectedTournament.id : null;
                this.getRows(tour_data_range, 'tournament', tour_id);
            },
            updateTrnmnt(tournament) {
                this.changeActab(this.acttab);
                axios.put('/ajax/table/tournament', {
                    table_tournament_id: tournament.id,
                    fields: tournament,
                }).then(({data}) => {
                    this.tableMeta._tournaments = data;
                }).catch(errors => {
                    Swal('Info', getErrors(errors));
                });
            },
            drawAddon() {
                //For MixinForAddon
            },
        },
        mounted() {
        },
        beforeDestroy() {
        }
    }
</script>

<style lang="scss" scoped>
    @import "./SettingsModule/TabSettingsPermissions";
    .flex--end {
        .form-control {
            padding: 3px 6px;
            height: 28px;
        }
    }
</style>