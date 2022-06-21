<template>
    <div class="full-height permissions-tab" :style="textSysStyle">
        <div class="permissions-menu-header">
            <button class="btn btn-default btn-sm" :style="textSysStyle" :class="{active : activeTab === 'UFV'}" @click="activeTab = 'UFV'">
                Updating Field Value (UFV)
            </button>
            <button class="btn btn-default btn-sm" :style="textSysStyle" :class="{active : activeTab === 'ANR'}" @click="activeTab = 'ANR'">
                Adding New Records (ANR)
            </button>
        </div>
        <div class="permissions-menu-body" style="border: 1px solid #CCC;">

            <alert-automation-ufv
                    v-show="activeTab === 'UFV'"
                    :table-meta="tableMeta"
                    :alert_sett="alert_sett"
                    :can_edit="can_edit"
                    style="padding: 5px;"
                    @update-alert="sendUpdate"
            ></alert-automation-ufv>

            <alert-automation-anr
                    v-show="activeTab === 'ANR'"
                    :table-meta="tableMeta"
                    :alert_sett="alert_sett"
                    :can_edit="can_edit"
                    style="padding: 5px;"
                    @update-alert="sendUpdate"
            ></alert-automation-anr>

        </div>
    </div>
</template>

<script>
    import CustomTable from "../../../../CustomTable/CustomTable";
    import AlertAutomationAnr from "./AlertAutomationAnr";
    import AlertAutomationUfv from "./AlertAutomationUfv";

    import CellStyleMixin from "../../../../_Mixins/CellStyleMixin";

    export default {
        name: "AlertAutomations",
        components: {
            AlertAutomationUfv,
            AlertAutomationAnr,
            CustomTable,
        },
        mixins: [
            CellStyleMixin,
        ],
        data: function () {
            return {
                activeTab: 'UFV',
                addingRow: {
                    active: true,
                    position: 'bottom'
                },
                availAleCols: ['table_ref_cond_id','table_field_id','ufv_source','ufv_input','ufv_inherit_field_id','is_active'],
            }
        },
        props:{
            tableMeta: Object,
            alert_sett: Object,
            can_edit: Boolean,
        },
        computed: {
        },
        watch: {
        },
        methods: {
            updateCheckBox(param) {
                this.alert_sett[param] = this.alert_sett[param] ? 0 : 1;
                this.sendUpdate();
            },
            sendUpdate() {
                this.$emit('update-alert', this.alert_sett);
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

    .spaced-table {
        width: 100%;

        label {
            margin: 0;
        }

        .th_style {
            display: block;
            padding: 5px;
            background: linear-gradient(rgb(255, 255, 255), rgb(209, 209, 209) 50%, rgb(199, 199, 199) 50%, rgb(230, 230, 230));
            border: 1px solid #CCC;
        }

        .pad-bot {
            padding-bottom: 15px;
        }
    }

    .permissions-menu-body {
        label {
            margin: 0;
        }
    }
    .btn-default {
        height: 30px;
    }
</style>