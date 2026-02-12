<template>
    <div class="calendar-settings">
        <div v-if="!startFld || !endFld || !titleFld" class="flex flex--center full-height" style="font-size: 2em;">Settings are empty!</div>
        <full-calendar v-else-if="can_calendar"
                       ref="full_calendar"
                       :options="calendarOptions"
                       class="full-height"
        ></full-calendar>

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
            :cell-height="1"
            :max-cell-rows="0"
            @popup-insert="insertRow"
            @popup-update="updateRow"
            @popup-copy="copyRow"
            @popup-delete="deleteRow"
            @show-src-record="showSrcRecord"
            @popup-close="closePopUp"
        ></custom-edit-pop-up>
    </div>
</template>

<script>
import {SpecialFuncs} from '../../../../../classes/SpecialFuncs';
import {Endpoints} from "../../../../../classes/Endpoints";

import {eventBus} from '../../../../../app';

import FullCalendar from '@fullcalendar/vue';
import dayGridPlugin from '@fullcalendar/daygrid';
import interactionPlugin from '@fullcalendar/interaction';
import timeGridPlugin from '@fullcalendar/timegrid';
import listPlugin from '@fullcalendar/list';
import allLocales from '@fullcalendar/core/locales-all';

import CellStyleMixin from "./../../../../_Mixins/CellStyleMixin.vue";
import MixinForAddons from "./../MixinForAddons";

import CustomEditPopUp from "../../../../CustomPopup/CustomEditPopUp";

export default {
    name: "TabCalendarView",
    mixins: [
        CellStyleMixin,
        MixinForAddons,
    ],
    components: {
        CustomEditPopUp,
        FullCalendar,
    },
    data: function () {
        return {
            tZone: this.selectedCalendar.calendar_timezone || this.$root.user.timezone,
            calendarOptions: {
                locales: allLocales,
                locale: this.selectedCalendar.calendar_locale || 'en',
                timeZone: 'UTC',
                plugins: [ dayGridPlugin, timeGridPlugin, listPlugin, interactionPlugin ],
                initialView: 'dayGridMonth',
                initialDate: moment()
                    .tz(this.selectedCalendar.calendar_timezone || this.$root.user.timezone)
                    .format(SpecialFuncs.dateTimeFormat()),
                events: this.calendar_events,
                headerToolbar: {
                    start: 'prev,next today',
                    center: 'title',
                    end: 'dayGridMonth,timeGridWeek,timeGridDay,listWeek',
                },
                droppable: true,
                editable: true,
                now: moment()
                    .tz(this.selectedCalendar.calendar_timezone || this.$root.user.timezone)
                    .format(SpecialFuncs.dateTimeFormat()),

                eventClick: (data) => { this.popupClick(data.event.id) },
                eventDrop: this.handleDropClick,
                eventResize: this.handleDropClick,
            },
            calendar_events: [],
            can_calendar: false,
            redrawOnEdit: true,
        }
    },
    props:{
        tableMeta: Object,
        requestParams: Object,//MixinForAddon
        currentPageRows: Array,//MixinForAddon
        selectedCalendar: Object,
        selectedState: Object,
        add_click: Number,
    },
    watch: {
    },
    computed: {
        startFld() {
            return _.find(this.tableMeta._fields, {id: Number(this.selectedCalendar.fldid_calendar_start)});
        },
        endFld() {
            return _.find(this.tableMeta._fields, {id: Number(this.selectedCalendar.fldid_calendar_end)})
                || _.find(this.tableMeta._fields, {id: Number(this.selectedCalendar.fldid_calendar_start)});
        },
        titleFld() {
            return _.find(this.tableMeta._fields, {id: Number(this.selectedCalendar.fldid_calendar_title)});
        },
        condFormatFld() {
            return _.find(this.tableMeta._fields, {id: Number(this.selectedCalendar.fldid_calendar_cond_format)});
        },
    },
    methods: {
        handleDropClick(data) {
            let row = _.find(this.tableRows, {id: Number(data.event.id)});
            if (row) {
                let onlydate = moment(data.event.end).diff( moment(data.event.start), 'days' );
                if (onlydate) {
                    row[this.startFld.field] = SpecialFuncs.convertToUTC( SpecialFuncs.dateTimeasDate(data.event.start, 0)+' 00:00:01', this.tZone);
                    row[this.endFld.field] = SpecialFuncs.convertToUTC( SpecialFuncs.dateTimeasDate(data.event.end, -1)+' 23:59:59', this.tZone);
                } else {
                    row[this.startFld.field] = SpecialFuncs.convertToUTC( SpecialFuncs.dateTimeasDate(data.event.start, 0, 1), this.tZone);
                    row[this.endFld.field] = SpecialFuncs.convertToUTC( SpecialFuncs.dateTimeasDate(data.event.end, 0, 1), this.tZone);
                }
                this.updateRow(row);
            }
        },
        buildEvents() {
            this.tZone = this.selectedCalendar.calendar_timezone || this.$root.user.timezone;
            this.calendar_events = [];
            _.each(this.tableRows, (row) => {
                if (this.startFld && this.endFld && this.titleFld) {
                    let onlydate = moment(row[this.endFld.field]).diff( moment(row[this.startFld.field]), 'days' );
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
                        switch (condForm.fontSize) {
                            case '10px':
                            case '11px':
                            case '12px': clsNms.push('fc-fontsize-12'); break;
                            case '13px':
                            case '14px': clsNms.push('fc-fontsize-14'); break;
                            case '15px':
                            case '16px': clsNms.push('fc-fontsize-16'); break;
                            case '17px':
                            case '18px': clsNms.push('fc-fontsize-18'); break;
                            case '19px':
                            case '20px': clsNms.push('fc-fontsize-20'); break;
                        }
                    }

                    this.calendar_events.push({
                        id: row.id,
                        start: onlydate ? SpecialFuncs.dateTimeasDate(start) : start,
                        end: onlydate ? SpecialFuncs.dateTimeasDate(end, 1) : end,
                        title: row[this.titleFld.field],
                        textColor: condForm && condForm.color ? condForm.color : '#222',
                        borderColor: condForm ? condForm.backgroundColor : '',
                        backgroundColor: condForm ? condForm.backgroundColor : '',
                        classNames: clsNms,
                    });
                }
            });
            this.calendarOptions.events = this.calendar_events;
            this.calendarOptions.locale = this.selectedCalendar.calendar_locale || 'en';
            this.findInitialDate();
        },
        findInitialDate() {
            let init = this.selectedCalendar.calendar_init_date;
            if (init === 'earliest' && this.startFld) {
                let earliest = '';
                _.each(this.tableRows, (row) => {
                    let date = SpecialFuncs.convertToLocal(row[this.startFld.field], this.tZone);
                    if (date && (!earliest || earliest > date)) {
                        earliest = date;
                    }
                });
                this.calendarOptions.initialDate = earliest || this.calendarOptions.initialDate;
            }
            if (init === 'latest' && this.startFld) {
                let latest = '';
                _.each(this.tableRows, (row) => {
                    let date = SpecialFuncs.convertToLocal(row[this.startFld.field], this.tZone);
                    if (date && latest < date) {
                        latest = date;
                    }
                });
                this.calendarOptions.initialDate = latest || this.calendarOptions.initialDate;
            }
        },
        storeStatus() {
            let calendarView = this.$refs.full_calendar ? this.$refs.full_calendar.getApi().view : {};
            let tmpType = calendarView.type;
            let tmpStart = calendarView.currentStart;
        },
        drawAddon() {
            this.storeStatus();
            this.can_calendar = false;
            this.$nextTick(() => {
                this.buildEvents();
                this.can_calendar = true;

                this.$nextTick(() => {
                    if (this.$refs.full_calendar && this.selectedState && this.selectedState.type) {
                        this.$refs.full_calendar.getApi().changeView(this.selectedState.type, this.selectedState.start);
                    }
                });
            });
        },
        mountedFunc() {
            this.getRows(this.selectedCalendar.calendar_data_range, 'calendar', this.selectedCalendar.id);
        },
    },
    mounted() {
        this.mountedFunc();
        eventBus.$on('new-request-params', this.mountedFunc);
    },
    beforeDestroy() {
        this.storeStatus();
        eventBus.$off('new-request-params', this.mountedFunc);
    },
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
    font-weight: bold !important;
}
.fc-date-italic {
    font-style: italic !important;
}
.fc-date-strike {
    text-decoration: line-through !important;
}
.fc-date-overline {
    text-decoration: overline !important;
}
.fc-date-underline {
    text-decoration: underline !important;
}
.fc-fontsize-12 {
    font-size: 12px !important;
}
.fc-fontsize-14 {
    font-size: 14px !important;
}
.fc-fontsize-16 {
    font-size: 16px !important;
}
.fc-fontsize-18 {
    font-size: 18px !important;
}
.fc-fontsize-20 {
    font-size: 20px !important;
}
</style>