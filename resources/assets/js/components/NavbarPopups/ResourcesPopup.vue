<template>
    <div class="popup-wrapper" @click.self="$emit('popup-close')">
        <div class="popup" :style="{width: (activeTab !== 'users' ? '700px' : '1000px')}">
            <div class="flex flex--col">
                <div class="popup-header">
                    Settings
                    <span class="glyphicon glyphicon-remove pull-right close-btn" @click="$emit('popup-close')"></span>
                </div>
                <div class="popup-content flex__elem-remain">

                    <div class="flex__elem__inner popup-main">

                        <div class="full-height" v-if="$root.settingsMeta">
                            <div class="resources-menu-header">
                                <button class="btn btn-default btn-sm"
                                        :class="{active : activeTab === 'connections'}"
                                        @click="activeTab = 'connections'">
                                    Connections
                                </button>
                                <button class="btn btn-default btn-sm"
                                        :class="{active : activeTab === 'users'}"
                                        @click="activeTab = 'users'">
                                    User Groups
                                </button>
                                <button class="btn btn-default btn-sm"
                                        :class="{active : activeTab === 'themes'}"
                                        @click="activeTab = 'themes'">
                                    Theme
                                </button>
                                <button v-if="$root.user.is_admin"
                                        class="btn btn-default btn-sm"
                                        :class="{active : activeTab === 'app_set'}"
                                        @click="activeTab = 'app_set'">
                                    System
                                </button>
                            </div>
                            <div class="resources-menu-body">

                                <div class="full-height" v-show="activeTab === 'connections'">
                                    <div class="resources-menu-header">
                                        <button class="btn btn-default btn-sm"
                                                :class="{active : activeConnTab === 'cloud'}"
                                                @click="activeConnTab = 'cloud'">Cloud</button>
                                        <button class="btn btn-default btn-sm"
                                                :class="{active : activeConnTab === 'mysql'}"
                                                @click="activeConnTab = 'mysql'">MySQL</button>
                                        <button class="btn btn-default btn-sm"
                                                :class="{active : activeConnTab === 'sendgrid'}"
                                                @click="activeConnTab = 'sendgrid'">SendGrid</button>
                                        <button class="btn btn-default btn-sm"
                                                :class="{active : activeConnTab === 'google'}"
                                                @click="activeConnTab = 'google'">Google</button>
                                        <button class="btn btn-default btn-sm"
                                                :class="{active : activeConnTab === 'payment'}"
                                                @click="activeConnTab = 'payment'">Payment</button>
                                    </div>
                                    <div class="resources-menu-body">

                                        <div v-show="activeConnTab === 'cloud'">
                                            <div class="cloud-block">
                                                <div class="table-block">
                                                    <custom-table
                                                            :cell_component_name="'custom-cell-connection'"
                                                            :global-meta="$root.settingsMeta.user_clouds"
                                                            :table-meta="$root.settingsMeta.user_clouds"
                                                            :settings-meta="$root.settingsMeta"
                                                            :all-rows="$root.settingsMeta.user_clouds_data"
                                                            :rows-count="$root.settingsMeta.user_clouds_data.length"
                                                            :cell-height="$root.cellHeight"
                                                            :max-cell-rows="$root.maxCellRows"
                                                            :is-full-width="true"
                                                            :fixed_ddl_pos="true"
                                                            :user="$root.user"
                                                            :behavior="'user_conn'"
                                                            :adding-row="addingRow"
                                                            :forbidden-columns="forbiddenColumns"
                                                            @added-row="addCloudRow"
                                                            @updated-row="updateCloudRow"
                                                            @delete-row="deleteCloudRow"
                                                    ></custom-table>
                                                    <label style="margin: 0;">* A folder will be created if not exists. Add directory from root for subfolder. </label>
                                                </div>
                                            </div>
                                        </div>

                                        <div v-show="activeConnTab === 'mysql'">
                                            <div class="cloud-block">
                                                <div class="table-block">
                                                    <custom-table
                                                            :cell_component_name="'custom-cell-connection'"
                                                            :global-meta="$root.settingsMeta.user_connections"
                                                            :table-meta="$root.settingsMeta.user_connections"
                                                            :settings-meta="$root.settingsMeta"
                                                            :all-rows="$root.settingsMeta.user_connections_data"
                                                            :rows-count="$root.settingsMeta.user_connections_data.length"
                                                            :cell-height="$root.cellHeight"
                                                            :max-cell-rows="$root.maxCellRows"
                                                            :is-full-width="true"
                                                            :fixed_ddl_pos="true"
                                                            :user="$root.user"
                                                            :behavior="'user_conn'"
                                                            :adding-row="addingRow"
                                                            :forbidden-columns="forbiddenColumns"
                                                            @added-row="addConnRow"
                                                            @updated-row="updateConnRow"
                                                            @delete-row="deleteConnRow"
                                                    ></custom-table>
                                                </div>
                                            </div>
                                        </div>

                                        <div v-show="activeConnTab === 'sendgrid'">
                                            <div class="cloud-block">
                                                <div>
                                                    <label>
                                                        <a href="https://sendgrid.com/docs/ui/account-and-settings/api-keys/#creating-an-api-key" target="_blank">API Keys:</a>
                                                        <i class="fa fa-info-circle" ref="gl_help" @click="showGridHover"></i>
                                                        <hover-block v-if="sg_tooltip"
                                                                     :html_str="$root.sendgrid_help"
                                                                     :p_left="sg_left"
                                                                     :p_top="sg_top"
                                                                     :c_offset="sg_offset"
                                                                     @another-click="sg_tooltip = false"
                                                        ></hover-block>
                                                    </label>
                                                </div>
                                                <div class="table-block">
                                                    <custom-table
                                                            :cell_component_name="'custom-cell-connection'"
                                                            :global-meta="$root.settingsMeta.user_api_keys"
                                                            :table-meta="$root.settingsMeta.user_api_keys"
                                                            :settings-meta="$root.settingsMeta"
                                                            :all-rows="$root.user._sendgrid_api_keys"
                                                            :rows-count="$root.user._sendgrid_api_keys.length"
                                                            :cell-height="$root.cellHeight"
                                                            :max-cell-rows="$root.maxCellRows"
                                                            :is-full-width="true"
                                                            :fixed_ddl_pos="true"
                                                            :user="$root.user"
                                                            :behavior="'user_conn'"
                                                            :adding-row="addingRow"
                                                            :forbidden-columns="forbiddenColumns"
                                                            @added-row="addGridApiRow"
                                                            @updated-row="updateApiRow"
                                                            @delete-row="deleteGridApiRow"
                                                    ></custom-table>
                                                </div>
                                            </div>
                                        </div>

                                        <div v-show="activeConnTab === 'google'">
                                            <div class="cloud-block">
                                                <div>
                                                    <label>
                                                        <a href="https://developers.google.com/maps/documentation/javascript/get-api-key" target="_blank">API Keys:</a>
                                                        <i class="fa fa-info-circle" ref="gl_help" @click="showGoogleHover"></i>
                                                        <hover-block v-if="gl_tooltip"
                                                                     :html_str="$root.google_help"
                                                                     :p_left="gl_left"
                                                                     :p_top="gl_top"
                                                                     :c_offset="gl_offset"
                                                                     @another-click="gl_tooltip = false"
                                                        ></hover-block>
                                                    </label>
                                                </div>
                                                <div class="table-block">
                                                    <custom-table
                                                            :cell_component_name="'custom-cell-connection'"
                                                            :global-meta="$root.settingsMeta.user_api_keys"
                                                            :table-meta="$root.settingsMeta.user_api_keys"
                                                            :settings-meta="$root.settingsMeta"
                                                            :all-rows="$root.user._google_api_keys"
                                                            :rows-count="$root.user._google_api_keys.length"
                                                            :cell-height="$root.cellHeight"
                                                            :max-cell-rows="$root.maxCellRows"
                                                            :is-full-width="true"
                                                            :fixed_ddl_pos="true"
                                                            :user="$root.user"
                                                            :behavior="'user_conn'"
                                                            :adding-row="addingRow"
                                                            :forbidden-columns="forbiddenColumns"
                                                            @added-row="addGoogleApiRow"
                                                            @updated-row="updateApiRow"
                                                            @delete-row="deleteGoogleApiRow"
                                                    ></custom-table>
                                                </div>
                                            </div>

                                            <div class="cloud-block">
                                                <div>
                                                    <label>
                                                        <a href="https://support.google.com/accounts/answer/185833" target="_blank">Email Account and App Password:</a>
                                                    </label>
                                                </div>
                                                <div class="table-block">
                                                    <custom-table
                                                            :cell_component_name="'custom-cell-connection'"
                                                            :global-meta="$root.settingsMeta.user_email_accounts"
                                                            :table-meta="$root.settingsMeta.user_email_accounts"
                                                            :settings-meta="$root.settingsMeta"
                                                            :all-rows="$root.user._google_email_accs"
                                                            :rows-count="$root.user._google_email_accs.length"
                                                            :cell-height="$root.cellHeight"
                                                            :max-cell-rows="$root.maxCellRows"
                                                            :is-full-width="true"
                                                            :fixed_ddl_pos="true"
                                                            :user="$root.user"
                                                            :behavior="'user_conn'"
                                                            :adding-row="addingRow"
                                                            :forbidden-columns="forbiddenColumns"
                                                            @added-row="addGoogleEmailAccRow"
                                                            @updated-row="updateGoogleEmailAccRow"
                                                            @delete-row="deleteGoogleEmailAccRow"
                                                    ></custom-table>
                                                </div>
                                            </div>
                                        </div>

                                        <div v-show="activeConnTab === 'payment'">
                                            <div class="cloud-block">
                                                <div>
                                                    <label>
                                                        <a href="https://developer.paypal.com/docs/api-basics/manage-apps/" target="_blank">PayPal:</a>
                                                    </label>
                                                </div>
                                                <div class="table-block">
                                                    <custom-table
                                                            :cell_component_name="'custom-cell-connection'"
                                                            :global-meta="$root.settingsMeta.user_payment_keys"
                                                            :table-meta="$root.settingsMeta.user_payment_keys"
                                                            :settings-meta="$root.settingsMeta"
                                                            :all-rows="$root.user._paypal_payment_keys"
                                                            :rows-count="$root.user._paypal_payment_keys.length"
                                                            :cell-height="$root.cellHeight"
                                                            :max-cell-rows="$root.maxCellRows"
                                                            :is-full-width="true"
                                                            :fixed_ddl_pos="true"
                                                            :user="$root.user"
                                                            :behavior="'user_conn'"
                                                            :adding-row="addingRow"
                                                            :available-columns="['mode','name','secret_key','public_key']"
                                                            @added-row="(row) => { addPaymentRow(row, $root.user._paypal_payment_keys, 'paypal') }"
                                                            @updated-row="(row) => { updatePaymentRow(row, $root.user._paypal_payment_keys, 'paypal') }"
                                                            @delete-row="(row) => { deletePaymentRow(row, $root.user._paypal_payment_keys, 'paypal') }"
                                                    ></custom-table>
                                                </div>
                                            </div>

                                            <div class="cloud-block">
                                                <div>
                                                    <label>
                                                        <a href="https://stripe.com/docs/keys" target="_blank">Stripe:</a>
                                                    </label>
                                                </div>
                                                <div class="table-block">
                                                    <custom-table
                                                            :cell_component_name="'custom-cell-connection'"
                                                            :global-meta="$root.settingsMeta.user_payment_keys"
                                                            :table-meta="$root.settingsMeta.user_payment_keys"
                                                            :settings-meta="$root.settingsMeta"
                                                            :all-rows="$root.user._stripe_payment_keys"
                                                            :rows-count="$root.user._stripe_payment_keys.length"
                                                            :cell-height="$root.cellHeight"
                                                            :max-cell-rows="$root.maxCellRows"
                                                            :is-full-width="true"
                                                            :fixed_ddl_pos="true"
                                                            :user="$root.user"
                                                            :behavior="'user_conn'"
                                                            :adding-row="addingRow"
                                                            :available-columns="['name','secret_key','public_key']"
                                                            @added-row="(row) => { addPaymentRow(row, $root.user._stripe_payment_keys, 'stripe') }"
                                                            @updated-row="(row) => { updatePaymentRow(row, $root.user._stripe_payment_keys, 'stripe') }"
                                                            @delete-row="(row) => { deletePaymentRow(row, $root.user._stripe_payment_keys, 'stripe') }"
                                                    ></custom-table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="full-frame container-fluid" v-show="activeTab === 'users'">
                                    <div class="row full-height resources-tab">
                                        <!--LEFT SIDE-->
                                        <div class="col-xs-12 col-md-4 full-height" :style="{paddingRight: 0}">
                                            <div class="full-height">
                                                <div class="top-text">
                                                    <span>User Groups</span>
                                                </div>
                                                <div class="resources-panel">
                                                    <div class="full-frame">
                                                        <custom-table
                                                                :cell_component_name="'custom-cell-user-groups'"
                                                                :global-meta="$root.settingsMeta['user_groups']"
                                                                :table-meta="$root.settingsMeta['user_groups']"
                                                                :all-rows="$root.user._user_groups"
                                                                :rows-count="$root.user._user_groups.length"
                                                                :cell-height="$root.cellHeight"
                                                                :max-cell-rows="$root.maxCellRows"
                                                                :is-full-width="true"
                                                                :behavior="'data_sets'"
                                                                :user="$root.user"
                                                                :adding-row="addingRow"
                                                                :selected-row="selectedUserGroup"
                                                                :forbidden-columns="$root.systemFields"
                                                                @added-row="addUserGroup"
                                                                @updated-row="updateUserGroup"
                                                                @delete-row="deleteUserGroup"
                                                                @row-index-clicked="rowIndexClickedUserGroup"
                                                        ></custom-table>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!--RIGHT SIDE-->

                                        <div class="col-xs-12 col-md-8 full-height">

                                            <div class="full-height">
                                                <div class="top-text">
                                                    <span>Selected: <span>{{ (selectedUserGroup > -1 ? $root.user._user_groups[selectedUserGroup].name : '') }}</span></span>
                                                </div>
                                                <div class="resources-panel" :style="{height: (activeSubUserGroup === 'individ' ? 'calc(100% - 64px)' : 'calc(100% - 30px)')}">
                                                    <div class="resources-menu-header">
                                                        <button class="btn btn-default btn-sm" :class="{active : activeSubUserGroup === 'individ'}" @click="activeSubUserGroup = 'individ'">
                                                            Individuals
                                                        </button>
                                                        <button class="btn btn-default btn-sm" :class="{active : activeSubUserGroup === 'cond'}" @click="activeSubUserGroup = 'cond'">
                                                            Conditions
                                                        </button>

                                                        <button v-show="activeSubUserGroup === 'cond'" class="btn btn-default btn-sm right-btn" @click="addToIndividuals()">
                                                            Add to Individuals
                                                        </button>
                                                    </div>
                                                    <div class="resources-menu-body" v-show="activeSubUserGroup === 'individ'">
                                                        <div class="full-frame">
                                                            <custom-table
                                                                    :cell_component_name="'custom-cell-user-groups'"
                                                                    :global-meta="$root.settingsMeta['user_groups_2_users']"
                                                                    :table-meta="$root.settingsMeta['user_groups_2_users']"
                                                                    :all-rows="selectedUserGroup > -1 ? $root.user._user_groups[selectedUserGroup]._individuals : null"
                                                                    :rows-count="selectedUserGroup > -1 ? $root.user._user_groups[selectedUserGroup]._individuals.length : 0"
                                                                    :cell-height="$root.cellHeight"
                                                                    :max-cell-rows="$root.maxCellRows"
                                                                    :is-full-width="true"
                                                                    :user="$root.user"
                                                                    :behavior="'data_sets_items'"
                                                                    :forbidden-columns="$root.systemFields"
                                                                    @updated-row="updateUserInUserGroup"
                                                                    @delete-row="deleteUserFromUserGroup"
                                                            ></custom-table>
                                                        </div>
                                                    </div>
                                                    <div class="resources-menu-body" v-show="activeSubUserGroup === 'cond'">
                                                        <div class="full-frame">
                                                            <custom-table
                                                                    :cell_component_name="'custom-cell-user-groups'"
                                                                    :global-meta="$root.settingsMeta['user_group_conditions']"
                                                                    :table-meta="$root.settingsMeta['user_group_conditions']"
                                                                    :all-rows="selectedUserGroup > -1 ? $root.user._user_groups[selectedUserGroup]._conditions : null"
                                                                    :rows-count="selectedUserGroup > -1 ? $root.user._user_groups[selectedUserGroup]._conditions.length : 0"
                                                                    :cell-height="$root.cellHeight"
                                                                    :max-cell-rows="$root.maxCellRows"
                                                                    :is-full-width="true"
                                                                    :user="$root.user"
                                                                    :behavior="'sett_usergroup_conds'"
                                                                    :adding-row="selectedUserGroup > -1 ? addingRow : {active: false}"
                                                                    :forbidden-columns="$root.systemFields"
                                                                    @added-row="addUserGroupCondition"
                                                                    @updated-row="updateUserGroupCondition"
                                                                    @delete-row="deleteUserGroupCondition"
                                                            ></custom-table>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="user-search-box" v-show="activeSubUserGroup === 'individ'">
                                                    <div class="search-wrapper">
                                                        <select ref="search_user"></select>
                                                    </div>
                                                    <button class="btn btn-primary btn-sm"
                                                            style="width: 80px"
                                                            :disabled="selectedUserGroup === -1"
                                                            @click="addUserToUserGroup()"
                                                    >Add User</button>
                                                </div>
                                            </div>

                                        </div>
                                    </div>
                                </div>

                                <div v-show="activeTab === 'themes'">
                                    <div class="theme-block">
                                        <theme-selector-block></theme-selector-block>
                                    </div>
                                </div>

                                <div v-show="activeTab === 'app_set'">
                                    <div class="appset-block">
                                        <div>
                                            <label>DCR links for:</label>
                                        </div>
                                        <div class="appset-block--settings">
                                            <div class="flex flex--center-v form-group" v-if="app_sett_demo">
                                                <span>Request for a demo:</span>
                                                <input type="text" class="form-control" v-model="app_sett_demo.val" @change="appSettChanged(app_sett_demo)"/>
                                            </div>
                                            <div class="flex flex--center-v form-group" v-if="app_sett_contact">
                                                <span>Contact Us:</span>
                                                <input type="text" class="form-control" v-model="app_sett_contact.val" @change="appSettChanged(app_sett_contact)"/>
                                            </div>
                                            <div class="flex flex--center-v form-group" v-if="app_sett_enterprise_dcr">
                                                <span>Enterprise Link:</span>
                                                <input type="text" class="form-control" v-model="app_sett_enterprise_dcr.val" @change="appSettChanged(app_sett_enterprise_dcr)"/>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import {eventBus} from './../../app';

    import CustomTable from './../CustomTable/CustomTable';
    import ThemeSelectorBlock from "../CommonBlocks/ThemeSelectorBlock";
    import HoverBlock from "../CommonBlocks/HoverBlock";

    export default {
        name: "ResourcesPopup",
        components: {
            HoverBlock,
            ThemeSelectorBlock,
            CustomTable
        },
        data: function () {
            return {
                gl_tooltip: false,
                gl_left: 0,
                gl_top: 0,
                gl_offset: 0,
                sg_tooltip: false,
                sg_left: 0,
                sg_top: 0,
                sg_offset: 0,

                addingRow: {
                    active: true,
                    position: 'bottom'
                },
                forbiddenColumns: [
                    'db',
                    'table',
                    'user_id',
                    'created_on',
                    'created_by',
                    'modified_on',
                    'modified_by',
                ],
                activeTab: 'connections',
                activeConnTab: 'cloud',
                activeSubUserGroup: 'individ',
                selectedUserGroup: -1,

                app_sett_demo: null,
                app_sett_contact: null,
                app_sett_enterprise_dcr: null,
            }
        },
        props:{
            is_vis: Boolean,
        },
        methods: {
            showGoogleHover(e) {
                this.showHover(e, 'gl');
            },
            showGridHover(e) {
                this.showHover(e, 'sg');
            },
            showHover(e, pref) {
                let bounds = this.$refs[pref+'_help'] ? this.$refs[pref+'_help'].getBoundingClientRect() : {};
                let px = (bounds.left + bounds.right) / 2;
                let py = (bounds.top + bounds.bottom) / 2;
                this[pref+'_tooltip'] = true;
                this[pref+'_left'] = px || e.clientX;
                this[pref+'_top'] = py || e.clientY;
                this[pref+'_offset'] = Math.abs(bounds.top - bounds.bottom) || 0;
            },
            rowIndexClickedUserGroup(index) {
                this.selectedUserGroup = index;
            },
            //Connections Functions
            addConnRow(tableRow) {
                $.LoadingOverlay('show');

                let fields = _.cloneDeep(tableRow);//copy object
                this.$root.deleteSystemFields(fields);

                axios.post('/ajax/user-conn', {
                    fields: fields
                }).then(({ data }) => {
                    this.$root.settingsMeta.user_connections_data.push( data );
                    this.$forceUpdate();
                }).catch(errors => {
                    Swal('', getErrors(errors));
                }).finally(() => {
                    $.LoadingOverlay('hide');
                });
            },
            updateConnRow(tableRow) {
                $.LoadingOverlay('show');

                let row_id = tableRow.id;
                let fields = _.cloneDeep(tableRow);//copy object
                this.$root.deleteSystemFields(fields);

                axios.put('/ajax/user-conn', {
                    user_conn_id: row_id,
                    fields: fields
                }).then(({ data }) => {
                }).catch(errors => {
                    Swal('', getErrors(errors));
                }).finally(() => {
                    $.LoadingOverlay('hide');
                });
            },
            deleteConnRow(tableRow) {
                $.LoadingOverlay('show');
                let row_id = tableRow.id;
                axios.delete('/ajax/user-conn', {
                    params: {
                        user_conn_id: row_id,
                    }
                }).then(({ data }) => {
                    let idx = _.findIndex(this.$root.settingsMeta.user_connections_data, {id: tableRow.id});
                    if (idx > -1) {
                        this.$root.settingsMeta.user_connections_data.splice(idx, 1);
                    }
                    this.$forceUpdate();
                }).catch(errors => {
                    Swal('', getErrors(errors));
                }).finally(() => {
                    $.LoadingOverlay('hide');
                });
            },

            //Cloud Functions
            addCloudRow(tableRow) {
                $.LoadingOverlay('show');

                let fields = _.cloneDeep(tableRow);//copy object
                this.$root.deleteSystemFields(fields);

                axios.post('/ajax/user-cloud', {
                    fields: fields
                }).then(({ data }) => {
                    this.$root.settingsMeta.user_clouds_data.push( data );
                    this.$forceUpdate();
                }).catch(errors => {
                    Swal('', getErrors(errors));
                }).finally(() => {
                    $.LoadingOverlay('hide');
                });
            },
            updateCloudRow(tableRow) {
                $.LoadingOverlay('show');

                let row_id = tableRow.id;
                let fields = _.cloneDeep(tableRow);//copy object
                this.$root.deleteSystemFields(fields);

                axios.put('/ajax/user-cloud', {
                    user_cloud_id: row_id,
                    fields: fields
                }).then(({ data }) => {
                }).catch(errors => {
                    Swal('', getErrors(errors));
                }).finally(() => {
                    $.LoadingOverlay('hide');
                });
            },
            deleteCloudRow(tableRow) {
                $.LoadingOverlay('show');
                let row_id = tableRow.id;
                axios.delete('/ajax/user-cloud', {
                    params: {
                        user_cloud_id: row_id,
                    }
                }).then(({ data }) => {
                    let idx = _.findIndex(this.$root.settingsMeta.user_clouds_data, {id: Number(tableRow.id)});
                    if (idx > -1) {
                        this.$root.settingsMeta.user_clouds_data.splice(idx, 1);
                    }
                    this.$forceUpdate();
                }).catch(errors => {
                    Swal('', getErrors(errors));
                }).finally(() => {
                    $.LoadingOverlay('hide');
                });
            },

            //Payment Functions
            addPaymentRow(tableRow, user_keys, type) {
                $.LoadingOverlay('show');
                tableRow.type = type;

                let fields = _.cloneDeep(tableRow);//copy object
                this.$root.deleteSystemFields(fields);

                axios.post('/ajax/user-payment-key', {
                    fields: fields
                }).then(({ data }) => {
                    user_keys.push( data );
                    this.$forceUpdate();
                }).catch(errors => {
                    Swal('', getErrors(errors));
                }).finally(() => {
                    $.LoadingOverlay('hide');
                });
            },
            updatePaymentRow(tableRow, user_keys, type) {
                $.LoadingOverlay('show');
                tableRow.type = type;

                let row_id = tableRow.id;
                let fields = _.cloneDeep(tableRow);//copy object
                this.$root.deleteSystemFields(fields);

                axios.put('/ajax/user-payment-key', {
                    this_id: row_id,
                    fields: fields
                }).then(({ data }) => {
                }).catch(errors => {
                    Swal('', getErrors(errors));
                }).finally(() => {
                    $.LoadingOverlay('hide');
                });
            },
            deletePaymentRow(tableRow, user_keys) {
                $.LoadingOverlay('show');
                let row_id = tableRow.id;
                axios.delete('/ajax/user-payment-key', {
                    params: {
                        this_id: row_id,
                    }
                }).then(({ data }) => {
                    let idx = _.findIndex(user_keys, {id: Number(tableRow.id)});
                    if (idx > -1) {
                        user_keys.splice(idx, 1);
                    }
                    this.$forceUpdate();
                }).catch(errors => {
                    Swal('', getErrors(errors));
                }).finally(() => {
                    $.LoadingOverlay('hide');
                });
            },

            //Cloud Functions
            addGoogleApiRow(tableRow) {
                $.LoadingOverlay('show');

                tableRow['type'] = 'google';
                let fields = _.cloneDeep(tableRow);//copy object
                this.$root.deleteSystemFields(fields);

                axios.post('/ajax/user-api-key', {
                    fields: fields
                }).then(({ data }) => {
                    this.$root.user._google_api_keys.push( data );
                }).catch(errors => {
                    Swal('', getErrors(errors));
                }).finally(() => {
                    $.LoadingOverlay('hide');
                });
            },
            deleteGoogleApiRow(tableRow) {
                $.LoadingOverlay('show');
                let row_id = tableRow.id;
                axios.delete('/ajax/user-api-key', {
                    params: {
                        user_api_id: row_id,
                    }
                }).then(({ data }) => {
                    let idx = _.findIndex(this.$root.user._google_api_keys, {id: Number(tableRow.id)});
                    if (idx > -1) {
                        this.$root.user._google_api_keys.splice(idx, 1);
                    }
                }).catch(errors => {
                    Swal('', getErrors(errors));
                }).finally(() => {
                    $.LoadingOverlay('hide');
                });
            },
            addGridApiRow(tableRow) {
                $.LoadingOverlay('show');

                tableRow['type'] = 'sendgrid';
                let fields = _.cloneDeep(tableRow);//copy object
                this.$root.deleteSystemFields(fields);

                axios.post('/ajax/user-api-key', {
                    fields: fields
                }).then(({ data }) => {
                    this.$root.user._sendgrid_api_keys.push( data );
                }).catch(errors => {
                    Swal('', getErrors(errors));
                }).finally(() => {
                    $.LoadingOverlay('hide');
                });
            },
            deleteGridApiRow(tableRow) {
                $.LoadingOverlay('show');
                let row_id = tableRow.id;
                axios.delete('/ajax/user-api-key', {
                    params: {
                        user_api_id: row_id,
                    }
                }).then(({ data }) => {
                    let idx = _.findIndex(this.$root.user._sendgrid_api_keys, {id: Number(tableRow.id)});
                    if (idx > -1) {
                        this.$root.user._sendgrid_api_keys.splice(idx, 1);
                    }
                }).catch(errors => {
                    Swal('', getErrors(errors));
                }).finally(() => {
                    $.LoadingOverlay('hide');
                });
            },
            updateApiRow(tableRow) {
                $.LoadingOverlay('show');
                axios.put('/ajax/user-api-key', {
                    user_api_id: tableRow.id,
                    fields: tableRow
                }).then(({ data }) => {
                    if (tableRow._changed_field == 'is_active') {
                        let arrays = tableRow.type == 'google' ? this.$root.user._google_api_keys : this.$root.user._sendgrid_api_keys;
                        _.each(arrays, (el) => {
                            el.is_active = el.id == tableRow.id ? 1 : 0;
                        });
                    }

                }).catch(errors => {
                    Swal('', getErrors(errors));
                }).finally(() => {
                    $.LoadingOverlay('hide');
                });
            },

            //Cloud Functions
            addGoogleEmailAccRow(tableRow) {
                $.LoadingOverlay('show');

                let fields = _.cloneDeep(tableRow);//copy object
                this.$root.deleteSystemFields(fields);

                axios.post('/ajax/user-email-acc', {
                    fields: fields
                }).then(({ data }) => {
                    this.$root.user._google_email_accs.push( data );
                }).catch(errors => {
                    Swal('', getErrors(errors));
                }).finally(() => {
                    $.LoadingOverlay('hide');
                });
            },
            updateGoogleEmailAccRow(tableRow) {
                $.LoadingOverlay('show');

                let row_id = tableRow.id;
                let fields = _.cloneDeep(tableRow);//copy object
                this.$root.deleteSystemFields(fields);

                axios.put('/ajax/user-email-acc', {
                    user_email_acc_id: row_id,
                    fields: fields
                }).then(({ data }) => {
                }).catch(errors => {
                    Swal('', getErrors(errors));
                }).finally(() => {
                    $.LoadingOverlay('hide');
                });
            },
            deleteGoogleEmailAccRow(tableRow) {
                $.LoadingOverlay('show');
                let row_id = tableRow.id;
                axios.delete('/ajax/user-email-acc', {
                    params: {
                        user_email_acc_id: row_id,
                    }
                }).then(({ data }) => {
                    let idx = _.findIndex(this.$root.user._google_email_accs, {id: Number(tableRow.id)});
                    if (idx > -1) {
                        this.$root.user._google_email_accs.splice(idx, 1);
                    }
                }).catch(errors => {
                    Swal('', getErrors(errors));
                }).finally(() => {
                    $.LoadingOverlay('hide');
                });
            },



            //User Group Functions
            addUserGroup(tableRow) {
                $.LoadingOverlay('show');
                axios.post('/ajax/user-group', {
                    name: tableRow.name,
                }).then(({ data }) => {
                    this.$root.user._user_groups.push( data );
                    eventBus.$emit('user-group-added');
                    this.$forceUpdate();
                }).catch(errors => {
                    Swal('', getErrors(errors));
                }).finally(() => {
                    $.LoadingOverlay('hide');
                });
            },
            updateUserGroup(tableRow) {
                $.LoadingOverlay('show');

                let group_id = tableRow.id;
                let fields = _.cloneDeep(tableRow);//copy object
                this.$root.deleteSystemFields(fields);

                axios.put('/ajax/user-group', {
                    user_group_id: group_id,
                    fields: fields
                }).then(({ data }) => {
                }).catch(errors => {
                    Swal('', getErrors(errors));
                }).finally(() => {
                    $.LoadingOverlay('hide');
                });
            },
            deleteUserGroup(tableRow) {
                $.LoadingOverlay('show');
                axios.delete('/ajax/user-group', {
                    params: {
                        user_group_id: tableRow.id
                    }
                }).then(({ data }) => {
                    let idx = _.findIndex(this.$root.user._user_groups, {id: tableRow.id});
                    if (idx > -1) {
                        this.selectedUserGroup = -1;
                        this.$root.user._user_groups.splice(idx, 1);
                    }

                    //sync with 'Assigning Permissions'
                    this.$root.tableMeta._user_groups_2_table_permissions = _.filter(this.$root.tableMeta._user_groups_2_table_permissions, (item) => {
                        return item.user_group_id != Number(tableRow.id);
                    });

                    eventBus.$emit('user-group-deleted');
                    this.$forceUpdate();
                }).catch(errors => {
                    Swal('', getErrors(errors));
                }).finally(() => {
                    $.LoadingOverlay('hide');
                });
            },

            //User Group Users Functions
            addUserToUserGroup(tableRow) {
                $.LoadingOverlay('show');
                axios.post('/ajax/user-group/user', {
                    user_group_id: this.$root.user._user_groups[this.selectedUserGroup].id,
                    user_id: $(this.$refs.search_user).val()
                }).then(({ data }) => {
                    this.$root.user._user_groups = data._user_groups;
                    $(this.$refs.search_user).val(null).trigger('change');
                    this.$forceUpdate();
                }).catch(errors => {
                    Swal('', getErrors(errors));
                }).finally(() => {
                    $.LoadingOverlay('hide');
                });
            },
            updateUserInUserGroup(tableRowLink) {
                $.LoadingOverlay('show');
                axios.put('/ajax/user-group/user', {
                    user_id: tableRowLink.user_id,
                    user_group_id: tableRowLink.user_group_id,
                    is_manager: Boolean(tableRowLink.is_edit_added)
                }).then(({ data }) => {
                }).catch(errors => {
                    Swal('', getErrors(errors));
                }).finally(() => {
                    $.LoadingOverlay('hide');
                });
            },
            deleteUserFromUserGroup(tableRow) {
                $.LoadingOverlay('show');
                axios.delete('/ajax/user-group/user', {
                    params: {
                        user_group_id: this.$root.user._user_groups[this.selectedUserGroup].id,
                        user_id: tableRow.id
                    }
                }).then(({ data }) => {
                    let idx = _.findIndex(this.$root.user._user_groups[this.selectedUserGroup]._individuals, {id: tableRow.id});
                    if (idx > -1) {
                        this.$root.user._user_groups[this.selectedUserGroup]._individuals.splice(idx, 1);
                    }
                    this.$forceUpdate();
                }).catch(errors => {
                    Swal('', getErrors(errors));
                }).finally(() => {
                    $.LoadingOverlay('hide');
                });
            },

            //User Group Condition Functions
            addUserGroupCondition(tableRow) {
                $.LoadingOverlay('show');

                let fields = _.cloneDeep(tableRow);//copy object
                this.$root.deleteSystemFields(fields);

                axios.post('/ajax/user-group/condition', {
                    user_group_id: this.$root.user._user_groups[this.selectedUserGroup].id,
                    fields: fields,
                }).then(({ data }) => {
                    this.$root.user._user_groups[this.selectedUserGroup]._conditions.push( data );
                    this.$forceUpdate();
                }).catch(errors => {
                    Swal('', getErrors(errors));
                }).finally(() => {
                    $.LoadingOverlay('hide');
                });
            },
            updateUserGroupCondition(tableRow) {
                $.LoadingOverlay('show');

                let group_id = tableRow.id;
                let fields = _.cloneDeep(tableRow);//copy object
                this.$root.deleteSystemFields(fields);

                axios.put('/ajax/user-group/condition', {
                    user_group_condition_id: group_id,
                    fields: fields
                }).then(({ data }) => {
                }).catch(errors => {
                    Swal('', getErrors(errors));
                }).finally(() => {
                    $.LoadingOverlay('hide');
                });
            },
            deleteUserGroupCondition(tableRow) {
                $.LoadingOverlay('show');
                axios.delete('/ajax/user-group/condition', {
                    params: {
                        user_group_condition_id: tableRow.id
                    }
                }).then(({ data }) => {
                    let idx = _.findIndex(this.$root.user._user_groups[this.selectedUserGroup]._conditions, {id: tableRow.id});
                    if (idx > -1) {
                        this.$root.user._user_groups[this.selectedUserGroup]._conditions.splice(idx, 1);
                    }
                    this.$forceUpdate();
                }).catch(errors => {
                    Swal('', getErrors(errors));
                }).finally(() => {
                    $.LoadingOverlay('hide');
                });
            },
            addToIndividuals() {
                $.LoadingOverlay('show');
                axios.post('/ajax/user-group/cond-to-user', {
                    user_group_id: this.$root.user._user_groups[this.selectedUserGroup].id,
                }).then(({ data }) => {
                    this.$root.user._user_groups[this.selectedUserGroup]._conditions = [];
                    this.$root.user._user_groups[this.selectedUserGroup]._individuals = data;
                    this.$forceUpdate();
                }).catch(errors => {
                    Swal('', getErrors(errors));
                }).finally(() => {
                    $.LoadingOverlay('hide');
                });
            },
            hideMenu(e) {
                if (this.is_vis && e.keyCode === 27 && !this.$root.e__used) {
                    this.$emit('popup-close');
                    this.$root.set_e__used(this);
                }
            },

            appSettChanged(sett) {
                $.LoadingOverlay('show');
                axios.put('/ajax/app/settings', {
                    app_key: sett.key,
                    app_val: sett.val
                }).then(({ data }) => {
                }).catch(errors => {
                    Swal('', getErrors(errors));
                }).finally(() => {
                    $.LoadingOverlay('hide');
                });
            }
        },
        mounted() {
            eventBus.$on('global-keydown', this.hideMenu);
            eventBus.$on('open-resource-popup', (tab, idx, subtab) => {
                this.activeTab = tab || 'users';
                if (this.activeTab === 'users') {
                    this.selectedUserGroup = !isNaN(idx) ? idx : -1;
                }
                if (this.activeTab === 'connections' && subtab) {
                    this.activeConnTab = subtab;
                }
            });

            $(this.$refs.search_user).select2({
                ajax: {
                    url: '/ajax/user/search',
                    dataType: 'json',
                    delay: 250
                },
                minimumInputLength: 1,
                width: '100%',
                height: '100%'
            });
            $(this.$refs.search_user).next().css('height', '28px');

            this.app_sett_demo = this.$root.settingsMeta.app_settings['dcr_home_demo'];
            this.app_sett_contact = this.$root.settingsMeta.app_settings['dcr_home_contact'];
            this.app_sett_enterprise_dcr = this.$root.settingsMeta.app_settings['app_enterprise_dcr'];
        },
        beforeDestroy() {
            eventBus.$off('global-keydown', this.hideMenu);
        }
    }
</script>

<style lang="scss" scoped>
    @import "../CustomPopup/CustomEditPopUp";

    .popup-wrapper {
        z-index: 1490;
    }
    
    .popup {
        width: 700px;
        transform: none;
        top: 10%;
        left: calc(50% - 400px);

        .popup-main {

            .cloud-block {
                margin-bottom: 30px;

                .table-block {
                    max-height: 300px;
                    overflow: auto;
                }
            }

            .appset-block {
                .appset-block--settings {
                    span {
                        margin: 0 10px 0 30px;
                        white-space: nowrap;
                    }
                }
            }

            .top-text {
                height: 30px;
                line-height: 30px;
            }

            .resources-panel {
                height: calc(100% - 30px);
            }

            .resources-menu-body {
                position: relative;
                height: calc(100% - 27px);
                top: -3px;
                overflow: auto;
                border: 1px solid #CCC;
                background: #FFF;
                padding: 5px;
            }

            .search-wrapper {
                position: relative;
                height: 100%;
                width: calc(100% - 85px);
                display: inline-block;
                margin-top: 5px;
            }

            .right-btn {
                float: right;
                position: relative;
                top: -5px;
            }
        }

        .header-block {
            h1 {
                text-align: center;
                margin-top: 0;
            }
        }

        .fa-info-circle {
            cursor: pointer;
        }
    }
</style>