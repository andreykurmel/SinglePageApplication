<template>
    <div v-if="tableMeta && $root.settingsMeta" class="container-fluid full-height" :style="sysStyleWiBg">
        <div v-if="!$root.AddonAvailableToUser(tableMeta, 'email')" class="row full-frame flex flex--center">
            <label>Addon is unavailable!</label>
        </div>
        <div v-else class="row full-height permissions-tab">
            <div class="permissions-panel full-height" :style="$root.themeMainBgStyle">
                <div class="permissions-menu-header">
                    <button class="btn btn-default btn-sm left-btn"
                            :class="{active : acttab === 'list'}"
                            :style="textSysStyle"
                            @click="changeTab('list')"
                    >List</button>
                    <button class="btn btn-default btn-sm left-btn"
                            :class="{active : acttab === 'setup'}"
                            :style="textSysStyle"
                            :disabled="!selected_addon"
                            @click="changeTab('setup')"
                    >Setup</button>
                    <button class="btn btn-default btn-sm left-btn"
                            :class="{active : acttab === 'body'}"
                            :style="textSysStyle"
                            :disabled="!selected_addon"
                            @click="changeTab('body')"
                    >Body</button>
                    <button class="btn btn-default btn-sm left-btn"
                            :class="{active : acttab === 'preview'}"
                            :style="textSysStyle"
                            :disabled="!selected_addon || !total_emails || total_emails > rows_limit || !selected_addon.email_active"
                            @click="changeTab('preview')"
                    >Preview</button>
                    <button class="btn btn-default btn-sm left-btn"
                            :class="{active : acttab === 'history'}"
                            :style="textSysStyle"
                            :disabled="!selected_addon || !total_emails || total_emails > rows_limit || !selected_addon.email_active"
                            @click="changeTab('history')"
                    >History</button>

                    <div v-if="selected_addon" class="flex flex--center-v flex--end" style="position: absolute; top: 0; right: 0; height: 32px; width: 70%;">
                        <label v-if="acttab === 'setup' && !total_emails" class="flex flex--center red" style="margin: 5px 15px; line-height: 20px;">No rows for email!</label>
                        <label v-if="acttab === 'setup' && total_emails > rows_limit" class="flex flex--center red" style="margin: 5px 15px; line-height: 20px;">Max records for email: {{ rows_limit }}!</label>

                        <div v-show="acttab === 'list'" class="flex flex--center-v ml15">
                            <button class="btn btn-default btn-sm blue-gradient mr5 full-height"
                                    :style="$root.themeButtonStyle"
                                    @click="copyAdnSett()"
                            >Copy</button>
                            <select class="form-control full-height" style="width: 150px;" v-model="sett_for_copy">
                                <option v-for="sett in tableMeta._email_addon_settings"
                                        v-if="sett.id != selected_addon.id"
                                        :value="sett.id"
                                >{{ sett.name }}</option>
                            </select>
                        </div>

                        <label class="flex flex--center ml15 mr0" style="margin-bottom: 0; white-space: nowrap;width: 300px;" :style="textSysStyleSmart">
                            Loaded Email:&nbsp;
                            <select-block
                                :options="emlOpts()"
                                :sel_value="selected_addon.id"
                                :style="{ maxWidth:'200px', height:'32px', }"
                                @option-select="emlChange"
                            ></select-block>
                        </label>

                        <info-sign-link v-show="acttab === 'list'" class="ml5 mr5 ilnk" :app_sett_key="'help_link_email_tab'" :hgt="30" :txt="'for Email/List'"></info-sign-link>
                        <info-sign-link v-show="acttab === 'setup'" class="ml5 mr5 ilnk" :app_sett_key="'help_link_email_tab_setup'" :hgt="30" :txt="'for Email/Setup'"></info-sign-link>
                        <info-sign-link v-show="acttab === 'body'" class="ml5 mr5 ilnk" :app_sett_key="'help_link_email_tab_body'" :hgt="30" :txt="'for Email/Body'"></info-sign-link>
                        <info-sign-link v-show="acttab === 'preview'" class="ml5 mr5 ilnk" :app_sett_key="'help_link_email_tab_preview'" :hgt="30" :txt="'for Email/Preview'"></info-sign-link>
                        <info-sign-link v-show="acttab === 'history'" class="ml5 mr5 ilnk" :app_sett_key="'help_link_email_tab_history'" :hgt="30" :txt="'for Email/History'"></info-sign-link>
                    </div>
                </div>
                <div class="permissions-menu-body" style="border: 1px solid #CCC;">

                    <!--LIST TAB-->
                    <div class="full-frame permissions-panel no-padding" v-if="acttab === 'list'" :style="$root.themeMainBgStyle">
                        <custom-table
                            :cell_component_name="'custom-cell-col-row-group'"
                            :global-meta="tableMeta"
                            :table-meta="$root.settingsMeta['table_email_addon_settings']"
                            :all-rows="tableMeta._email_addon_settings"
                            :rows-count="tableMeta._email_addon_settings.length"
                            :cell-height="1"
                            :max-cell-rows="0"
                            :is-full-width="true"
                            :with_edit="canAddonEdit()"
                            :behavior="'data_sets'"
                            :user="user"
                            :adding-row="addingRow"
                            :selected-row="adn_idx"
                            :available-columns="['name','description','email_active']"
                            :use_theme="true"
                            @added-row="addAdn"
                            @updated-row="updateAdn"
                            @delete-row="deleteAdn"
                            @row-index-clicked="rowIndexClickedAdn"
                        ></custom-table>
                    </div>
                    <!--SETUP TAB-->
                    <div class="full-frame p5 permissions-panel" v-if="acttab === 'setup'" :style="$root.themeMainBgStyle">
                        <email-setup
                                :table-meta="tableMeta"
                                :email-settings="selected_addon"
                                :total_emails="total_emails"
                                :can_edit="canPermisEdit()"
                                @save-backend="updateAdn"
                        ></email-setup>
                    </div>
                    <!--BODY TAB-->
                    <div class="full-frame p5 permissions-panel" v-if="isVisible && first_body" v-show="acttab === 'body'" :style="$root.themeMainBgStyle">
                        <tab-ckeditor
                                :table-meta="tableMeta"
                                :target-row="selected_addon"
                                :field-name="'email_body'"
                                :type="'email'"
                                :is_disabled="!canPermisEdit()"
                                @save-row="updateAdn"
                        ></tab-ckeditor>
                    </div>
                    <!--PREVIEW TAB-->
                    <div class="full-frame permissions-panel no-padding" v-if="acttab === 'preview'" :style="$root.themeMainBgStyle">
                        <email-preview
                                :table-meta="tableMeta"
                                :email-settings="selected_addon"
                                :total_emails="total_emails"
                                :can_edit="canPermisEdit()"
                                @update-addon="updateAdn"
                        ></email-preview>
                    </div>
                    <!--HISTORY TAB-->
                    <div class="full-frame permissions-panel no-padding" v-if="acttab === 'history'" :style="$root.themeMainBgStyle">
                        <email-history
                                :table-meta="tableMeta"
                                :email-settings="selected_addon"
                                :total_emails="total_emails"
                                :can_edit="canPermisEdit()"
                                @update-addon="updateAdn"
                        ></email-history>
                    </div>

                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import {eventBus} from "../../../../app";

    import {SpecialFuncs} from "../../../../classes/SpecialFuncs";
    import {RefCondHelper} from "../../../../classes/helpers/RefCondHelper";

    import CellStyleMixin from "../../../_Mixins/CellStyleMixin";

    import EmailSetup from "./EmailAddon/EmailSetup";
    import EmailPreview from "./EmailAddon/EmailPreview";
    import TabCkeditor from "../../../CommonBlocks/TabCkeditor";
    import CustomTable from "../../../CustomTable/CustomTable";
    import SelectBlock from "../../../CommonBlocks/SelectBlock";
    import InfoSignLink from "../../../CustomTable/Specials/InfoSignLink";
    import EmailHistory from "./EmailAddon/EmailHistory";

    export default {
        name: "TabEmailView",
        components: {
            EmailHistory,
            InfoSignLink,
            SelectBlock,
            CustomTable,
            TabCkeditor,
            EmailPreview,
            EmailSetup,
        },
        mixins: [
            CellStyleMixin,
        ],
        data: function () {
            return {
                first_body: false,
                acttab: 'list',
                total_emails: null,
                sett_for_copy: null,
                adn_idx: -1,
                addingRow: {
                    active: true,
                    position: 'bottom'
                },
            }
        },
        props:{
            tableMeta: Object,
            user: Object,
            isVisible: Boolean,
        },
        watch: {
            isVisible(val) {
                this.first_body = false;
                this.$nextTick(() => {
                    this.changeTab(this.acttab);
                });
            },
        },
        computed: {
            selected_addon() {
                return this.tableMeta._email_addon_settings[this.adn_idx];
            },
            rows_limit() {
                let limit = this.$root.settingsMeta.app_settings['app_max_records_email_adn'];
                return Number(limit.val);
            },
            sysStyleWiBg() {
                return {
                    ...this.textSysStyle,
                    ...this.$root.themeMainBgStyle,
                };
            },
        },
        methods: {
            canPermisEdit() {
                return this.$root.addonCanPermisEdit(this.tableMeta, this.selected_addon, '_email_rights');
            },
            canAddonEdit() {
                return this.$root.addonCanEditGeneral(this.tableMeta, 'email');
            },
            emlOpts() {
                return _.map(this.tableMeta._email_addon_settings, (dcr) => {
                    return { val:dcr.id, show:dcr.name };
                });
            },
            emlChange(opt) {
                this.adn_idx = _.findIndex(this.tableMeta._email_addon_settings, {id: Number(opt.val)});
                this.first_body = false;
                this.$nextTick(() => {
                    this.changeTab(this.acttab);
                });
            },
            changeTab(key) {
                this.acttab = key;
                if (! this.first_body && key === 'body') {
                    this.first_body = true;
                }
            },
            countRows() {
                axios.post('/ajax/row-group/count', {
                    table_row_group_id: this.selected_addon.limit_row_group_id,
                }).then(({data}) => {
                    this.total_emails = data.total;
                });
            },
            
            //Addon Email Functions
            copyAdnSett() {
                this.$root.sm_msg_type = 1;
                axios.post('/ajax/addon-email-sett/copy', {
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
                axios.post('/ajax/addon-email-sett', {
                    table_id: this.tableMeta.id,
                    fields: fields,
                }).then(({ data }) => {
                    this.tableMeta._email_addon_settings.push( data );
                    this.rowIndexClickedAdn(this.tableMeta._email_addon_settings.length-1);
                }).catch(errors => {
                    Swal('Info', getErrors(errors));
                }).finally(() => {
                    this.$root.sm_msg_type = 0;
                });
            },
            updateAdn(tableRow, type) {
                if (tableRow.email_body) {
                    tableRow.email_body =
                        tableRow.email_body.replaceAll('?s="', '?s=' + tableRow.hash + '"');
                }
                axios.put('/ajax/addon-email-sett', {
                    email_add_id: tableRow.id,
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
                axios.delete('/ajax/addon-email-sett', {
                    params: {
                        email_add_id: tableRow.id
                    }
                }).then(({ data }) => {
                    let idx = _.findIndex(this.tableMeta._email_addon_settings, {id: Number(tableRow.id)});
                    if (idx > -1) {
                        this.rowIndexClickedAdn(-1);
                        this.tableMeta._email_addon_settings.splice(idx, 1);
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
                        this.changeTab('list');
                    }
                });
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
    @import "./SettingsModule/TabSettingsPermissions";

    .permissions-tab {
        padding: 5px;

        .permissions-panel {
            height: 100%;
        }
    }
</style>