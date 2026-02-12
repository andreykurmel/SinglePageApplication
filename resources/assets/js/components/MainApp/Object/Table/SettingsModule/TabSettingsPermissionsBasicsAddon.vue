<template>
    <table class="table-others">
        <tr>
            <td :style="{width: addon_widths.title, cursor: 'pointer'}"
                @click="tglAddonRights(selAddon)"
            >
                <span>{{ selAddon.name }}</span>
                <span>
                    ({{ (!showAddonRight[selAddon.code] ? '+' : '-') }})
                </span>
            </td>
            <td :style="{width: addon_widths.view}">
                <label>
                    <span class="indeterm_check__wrap">
                        <span class="indeterm_check" @click="toggleAddonRight('view')">
                            <i v-if="findAddonRight('view')" class="glyphicon glyphicon-ok group__icon"></i>
                        </span>
                    </span>
                    <span>&nbsp;Available</span>
                </label>
            </td>
            <td :style="{width: addon_widths.edit}">
                <label>
                    <span class="indeterm_check__wrap">
                        <span class="indeterm_check" @click="toggleAddonRight('edit')">
                            <i v-if="findAddonRight('edit')" class="glyphicon glyphicon-ok group__icon"></i>
                        </span>
                    </span>
                    <span>&nbsp;Edit</span>
                </label>
            </td>
            <td v-if="addon_widths.activate" :style="{width: addon_widths.activate}"></td>
        </tr>
        <template v-if="in_array(selAddon.code, ['bi']) && showAddonRight[selAddon.code]">
            <tr v-if="findAddonRight('edit')">
                <td :colspan="addon_widths.activate ? 4 : 3">
                    <div class="flex">
                        <label class="flex flex--center-v">
                            <span class="indeterm_check__wrap">
                                <span class="indeterm_check" @click="updateAddonRight(!findAddonRight(1, 'lockout_layout'), 'lockout_layout')">
                                    <i v-if="findAddonRight(1, 'lockout_layout')" class="glyphicon glyphicon-ok group__icon"></i>
                                </span>
                            </span>
                            <span>&nbsp;Lockout Layout&nbsp;&nbsp;&nbsp;</span>
                        </label>
                        <label class="flex flex--center-v">
                            <span class="indeterm_check__wrap">
                                <span class="indeterm_check" @click="updateAddonRight(!findAddonRight(1, 'add_new'), 'add_new')">
                                    <i v-if="findAddonRight(1, 'add_new')" class="glyphicon glyphicon-ok group__icon"></i>
                                </span>
                            </span>
                            <span>&nbsp;Add New&nbsp;&nbsp;&nbsp;</span>
                        </label>
                        <label class="flex flex--center-v">
                            <span class="indeterm_check__wrap">
                                <span class="indeterm_check" @click="updateAddonRight(!findAddonRight(1, 'vert_grid_step'), 'vert_grid_step')">
                                    <i v-if="findAddonRight(1, 'vert_grid_step')" class="glyphicon glyphicon-ok group__icon"></i>
                                </span>
                            </span>
                            <span>&nbsp;Block Vertical Grid Step</span>
                        </label>
                    </div>
                    <div class="flex">
                        <label class="flex flex--center-v">
                            <span class="indeterm_check__wrap">
                                <span class="indeterm_check" @click="updateAddonRight(!findAddonRight(1, 'hide_settings'), 'hide_settings')">
                                    <i v-if="findAddonRight(1, 'hide_settings')" class="glyphicon glyphicon-ok group__icon"></i>
                                </span>
                            </span>
                            <span>&nbsp;Hide Settings&nbsp;&nbsp;&nbsp;</span>
                        </label>
                        <label class="flex flex--center-v">
                            <span class="indeterm_check__wrap">
                                <span class="indeterm_check" @click="updateAddonRight(!findAddonRight(1, 'block_spacing'), 'block_spacing')">
                                    <i v-if="findAddonRight(1, 'block_spacing')" class="glyphicon glyphicon-ok group__icon"></i>
                                </span>
                            </span>
                            <span>&nbsp;Block Spacing&nbsp;&nbsp;&nbsp;</span>
                        </label>
                        <label class="flex flex--center-v">
                            <span class="indeterm_check__wrap">
                                <span class="indeterm_check" @click="updateAddonRight(!findAddonRight(1, 'crnr_radius'), 'crnr_radius')">
                                    <i v-if="findAddonRight(1, 'crnr_radius')" class="glyphicon glyphicon-ok group__icon"></i>
                                </span>
                            </span>
                            <span>&nbsp;Corner Radius</span>
                        </label>
                    </div>
                </td>
            </tr>
            <tr v-for="chart in sortedCharts">
                <td :style="{width: addon_widths.title}" class="right-txt">{{ getChartTitle(chart) }}</td>
                <td :style="{width: addon_widths.view}">
                    <label>
                        <span class="indeterm_check__wrap">
                            <span class="indeterm_check" @click="toggleChartRight(chart, 'view')">
                                <i v-if="findChartRight(chart, 'view')" class="glyphicon glyphicon-ok group__icon"></i>
                            </span>
                        </span>
                        <span>&nbsp;View</span>
                    </label>
                </td>
                <td :style="{width: addon_widths.edit}">
                    <label>
                        <span class="indeterm_check__wrap">
                            <span class="indeterm_check" @click="toggleChartRight(chart, 'edit')">
                                <i v-if="findChartRight(chart, 'edit')" class="glyphicon glyphicon-ok group__icon"></i>
                            </span>
                        </span>
                        <span>&nbsp;Edit</span>
                    </label>
                </td>
                <td v-if="addon_widths.activate" :style="{width: addon_widths.activate}"></td>
            </tr>
        </template>
        <template v-if="in_array(selAddon.code, ['request']) && showAddonRight[selAddon.code]">
            <tr v-for="dcr in sortedDcrs">
                <td :style="{width: addon_widths.title}" class="right-txt">{{ dcrName(dcr) }}</td>
                <td :style="{width: addon_widths.view}">
                    <label>
                        <span class="indeterm_check__wrap">
                            <span class="indeterm_check" @click="toggleDcrRight(dcr, 'view')">
                                <i v-if="findDcrRight(dcr, 'view')" class="glyphicon glyphicon-ok group__icon"></i>
                            </span>
                        </span>
                        <span>&nbsp;View</span>
                    </label>
                </td>
                <td :style="{width: addon_widths.edit}">
                    <label>
                        <span class="indeterm_check__wrap">
                            <span class="indeterm_check" @click="toggleDcrRight(dcr, 'edit')">
                                <i v-if="findDcrRight(dcr, 'edit')" class="glyphicon glyphicon-ok group__icon"></i>
                            </span>
                        </span>
                        <span>&nbsp;Edit</span>
                    </label>
                </td>
                <td v-if="addon_widths.activate" :style="{width: addon_widths.activate}"></td>
            </tr>
        </template>
        <template v-if="in_array(selAddon.code, ['report']) && showAddonRight[selAddon.code]">
            <tr v-for="report in sortedReports">
                <td :style="{width: addon_widths.title}" class="right-txt">{{ reportName(report) }}</td>
                <td :style="{width: addon_widths.view}">
                    <label>
                        <span class="indeterm_check__wrap">
                            <span class="indeterm_check" @click="toggleReportRight(report, 'view')">
                                <i v-if="findReportRight(report, 'view')" class="glyphicon glyphicon-ok group__icon"></i>
                            </span>
                        </span>
                        <span>&nbsp;View</span>
                    </label>
                </td>
                <td :style="{width: addon_widths.edit}">
                    <label>
                        <span class="indeterm_check__wrap">
                            <span class="indeterm_check" @click="toggleReportRight(report, 'edit')">
                                <i v-if="findReportRight(report, 'edit')" class="glyphicon glyphicon-ok group__icon"></i>
                            </span>
                        </span>
                        <span>&nbsp;Edit</span>
                    </label>
                </td>
                <td v-if="addon_widths.activate" :style="{width: addon_widths.activate}"></td>
            </tr>
        </template>
        <template v-if="in_array(selAddon.code, ['gantt']) && showAddonRight[selAddon.code]">
            <tr v-for="gantt in sortedGantts">
                <td :style="{width: addon_widths.title}" class="right-txt">{{ ganttName(gantt) }}</td>
                <td :style="{width: addon_widths.view}">
                    <label>
                        <span class="indeterm_check__wrap">
                            <span class="indeterm_check" @click="toggleGanttRight(gantt, 'view')">
                                <i v-if="findGanttRight(gantt, 'view')" class="glyphicon glyphicon-ok group__icon"></i>
                            </span>
                        </span>
                        <span>&nbsp;View</span>
                    </label>
                </td>
                <td :style="{width: addon_widths.edit}">
                    <label>
                        <span class="indeterm_check__wrap">
                            <span class="indeterm_check" @click="toggleGanttRight(gantt, 'edit')">
                                <i v-if="findGanttRight(gantt, 'edit')" class="glyphicon glyphicon-ok group__icon"></i>
                            </span>
                        </span>
                        <span>&nbsp;Edit</span>
                    </label>
                </td>
                <td v-if="addon_widths.activate" :style="{width: addon_widths.activate}"></td>
            </tr>
        </template>
        <template v-if="in_array(selAddon.code, ['map']) && showAddonRight[selAddon.code]">
            <tr v-for="map in sortedMaps">
                <td :style="{width: addon_widths.title}" class="right-txt">{{ mapName(map) }}</td>
                <td :style="{width: addon_widths.view}">
                    <label>
                        <span class="indeterm_check__wrap">
                            <span class="indeterm_check" @click="toggleMapRight(map, 'view')">
                                <i v-if="findMapRight(map, 'view')" class="glyphicon glyphicon-ok group__icon"></i>
                            </span>
                        </span>
                        <span>&nbsp;View</span>
                    </label>
                </td>
                <td :style="{width: addon_widths.edit}">
                    <label>
                        <span class="indeterm_check__wrap">
                            <span class="indeterm_check" @click="toggleMapRight(map, 'edit')">
                                <i v-if="findMapRight(map, 'edit')" class="glyphicon glyphicon-ok group__icon"></i>
                            </span>
                        </span>
                        <span>&nbsp;Edit</span>
                    </label>
                </td>
                <td v-if="addon_widths.activate" :style="{width: addon_widths.activate}"></td>
            </tr>
        </template>
        <template v-if="in_array(selAddon.code, ['email']) && showAddonRight[selAddon.code]">
            <tr v-for="email in sortedEmails">
                <td :style="{width: addon_widths.title}" class="right-txt">{{ emailName(email) }}</td>
                <td :style="{width: addon_widths.view}">
                    <label>
                        <span class="indeterm_check__wrap">
                            <span class="indeterm_check" @click="toggleEmailRight(email, 'view')">
                                <i v-if="findEmailRight(email, 'view')" class="glyphicon glyphicon-ok group__icon"></i>
                            </span>
                        </span>
                        <span>&nbsp;View</span>
                    </label>
                </td>
                <td :style="{width: addon_widths.edit}">
                    <label>
                        <span class="indeterm_check__wrap">
                            <span class="indeterm_check" @click="toggleEmailRight(email, 'edit')">
                                <i v-if="findEmailRight(email, 'edit')" class="glyphicon glyphicon-ok group__icon"></i>
                            </span>
                        </span>
                        <span>&nbsp;Edit</span>
                    </label>
                </td>
                <td v-if="addon_widths.activate" :style="{width: addon_widths.activate}"></td>
            </tr>
        </template>
        <template v-if="in_array(selAddon.code, ['twilio']) && showAddonRight[selAddon.code]">
            <tr v-for="twilio in sortedTwilios">
                <td :style="{width: addon_widths.title}" class="right-txt">{{ twilioName(twilio) }}</td>
                <td :style="{width: addon_widths.view}">
                    <label>
                        <span class="indeterm_check__wrap">
                            <span class="indeterm_check" @click="toggleTwilioRight(twilio, 'view')">
                                <i v-if="findTwilioRight(twilio, 'view')" class="glyphicon glyphicon-ok group__icon"></i>
                            </span>
                        </span>
                        <span>&nbsp;View</span>
                    </label>
                </td>
                <td :style="{width: addon_widths.edit}">
                    <label>
                        <span class="indeterm_check__wrap">
                            <span class="indeterm_check" @click="toggleTwilioRight(twilio, 'edit')">
                                <i v-if="findTwilioRight(twilio, 'edit')" class="glyphicon glyphicon-ok group__icon"></i>
                            </span>
                        </span>
                        <span>&nbsp;Edit</span>
                    </label>
                </td>
                <td v-if="addon_widths.activate" :style="{width: addon_widths.activate}"></td>
            </tr>
        </template>
        <template v-if="in_array(selAddon.code, ['tournament']) && showAddonRight[selAddon.code]">
            <tr v-for="tournament in sortedTournaments">
                <td :style="{width: addon_widths.title}" class="right-txt">{{ tournamentName(tournament) }}</td>
                <td :style="{width: addon_widths.view}">
                    <label>
                        <span class="indeterm_check__wrap">
                            <span class="indeterm_check" @click="toggleTournamentRight(tournament, 'view')">
                                <i v-if="findTournamentRight(tournament, 'view')" class="glyphicon glyphicon-ok group__icon"></i>
                            </span>
                        </span>
                        <span>&nbsp;View</span>
                    </label>
                </td>
                <td :style="{width: addon_widths.edit}">
                    <label>
                        <span class="indeterm_check__wrap">
                            <span class="indeterm_check" @click="toggleTournamentRight(tournament, 'edit')">
                                <i v-if="findTournamentRight(tournament, 'edit')" class="glyphicon glyphicon-ok group__icon"></i>
                            </span>
                        </span>
                        <span>&nbsp;Edit</span>
                    </label>
                </td>
                <td v-if="addon_widths.activate" :style="{width: addon_widths.activate}"></td>
            </tr>
        </template>
        <template v-if="in_array(selAddon.code, ['grouping']) && showAddonRight[selAddon.code]">
            <tr v-for="grouping in sortedGroupings">
                <td :style="{width: addon_widths.title}" class="right-txt">{{ groupingName(grouping) }}</td>
                <td :style="{width: addon_widths.view}">
                    <label>
                        <span class="indeterm_check__wrap">
                            <span class="indeterm_check" @click="toggleGroupingRight(grouping, 'view')">
                                <i v-if="findGroupingRight(grouping, 'view')" class="glyphicon glyphicon-ok group__icon"></i>
                            </span>
                        </span>
                        <span>&nbsp;View</span>
                    </label>
                </td>
                <td :style="{width: addon_widths.edit}">
                    <label>
                        <span class="indeterm_check__wrap">
                            <span class="indeterm_check" @click="toggleGroupingRight(grouping, 'edit')">
                                <i v-if="findGroupingRight(grouping, 'edit')" class="glyphicon glyphicon-ok group__icon"></i>
                            </span>
                        </span>
                        <span>&nbsp;Edit</span>
                    </label>
                </td>
                <td v-if="addon_widths.activate" :style="{width: addon_widths.activate}"></td>
            </tr>
        </template>
        <template v-if="in_array(selAddon.code, ['ai']) && showAddonRight[selAddon.code]">
            <tr v-for="ai in sortedAis">
                <td :style="{width: addon_widths.title}" class="right-txt">{{ aiName(ai) }}</td>
                <td :style="{width: addon_widths.view}">
                    <label>
                        <span class="indeterm_check__wrap">
                            <span class="indeterm_check" @click="toggleAiRight(ai, 'view')">
                                <i v-if="findAiRight(ai, 'view')" class="glyphicon glyphicon-ok group__icon"></i>
                            </span>
                        </span>
                        <span>&nbsp;View</span>
                    </label>
                </td>
                <td :style="{width: addon_widths.edit}">
                    <label>
                        <span class="indeterm_check__wrap">
                            <span class="indeterm_check" @click="toggleAiRight(ai, 'edit')">
                                <i v-if="findAiRight(ai, 'edit')" class="glyphicon glyphicon-ok group__icon"></i>
                            </span>
                        </span>
                        <span>&nbsp;Edit</span>
                    </label>
                </td>
                <td v-if="addon_widths.activate" :style="{width: addon_widths.activate}"></td>
            </tr>
        </template>
        <template v-if="in_array(selAddon.code, ['calendar']) && showAddonRight[selAddon.code]">
            <tr v-for="calendar in sortedCalendars">
                <td :style="{width: addon_widths.title}" class="right-txt">{{ calendarName(calendar) }}</td>
                <td :style="{width: addon_widths.view}">
                    <label>
                        <span class="indeterm_check__wrap">
                            <span class="indeterm_check" @click="toggleCalendarRight(calendar, 'view')">
                                <i v-if="findCalendarRight(calendar, 'view')" class="glyphicon glyphicon-ok group__icon"></i>
                            </span>
                        </span>
                        <span>&nbsp;View</span>
                    </label>
                </td>
                <td :style="{width: addon_widths.edit}">
                    <label>
                        <span class="indeterm_check__wrap">
                            <span class="indeterm_check" @click="toggleCalendarRight(calendar, 'edit')">
                                <i v-if="findCalendarRight(calendar, 'edit')" class="glyphicon glyphicon-ok group__icon"></i>
                            </span>
                        </span>
                        <span>&nbsp;Edit</span>
                    </label>
                </td>
                <td v-if="addon_widths.activate" :style="{width: addon_widths.activate}"></td>
            </tr>
        </template>
        <template v-if="in_array(selAddon.code, ['kanban']) && showAddonRight[selAddon.code]">
            <tr v-for="kanban in sortedKanbans">
                <td :style="{width: addon_widths.title}" class="right-txt">{{ kbName(kanban) }}</td>
                <td :style="{width: addon_widths.view}">
                    <label>
                        <span class="indeterm_check__wrap">
                            <span class="indeterm_check" @click="toggleKanbanRight(kanban, 'view')">
                                <i v-if="findKanbanRight(kanban, 'view')" class="glyphicon glyphicon-ok group__icon"></i>
                            </span>
                        </span>
                        <span>&nbsp;View</span>
                    </label>
                </td>
                <td :style="{width: addon_widths.edit}">
                    <label>
                        <span class="indeterm_check__wrap">
                            <span class="indeterm_check" @click="toggleKanbanRight(kanban, 'edit')">
                                <i v-if="findKanbanRight(kanban, 'edit')" class="glyphicon glyphicon-ok group__icon"></i>
                            </span>
                        </span>
                        <span>&nbsp;Edit</span>
                    </label>
                </td>
                <td v-if="addon_widths.activate" :style="{width: addon_widths.activate}"></td>
            </tr>
        </template>
        <template v-if="in_array(selAddon.code, ['simplemap']) && showAddonRight[selAddon.code]">
            <tr v-for="simplemap in sortedSimplemaps">
                <td :style="{width: addon_widths.title}" class="right-txt">{{ smpName(simplemap) }}</td>
                <td :style="{width: addon_widths.view}">
                    <label>
                        <span class="indeterm_check__wrap">
                            <span class="indeterm_check" @click="toggleSimplemapRight(simplemap, 'view')">
                                <i v-if="findSimplemapRight(simplemap, 'view')" class="glyphicon glyphicon-ok group__icon"></i>
                            </span>
                        </span>
                        <span>&nbsp;View</span>
                    </label>
                </td>
                <td :style="{width: addon_widths.edit}">
                    <label>
                        <span class="indeterm_check__wrap">
                            <span class="indeterm_check" @click="toggleSimplemapRight(simplemap, 'edit')">
                                <i v-if="findSimplemapRight(simplemap, 'edit')" class="glyphicon glyphicon-ok group__icon"></i>
                            </span>
                        </span>
                        <span>&nbsp;Edit</span>
                    </label>
                </td>
                <td v-if="addon_widths.activate" :style="{width: addon_widths.activate}"></td>
            </tr>
        </template>
        <template v-if="in_array(selAddon.code, ['alert']) && showAddonRight[selAddon.code]">
            <tr v-for="alert in tableMeta._alerts">
                <td :style="{width: addon_widths.title}" class="right-txt">{{ getAlertTitle(alert) }}</td>
                <td :style="{width: addon_widths.view}">
                    <label>
                        <span class="indeterm_check__wrap">
                            <span class="indeterm_check" @click="toggleAlertRight(alert, 'view')">
                                <i v-if="findAlertRight(alert)" class="glyphicon glyphicon-ok group__icon"></i>
                            </span>
                        </span>
                        <span>&nbsp;View</span>
                    </label>
                </td>
                <td :style="{width: addon_widths.edit}">
                    <label>
                        <span class="indeterm_check__wrap">
                            <span class="indeterm_check" @click="toggleAlertRight(alert, 'edit')">
                                <i v-if="findAlertRight(alert, 'can_edit')" class="glyphicon glyphicon-ok group__icon"></i>
                            </span>
                        </span>
                        <span>&nbsp;Edit</span>
                    </label>
                </td>
                <td v-if="addon_widths.activate" :style="{width: addon_widths.activate}">
                    <label>
                        <span class="indeterm_check__wrap">
                            <span class="indeterm_check" @click="toggleAlertRight(alert, 'activate')">
                                <i v-if="findAlertRight(alert, 'can_activate')" class="glyphicon glyphicon-ok group__icon"></i>
                            </span>
                        </span>
                        <span>&nbsp;Activate</span>
                    </label>
                </td>
            </tr>
        </template>
    </table>
</template>

<script>
    import {eventBus} from './../../../../../app';

    export default {
        name: "TabSettingsPermissionsBasicsAddon",
        components: {
        },
        data: function () {
            return {
                showAddonRight: {},
                addon_widths: {
                    title: '53%',
                    view: this.selAddon.code === 'alert' ? '18%' : '27%',
                    edit: this.selAddon.code === 'alert' ? '18%' : '27%',
                    activate: this.selAddon.code === 'alert' ? '18%' : '',
                },
            }
        },
        props:{
            tableMeta: Object,
            selAddon: Object,
            selPermission: Object,
        },
        computed: {
            sortedCharts() {
                return _.sortBy(this.tableMeta._bi_charts, ['row_idx', 'col_idx'])
            },
            sortedKanbans() {
                return _.sortBy(this.tableMeta._kanban_settings, ['table_field_id']);
            },
            sortedSimplemaps() {
                return _.sortBy(this.tableMeta._simplemaps, ['name']);
            },
            sortedDcrs() {
                return _.sortBy(this.tableMeta._table_requests, ['name']);
            },
            sortedReports() {
                return _.sortBy(this.tableMeta._reports, ['report_name']);
            },
            sortedGantts() {
                return _.sortBy(this.tableMeta._gantt_addons, ['name']);
            },
            sortedMaps() {
                return _.sortBy(this.tableMeta._map_addons, ['name']);
            },
            sortedCalendars() {
                return _.sortBy(this.tableMeta._calendar_addons, ['name']);
            },
            sortedEmails() {
                return _.sortBy(this.tableMeta._email_addon_settings, ['name']);
            },
            sortedTwilios() {
                return _.sortBy(this.tableMeta._twilio_addon_settings, ['name']);
            },
            sortedTournaments() {
                return _.sortBy(this.tableMeta._tournaments, ['name']);
            },
            sortedGroupings() {
                return _.sortBy(this.tableMeta._groupings, ['name']);
            },
            sortedAis() {
                return _.sortBy(this.tableMeta._table_ais, ['name']);
            },
        },
        methods: {
            in_array(key, arr) {
                return window.in_array(key, arr);
            },
            tglAddonRights() {
                this.showAddonRight[this.selAddon.code] = !this.showAddonRight[this.selAddon.code] && this.findAddonRight('view');
            },

            //Addons
            findAddonRight(value, field) {
                field = field || 'type';
                let present = false;
                let addons = this.selPermission ? this.selPermission._addons : [];
                _.each(addons || [], (element) => {
                    if (element.id === this.selAddon.id && element._link[field] == value) {
                        present = true;
                    }
                });
                return present;
            },
            updateAddonRight(value, field) {
                this.$root.sm_msg_type = 1;
                axios.put('/ajax/table-permission/addon-right', {
                    addon_id: this.selAddon.id,
                    table_permission_id: this.selPermission.id,
                    fld: field,
                    val: value,
                }).then(({ data }) => {
                    this.selPermission._addons = data;
                }).catch(errors => {
                    Swal('Info', getErrors(errors));
                }).finally(() => {
                    this.$root.sm_msg_type = 0;
                });
            },
            toggleAddonRight(type) {
                this.$root.sm_msg_type = 1;
                if (this.findAddonRight(type)) {
                    axios.delete('/ajax/table-permission/addon-right', {
                        params: {
                            addon_id: this.selAddon.id,
                            table_permission_id: this.selPermission.id,
                            type: type
                        }
                    }).then(({ data }) => {
                        this.selPermission._addons = data;
                    }).catch(errors => {
                        Swal('Info', getErrors(errors));
                    }).finally(() => {
                        this.$root.sm_msg_type = 0;
                    });
                } else {
                    axios.post('/ajax/table-permission/addon-right', {
                        addon_id: this.selAddon.id,
                        table_permission_id: this.selPermission.id,
                        type: type
                    }).then(({ data }) => {
                        this.selPermission._addons = data;
                    }).catch(errors => {
                        Swal('Info', getErrors(errors));
                    }).finally(() => {
                        this.$root.sm_msg_type = 0;
                    });
                }
            },

            //Addon: Charts
            findChartRight(chart, type) {
                let right = _.find(chart._chart_rights, {table_permission_id: Number(this.selPermission.id)});
                return (type == 'edit' ? right && right.can_edit : right);
            },
            toggleChartRight(chart, type) {
                console.log('toggleChartRight', chart);
                this.$root.sm_msg_type = 1;
                let rightIdx = _.findIndex(chart._chart_rights, {table_permission_id: Number(this.selPermission.id)});
                let right = (rightIdx > -1 ? chart._chart_rights[rightIdx] : null);
                if (right && type === 'view') {//present right -> so delete it
                    axios.delete('/ajax/table/chart/right', {
                        params: {
                            chart_id: chart.id,
                            permission_id: this.selPermission.id,
                            can_edit: 0,//not used
                        }
                    }).then(({ data }) => {
                        chart._chart_rights.splice(rightIdx,1);
                    }).catch(errors => {
                        Swal('Info', getErrors(errors));
                    }).finally(() => {
                        this.$root.sm_msg_type = 0;
                    });
                } else {
                    let canedit = Number(right && right.can_edit);
                    axios.post('/ajax/table/chart/right', {
                        chart_id: chart.id,
                        permission_id: this.selPermission.id,
                        can_edit: (type === 'edit') ? Number(!canedit) : canedit,
                    }).then(({ data }) => {
                        if (rightIdx > -1) {
                            chart._chart_rights.splice(rightIdx, 1 ,data);
                        } else {
                            chart._chart_rights.push( data );
                        }
                    }).catch(errors => {
                        Swal('Info', getErrors(errors));
                    }).finally(() => {
                        this.$root.sm_msg_type = 0;
                    });
                }
            },
            getChartTitle(chart) {
                let pos = '(' + (Number(chart.row_idx)+1) + ',' + (Number(chart.col_idx)+1) + ')';
                return (chart.name || pos) + ' ' + chart.title;
            },

            //Addon: Dcrs
            findDcrRight(dcr, type) {
                let right = _.find(dcr._dcr_rights, {table_permission_id: Number(this.selPermission.id)});
                return (type == 'edit' ? right && right.can_edit : right);
            },
            toggleDcrRight(dcr, type) {
                this.$root.sm_msg_type = 1;
                let rightIdx = _.findIndex(dcr._dcr_rights, {table_permission_id: Number(this.selPermission.id)});
                let right = (rightIdx > -1 ? dcr._dcr_rights[rightIdx] : null);
                if (right && type === 'view') {//present right -> so delete it
                    axios.delete('/ajax/table-data-request/right', {
                        params: {
                            dcr_id: dcr.id,
                            permission_id: this.selPermission.id,
                            can_edit: 0,//not used
                        }
                    }).then(({ data }) => {
                        dcr._dcr_rights.splice(rightIdx,1);
                    }).catch(errors => {
                        Swal('Info', getErrors(errors));
                    }).finally(() => {
                        this.$root.sm_msg_type = 0;
                    });
                } else {
                    let canedit = Number(right && right.can_edit);
                    axios.post('/ajax/table-data-request/right', {
                        dcr_id: dcr.id,
                        permission_id: this.selPermission.id,
                        can_edit: (type === 'edit') ? Number(!canedit) : canedit,
                    }).then(({ data }) => {
                        if (rightIdx > -1) {
                            dcr._dcr_rights.splice(rightIdx, 1 ,data);
                        } else {
                            dcr._dcr_rights.push( data );
                        }
                    }).catch(errors => {
                        Swal('Info', getErrors(errors));
                    }).finally(() => {
                        this.$root.sm_msg_type = 0;
                    });
                }
            },
            dcrName(dcr) {
                return dcr.name;
            },

            //Addon: Reports
            findReportRight(report, type) {
                let right = _.find(report._report_rights, {table_permission_id: Number(this.selPermission.id)});
                return (type == 'edit' ? right && right.can_edit : right);
            },
            toggleReportRight(report, type) {
                this.$root.sm_msg_type = 1;
                let rightIdx = _.findIndex(report._report_rights, {table_permission_id: Number(this.selPermission.id)});
                let right = (rightIdx > -1 ? report._report_rights[rightIdx] : null);
                if (right && type === 'view') {//present right -> so delete it
                    axios.delete('/ajax/addon-report/right', {
                        params: {
                            report_id: report.id,
                            permission_id: this.selPermission.id,
                            can_edit: 0,//not used
                        }
                    }).then(({ data }) => {
                        report._report_rights.splice(rightIdx,1);
                    }).catch(errors => {
                        Swal('Info', getErrors(errors));
                    }).finally(() => {
                        this.$root.sm_msg_type = 0;
                    });
                } else {
                    let canedit = Number(right && right.can_edit);
                    axios.post('/ajax/addon-report/right', {
                        report_id: report.id,
                        permission_id: this.selPermission.id,
                        can_edit: (type === 'edit') ? Number(!canedit) : canedit,
                    }).then(({ data }) => {
                        if (rightIdx > -1) {
                            report._report_rights.splice(rightIdx, 1 ,data);
                        } else {
                            report._report_rights.push( data );
                        }
                    }).catch(errors => {
                        Swal('Info', getErrors(errors));
                    }).finally(() => {
                        this.$root.sm_msg_type = 0;
                    });
                }
            },
            reportName(report) {
                return report.report_name;
            },

            //Addon: Gantts
            findGanttRight(gantt, type) {
                let right = _.find(gantt._gantt_rights, {table_permission_id: Number(this.selPermission.id)});
                return (type == 'edit' ? right && right.can_edit : right);
            },
            toggleGanttRight(gantt, type) {
                this.$root.sm_msg_type = 1;
                let rightIdx = _.findIndex(gantt._gantt_rights, {table_permission_id: Number(this.selPermission.id)});
                let right = (rightIdx > -1 ? gantt._gantt_rights[rightIdx] : null);
                if (right && type === 'view') {//present right -> so delete it
                    axios.delete('/ajax/addon-gantt/right', {
                        params: {
                            gantt_id: gantt.id,
                            permission_id: this.selPermission.id,
                            can_edit: 0,//not used
                        }
                    }).then(({ data }) => {
                        gantt._gantt_rights.splice(rightIdx,1);
                    }).catch(errors => {
                        Swal('Info', getErrors(errors));
                    }).finally(() => {
                        this.$root.sm_msg_type = 0;
                    });
                } else {
                    let canedit = Number(right && right.can_edit);
                    axios.post('/ajax/addon-gantt/right', {
                        gantt_id: gantt.id,
                        permission_id: this.selPermission.id,
                        can_edit: (type === 'edit') ? Number(!canedit) : canedit,
                    }).then(({ data }) => {
                        if (rightIdx > -1) {
                            gantt._gantt_rights.splice(rightIdx, 1 ,data);
                        } else {
                            gantt._gantt_rights.push( data );
                        }
                    }).catch(errors => {
                        Swal('Info', getErrors(errors));
                    }).finally(() => {
                        this.$root.sm_msg_type = 0;
                    });
                }
            },
            ganttName(gantt) {
                return gantt.name;
            },

            //Addon: Maps
            findMapRight(map, type) {
                let right = _.find(map._map_rights, {table_permission_id: Number(this.selPermission.id)});
                return (type == 'edit' ? right && right.can_edit : right);
            },
            toggleMapRight(map, type) {
                this.$root.sm_msg_type = 1;
                let rightIdx = _.findIndex(map._map_rights, {table_permission_id: Number(this.selPermission.id)});
                let right = (rightIdx > -1 ? map._map_rights[rightIdx] : null);
                if (right && type === 'view') {//present right -> so delete it
                    axios.delete('/ajax/map-addon/right', {
                        params: {
                            map_id: map.id,
                            permission_id: this.selPermission.id,
                            can_edit: 0,//not used
                        }
                    }).then(({ data }) => {
                        map._map_rights.splice(rightIdx,1);
                    }).catch(errors => {
                        Swal('Info', getErrors(errors));
                    }).finally(() => {
                        this.$root.sm_msg_type = 0;
                    });
                } else {
                    let canedit = Number(right && right.can_edit);
                    axios.post('/ajax/map-addon/right', {
                        map_id: map.id,
                        permission_id: this.selPermission.id,
                        can_edit: (type === 'edit') ? Number(!canedit) : canedit,
                    }).then(({ data }) => {
                        if (rightIdx > -1) {
                            map._map_rights.splice(rightIdx, 1 ,data);
                        } else {
                            map._map_rights.push( data );
                        }
                    }).catch(errors => {
                        Swal('Info', getErrors(errors));
                    }).finally(() => {
                        this.$root.sm_msg_type = 0;
                    });
                }
            },
            mapName(map) {
                return map.name;
            },

            //Addon: Calendars
            findCalendarRight(calendar, type) {
                let right = _.find(calendar._calendar_rights, {table_permission_id: Number(this.selPermission.id)});
                return (type == 'edit' ? right && right.can_edit : right);
            },
            toggleCalendarRight(calendar, type) {
                this.$root.sm_msg_type = 1;
                let rightIdx = _.findIndex(calendar._calendar_rights, {table_permission_id: Number(this.selPermission.id)});
                let right = (rightIdx > -1 ? calendar._calendar_rights[rightIdx] : null);
                if (right && type === 'view') {//present right -> so delete it
                    axios.delete('/ajax/addon-calendar/right', {
                        params: {
                            calendar_id: calendar.id,
                            permission_id: this.selPermission.id,
                            can_edit: 0,//not used
                        }
                    }).then(({ data }) => {
                        calendar._calendar_rights.splice(rightIdx,1);
                    }).catch(errors => {
                        Swal('Info', getErrors(errors));
                    }).finally(() => {
                        this.$root.sm_msg_type = 0;
                    });
                } else {
                    let canedit = Number(right && right.can_edit);
                    axios.post('/ajax/addon-calendar/right', {
                        calendar_id: calendar.id,
                        permission_id: this.selPermission.id,
                        can_edit: (type === 'edit') ? Number(!canedit) : canedit,
                    }).then(({ data }) => {
                        if (rightIdx > -1) {
                            calendar._calendar_rights.splice(rightIdx, 1 ,data);
                        } else {
                            calendar._calendar_rights.push( data );
                        }
                    }).catch(errors => {
                        Swal('Info', getErrors(errors));
                    }).finally(() => {
                        this.$root.sm_msg_type = 0;
                    });
                }
            },
            calendarName(calendar) {
                return calendar.name;
            },

            //Addon: Emails
            findEmailRight(email, type) {
                let right = _.find(email._email_rights, {table_permission_id: Number(this.selPermission.id)});
                return (type == 'edit' ? right && right.can_edit : right);
            },
            toggleEmailRight(email, type) {
                this.$root.sm_msg_type = 1;
                let rightIdx = _.findIndex(email._email_rights, {table_permission_id: Number(this.selPermission.id)});
                let right = (rightIdx > -1 ? email._email_rights[rightIdx] : null);
                if (right && type === 'view') {//present right -> so delete it
                    axios.delete('/ajax/addon-email-sett/right', {
                        params: {
                            email_id: email.id,
                            permission_id: this.selPermission.id,
                            can_edit: 0,//not used
                        }
                    }).then(({ data }) => {
                        email._email_rights.splice(rightIdx,1);
                    }).catch(errors => {
                        Swal('Info', getErrors(errors));
                    }).finally(() => {
                        this.$root.sm_msg_type = 0;
                    });
                } else {
                    let canedit = Number(right && right.can_edit);
                    axios.post('/ajax/addon-email-sett/right', {
                        email_id: email.id,
                        permission_id: this.selPermission.id,
                        can_edit: (type === 'edit') ? Number(!canedit) : canedit,
                    }).then(({ data }) => {
                        if (rightIdx > -1) {
                            email._email_rights.splice(rightIdx, 1 ,data);
                        } else {
                            email._email_rights.push( data );
                        }
                    }).catch(errors => {
                        Swal('Info', getErrors(errors));
                    }).finally(() => {
                        this.$root.sm_msg_type = 0;
                    });
                }
            },
            emailName(email) {
                return email.name;
            },

            //Addon: Twilios
            findTwilioRight(twilio, type) {
                let right = _.find(twilio._twilio_rights, {table_permission_id: Number(this.selPermission.id)});
                return (type == 'edit' ? right && right.can_edit : right);
            },
            toggleTwilioRight(twilio, type) {
                this.$root.sm_msg_type = 1;
                let rightIdx = _.findIndex(twilio._twilio_rights, {table_permission_id: Number(this.selPermission.id)});
                let right = (rightIdx > -1 ? twilio._twilio_rights[rightIdx] : null);
                if (right && type === 'view') {//present right -> so delete it
                    axios.delete('/ajax/addon-twilio-sett/right', {
                        params: {
                            twilio_id: twilio.id,
                            permission_id: this.selPermission.id,
                            can_edit: 0,//not used
                        }
                    }).then(({ data }) => {
                        twilio._twilio_rights.splice(rightIdx,1);
                    }).catch(errors => {
                        Swal('Info', getErrors(errors));
                    }).finally(() => {
                        this.$root.sm_msg_type = 0;
                    });
                } else {
                    let canedit = Number(right && right.can_edit);
                    axios.post('/ajax/addon-twilio-sett/right', {
                        twilio_id: twilio.id,
                        permission_id: this.selPermission.id,
                        can_edit: (type === 'edit') ? Number(!canedit) : canedit,
                    }).then(({ data }) => {
                        if (rightIdx > -1) {
                            twilio._twilio_rights.splice(rightIdx, 1 ,data);
                        } else {
                            twilio._twilio_rights.push( data );
                        }
                    }).catch(errors => {
                        Swal('Info', getErrors(errors));
                    }).finally(() => {
                        this.$root.sm_msg_type = 0;
                    });
                }
            },
            twilioName(twilio) {
                return twilio.name;
            },

            //Addon: Tournaments
            findTournamentRight(tournament, type) {
                let right = _.find(tournament._tournament_rights, {table_permission_id: Number(this.selPermission.id)});
                return (type == 'edit' ? right && right.can_edit : right);
            },
            toggleTournamentRight(tournament, type) {
                this.$root.sm_msg_type = 1;
                let rightIdx = _.findIndex(tournament._tournament_rights, {table_permission_id: Number(this.selPermission.id)});
                let right = (rightIdx > -1 ? tournament._tournament_rights[rightIdx] : null);
                if (right && type === 'view') {//present right -> so delete it
                    axios.delete('/ajax/table/tournament/right', {
                        params: {
                            tournament_id: tournament.id,
                            permission_id: this.selPermission.id,
                            can_edit: 0,//not used
                        }
                    }).then(({ data }) => {
                        tournament._tournament_rights.splice(rightIdx,1);
                    }).catch(errors => {
                        Swal('Info', getErrors(errors));
                    }).finally(() => {
                        this.$root.sm_msg_type = 0;
                    });
                } else {
                    let canedit = Number(right && right.can_edit);
                    axios.post('/ajax/table/tournament/right', {
                        tournament_id: tournament.id,
                        permission_id: this.selPermission.id,
                        can_edit: (type === 'edit') ? Number(!canedit) : canedit,
                    }).then(({ data }) => {
                        if (rightIdx > -1) {
                            tournament._tournament_rights.splice(rightIdx, 1 ,data);
                        } else {
                            tournament._tournament_rights.push( data );
                        }
                    }).catch(errors => {
                        Swal('Info', getErrors(errors));
                    }).finally(() => {
                        this.$root.sm_msg_type = 0;
                    });
                }
            },
            tournamentName(tournament) {
                return tournament.name;
            },

            //Addon: Groupings
            findGroupingRight(grouping, type) {
                let right = _.find(grouping._grouping_rights, {table_permission_id: Number(this.selPermission.id)});
                return (type == 'edit' ? right && right.can_edit : right);
            },
            toggleGroupingRight(grouping, type) {
                this.$root.sm_msg_type = 1;
                let rightIdx = _.findIndex(grouping._grouping_rights, {table_permission_id: Number(this.selPermission.id)});
                let right = (rightIdx > -1 ? grouping._grouping_rights[rightIdx] : null);
                if (right && type === 'view') {//present right -> so delete it
                    axios.delete('/ajax/table/grouping/right', {
                        params: {
                            grouping_id: grouping.id,
                            permission_id: this.selPermission.id,
                            can_edit: 0,//not used
                        }
                    }).then(({ data }) => {
                        grouping._grouping_rights.splice(rightIdx,1);
                    }).catch(errors => {
                        Swal('Info', getErrors(errors));
                    }).finally(() => {
                        this.$root.sm_msg_type = 0;
                    });
                } else {
                    let canedit = Number(right && right.can_edit);
                    axios.post('/ajax/table/grouping/right', {
                        grouping_id: grouping.id,
                        permission_id: this.selPermission.id,
                        can_edit: (type === 'edit') ? Number(!canedit) : canedit,
                    }).then(({ data }) => {
                        if (rightIdx > -1) {
                            grouping._grouping_rights.splice(rightIdx, 1 ,data);
                        } else {
                            grouping._grouping_rights.push( data );
                        }
                    }).catch(errors => {
                        Swal('Info', getErrors(errors));
                    }).finally(() => {
                        this.$root.sm_msg_type = 0;
                    });
                }
            },
            groupingName(grouping) {
                return grouping.name;
            },

            //Addon: Ais
            findAiRight(ai, type) {
                let right = _.find(ai._ai_rights, {table_permission_id: Number(this.selPermission.id)});
                return (type == 'edit' ? right && right.can_edit : right);
            },
            toggleAiRight(ai, type) {
                this.$root.sm_msg_type = 1;
                let rightIdx = _.findIndex(ai._ai_rights, {table_permission_id: Number(this.selPermission.id)});
                let right = (rightIdx > -1 ? ai._ai_rights[rightIdx] : null);
                if (right && type === 'view') {//present right -> so delete it
                    axios.delete('/ajax/table/addon-ai/right', {
                        params: {
                            ai_id: ai.id,
                            permission_id: this.selPermission.id,
                            can_edit: 0,//not used
                        }
                    }).then(({ data }) => {
                        ai._ai_rights.splice(rightIdx,1);
                    }).catch(errors => {
                        Swal('Info', getErrors(errors));
                    }).finally(() => {
                        this.$root.sm_msg_type = 0;
                    });
                } else {
                    let canedit = Number(right && right.can_edit);
                    axios.post('/ajax/table/addon-ai/right', {
                        ai_id: ai.id,
                        permission_id: this.selPermission.id,
                        can_edit: (type === 'edit') ? Number(!canedit) : canedit,
                    }).then(({ data }) => {
                        if (rightIdx > -1) {
                            ai._ai_rights.splice(rightIdx, 1 ,data);
                        } else {
                            ai._ai_rights.push( data );
                        }
                    }).catch(errors => {
                        Swal('Info', getErrors(errors));
                    }).finally(() => {
                        this.$root.sm_msg_type = 0;
                    });
                }
            },
            aiName(ai) {
                return ai.name;
            },

            //Addon: Kanbans
            findKanbanRight(kanban, type) {
                let right = _.find(kanban._kanban_rights, {table_permission_id: Number(this.selPermission.id)});
                return (type == 'edit' ? right && right.can_edit : right);
            },
            toggleKanbanRight(kanban, type) {
                this.$root.sm_msg_type = 1;
                let rightIdx = _.findIndex(kanban._kanban_rights, {table_permission_id: Number(this.selPermission.id)});
                let right = (rightIdx > -1 ? kanban._kanban_rights[rightIdx] : null);
                if (right && type === 'view') {//present right -> so delete it
                    axios.delete('/ajax/settings/kanban/right', {
                        params: {
                            kanban_id: kanban.id,
                            permission_id: this.selPermission.id,
                            can_edit: 0,//not used
                        }
                    }).then(({ data }) => {
                        kanban._kanban_rights.splice(rightIdx,1);
                    }).catch(errors => {
                        Swal('Info', getErrors(errors));
                    }).finally(() => {
                        this.$root.sm_msg_type = 0;
                    });
                } else {
                    let canedit = Number(right && right.can_edit);
                    axios.post('/ajax/settings/kanban/right', {
                        kanban_id: kanban.id,
                        permission_id: this.selPermission.id,
                        can_edit: (type === 'edit') ? Number(!canedit) : canedit,
                    }).then(({ data }) => {
                        if (rightIdx > -1) {
                            kanban._kanban_rights.splice(rightIdx, 1 ,data);
                        } else {
                            kanban._kanban_rights.push( data );
                        }
                    }).catch(errors => {
                        Swal('Info', getErrors(errors));
                    }).finally(() => {
                        this.$root.sm_msg_type = 0;
                    });
                }
            },
            kbName(knb) {
                return knb.kanban_field_name || _.find(this.tableMeta._fields, {id: Number(knb.table_field_id)}).name;
            },

            //Addon: Simplemaps
            findSimplemapRight(simplemap, type) {
                let right = _.find(simplemap._simplemap_rights, {table_permission_id: Number(this.selPermission.id)});
                return (type == 'edit' ? right && right.can_edit : right);
            },
            toggleSimplemapRight(simplemap, type) {
                this.$root.sm_msg_type = 1;
                let rightIdx = _.findIndex(simplemap._simplemap_rights, {table_permission_id: Number(this.selPermission.id)});
                let right = (rightIdx > -1 ? simplemap._simplemap_rights[rightIdx] : null);
                if (right && type === 'view') {//present right -> so delete it
                    axios.delete('/ajax/addon-simplemap/right', {
                        params: {
                            simplemap_id: simplemap.id,
                            permission_id: this.selPermission.id,
                            can_edit: 0,//not used
                        }
                    }).then(({ data }) => {
                        simplemap._simplemap_rights.splice(rightIdx,1);
                    }).catch(errors => {
                        Swal('Info', getErrors(errors));
                    }).finally(() => {
                        this.$root.sm_msg_type = 0;
                    });
                } else {
                    let canedit = Number(right && right.can_edit);
                    axios.post('/ajax/addon-simplemap/right', {
                        simplemap_id: simplemap.id,
                        permission_id: this.selPermission.id,
                        can_edit: (type === 'edit') ? Number(!canedit) : canedit,
                    }).then(({ data }) => {
                        if (rightIdx > -1) {
                            simplemap._simplemap_rights.splice(rightIdx, 1 ,data);
                        } else {
                            simplemap._simplemap_rights.push( data );
                        }
                    }).catch(errors => {
                        Swal('Info', getErrors(errors));
                    }).finally(() => {
                        this.$root.sm_msg_type = 0;
                    });
                }
            },
            smpName(knb) {
                return knb.name;
            },

            //Addon: Alerts
            findAlertRight(alert, type) {
                let right = _.find(alert._alert_rights, {table_permission_id: Number(this.selPermission.id)});
                return (type ? right && right[type] : right);
            },
            toggleAlertRight(alert, type) {
                this.$root.sm_msg_type = 1;
                let rightIdx = _.findIndex(alert._alert_rights, {table_permission_id: Number(this.selPermission.id)});
                let right = (rightIdx > -1 ? alert._alert_rights[rightIdx] : null);
                if (right && type === 'view') {//present right -> so delete it
                    axios.delete('/ajax/table/alert/right', {
                        params: {
                            alert_id: alert.id,
                            permission_id: this.selPermission.id,
                            can_edit: 0,//not used
                            can_activate: 0,//not used
                        }
                    }).then(({ data }) => {
                        alert._alert_rights.splice(rightIdx,1);
                    }).catch(errors => {
                        Swal('Info', getErrors(errors));
                    }).finally(() => {
                        this.$root.sm_msg_type = 0;
                    });
                } else {
                    let canedit = Number(right && right.can_edit);
                    let canactivate = Number(right && right.can_activate);
                    axios.post('/ajax/table/alert/right', {
                        alert_id: alert.id,
                        permission_id: this.selPermission.id,
                        can_edit: (type === 'edit') ? Number(!canedit) : canedit,
                        can_activate: (type === 'activate') ? Number(!canactivate) : canactivate,
                    }).then(({ data }) => {
                        if (rightIdx > -1) {
                            alert._alert_rights.splice(rightIdx, 1 ,data);
                        } else {
                            alert._alert_rights.push( data );
                        }
                    }).catch(errors => {
                        Swal('Info', getErrors(errors));
                    }).finally(() => {
                        this.$root.sm_msg_type = 0;
                    });
                }
            },
            getAlertTitle(alert) {
                return alert.name;
            },
        },
        mounted() {
            let obj = {};
            _.each(this.$root.settingsMeta.all_addons, (adn) => {
                obj[adn.code] = false;
            });
            this.showAddonRight = obj;
        }
    }
</script>

<style lang="scss" scoped>
    @import "TabSettingsPermissions";
</style>