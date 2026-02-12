<template>
    <div class="full-height" :style="$root.themeMainBgStyle">
        <div v-if="!$root.AddonAvailableToUser(tableMeta, 'twilio')" class="row full-frame flex flex--center">
            <label>Addon is unavailable!</label>
        </div>
        <div v-else class="tabs-vertical" style="padding: 5px;">
            <div class="tabs-vertical--header">
                <div class="tabs-vertical--buttons">
                    <button class="btn btn-default btn-sm left-btn"
                            :class="{active : acttab === 'tw_settings'}"
                            :style="textSysStyle"
                            @click="changeTab('tw_settings')"
                    >Settings</button>
                    <button class="btn btn-default btn-sm left-btn"
                            :class="{active : acttab === 'tw_preview'}"
                            :style="textSysStyle"
                            :disabled="!selected_addon || !total_messages || !selected_addon.twilio_active"
                            @click="changeTab('tw_preview')"
                    >Preview</button>
                    <button class="btn btn-default btn-sm left-btn"
                            :class="{active : acttab === 'tw_history'}"
                            :style="textSysStyle"
                            :disabled="!selected_addon || !total_messages || !selected_addon.twilio_active"
                            @click="changeTab('tw_history')"
                    >History</button>

                    <div v-if="selected_addon" style="position: absolute; top: 0; right: 0; width: 70%;" :style="$root.themeMainTxtColor">
                        <div v-show="acttab === 'list'" class="pull-right flex flex--center-v" style="margin: 0 10px;">
                            <button class="btn btn-default btn-sm blue-gradient full-height"
                                    :style="$root.themeButtonStyle"
                                    @click="copyAdnSett()"
                            >Copy</button>
                            <select class="form-control full-height" style="width: 150px;" v-model="sett_for_copy">
                                <option v-for="sett in tableMeta._twilio_addon_settings"
                                        v-if="sett.id != selected_addon.id"
                                        :value="sett.id"
                                >{{ sett.name }}</option>
                            </select>
                        </div>

                        <label class="pull-right flex flex--center" style="margin: -5px 15px; line-height: 20px;">
                            SMS Template Loaded:
                            <select-block
                                :options="twsettOpts()"
                                :sel_value="selected_addon.id"
                                :style="{ width:'150px', height:'32px', }"
                                @option-select="twsettChange"
                            ></select-block>

                            <info-sign-link v-show="acttab === 'tw_settings'" class="ml5 mr5 ilnk" :app_sett_key="'help_link_twilio_sms_settings'" :hgt="30" :txt="'for SMS/Settings'"></info-sign-link>
                            <info-sign-link v-show="acttab === 'tw_preview'" class="ml5 mr5 ilnk" :app_sett_key="'help_link_twilio_sms_preview'" :hgt="30" :txt="'for SMS/Preview'"></info-sign-link>
                            <info-sign-link v-show="acttab === 'tw_history'" class="ml5 mr5 ilnk" :app_sett_key="'help_link_twilio_sms_history'" :hgt="30" :txt="'for SMS/History'"></info-sign-link>
                        </label>
                        <label v-if="acttab === 'tw_settings' && !total_messages" class="pull-right flex flex--center red" style="margin: 0 15px; line-height: 20px;">No rows for sms!</label>
                    </div>
                </div>
            </div>
            <div class="tabs-vertical--body" :style="$root.themeMainBgStyle">

                <!--SETTINGS TAB-->
                <div class="full-frame" v-if="acttab === 'tw_settings'">
                    <div style="height: 30%">
                        <!--label style="font-size: 16px;margin: 0;height: 25px;">Twilio settings:</label -->
                        <custom-table
                            :cell_component_name="'custom-cell-col-row-group'"
                            :global-meta="tableMeta"
                            :table-meta="$root.settingsMeta['table_twilio_addon_settings']"
                            :all-rows="tableMeta._twilio_addon_settings"
                            :rows-count="tableMeta._twilio_addon_settings.length"
                            :cell-height="1"
                            :max-cell-rows="0"
                            :is-full-width="true"
                            :with_edit="canAddonEdit()"
                            :behavior="'data_sets'"
                            :user="$root.user"
                            :adding-row="addingRow"
                            :selected-row="adn_idx"
                            :available-columns="['name','acc_twilio_key_id','description','twilio_active']"
                            :use_theme="true"
                            :style="{height: 'calc(100% - 25px)'}"
                            @added-row="addAdn"
                            @updated-row="updateAdn"
                            @delete-row="deleteAdn"
                            @row-index-clicked="rowIndexClickedAdn"
                        ></custom-table>
                    </div>
                    <div style="height: 70%">
                        <twilio-setup
                            v-if="selected_addon"
                            :table-meta="tableMeta"
                            :twilio-settings="selected_addon"
                            :total_messages="total_messages"
                            :can_edit="canPermisEdit()"
                            @save-backend="updateAdn"
                        ></twilio-setup>
                        <label v-else class="flex flex--center">Please select Twilio settings</label>
                    </div>
                </div>
                <!--PREVIEW TAB-->
                <div class="full-frame" v-if="acttab === 'tw_preview' && selected_addon">
                    <twilio-preview
                        :table-meta="tableMeta"
                        :twilio-settings="selected_addon"
                        :total_messages="total_messages"
                        :can_edit="canPermisEdit()"
                        @update-addon="updateAdn"
                    ></twilio-preview>
                </div>
                <!--HISTORY TAB-->
                <div class="full-frame" v-if="acttab === 'tw_history' && selected_addon">
                    <twilio-history
                        :table-meta="tableMeta"
                        :twilio-settings="selected_addon"
                        :total_messages="total_messages"
                        :can_edit="canPermisEdit()"
                        @update-addon="updateAdn"
                    ></twilio-history>
                </div>

            </div>
        </div>
    </div>
</template>

<script>
    import CellStyleMixin from "../../../_Mixins/CellStyleMixin";

    import PhoneBlock from "../../../CommonBlocks/PhoneBlock";
    import CustomTable from "../../../CustomTable/CustomTable";
    import TwilioSetup from "./Twilio/TwilioSetup";
    import TwilioPreview from "./Twilio/TwilioPreview";
    import SelectBlock from "../../../CommonBlocks/SelectBlock";
    import TwilioHistory from "./Twilio/TwilioHistory";
    import InfoSignLink from "../../../CustomTable/Specials/InfoSignLink.vue";
    import EmailSetup from "./EmailAddon/EmailSetup.vue";

    export default {
        name: "TabTwilioView",
        mixins: [
            CellStyleMixin,
        ],
        components: {
            EmailSetup,
            InfoSignLink,
            TwilioHistory,
            SelectBlock,
            TwilioPreview,
            TwilioSetup,
            CustomTable,
            PhoneBlock
        },
        data: function () {
            return {
                acttab: 'tw_settings',
                total_messages: null,
                sett_for_copy: null,
                adn_idx: -1,
                addingRow: {
                    active: true,
                    position: 'bottom'
                },
            }
        },
        props: {
            tableMeta: Object,
            isVisible: Boolean,
        },
        computed: {
            selected_addon() {
                return this.tableMeta._twilio_addon_settings[this.adn_idx];
            },
        },
        methods: {
            canPermisEdit() {
                return this.$root.addonCanPermisEdit(this.tableMeta, this.selected_addon, '_twilio_rights');
            },
            canAddonEdit() {
                return this.$root.addonCanEditGeneral(this.tableMeta, 'twilio');
            },
            changeTab(key) {
                this.acttab = key;
            },
            countRows() {
                axios.post('/ajax/row-group/count', {
                    table_row_group_id: this.selected_addon.limit_row_group_id,
                }).then(({data}) => {
                    this.total_messages = data.total;
                });
            },

            //Addon Email Functions
            copyAdnSett() {
                this.$root.sm_msg_type = 1;
                axios.post('/ajax/addon-twilio-sett/copy', {
                    from_adn_id: this.sett_for_copy,
                    to_adn_id: this.selected_addon.id,
                }).then(({ data }) => {
                    this.$root.assignObject(data, this.selected_addon);
                }).catch(errors => {
                    Swal('Info', getErrors(errors));
                }).finally(() => {
                    this.$root.sm_msg_type = 0;
                });
            },
            addAdn(tableRow) {
                let fields = _.cloneDeep(tableRow);//copy object
                this.$root.deleteSystemFields(fields);

                this.$root.sm_msg_type = 1;
                axios.post('/ajax/addon-twilio-sett', {
                    table_id: this.tableMeta.id,
                    fields: fields,
                }).then(({ data }) => {
                    this.tableMeta._twilio_addon_settings.push( data );
                    this.rowIndexClickedAdn(this.tableMeta._twilio_addon_settings.length-1);
                }).catch(errors => {
                    Swal('Info', getErrors(errors));
                }).finally(() => {
                    this.$root.sm_msg_type = 0;
                });
            },
            updateAdn(tableRow, type) {
                axios.put('/ajax/addon-twilio-sett', {
                    twilio_add_id: tableRow.id,
                    fields: tableRow,
                }).then(({data}) => {
                    if (type === 'limit_row_group_id') {
                        this.countRows();
                    }
                }).catch(errors => {
                    Swal('Info', getErrors(errors));
                });
            },
            deleteAdn(tableRow) {
                this.$root.sm_msg_type = 1;
                axios.delete('/ajax/addon-twilio-sett', {
                    params: {
                        twilio_add_id: tableRow.id
                    }
                }).then(({ data }) => {
                    let idx = _.findIndex(this.tableMeta._twilio_addon_settings, {id: Number(tableRow.id)});
                    if (idx > -1) {
                        this.rowIndexClickedAdn(-1);
                        this.tableMeta._twilio_addon_settings.splice(idx, 1);
                    }
                }).catch(errors => {
                    Swal('Info', getErrors(errors));
                }).finally(() => {
                    this.$root.sm_msg_type = 0;
                });
            },
            rowIndexClickedAdn(idx) {
                this.adn_idx = -1;
                this.$nextTick(() => {
                    this.adn_idx = idx;
                    if (this.selected_addon) {
                        this.countRows();
                    } else {
                        this.changeTab('tw_settings');
                    }
                });
            },
            //top select
            twsettOpts() {
                return _.map(this.tableMeta._twilio_addon_settings, (fld) => {
                    return { val:fld.id, show:fld.name, };
                });
            },
            twsettChange(opt) {
                let ii = _.findIndex(this.tableMeta._twilio_addon_settings, {id: Number(opt.val)});
                this.rowIndexClickedAdn(ii);
            },
        },
        mounted() {
            this.rowIndexClickedAdn(0);
        },
        beforeDestroy() {
        }
    }
</script>

<style lang="scss" scoped>
//
</style>