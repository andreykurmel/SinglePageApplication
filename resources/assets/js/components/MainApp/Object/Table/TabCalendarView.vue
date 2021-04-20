<template>
    <div class="calendar-settings">
        <div v-if="!$root.AddonAvailableToUser(tableMeta, 'calendar')" class="row full-frame flex flex--center">
            <label>Addon is unavailable!</label>
        </div>
        <template v-else="">
            <div v-if="!startFld || !endFld || !titleFld" class="flex flex--center full-height" style="font-size: 2em;">Settings are empty!</div>
            <full-calendar v-else-if="isVisible && can_calendar"
                           ref="full_calendar"
                           :options="calendarOptions"
                           class="full-height"
            ></full-calendar>

            <!--Settings-->
            <slot-popup v-if="tableMeta._is_owner && show_calendar_sett" :popup_width="700" @popup-close="redrCalend()">
                <template v-slot:title>
                    <span>Calendar Setup</span>
                </template>
                <template v-slot:body>
                    <calendar-settings
                            :table-meta="tableMeta"
                            style="background-color: #005fa4;"
                            @save-backend="saveTbOnBknd"
                    ></calendar-settings>
                </template>
            </slot-popup>

            <!--Edit Row-->
            <custom-edit-pop-up
                    v-if="tableMeta && editPopupRow"
                    :global-meta="tableMeta"
                    :table-meta="tableMeta"
                    :table-row="editPopupRow"
                    :settings-meta="$root.settingsMeta"
                    :role="editPopupRole"
                    :input_component_name="$root.tdCellComponent(tableMeta.is_system)"
                    :behavior="'list_view'"
                    :user="$root.user"
                    :cell-height="$root.cellHeight"
                    :max-cell-rows="$root.maxCellRows"
                    @popup-insert="insertRow"
                    @popup-update="updateRow"
                    @popup-copy="copyRow"
                    @popup-delete="deleteRow"
                    @show-src-record="showSrcRecord"
                    @popup-close="closePopUp"
            ></custom-edit-pop-up>
        </template>
    </div>
</template>

<script>
    import {SpecialFuncs} from './../../../../classes/SpecialFuncs';

    import {eventBus} from './../../../../app';

    import FullCalendar from '@fullcalendar/vue';
    import dayGridPlugin from '@fullcalendar/daygrid';
    import interactionPlugin from '@fullcalendar/interaction';
    import timeGridPlugin from '@fullcalendar/timegrid';
    import listPlugin from '@fullcalendar/list';
    import allLocales from '@fullcalendar/core/locales-all';

    import CellStyleMixin from "./../../../_Mixins/CellStyleMixin.vue";

    import CalendarSettings from "./Calendar/CalendarSettings";
    import SlotPopup from "../../../CustomPopup/SlotPopup";
    import CustomEditPopUp from "../../../CustomPopup/CustomEditPopUp";

    export default {
        name: "TabCalendarView",
        mixins: [
            CellStyleMixin,
        ],
        components: {
            CustomEditPopUp,
            SlotPopup,
            CalendarSettings,
            FullCalendar,
        },
        data: function () {
            return {
                tZone: this.tableMeta.calendar_timezone || this.$root.user.timezone,
                calendarOptions: {
                    locales: allLocales,
                    locale: this.tableMeta.calendar_locale || 'en',
                    timeZone: this.tZone,
                    plugins: [ dayGridPlugin, timeGridPlugin, listPlugin, interactionPlugin ],
                    initialView: 'dayGridMonth',
                    events: this.calendar_events,
                    headerToolbar: {
                        start: 'prev,next today',
                        center: 'title',
                        end: 'dayGridMonth,timeGridWeek,timeGridDay,listWeek',
                    },
                    droppable: true,
                    editable: true,

                    eventClick: this.handleEventClick,
                    eventDrop: this.handleDropClick,
                    eventResize: this.handleDropClick,
                },

                calendar_events: [],
                show_calendar_sett: false,
                can_calendar: false,
                editPopupRow: null,
                editPopupRole: 'add',
            }
        },
        props:{
            tableMeta: Object,
            tableRows: Array,
            isVisible: Boolean,
            add_click: Number,
            settings_click: Number,
        },
        watch: {
            isVisible(val) {
                if (val) {
                    this.redrCalend();
                }
            },
            add_click(val) {
                this.editPopupRow = this.$root.emptyObject(this.tableMeta);
                this.editPopupRole = 'add';
            },
            settings_click(val) {
                this.show_calendar_sett = true;
            },
        },
        computed: {
            startFld() {
                return _.find(this.tableMeta._fields, (fld) => { return !!fld.is_calendar_start; });
            },
            endFld() {
                return _.find(this.tableMeta._fields, (fld) => { return !!fld.is_calendar_end; })
                    || _.find(this.tableMeta._fields, (fld) => { return !!fld.is_calendar_start; });
            },
            titleFld() {
                return _.find(this.tableMeta._fields, (fld) => { return !!fld.is_calendar_title; });
            },
            condFormatFld() {
                return _.find(this.tableMeta._fields, (fld) => { return !!fld.is_calendar_cond_format; });
            },
        },
        methods: {
            handleEventClick(data) {
                this.editPopupRow = _.find(this.tableRows, {id: Number(data.event.id)});
                this.editPopupRole = 'update';
            },
            handleDropClick(data) {
                let row = _.find(this.tableRows, {id: Number(data.event.id)});
                if (row) {
                    row[this.startFld.field] = SpecialFuncs.convertToUTC(data.event.start, this.tZone);
                    row[this.endFld.field] = SpecialFuncs.convertToUTC(data.event.end, this.tZone);
                    this.updateRow(row);
                }
            },
            buildEvents() {
                this.tZone = this.tableMeta.calendar_timezone || this.$root.user.timezone;
                this.calendar_events = [];
                _.each(this.tableRows, (row) => {
                    if (this.startFld && this.endFld && this.titleFld) {
                        let onlydate = moment(row[this.endFld.field]) - moment(row[this.startFld.field]) > 1000*3600*24;
                        let start = SpecialFuncs.convertToLocal(row[this.startFld.field], this.tZone);
                        let end = SpecialFuncs.convertToLocal(row[this.endFld.field], this.tZone);

                        let condForm = this.condFormatFld ? this.getCellStyleMethod(row, this.condFormatFld) : null;
                        let clsNms = [];
                        if (condForm) {
                            switch (condForm.textDecoration) {
                                case 'line-through': clsNms.push('fc-date-strike'); break;
                                case 'overline': clsNms.push('fc-date-overline'); break;
                                case 'underline': clsNms.push('fc-date-underline'); break;
                            }
                            if (condForm.fontStyle) { clsNms.push('fc-date-italic'); }
                            if (condForm.fontWeight) { clsNms.push('fc-date-bold'); }
                        }

                        this.calendar_events.push({
                            id: row.id,
                            start: onlydate ? SpecialFuncs.dateTimeasDate(start) : start,
                            end: onlydate ? SpecialFuncs.dateTimeasDate(end, true) : end,
                            title: row[this.titleFld.field],
                            textColor: condForm && condForm.color ? condForm.color : '#222',
                            borderColor: condForm ? condForm.backgroundColor : '',
                            backgroundColor: condForm ? condForm.backgroundColor : '',
                            classNames: clsNms,
                        });
                    }
                });
                this.calendarOptions.events = this.calendar_events;
                this.calendarOptions.locale = this.tableMeta.calendar_locale || 'en';
                this.calendarOptions.timeZone = this.tZone;//test
            },
            redrCalend(type) {
                if (!this.isVisible) {
                    return;
                }
                let tmpVi = this.$refs.full_calendar ? this.$refs.full_calendar.getApi().view.type : this.calendarOptions.initialView;
                this.show_calendar_sett = false;
                this.can_calendar = false;
                this.$nextTick(() => {
                    this.buildEvents();
                    this.calendarOptions.initialView = tmpVi;
                    this.can_calendar = true;
                });
            },
            saveTbOnBknd(field, val) {
                axios.put('/ajax/table', {
                    table_id: this.tableMeta.id,
                    calendar_locale: this.tableMeta.calendar_locale,
                    calendar_timezone: this.tableMeta.calendar_timezone,
                }).catch(errors => {
                    Swal('', getErrors(errors));
                });
            },

            //Popup
            insertRow(tableRow) {
                eventBus.$emit('list-view-insert-row', tableRow);
            },
            copyRow(tableRow) {
                eventBus.$emit('list-view-copy-row', tableRow);
            },
            updateRow(tableRow) {
                eventBus.$emit('list-view-update-row', tableRow);
            },
            deleteRow(tableRow) {
                eventBus.$emit('list-view-delete-row', tableRow);
            },
            showSrcRecord(lnk, header, tableRow) {
                this.$emit('show-src-record', lnk, header, tableRow);
            },
            closePopUp() {
                this.editPopupRow = null;
            },
        },
        mounted() {
            eventBus.$on('new-request-params', this.redrCalend);
        },
        beforeDestroy() {
            eventBus.$off('new-request-params', this.redrCalend);
        }
    }
</script>

<style lang="scss" scoped>
    .calendar-settings {
        position: relative;
        height: 100%;
        background-color: #FFF;
        padding: 10px;
    }
</style>

<style lang="scss">
    .fc-date-bold {
        font-weight: bold;
    }
    .fc-date-italic {
        font-style: italic;
    }
    .fc-date-strike {
        text-decoration: 'line-through';
    }
    .fc-date-overline {
        text-decoration: 'overline';
    }
    .fc-date-underline {
        text-decoration: 'underline';
    }
</style>