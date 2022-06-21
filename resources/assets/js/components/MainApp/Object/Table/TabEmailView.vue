<template>
    <div v-if="tableMeta && settingsMeta" class="tab-settings full-height" :style="textSysStyle">
        <div v-if="!$root.AddonAvailableToUser(tableMeta, 'email')" class="row full-frame flex flex--center">
            <label>Addon is unavailable!</label>
        </div>
        <template v-else-if="tableMeta._is_owner">
            <div class="menu-header">
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
                        :disabled="!selected_addon"
                        @click="changeTab('preview')"
                >Preview</button>

                <div v-if="selected_addon" style="position: absolute; top: 0; right: 0; width: 70%;">
                    <div v-show="acttab === 'list'" class="pull-right flex flex--center-v" style="margin: 0 10px;">
                        <button class="btn btn-default btn-sm blue-gradient full-height"
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

                    <label class="pull-right flex flex--center" style="margin: 5px 15px; line-height: 20px;">Email Loaded: {{ selected_addon.name }}</label>
                </div>
            </div>
            <div class="menu-body" :style="{padding: acttab === 'preview' ? '0' : '10px'}">

                <!--LIST TAB-->
                <div class="full-frame" v-if="acttab === 'list'">
                    <custom-table
                        :cell_component_name="'custom-cell-col-row-group'"
                        :global-meta="tableMeta"
                        :table-meta="settingsMeta['table_email_addon_settings']"
                        :all-rows="tableMeta._email_addon_settings"
                        :rows-count="tableMeta._email_addon_settings.length"
                        :cell-height="1"
                        :max-cell-rows="0"
                        :is-full-width="true"
                        :behavior="'data_sets'"
                        :user="user"
                        :adding-row="addingRow"
                        :selected-row="adn_idx"
                        :available-columns="['name','description']"
                        :use_theme="true"
                        :no_width="true"
                        @added-row="addAdn"
                        @updated-row="updateAdn"
                        @delete-row="deleteAdn"
                        @row-index-clicked="rowIndexClickedAdn"
                    ></custom-table>
                </div>
                <!--SETUP TAB-->
                <div class="full-frame" v-if="acttab === 'setup'">
                    <email-setup
                            :table-meta="tableMeta"
                            :email-settings="selected_addon"
                            :total_emails="total_emails"
                            @save-backend="updateAdn"
                    ></email-setup>
                </div>
                <!--BODY TAB-->
                <div class="full-frame" v-if="acttab === 'body'">
                    <tab-ckeditor
                            :table-meta="tableMeta"
                            :target-row="selected_addon"
                            :field-name="'email_body'"
                            :type="'email'"
                            @save-row="updateAdn"
                    ></tab-ckeditor>
                </div>
                <!--PREVIEW TAB-->
                <div class="full-frame" v-if="acttab === 'preview'">
                    <email-preview
                            :table-meta="tableMeta"
                            :email-settings="selected_addon"
                            :total_emails="total_emails"
                            @update-addon="updateAdn"
                    ></email-preview>
                </div>

            </div>
        </template>
    </div>
</template>

<script>
    import {eventBus} from "../../../../app";

    import {SpecialFuncs} from "../../../../classes/SpecialFuncs";
    import {RefCondHelper} from "../../../../classes/helpers/RefCondHelper";

    import EmailSetup from "./EmailAddon/EmailSetup";
    import EmailPreview from "./EmailAddon/EmailPreview";
    import TabCkeditor from "../../../CommonBlocks/TabCkeditor";
    import CustomTable from "../../../CustomTable/CustomTable";

    import CellStyleMixin from "../../../_Mixins/CellStyleMixin";

    export default {
        name: "TabEmailView",
        components: {
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
            settingsMeta: Object,
            user: Object,
        },
        computed: {
            selected_addon() {
                return this.tableMeta._email_addon_settings[this.adn_idx];
            },
        },
        methods: {
            inArray(type, array) {
                return array.indexOf(type) > -1;
            },
            changeTab(key) {
                this.acttab = key;
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
                    Swal('', getErrors(errors));
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
                    Swal('', getErrors(errors));
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
                    Swal('', getErrors(errors));
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
                    Swal('', getErrors(errors));
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
                height: 30px;
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
            border-radius: 5px;
            background-color: inherit;
            border: 1px solid #CCC;
            padding: 10px;
        }

        .email_status {
            height: 28px;
            margin-right: 10px;
            font-weight: bold;
        }
        .email_cancel {
            background-color: #bf5329 !important;
            border-color: #aa4a24;
            padding: 0 3px;
            margin-top: 3px;
            margin-right: 10px;
        }

        .w-sm {
            width: 70px;
            padding: 3px;
        }
        .w-md {
            width: 150px;
            padding: 3px;
        }
    }
    .btn-default {
        height: 30px;
    }
</style>