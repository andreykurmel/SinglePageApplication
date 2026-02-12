<template>
    <div class="full-height permissions-tab p5" :style="$root.themeMainBgStyle">

        <div v-if="!$root.AddonAvailableToUser(tableMeta, 'ai')" class="permissions-panel full-height flex flex--center" :style="$root.themeMainBgStyle">
            <label>Addon is unavailable!</label>
        </div>
        <div v-else class="permissions-panel full-height" :style="$root.themeMainBgStyle">
            <div class="permissions-menu-header">
                <button class="btn btn-default btn-sm" :style="textSysStyle" :class="{active : activeAiTab === 'list'}" @click="changeAiTab('list')">
                    List
                </button>
                <template v-for="ai in tableMeta._table_ais" v-if="! ai.is_right_panel">
                    <button class="btn btn-default btn-sm"
                            :class="{active : activeAiTab === ai.id}"
                            :style="textSysStyle"
                            @click="changeAiTab(ai.id)"
                    ><i class="fas fa-chart-bar"></i>&nbsp;{{ ai.name }}</button>
                </template>

                <div v-if="selAi" class="flex flex--center-v flex--end no-wrap" style="position: absolute; top: 0; right: 0; height: 32px; width: 50%;">

                    <button class="btn btn-primary btn-sm blue-gradient mr15"
                            @click="removeMessage(-1)"
                            :style="$root.themeButtonStyle"
                    >Delete All</button>

                    <div class="flex flex--center" :style="textSysStyleSmart">
                        <ai-settings
                            :sel-ai="selAi"
                            :can_edit="canPermisEdit()"
                            @ai-updated="updateAi(selAi)"
                        ></ai-settings>
                    </div>
                    <label class="flex flex--center ml15 mr15 mb0" :style="textSysStyleSmart">
                        <input type="checkbox" class="no-margin" v-model="selAi.with_table_data" @change="updateAi(selAi)">
                        &nbsp;Table Data
                    </label>
<!--                    <label class="flex flex&#45;&#45;center ml5 mr15 mb0" :style="textSysStyleSmart">-->
<!--                        <input type="checkbox" class="no-margin" v-model="selAi.with_outside_data" @change="updateAi(selAi)">-->
<!--                        &nbsp;Outside Data&nbsp;-->
<!--                    </label>-->
                    <label class="flex flex--center mr0" style="margin: 0 0 0 5px;white-space: nowrap;width: 300px;" :style="textSysStyleSmart">
                        Loaded Module:
                        <select-block
                            :options="aiOpts()"
                            :sel_value="selAi.id"
                            :style="{ maxWidth:'200px', height:'32px', }"
                            @option-select="aiChange"
                        ></select-block>
                    </label>

                    <info-sign-link v-show="activeAiTab === 'list'" class="ml5 mr5 ilnk" :app_sett_key="'help_link_alerts_popup'" :hgt="30" :txt="'for AI/List'"></info-sign-link>
                </div>
            </div>
            <div class="permissions-menu-body" style="border: 1px solid #CCC;">

                <div class="full-height permissions-panel no-padding" v-show="activeAiTab === 'list'" :style="$root.themeMainBgStyle">
                    <custom-table
                        :cell_component_name="'custom-cell-addon'"
                        :global-meta="tableMeta"
                        :table-meta="$root.settingsMeta['table_ais']"
                        :all-rows="tableMeta._table_ais"
                        :rows-count="tableMeta._table_ais.length"
                        :cell-height="2"
                        :max-cell-rows="0"
                        :is-full-width="true"
                        :user="$root.user"
                        :behavior="'ai_addon'"
                        :adding-row="addingRow"
                        :selected-row="selectedCol"
                        :available-columns="['name','openai_key_id','related_table_ids','ai_data_range','description','is_right_panel']"
                        :with_edit="canAddonEdit()"
                        :use_theme="true"
                        @added-row="addAi"
                        @updated-row="updateAi"
                        @delete-row="deleteAi"
                        @row-index-clicked="rowIndexClicked"
                    ></custom-table>
                </div>

                <div class="full-frame flex flex--col"
                     v-show="activeAiTab !== 'list'"
                     v-if="canDraw"
                     :style="aiModuleStyle()"
                >
                    <ai-module :selected-ai="selAi" :request_params="request_params" @remove-msg="removeMessage"></ai-module>
                </div>

            </div>
        </div>
    </div>
</template>

<script>
    import {SpecialFuncs} from "../../../../classes/SpecialFuncs";
    import {Endpoints} from "../../../../classes/Endpoints";

    import CellStyleMixin from "./../../../_Mixins/CellStyleMixin.vue";
    import ModuleViewMixin from "./AiAddon/ModuleViewMixin.vue";
    
    import CustomTable from "../../../CustomTable/CustomTable.vue";
    import SelectBlock from "../../../CommonBlocks/SelectBlock.vue";
    import InfoSignLink from "../../../CustomTable/Specials/InfoSignLink.vue";
    import AiModule from "./AiAddon/AiModule.vue";
    import TabldaColopicker from "../../../CustomCell/InCell/TabldaColopicker.vue";
    import TabldaSelectSimple from "../../../CustomCell/Selects/TabldaSelectSimple.vue";
    import AiSettings from "./AiAddon/AiSettings.vue";

    export default {
        name: "TabAiView",
        mixins: [
            ModuleViewMixin,
            CellStyleMixin,
        ],
        components: {
            AiSettings,
            TabldaSelectSimple,
            TabldaColopicker,
            AiModule,
            InfoSignLink,
            SelectBlock,
            CustomTable,
        },
        data: function () {
            return {
                selectedCol: -1,
                activeAiTab: 'list',
            }
        },
        props: {
            table_id: Number,
            tableMeta: Object,
            tableRows: Array,
            isVisible: Boolean,
            request_params: Object,
        },
        computed: {
            selAi() {
                return this.tableMeta._table_ais && this.tableMeta._table_ais[this.selectedCol]
                    ? this.tableMeta._table_ais[this.selectedCol]
                    : null;
            },
            isOwner() {
                return this.$root.user.id === this.tableMeta.user_id;
            },
            canDraw() {
                return this.isVisible && this.activeAiTab;
            },
            addingRow() {
                return {
                    active: this.isOwner,
                    position: 'bottom'
                };
            },
        },
        watch: {
            table_id(val) {
                this.rowIndexClicked(-1);
            },
        },
        methods: {
            changeAiTab(val) {
                this.activeAiTab = '';
                this.$nextTick(() => {
                    this.aiChange({val: val});
                });
            },
            canPermisEdit() {
                return this.$root.addonCanPermisEdit(this.tableMeta, this.selAi, '_ai_rights');
            },
            canAddonEdit() {
                return this.$root.addonCanEditGeneral(this.tableMeta, 'ai');
            },
            aiOpts() {
                return _.map(this.tableMeta._table_ais, (dcr) => {
                    return { val:dcr.id, show:dcr.name };
                });
            },
            aiChange(opt) {
                this.rowIndexClicked(
                    _.findIndex(this.tableMeta._table_ais, {id: Number(opt.val)})
                );
            },
            rowIndexClicked(index) {
                this.selectedCol = index;
                this.$nextTick(() => {
                    this.activeAiTab = this.selAi ? this.selAi.id : 'list';
                });
            },
            addAi(tableRow) {
                let fields = _.cloneDeep(tableRow);//copy object
                this.$root.deleteSystemFields(fields);

                $.LoadingOverlay('show');
                axios.post('/ajax/addon-ai', {
                    table_id: this.tableMeta.id,
                    fields: fields
                }).then(({ data }) => {
                    this.tableMeta._table_ais = data;
                }).catch(errors => {
                    Swal('Info', getErrors(errors));
                }).finally(() => {
                    $.LoadingOverlay('hide');
                });
            },
            updateAi(tableRow) {
                let alert_id = tableRow.id;
                let fields = _.cloneDeep(tableRow);//copy object
                this.$root.deleteSystemFields(fields);

                $.LoadingOverlay('show');
                axios.put('/ajax/addon-ai', {
                    model_id: alert_id,
                    fields: fields
                }).then(({ data }) => {
                    this.tableMeta._table_ais = data;
                }).catch(errors => {
                    Swal('Info', getErrors(errors));
                }).finally(() => {
                    $.LoadingOverlay('hide');
                });
            },
            deleteAi(tableRow) {
                $.LoadingOverlay('show');
                axios.delete('/ajax/addon-ai', {
                    params: {
                        model_id: tableRow.id
                    }
                }).then(({ data }) => {
                    this.tableMeta._table_ais = data;
                    this.selectedCol = -1;
                }).catch(errors => {
                    Swal('Info', getErrors(errors));
                }).finally(() => {
                    $.LoadingOverlay('hide');
                });
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