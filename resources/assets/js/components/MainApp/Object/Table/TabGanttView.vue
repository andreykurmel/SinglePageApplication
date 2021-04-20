<template>
    <div v-if="tableMeta && settingsMeta" class="tab-settings full-height">
        <div v-if="!$root.AddonAvailableToUser(tableMeta, 'gantt')" class="row full-frame flex flex--center">
            <label>Addon is unavailable!</label>
        </div>
        <template v-else="">
            <div class="full-frame">
                <gantt-chart
                        v-if="can_gantt"
                        :table-meta="tableMeta"
                        :all-rows="$root.listTableRows"
                        :add_click="add_click"
                        @show-src-record="showSrcRecord"
                ></gantt-chart>
            </div>

            <slot-popup v-if="tableMeta._is_owner && show_gantt_sett" :popup_width="1200" @popup-close="redrGantt()">
                <template v-slot:title>
                    <span>Gantt Setup</span>
                </template>
                <template v-slot:body>
                    <gantt-settings
                            :table-meta="tableMeta"
                            style="background-color: #005fa4;"
                            @save-backend="saveTbOnBknd"
                    ></gantt-settings>
                </template>
            </slot-popup>
        </template>
    </div>
</template>

<script>
    import GanttSettings from "./Gantt/GanttSettings";
    import GanttChart from "./Gantt/GanttChart";
    import SlotPopup from "../../../CustomPopup/SlotPopup";

    export default {
        name: "TabGanttView",
        components: {
            SlotPopup,
            GanttChart,
            GanttSettings,
        },
        data: function () {
            return {
                can_gantt: true,
                show_gantt_sett: false,
            }
        },
        props:{
            table_id: Number,
            tableMeta: Object,
            settingsMeta: Object,
            user: Object,
            add_click: Number,
            settings_click: Number,
        },
        computed: {
        },
        watch: {
            table_id(val) {
                this.redrGantt();
            },
            settings_click(val) {
                this.show_gantt_sett = true;
            },
        },
        methods: {
            redrGantt() {
                this.show_gantt_sett = false;
                this.can_gantt = false;
                this.$nextTick(() => {
                    this.can_gantt = true;
                });
            },
            saveTbOnBknd(field, val) {
                axios.put('/ajax/table', {
                    table_id: this.tableMeta.id,
                    gantt_info_type: this.tableMeta.gantt_info_type,
                    gantt_navigation: this.tableMeta.gantt_navigation,
                    gantt_show_names: this.tableMeta.gantt_show_names,
                    gantt_highlight: this.tableMeta.gantt_highlight,
                    gantt_day_format: this.tableMeta.gantt_day_format,
                }).catch(errors => {
                    Swal('', getErrors(errors));
                });
            },
            showSrcRecord(lnk, header, tableRow) {
                this.$emit('show-src-record', lnk, header, tableRow);
            },
        },
        mounted() {
        },
        beforeDestroy() {
        }
    }
</script>

<style lang="scss" scoped>
    .tab-settings {
        position: relative;
        height: 100%;
        background-color: #FFF;

        .menu-header {
            position: relative;
            margin-left: 10px;
            top: 3px;

            .left-btn {
                position: relative;
                top: 5px;
            }

            button {
                background-color: #CCC;
                outline: none;
                &.active {
                    background-color: #FFF;
                }
                &:not(.active):hover {
                    color: black;
                }
            }
        }

        .menu-body {
            position: absolute;
            top: 35px;
            right: 5px;
            left: 5px;
            bottom: 5px;
            background-color: #005fa4;
            border-radius: 5px;
        }

        .pull-right {
            margin-right: 5px;
        }
    }
</style>