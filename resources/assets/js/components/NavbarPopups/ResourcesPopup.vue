<template>
    <div class="popup-wrapper" @click.self="$emit('popup-close')">
        <div class="popup" :style="getPopupStyle()">
            <div class="flex flex--col">
                <div class="popup-header full-width">
                    <div class="drag-bkg" draggable="true" @dragstart="dragPopSt()" @drag="dragPopup()"></div>
                    <div class="flex">
                        <div class="flex__elem-remain">Settings</div>
                        <div class="" style="position: absolute;right: 5px;z-index: 15;">
                            <span class="glyphicon glyphicon-remove pull-right close-btn" @click="$emit('popup-close')"></span>
                        </div>
                    </div>
                </div>
                <div class="popup-content flex__elem-remain">

                    <div class="flex__elem__inner popup-main">

                        <div class="full-height" v-if="$root.settingsMeta">
                            <div class="resources-menu-header">
                                <button class="btn btn-default btn-sm"
                                        :class="{active : activeTab === 'connections'}"
                                        :style="textSysStyle"
                                        @click="activeTab = 'connections'">
                                    Connections
                                </button>
                                <button class="btn btn-default btn-sm"
                                        :class="{active : activeTab === 'users'}"
                                        :style="textSysStyle"
                                        @click="activeTab = 'users'">
                                    User Groups
                                </button>
                                <button class="btn btn-default btn-sm"
                                        :class="{active : activeTab === 'themes'}"
                                        :style="textSysStyle"
                                        @click="activeTab = 'themes'">
                                    Theme
                                </button>
                                <button v-if="$root.user.is_admin"
                                        class="btn btn-default btn-sm"
                                        :class="{active : activeTab === 'app_set'}"
                                        :style="textSysStyle"
                                        @click="activeTab = 'app_set'">
                                    System
                                </button>
                            </div>
                            <div class="resources-menu-body">

                                <div class="full-height" v-show="activeTab === 'connections'">
                                    <div class="resources-menu-header">
                                        <button class="btn btn-default btn-sm"
                                                :class="{active : activeConnTab === 'cloud'}"
                                                :style="textSysStyle"
                                                @click="activeConnTab = 'cloud'">Cloud</button>
                                        <button class="btn btn-default btn-sm"
                                                :class="{active : activeConnTab === 'mysql'}"
                                                :style="textSysStyle"
                                                @click="activeConnTab = 'mysql'">MySQL</button>
                                        <button class="btn btn-default btn-sm"
                                                :class="{active : activeConnTab === 'twilio_tab'}"
                                                :style="textSysStyle"
                                                @click="activeConnTab = 'twilio_tab'">Twilio</button>
                                        <button class="btn btn-default btn-sm"
                                                :class="{active : activeConnTab === 'google'}"
                                                :style="textSysStyle"
                                                @click="activeConnTab = 'google'">Google</button>
                                        <button class="btn btn-default btn-sm"
                                                :class="{active : activeConnTab === 'payment'}"
                                                :style="textSysStyle"
                                                @click="activeConnTab = 'payment'">Payment</button>
                                        <button class="btn btn-default btn-sm"
                                                :class="{active : activeConnTab === 'others'}"
                                                :style="textSysStyle"
                                                @click="activeConnTab = 'others'">Others</button>
                                    </div>
                                    <div class="resources-menu-body">

                                        <div v-show="activeConnTab === 'cloud'">
                                            <div class="cloud-block">
                                                <div class="table-block">
                                                    <custom-table
                                                            :cell_component_name="'custom-cell-connection'"
                                                            :global-meta="$root.tableMeta || $root.settingsMeta.user_clouds"
                                                            :table-meta="$root.settingsMeta.user_clouds"
                                                            :settings-meta="$root.settingsMeta"
                                                            :all-rows="$root.settingsMeta.user_clouds_data"
                                                            :rows-count="$root.settingsMeta.user_clouds_data.length"
                                                            :cell-height="1"
                                                            :max-cell-rows="0"
                                                            :is-full-width="true"
                                                            :user="$root.user"
                                                            :behavior="'user_conn'"
                                                            :adding-row="addingRow"
                                                            :forbidden-columns="forbiddenColumns"
                                                            :special_extras="{force_delete: true}"
                                                            @added-row="addCloudRow"
                                                            @updated-row="updateCloudRow"
                                                            @delete-row="deleteCloudRow"
                                                    ></custom-table>
                                                </div>
                                            </div>
                                        </div>

                                        <div v-show="activeConnTab === 'mysql'">
                                            <div class="cloud-block">
                                                <div class="table-block">
                                                    <custom-table
                                                            :cell_component_name="'custom-cell-connection'"
                                                            :global-meta="$root.tableMeta || $root.settingsMeta.user_connections"
                                                            :table-meta="$root.settingsMeta.user_connections"
                                                            :settings-meta="$root.settingsMeta"
                                                            :all-rows="$root.settingsMeta.user_connections_data"
                                                            :rows-count="$root.settingsMeta.user_connections_data.length"
                                                            :cell-height="1"
                                                            :max-cell-rows="0"
                                                            :is-full-width="true"
                                                            :user="$root.user"
                                                            :behavior="'user_conn'"
                                                            :adding-row="addingRow"
                                                            :forbidden-columns="forbiddenColumns"
                                                            :special_extras="{force_delete: true}"
                                                            @added-row="addConnRow"
                                                            @updated-row="updateConnRow"
                                                            @delete-row="deleteConnRow"
                                                    ></custom-table>
                                                </div>
                                            </div>
                                        </div>

                                        <div v-show="activeConnTab === 'twilio_tab'">
                                            <div class="cloud-block">
                                                <div>
                                                    <label>
                                                        <span>Email - </span>
                                                        <a href="https://sendgrid.com/docs/ui/account-and-settings/api-keys/#creating-an-api-key" target="_blank">API Keys</a>
                                                        <span>by <a target="_blank" href="https://sendgrid.com/">SendGrid</a></span>
                                                        <i class="fa fa-info-circle" ref="sg_help" @click="showGridHover"></i>
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
                                                            :global-meta="$root.tableMeta || $root.settingsMeta.user_api_keys"
                                                            :table-meta="$root.settingsMeta.user_api_keys"
                                                            :settings-meta="$root.settingsMeta"
                                                            :all-rows="$root.user._sendgrid_api_keys"
                                                            :rows-count="$root.user._sendgrid_api_keys.length"
                                                            :cell-height="1"
                                                            :max-cell-rows="0"
                                                            :is-full-width="true"
                                                            :user="$root.user"
                                                            :behavior="'user_conn'"
                                                            :adding-row="addingRow"
                                                            :available-columns="['is_active','name','key']"
                                                            :special_extras="{force_delete: true}"
                                                            @added-row="addGridApiRow"
                                                            @updated-row="updateGridApiRow"
                                                            @delete-row="deleteGridApiRow"
                                                    ></custom-table>
                                                </div>
                                            </div>

                                            <div class="cloud-block">
                                                <div>
                                                    <label>
                                                        <span>SMS/Voice - </span>
                                                        <a href="https://console.twilio.com/" target="_blank">SID/Token and Phone</a>
                                                        <span>by <a target="_blank" href="https://twilio.com/">Twilio</a></span>
                                                        <i class="fa fa-info-circle" ref="tw_help" @click="showTwilioHover"></i>
                                                        <hover-block v-if="tw_tooltip"
                                                                     :html_str="$root.twilio_help"
                                                                     :p_left="tw_left"
                                                                     :p_top="tw_top"
                                                                     :c_offset="tw_offset"
                                                                     @another-click="tw_tooltip = false"
                                                        ></hover-block>
                                                    </label>
                                                </div>
                                                <div class="table-block">
                                                    <custom-table
                                                            :cell_component_name="'custom-cell-connection'"
                                                            :global-meta="$root.tableMeta || $root.settingsMeta.user_api_keys"
                                                            :table-meta="$root.settingsMeta.user_api_keys"
                                                            :settings-meta="$root.settingsMeta"
                                                            :all-rows="$root.user._twilio_api_keys"
                                                            :rows-count="$root.user._twilio_api_keys.length"
                                                            :cell-height="1"
                                                            :max-cell-rows="0"
                                                            :is-full-width="true"
                                                            :user="$root.user"
                                                            :behavior="'twilio_keys'"
                                                            :adding-row="addingRow"
                                                            :available-columns="['is_active','name','key','auth_token','twiml_app_id','twilio_phone']"
                                                            :headers-changer="{'key': 'SID'}"
                                                            :required-changer="['twilio_phone']"
                                                            :special_extras="{force_delete: true}"
                                                            @added-row="addTwilioApiRow"
                                                            @updated-row="updateTwilioApiRow"
                                                            @delete-row="deleteTwilioApiRow"
                                                    ></custom-table>
                                                </div>
                                                <div>
                                                    <label>
                                                        <span>* A Twilio number or</span>
                                                        <a href="https://support.twilio.com/hc/en-us/articles/223180048-Adding-a-Verified-Phone-Number-or-Caller-ID-with-Twilio" target="_blank">verified caller ID number</a>.
                                                    </label>
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
                                                            :global-meta="$root.tableMeta || $root.settingsMeta.user_api_keys"
                                                            :table-meta="$root.settingsMeta.user_api_keys"
                                                            :settings-meta="$root.settingsMeta"
                                                            :all-rows="$root.user._google_api_keys"
                                                            :rows-count="$root.user._google_api_keys.length"
                                                            :cell-height="1"
                                                            :max-cell-rows="0"
                                                            :is-full-width="true"
                                                            :user="$root.user"
                                                            :behavior="'user_conn'"
                                                            :adding-row="addingRow"
                                                            :available-columns="['is_active','name','key']"
                                                            :special_extras="{force_delete: true}"
                                                            @added-row="addGoogleApiRow"
                                                            @updated-row="updateGoogleApiRow"
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
                                                            :global-meta="$root.tableMeta || $root.settingsMeta.user_email_accounts"
                                                            :table-meta="$root.settingsMeta.user_email_accounts"
                                                            :settings-meta="$root.settingsMeta"
                                                            :all-rows="$root.user._google_email_accs"
                                                            :rows-count="$root.user._google_email_accs.length"
                                                            :cell-height="1"
                                                            :max-cell-rows="0"
                                                            :is-full-width="true"
                                                            :user="$root.user"
                                                            :behavior="'user_conn'"
                                                            :adding-row="addingRow"
                                                            :forbidden-columns="forbiddenColumns"
                                                            :special_extras="{force_delete: true}"
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
                                                            :global-meta="$root.tableMeta || $root.settingsMeta.user_payment_keys"
                                                            :table-meta="$root.settingsMeta.user_payment_keys"
                                                            :settings-meta="$root.settingsMeta"
                                                            :all-rows="$root.user._paypal_payment_keys"
                                                            :rows-count="$root.user._paypal_payment_keys.length"
                                                            :cell-height="1"
                                                            :max-cell-rows="0"
                                                            :is-full-width="true"
                                                            :user="$root.user"
                                                            :behavior="'user_conn'"
                                                            :adding-row="addingRow"
                                                            :available-columns="['mode','name','secret_key','public_key']"
                                                            :special_extras="{force_delete: true}"
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
                                                            :global-meta="$root.tableMeta || $root.settingsMeta.user_payment_keys"
                                                            :table-meta="$root.settingsMeta.user_payment_keys"
                                                            :settings-meta="$root.settingsMeta"
                                                            :all-rows="$root.user._stripe_payment_keys"
                                                            :rows-count="$root.user._stripe_payment_keys.length"
                                                            :cell-height="1"
                                                            :max-cell-rows="0"
                                                            :is-full-width="true"
                                                            :user="$root.user"
                                                            :behavior="'user_conn'"
                                                            :adding-row="addingRow"
                                                            :available-columns="['name','secret_key','public_key']"
                                                            :special_extras="{force_delete: true}"
                                                            @added-row="(row) => { addPaymentRow(row, $root.user._stripe_payment_keys, 'stripe') }"
                                                            @updated-row="(row) => { updatePaymentRow(row, $root.user._stripe_payment_keys, 'stripe') }"
                                                            @delete-row="(row) => { deletePaymentRow(row, $root.user._stripe_payment_keys, 'stripe') }"
                                                    ></custom-table>
                                                </div>
                                            </div>
                                        </div>

                                        <div v-show="activeConnTab === 'others'">
                                            <div class="cloud-block">
                                                <div>
                                                    <label>
                                                        <a href="https://airtable.com/api" target="_blank">Airtable APIs</a>
                                                    </label>
                                                </div>
                                                <div class="table-block">
                                                    <custom-table
                                                        :cell_component_name="'custom-cell-connection'"
                                                        :global-meta="$root.tableMeta || $root.settingsMeta.user_api_keys"
                                                        :table-meta="$root.settingsMeta.user_api_keys"
                                                        :settings-meta="$root.settingsMeta"
                                                        :all-rows="$root.user._airtable_api_keys"
                                                        :rows-count="$root.user._airtable_api_keys.length"
                                                        :cell-height="1"
                                                        :max-cell-rows="0"
                                                        :is-full-width="true"
                                                        :user="$root.user"
                                                        :behavior="'user_conn'"
                                                        :adding-row="addingRow"
                                                        :available-columns="['name','air_type','air_base','key']"
                                                        :headers-changer="{key: 'Key'}"
                                                        :special_extras="{force_delete: true}"
                                                        @added-row="addAirtableApiRow"
                                                        @updated-row="updateAirtableApiRow"
                                                        @delete-row="deleteAirtableApiRow"
                                                    ></custom-table>
                                                </div>
                                            </div>
                                            <div class="cloud-block">
                                                <div>
                                                    <label>
                                                        <a href="https://www.extracttable.com/" target="_blank">Extracttable APIs</a>
                                                    </label>
                                                    <div :style="textSysStyle">
                                                        <span class="indeterm_check__wrap">
                                                            <span class="indeterm_check checkbox-input"
                                                                  :class="{'disabled': !extracttable_clicked}"
                                                                  @click="extractTableAccepted()"
                                                                  :style="checkboxSys"
                                                            >
                                                                <i v-if="$root.user.extracttable_terms" class="glyphicon glyphicon-ok group__icon"></i>
                                                            </span>
                                                        </span>
                                                        <span>I have read and agree to the</span>
                                                        <a href="https://www.extracttable.com/tc.html" target="_blank" @click="extracttable_clicked = true">Terms and Privacy</a>
                                                        <span>by Extracttable</span>
                                                    </div>
                                                </div>
                                                <div class="table-block">
                                                    <custom-table
                                                        v-if="$root.user.extracttable_terms"
                                                        :cell_component_name="'custom-cell-connection'"
                                                        :global-meta="$root.tableMeta || $root.settingsMeta.user_api_keys"
                                                        :table-meta="$root.settingsMeta.user_api_keys"
                                                        :settings-meta="$root.settingsMeta"
                                                        :all-rows="$root.user._extracttable_api_keys"
                                                        :rows-count="$root.user._extracttable_api_keys.length"
                                                        :cell-height="1"
                                                        :max-cell-rows="0"
                                                        :is-full-width="true"
                                                        :user="$root.user"
                                                        :behavior="'user_conn'"
                                                        :adding-row="addingRow"
                                                        :available-columns="['name','key','notes']"
                                                        :special_extras="{force_delete: true}"
                                                        @added-row="addExtracttableApiRow"
                                                        @updated-row="updateExtracttableApiRow"
                                                        @delete-row="deleteExtracttableApiRow"
                                                    ></custom-table>
                                                </div>
                                            </div>
                                            <div class="cloud-block">
                                                <div>
                                                    <label>
                                                        <a href="https://id.atlassian.com/manage-profile/security/api-tokens" target="_blank">Jira APIs</a>
                                                    </label>
                                                </div>
                                                <div class="table-block">
                                                    <custom-table
                                                        :cell_component_name="'custom-cell-connection'"
                                                        :global-meta="$root.tableMeta || $root.settingsMeta.user_api_keys"
                                                        :table-meta="$root.settingsMeta.user_api_keys"
                                                        :settings-meta="$root.settingsMeta"
                                                        :all-rows="$root.user._jira_api_keys"
                                                        :rows-count="$root.user._jira_api_keys.length"
                                                        :cell-height="1"
                                                        :max-cell-rows="0"
                                                        :is-full-width="true"
                                                        :user="$root.user"
                                                        :behavior="'user_conn'"
                                                        :adding-row="addingRow"
                                                        :available-columns="['name','jira_email','key','jira_host','notes']"
                                                        :special_extras="{force_delete: true}"
                                                        @added-row="addJiraApiRow"
                                                        @updated-row="updateJiraApiRow"
                                                        @delete-row="deleteJiraApiRow"
                                                    ></custom-table>
                                                </div>
                                            </div>
                                            <div class="cloud-block">
                                                <div>
                                                    <label>
                                                        AI APIs
                                                        (
                                                        <a href="https://openai.com/" target="_blank">OpenAI</a>
                                                        ,
                                                        <a href="https://ai.google.dev/" target="_blank">Gemini</a>
                                                        )
                                                    </label>
                                                </div>
                                                <div class="table-block">
                                                    <custom-table
                                                        :cell_component_name="'custom-cell-connection'"
                                                        :global-meta="$root.tableMeta || $root.settingsMeta.user_api_keys"
                                                        :table-meta="$root.settingsMeta.user_api_keys"
                                                        :settings-meta="$root.settingsMeta"
                                                        :all-rows="$root.user._ai_api_keys"
                                                        :rows-count="$root.user._ai_api_keys.length"
                                                        :cell-height="1.5"
                                                        :max-cell-rows="0"
                                                        :is-full-width="true"
                                                        :user="$root.user"
                                                        :behavior="'user_conn'"
                                                        :adding-row="addingRow"
                                                        :available-columns="['is_active','name','key','notes','type','model']"
                                                        :special_extras="{force_delete: true}"
                                                        @added-row="addAiApiRow"
                                                        @updated-row="updateAiApiRow"
                                                        @delete-row="deleteAiApiRow"
                                                    ></custom-table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="full-frame container-fluid" v-show="activeTab === 'users'">
                                    <div class="row full-height resources-tab">
                                        <!--LEFT SIDE-->
                                        <div class="col-xs-12 col-md-4 full-height" :style="{padding: 0}">
                                            <div class="full-height">
                                                <div class="top-text" :style="textSysStyle">
                                                    <span>User Groups</span>
                                                </div>
                                                <div class="resources-panel">
                                                    <div class="full-frame">
                                                        <custom-table
                                                                :cell_component_name="'custom-cell-user-groups'"
                                                                :global-meta="$root.tableMeta || $root.settingsMeta['user_groups']"
                                                                :table-meta="$root.settingsMeta['user_groups']"
                                                                :all-rows="$root.user._user_groups"
                                                                :rows-count="$root.user._user_groups.length"
                                                                :cell-height="1"
                                                                :max-cell-rows="0"
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

                                        <div class="col-xs-12 col-md-8 full-height" :style="{padding: 0}">

                                            <div class="full-height" :style="textSysStyle">
                                                <div class="top-text">
                                                    <span>Selected Usergroup: <span>{{ (curUserGroup ? curUserGroup.name : '') }}</span></span>
                                                </div>
                                                <div class="resources-panel" :style="{height: (activeSubUserGroup === 'individ' ? 'calc(100% - 65px)' : 'calc(100% - 30px)')}">
                                                    <div class="resources-menu-header" style="position: relative">
                                                        <button class="btn btn-default btn-sm" :style="textSysStyle" :class="{active: activeSubUserGroup === 'individ'}" @click="activeSubUserGroup = 'individ'">
                                                            Individuals
                                                        </button>
                                                        <button class="btn btn-default btn-sm" :style="textSysStyle" v-if="$root.user._usr_email_domain" :class="{active: activeSubUserGroup === 'cond'}" @click="activeSubUserGroup = 'cond'">
                                                            Criteria
                                                        </button>
                                                        <button class="btn btn-default btn-sm" :style="textSysStyle" v-if="!$root.user._usr_email_domain && curUserGroup" :class="{active: activeSubUserGroup === 'subgroup'}" @click="activeSubUserGroup = 'subgroup'">
                                                            Subgroups
                                                        </button>

                                                        <button v-if="$root.user._usr_email_domain" v-show="activeSubUserGroup === 'cond'" class="btn btn-default btn-sm right-btn" @click="addToIndividuals()">
                                                            Search & Add to "Individuals"
                                                        </button>
                                                    </div>
                                                    <div class="resources-menu-body" v-show="activeSubUserGroup === 'individ'">
                                                        <div class="full-frame">
                                                            <custom-table
                                                                    :cell_component_name="'custom-cell-user-groups'"
                                                                    :global-meta="$root.tableMeta || $root.settingsMeta['user_groups_2_users']"
                                                                    :table-meta="$root.settingsMeta['user_groups_2_users']"
                                                                    :all-rows="curUserGroup ? curUserGroup._individuals : null"
                                                                    :rows-count="curUserGroup ? curUserGroup._individuals.length : 0"
                                                                    :cell-height="1"
                                                                    :max-cell-rows="0"
                                                                    :is-full-width="true"
                                                                    :user="$root.user"
                                                                    :behavior="'data_sets_items'"
                                                                    :forbidden-columns="$root.systemFields"
                                                                    @updated-row="updateUserInUserGroup"
                                                                    @delete-row="deleteUserFromUserGroup"
                                                            ></custom-table>
                                                        </div>
                                                    </div>
                                                    <div class="resources-menu-body" v-if="$root.user._usr_email_domain" v-show="activeSubUserGroup === 'cond'">
                                                        <div class="full-frame">
                                                            <custom-table
                                                                    :cell_component_name="'custom-cell-user-groups'"
                                                                    :global-meta="$root.tableMeta || $root.settingsMeta['user_group_conditions']"
                                                                    :table-meta="$root.settingsMeta['user_group_conditions']"
                                                                    :all-rows="conditiRows"
                                                                    :rows-count="conditiRows ? conditiRows.length : 0"
                                                                    :cell-height="1"
                                                                    :max-cell-rows="0"
                                                                    :is-full-width="true"
                                                                    :user="$root.user"
                                                                    :behavior="'sett_usergroup_conds'"
                                                                    :adding-row="curUserGroup ? addingRow : {active: false}"
                                                                    :forbidden-columns="$root.systemFields"
                                                                    @added-row="addUserGroupCondition"
                                                                    @updated-row="updateUserGroupCondition"
                                                                    @delete-row="deleteUserGroupCondition"
                                                            ></custom-table>
                                                        </div>
                                                    </div>
                                                    <div class="resources-menu-body" v-if="!$root.user._usr_email_domain && curUserGroup" v-show="activeSubUserGroup === 'subgroup'">
                                                        <div class="full-frame">
                                                            <div v-if="usergroupIsSub" class="flex flex--center">This Usergroup is already used as Subgroup!</div>
                                                            <custom-table
                                                                v-else
                                                                :cell_component_name="'custom-cell-user-groups'"
                                                                :global-meta="$root.tableMeta || $root.settingsMeta['user_group_subgroups']"
                                                                :table-meta="$root.settingsMeta['user_group_subgroups']"
                                                                :all-rows="subGroups"
                                                                :rows-count="subGroups ? subGroups.length : 0"
                                                                :cell-height="1"
                                                                :max-cell-rows="0"
                                                                :is-full-width="true"
                                                                :user="$root.user"
                                                                :behavior="'sett_usergroup_conds'"
                                                                :parent-row="curUserGroup"
                                                                :adding-row="curUserGroup ? addingRow : {active: false}"
                                                                :forbidden-columns="$root.systemFields"
                                                                :special_extras="{force_delete: true}"
                                                                @added-row="addUserSubGroup"
                                                                @updated-row="updateUserSubGroup"
                                                                @delete-row="deleteUserSubGroup"
                                                            ></custom-table>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="user-search-box flex usb-full-wi" v-show="activeSubUserGroup === 'individ'">
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

                                <div v-show="activeTab === 'app_set'" :style="textSysStyle">
                                    <div class="appset-block">
                                        <div>
                                            <label>DCR links for:</label>
                                        </div>
                                        <div class="appset-block--settings">
                                            <div class="flex flex--center-v form-group" v-if="app_sett_demo">
                                                <span>Request for a demo:</span>
                                                <input type="text" :style="textSysStyle" class="form-control" v-model="app_sett_demo.val" @change="appSettChanged(app_sett_demo)"/>
                                            </div>
                                            <div class="flex flex--center-v form-group" v-if="app_sett_contact">
                                                <span>Contact Us:</span>
                                                <input type="text" :style="textSysStyle" class="form-control" v-model="app_sett_contact.val" @change="appSettChanged(app_sett_contact)"/>
                                            </div>
                                            <div class="flex flex--center-v form-group" v-if="app_sett_enterprise_dcr">
                                                <span>Enterprise Link:</span>
                                                <input type="text" :style="textSysStyle" class="form-control" v-model="app_sett_enterprise_dcr.val" @change="appSettChanged(app_sett_enterprise_dcr)"/>
                                            </div>
                                        </div>
                                        <div>
                                            <label>Views for:</label>
                                        </div>
                                        <div class="appset-block--settings">
                                            <div class="flex flex--center-v form-group" v-if="app_sett_plan_comp">
                                                <span>Comparision: plan features</span>
                                                <input type="text" :style="textSysStyle" class="form-control" v-model="app_sett_plan_comp.val" @change="appSettChanged(app_sett_plan_comp)"/>
                                            </div>
                                        </div>
                                        <div class="appset-block--settings">
                                            <div class="flex flex--center-v form-group" v-if="app_sett_pricing_view">
                                                <span>Homepage: pricing</span>
                                                <input type="text" :style="textSysStyle" class="form-control" v-model="app_sett_pricing_view.val" @change="appSettChanged(app_sett_pricing_view)"/>
                                            </div>
                                        </div>
                                        <div class="appset-block--settings">
                                            <div class="flex flex--center-v form-group" v-if="app_sett_our_benefits">
                                                <span>Homepage: why {{ $root.app_name }}?</span>
                                                <input type="text" :style="textSysStyle" class="form-control" v-model="app_sett_our_benefits.val" @change="appSettChanged(app_sett_our_benefits)"/>
                                            </div>
                                        </div>

                                        <div>
                                            <label>User searching for adding to usergroup:</label>
                                        </div>
                                        <div class="appset-block--settings">
                                            <div class="flex flex--center-v form-group" v-if="app_sett_public_domain">
                                                <span>Public domains:</span>
                                                <input type="text" :style="textSysStyle" class="form-control" v-model="app_sett_public_domain.val" @change="appSettChanged(app_sett_public_domain)"/>
                                            </div>
                                        </div>


                                        <div>
                                            <label>Other Parameters:</label>
                                        </div>
                                        <div class="appset-block--settings">
                                            <div class="flex flex--center-v form-group" v-if="app_sett_max_nbr_mirror">
                                                <span>Input Type/Mirror: max. # of records that can be mirrored:</span>
                                                <input type="number" :style="textSysStyle" class="form-control" v-model="app_sett_max_nbr_mirror.val" @change="appSettChanged(app_sett_max_nbr_mirror)"/>
                                            </div>
                                            <div class="flex flex--center-v form-group" v-if="app_sett_max_rcds_email">
                                                <span>Add-on/Email: max. records that can be inserted into an email:</span>
                                                <input type="number" :style="textSysStyle" class="form-control" v-model="app_sett_max_rcds_email.val" @change="appSettChanged(app_sett_max_rcds_email)"/>
                                            </div>
                                            <div class="flex flex--center-v" v-if="app_max_backend_sync_delay">
                                                <span>Time interval (seconds) for calling backend to sync up changes:</span>
                                                <input type="number" :style="textSysStyle" class="form-control" v-model="app_max_backend_sync_delay.val" @change="appSettChanged(app_max_backend_sync_delay)"/>
                                            </div>
                                            <div class="flex flex--center-v" v-if="app_max_backend_sync_delay">
                                                <span>Affected functions: collaboration updating, formula calculation,</span>
                                            </div>
                                            <div class="flex flex--center-v form-group" v-if="app_max_backend_sync_delay">
                                                <span>mirroring, ANA addon and other backend changes.</span>
                                            </div>
                                            <div class="flex flex--center-v form-group" v-if="app_sett_referral_credit">
                                                <span>Referral credit ($) per accepted invite:</span>
                                                <input type="number" :style="textSysStyle" class="form-control" v-model="app_sett_referral_credit.val" @change="appSettChanged(app_sett_referral_credit)"/>
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
    import {eventBus} from '../../app';

    import CustomTable from './../CustomTable/CustomTable';
    import ThemeSelectorBlock from "../CommonBlocks/ThemeSelectorBlock";
    import HoverBlock from "../CommonBlocks/HoverBlock";

    import PopupAnimationMixin from '../_Mixins/PopupAnimationMixin';
    import UserApiMixin from "./UserApiMixin";
    import CellStyleMixin from "../_Mixins/CellStyleMixin";

    export default {
        name: "ResourcesPopup",
        components: {
            HoverBlock,
            ThemeSelectorBlock,
            CustomTable,
        },
        mixins: [
            PopupAnimationMixin,
            UserApiMixin,
            CellStyleMixin,
        ],
        data: function () {
            return {
                telinput: '',
                telmsg: '',
                extracttable_clicked: false,

                gl_tooltip: false,
                gl_left: 0,
                gl_top: 0,
                gl_offset: 0,
                sg_tooltip: false,
                sg_left: 0,
                sg_top: 0,
                sg_offset: 0,
                tw_tooltip: false,
                tw_left: 0,
                tw_top: 0,
                tw_offset: 0,

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
                app_sett_public_domain: null,
                app_sett_plan_comp: null,
                app_sett_pricing_view: null,
                app_sett_our_benefits: null,
                app_sett_max_nbr_mirror: null,
                app_max_backend_sync_delay: null,
                app_sett_max_rcds_email: null,
                app_sett_referral_credit: null,
                //PopupAnimationMixin
                idx: 0,
            }
        },
        props:{
            is_visible: Boolean,
        },
        computed: {
            curUserGroup() {
                return this.$root.user._user_groups[this.selectedUserGroup];
            },
            getPopupWidth() {
                switch (this.activeTab) {
                    case 'users': return 1000;
                    case 'themes': return 850;
                    default: return 1000;
                }
            },
            conditiRows() {
                let rows = null;
                if (this.curUserGroup) {
                    rows = this.curUserGroup._conditions;
                    if (!_.find(rows, {is_system: 1})) {
                        rows.unshift({
                            id:null,
                            is_system:1,
                            user_field:'email',
                            logic_operator:'AND',
                            compared_operator:'Include',
                            compared_value:'@'+this.$root.user._usr_email_domain
                        });
                    }
                }
                return rows;
            },
            subGroups() {
                let usrGrp = this.curUserGroup || {};
                return usrGrp._subgroups || [];
            },
            usergroupIsSub() {
                let usedSubs = _.flatten(_.map(this.$root.user._user_groups, '_subgroups'));
                usedSubs = _.map(usedSubs, 'subgroup_id');
                let group = this.curUserGroup;
                return !group || usedSubs.indexOf(group.id) !== -1;
            },
        },
        methods: {
            showGoogleHover(e) {
                this.showHover(e, 'gl');
            },
            showGridHover(e) {
                this.showHover(e, 'sg');
            },
            showTwilioHover(e) {
                this.showHover(e, 'tw');
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
                    Swal('Info', getErrors(errors));
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
                    Swal('Info', getErrors(errors));
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
                    Swal('Info', getErrors(errors));
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
                    Swal('Info', getErrors(errors));
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
                    Swal('Info', getErrors(errors));
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
                    Swal('Info', getErrors(errors));
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
                    Swal('Info', getErrors(errors));
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
                    Swal('Info', getErrors(errors));
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
                    Swal('Info', getErrors(errors));
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
                    Swal('Info', getErrors(errors));
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
                    Swal('Info', getErrors(errors));
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
                    Swal('Info', getErrors(errors));
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
                    Swal('Info', getErrors(errors));
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
                    Swal('Info', getErrors(errors));
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
                    Swal('Info', getErrors(errors));
                }).finally(() => {
                    $.LoadingOverlay('hide');
                });
            },

            //User Group Users Functions
            addUserToUserGroup(tableRow) {
                $.LoadingOverlay('show');
                axios.post('/ajax/user-group/user', {
                    user_group_id: this.curUserGroup.id,
                    user_id: $(this.$refs.search_user).val()
                }).then(({ data }) => {
                    this.$root.user._user_groups = data._user_groups;
                    $(this.$refs.search_user).val(null).trigger('change');
                    this.$forceUpdate();
                }).catch(errors => {
                    Swal('Info', getErrors(errors));
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
                    Swal('Info', getErrors(errors));
                }).finally(() => {
                    $.LoadingOverlay('hide');
                });
            },
            deleteUserFromUserGroup(tableRow) {
                $.LoadingOverlay('show');
                axios.delete('/ajax/user-group/user', {
                    params: {
                        user_group_id: this.curUserGroup.id,
                        user_id: tableRow.id
                    }
                }).then(({ data }) => {
                    let idx = _.findIndex(this.curUserGroup._individuals, {id: tableRow.id});
                    if (idx > -1) {
                        this.curUserGroup._individuals.splice(idx, 1);
                    }
                    this.$forceUpdate();
                }).catch(errors => {
                    Swal('Info', getErrors(errors));
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
                    user_group_id: this.curUserGroup.id,
                    fields: fields,
                }).then(({ data }) => {
                    this.curUserGroup._conditions = data;
                    this.$forceUpdate();
                }).catch(errors => {
                    Swal('Info', getErrors(errors));
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
                    Swal('Info', getErrors(errors));
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
                    let idx = _.findIndex(this.curUserGroup._conditions, {id: tableRow.id});
                    if (idx > -1) {
                        this.curUserGroup._conditions.splice(idx, 1);
                    }
                    this.$forceUpdate();
                }).catch(errors => {
                    Swal('Info', getErrors(errors));
                }).finally(() => {
                    $.LoadingOverlay('hide');
                });
            },
            addToIndividuals() {
                $.LoadingOverlay('show');
                axios.post('/ajax/user-group/cond-to-user', {
                    user_group_id: this.curUserGroup.id,
                }).then(({ data }) => {
                    this.curUserGroup._conditions = [];
                    this.curUserGroup._individuals = data;
                    this.$forceUpdate();
                }).catch(errors => {
                    Swal('Info', getErrors(errors));
                }).finally(() => {
                    $.LoadingOverlay('hide');
                });
            },


            //User Group Subgroup Functions
            addUserSubGroup(tableRow) {
                $.LoadingOverlay('show');

                let fields = _.cloneDeep(tableRow);//copy object
                this.$root.deleteSystemFields(fields);

                axios.post('/ajax/user-group/subgroup', {
                    user_group_id: this.curUserGroup.id,
                    fields: fields,
                }).then(({ data }) => {
                    this.curUserGroup._subgroups = data;
                    this.$forceUpdate();
                }).catch(errors => {
                    Swal('Info', getErrors(errors));
                }).finally(() => {
                    $.LoadingOverlay('hide');
                });
            },
            updateUserSubGroup(tableRow) {
                $.LoadingOverlay('show');

                let group_id = tableRow.id;
                let fields = _.cloneDeep(tableRow);//copy object
                this.$root.deleteSystemFields(fields);

                axios.put('/ajax/user-group/subgroup', {
                    user_group_id: this.curUserGroup.id,
                    model_id: group_id,
                    fields: fields
                }).then(({ data }) => {
                }).catch(errors => {
                    Swal('Info', getErrors(errors));
                }).finally(() => {
                    $.LoadingOverlay('hide');
                });
            },
            deleteUserSubGroup(tableRow) {
                $.LoadingOverlay('show');
                axios.delete('/ajax/user-group/subgroup', {
                    params: {
                        user_group_id: this.curUserGroup.id,
                        model_id: tableRow.id
                    }
                }).then(({ data }) => {
                    let idx = _.findIndex(this.curUserGroup._subgroups, {id: tableRow.id});
                    if (idx > -1) {
                        this.curUserGroup._subgroups.splice(idx, 1);
                    }
                    this.$forceUpdate();
                }).catch(errors => {
                    Swal('Info', getErrors(errors));
                }).finally(() => {
                    $.LoadingOverlay('hide');
                });
            },

            hideMenu(e) {
                if (this.is_visible && e.keyCode === 27 && !this.$root.e__used) {
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
                    Swal('Info', getErrors(errors));
                }).finally(() => {
                    $.LoadingOverlay('hide');
                });
            },
            extractTableAccepted() {
                if (!this.extracttable_clicked) {
                    return;
                }
                $.LoadingOverlay('show');
                axios.put('/ajax/user/set-ectracttable-terms', {
                    extracttable_terms: 1,
                }).then(({ data }) => {
                    this.$root.user.extracttable_terms = 1;
                }).catch(errors => {
                    Swal('Info', getErrors(errors));
                }).finally(() => {
                    $.LoadingOverlay('hide');
                });
            },
        },
        mounted() {
            this.$root.tablesZidxIncrease();
            this.zIdx = this.$root.tablesZidx;
            this.runAnimation({anim_transform:'none'});

            eventBus.$on('global-keydown', this.hideMenu);
            eventBus.$on('open-resource-popup', (tab, idx, subtab) => {
                this.activeTab = tab || 'users';
                if (this.activeTab === 'users') {
                    this.selectedUserGroup = isNumber(idx) ? parseInt(idx) : -1;
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
                minimumInputLength: {val:3},
                width: '100%',
                height: '100%'
            });
            $(this.$refs.search_user).next().css('height', '28px');

            this.app_sett_demo = this.$root.settingsMeta.app_settings['dcr_home_demo'];
            this.app_sett_contact = this.$root.settingsMeta.app_settings['dcr_home_contact'];
            this.app_sett_enterprise_dcr = this.$root.settingsMeta.app_settings['app_enterprise_dcr'];
            this.app_sett_public_domain = this.$root.settingsMeta.app_settings['app_usr_public_domains'];
            this.app_sett_plan_comp = this.$root.settingsMeta.app_settings['app_plan_comparison'];
            this.app_sett_pricing_view = this.$root.settingsMeta.app_settings['app_pricing_view'];
            this.app_sett_our_benefits = this.$root.settingsMeta.app_settings['app_our_benefits'];
            this.app_sett_max_nbr_mirror = this.$root.settingsMeta.app_settings['app_max_mirror_records'];
            this.app_max_backend_sync_delay = this.$root.settingsMeta.app_settings['app_max_backend_sync_delay'];
            this.app_sett_max_rcds_email = this.$root.settingsMeta.app_settings['app_max_records_email_adn'];
            this.app_sett_referral_credit = this.$root.settingsMeta.app_settings['invite_reward_amount'];
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
            padding: 5px;

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
                line-height: 30px !important;
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

            .user-search-box {
                margin-top: 5px;

                .search-wrapper {
                    position: relative;
                    height: 100%;
                    width: 100%;
                    margin-right: 5px;
                }
                .btn-primary {
                    height: 28px !important;
                }
            }

            .right-btn {
                position: absolute;
                right: 0;
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
<style lang="scss">
    .usb-full-wi {
        .select2-container {
            max-width: 100% !important;
        }
    }
</style>