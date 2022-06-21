<template>
    <div v-if="tableMeta && settingsMeta" class="tab-settings full-height">
        <div v-if="!$root.AddonAvailableToUser(tableMeta, 'kanban')" class="row full-frame flex flex--center">
            <label>Addon is unavailable!</label>
        </div>
        <div v-else="" class="full-height">
            <div class="menu-header">
                <button v-if="tableMeta._is_owner"
                        class="btn btn-default btn-sm left-btn"
                        :class="{active : acttab === 'settings'}"
                        :style="textSysStyle"
                        @click="changeActab('settings')"
                >Settings</button>
                <template v-for="hdr in ActiveKanbanFields">
                    <button class="btn btn-default btn-sm left-btn"
                            :class="{active : acttab === hdr.field}"
                            :style="textSysStyle"
                            style="margin-right: 3px;"
                            @click="changeActab(hdr.field)"
                    ><i class="fab fa-trello"></i>&nbsp;{{ hdr.kanban_field_name || hdr.name }}</button>
                </template>
                <button v-show="acttab !== 'settings'"
                        class="btn btn-primary btn-sm blue-gradient pull-right"
                        :disabled="!canAdd"
                        :style="$root.themeButtonStyle"
                        @click="add_click++"
                >Add</button>
            </div>
            <div class="menu-body">

                <!--SETTINGS TAB-->

                <div class="full-frame" v-if="tableMeta._is_owner" v-show="acttab === 'settings'">
                    <kanban-settings
                        :table_id="tableMeta.id"
                        :table-meta="tableMeta"
                        @save-backend="saveTbOnBknd"
                    ></kanban-settings>
                </div>

                <!--BASICS TAB-->

                <div class="full-frame" v-show="acttab !== 'settings'">
                    <kanban-tab
                            v-if="tableMeta && $root.listTableRows && selectedKanbanFld"
                            :table-meta="tableMeta"
                            :all-rows="$root.listTableRows"
                            :kanban-fld="selectedKanbanFld"
                            :add_click="add_click"
                            @save-backend="saveTbOnBknd"
                            @show-src-record="showSrcRecord"
                    ></kanban-tab>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import KanbanSettings from "./Kanban/KanbanSettings";
    import KanbanTab from "./Kanban/KanbanTab";

    import CanEditMixin from "../../../_Mixins/CanViewEditMixin";
    import CellStyleMixin from "../../../_Mixins/CellStyleMixin";

    export default {
        name: "TabKanbanView",
        mixins: [
            CanEditMixin,
            CellStyleMixin,
        ],
        components: {
            KanbanTab,
            KanbanSettings,
        },
        data: function () {
            return {
                acttab: '',
                add_click: 0,
            }
        },
        props:{
            table_id: Number,
            tableMeta: Object,
            settingsMeta: Object,
            user: Object,
        },
        computed: {
            ActiveKanbanFields() {
                return _.filter(this.tableMeta._fields, (fld) => {
                    return fld.kanban_group == 1 && fld._kanban_setting && fld._kanban_setting.kanban_group_field_id;
                });
            },
            selectedKanbanFld() {
                return _.find(this.tableMeta._fields, {field: this.acttab});
            },
        },
        watch: {
            table_id(val) {
                this.changeActab('settings');
            }
        },
        methods: {
            addClicked() {
                this.editPopUpRow = this.emptyObject();
                this.popUpRole = 'add';
            },
            changeActab(val) {
                this.acttab = '';
                this.$nextTick(() => {
                    this.acttab = val;
                });
            },
            saveTbOnBknd(field, val) {
                if (field && val) {
                    this.tableMeta[field] = val;
                }
                axios.put('/ajax/table', {
                    table_id: this.tableMeta.id,
                    kanban_form_table: this.tableMeta.kanban_form_table,
                    kanban_center_align: this.tableMeta.kanban_center_align,
                    kanban_card_width: this.tableMeta.kanban_card_width,
                    kanban_card_height: this.tableMeta.kanban_card_height,
                    kanban_sort_type: this.tableMeta.kanban_sort_type,
                    kanban_header_color: this.tableMeta.kanban_header_color,
                    kanban_hide_empty_tab: this.tableMeta.kanban_hide_empty_tab,
                    kanban_picture_field: this.tableMeta.kanban_picture_field,
                    kanban_picture_width: this.tableMeta.kanban_picture_width,
                }).catch(errors => {
                    Swal('', getErrors(errors));
                });
            },
            showSrcRecord(lnk, header, tableRow) {
                this.$emit('show-src-record', lnk, header, tableRow);
            },
        },
        mounted() {
            this.acttab = this.tableMeta._is_owner ? 'settings' : _.first(this.ActiveKanbanFields).field;
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

        .blue-gradient {
            margin-right: 5px;
        }

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

            .right-elm {
                float: right;
                margin-left: 10px;
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

        .btn-default {
            height: 30px;
        }
    }
</style>