<template>
    <div class="full-height setup_wrapper flex flex--col" :style="textSysStyleSmart">
        <div class="top_elem form-group">
            <div class="flex flex--center">
                <label>Select SMTP Server:&nbsp;</label>
                <select class="form-control"
                        :style="textSysStyle"
                        v-model="emailSettings.server_type"
                        :disabled="!can_edit"
                        @change="emitUpd()"
                >
                    <option value="google">Google</option>
                    <option value="sendgrid">SendGrid</option>
                </select>
            </div>
        </div>

        <!--GOOGLE-->
        <div class="rest_elem" v-if="emailSettings.server_type === 'google'">
            <div :class="[emailSettings.smtp_key_mode === 'table' ? 'form-group' : '']">
                <label>Use</label>
                <select class="form-control l-inl-control"
                        @change="emitUpd()"
                        v-model="emailSettings.smtp_key_mode"
                        :style="textSysStyle"
                        :disabled="!can_edit"
                        style="width: 150px;padding: 0;"
                >
                    <option value="table">Table specific</option>
                    <option value="account">Account</option>
                </select>
                <label class="">
                    <a href="https://support.google.com/accounts/answer/185833" target="_blank">App Password</a>
                </label>
                <template v-if="emailSettings.smtp_key_mode === 'account'">
                    <select class="form-control l-inl-control"
                            @change="emitUpd()"
                            v-model="emailSettings.acc_google_key_id"
                            :style="textSysStyle"
                            :disabled="!can_edit"
                            style="width: 300px;padding: 0;"
                    >
                        <option :value="null">No App Password</option>
                        <option v-for="(eml,kk) in $root.user._google_email_accs" :value="eml.id">#{{ kk+1 }}: {{ eml.email }}</option>
                    </select>
                    <label>.</label>
                </template>
                <label v-if="emailSettings.smtp_key_mode === 'table'">Enter below:</label>
            </div>
            <div v-if="emailSettings.smtp_key_mode === 'table'" class="flex flex--center">
                <label>Email:</label>
                <input class="form-control l-inl-control"
                       @change="emitUpd();"
                       v-model="emailSettings.google_email"
                       :disabled="!can_edit"
                       :style="textSysStyle"/>
                <label>App Password</label>
                <input class="form-control l-inl-control"
                       @click="hide_google = false"
                       @change="emitUpd();setGoogleDots();hide_google = true;"
                       v-model="hide_google ? google_dots : emailSettings.google_app_pass"
                       :disabled="!can_edit"
                       :style="textSysStyle"/>
                <button v-if="emailSettings.google_app_pass" class="btn btn-danger btn-sm" @click="removeProp('google_app_pass')">&times;</button>
                <i class="fa fa-eye" :style="{color: hide_google ? '' : '#F00'}" @click="hide_google = !hide_google"></i>
                <i class="fa fa-info-circle" ref="gl_help" @click="showGoogleHover"></i>
                <hover-block v-if="gl_tooltip"
                             :html_str="$root.google_help"
                             :p_left="gl_left"
                             :p_top="gl_top"
                             :c_offset="gl_offset"
                             @another-click="gl_tooltip = false"
                ></hover-block>
            </div>
        </div>

        <!--SENDGRID-->
        <div class="rest_elem" v-if="emailSettings.server_type === 'sendgrid'">
            <div :class="[emailSettings.smtp_key_mode === 'table' ? 'form-group' : '']">
                <label>Use</label>
                <select class="form-control l-inl-control"
                        @change="emitUpd()"
                        v-model="emailSettings.smtp_key_mode"
                        :disabled="!can_edit"
                        :style="textSysStyle"
                        style="width: 150px;padding: 0;"
                >
                    <option value="table">Table specific</option>
                    <option value="account">Account</option>
                </select>
                <label class="">
                    <a href="https://sendgrid.com/docs/ui/account-and-settings/api-keys/#creating-an-api-key" target="_blank">API Key</a>
                </label>
                <template v-if="emailSettings.smtp_key_mode === 'account'">
                    <select class="form-control l-inl-control"
                            @change="emitUpd()"
                            v-model="emailSettings.acc_sendgrid_key_id"
                            :disabled="!can_edit"
                            :style="textSysStyle"
                            style="width: 200px;padding: 0;"
                    >
                        <option :value="null">No API Key</option>
                        <option v-for="(kkey,kk) in $root.user._sendgrid_api_keys" :value="kkey.id">{{ kkey.name || ('#'+(kk+1)) }}</option>
                    </select>
                    <label>.</label>
                </template>
                <label v-if="emailSettings.smtp_key_mode === 'table'">Enter below:</label>
            </div>
            <div v-if="emailSettings.smtp_key_mode === 'table'" class="flex flex--center">
                <input class="form-control l-inl-control"
                       @click="hide_sendgrid = false"
                       @change="emitUpd();setSendgridDots();hide_sendgrid = true;"
                       v-model="hide_sendgrid ? sendgrid_dots : emailSettings.sendgrid_api_key"
                       :disabled="!can_edit"
                       :style="textSysStyle"/>
                <button v-if="emailSettings.sendgrid_api_key" class="btn btn-danger btn-sm" @click="removeProp('sendgrid_api_key')">&times;</button>
                <i class="fa fa-eye" :style="{color: hide_sendgrid ? '' : '#F00'}" @click="hide_sendgrid = !hide_sendgrid"></i>
                <i class="fa fa-info-circle" ref="gl_help" @click="showGridHover"></i>
                <hover-block v-if="sg_tooltip"
                             :html_str="$root.sendgrid_help"
                             :p_left="sg_left"
                             :p_top="sg_top"
                             :c_offset="sg_offset"
                             @another-click="sg_tooltip = false"
                ></hover-block>
            </div>
        </div>

        <div class="divider"></div>

        <div class="form-group flex flex--center">
            <label>Sender:&nbsp;</label>
            <label>&nbsp;Name:&nbsp;</label>
            <input class="form-control"
                   @change="emitUpd()"
                   v-model="emailSettings.sender_name"
                   :disabled="!can_edit"
                   :style="textSysStyle"/>
            <template v-if="emailSettings.server_type === 'sendgrid'">
                <label>&nbsp;&nbsp;&nbsp;Email:&nbsp;</label>
                <span class="indeterm_check__wrap">
                    <span class="indeterm_check" @click="emailSettings.sender_email_isdif = !emailSettings.sender_email_isdif;emitUpd()">
                        <i v-if="emailSettings.sender_email_isdif" class="glyphicon glyphicon-ok group__icon"></i>
                    </span>
                </span>
                <label>&nbsp;by record&nbsp;</label>
                <select v-if="emailSettings.sender_email_isdif"
                        class="form-control"
                        @change="emitUpd()"
                        v-model="emailSettings.sender_email_fld_id"
                        :disabled="!can_edit"
                        :style="textSysStyle"
                >
                    <option v-for="fld in tableMeta._fields"
                            v-if="inArray(fld.f_type, ['Email'])"
                            :value="fld.id"
                    >{{ fld.name }}</option>
                </select>
                <input v-else
                       class="form-control"
                       @change="emitUpd()"
                       v-model="emailSettings.sender_email"
                       :disabled="!can_edit"
                       :style="textSysStyle"/>
            </template>
            <label>&nbsp;&nbsp;&nbsp;Reply To Email:&nbsp;</label>
            <span class="indeterm_check__wrap">
                <span class="indeterm_check" @click="emailSettings.sender_reply_to_isdif = !emailSettings.sender_reply_to_isdif;emitUpd()">
                    <i v-if="emailSettings.sender_reply_to_isdif" class="glyphicon glyphicon-ok group__icon"></i>
                </span>
            </span>
            <label>&nbsp;by record&nbsp;</label>
            <select v-if="emailSettings.sender_reply_to_isdif"
                    class="form-control"
                    @change="emitUpd()"
                    v-model="emailSettings.sender_reply_to_fld_id"
                    :disabled="!can_edit"
                    :style="textSysStyle"
            >
                <option v-for="fld in tableMeta._fields"
                        v-if="inArray(fld.f_type, ['Email'])"
                        :value="fld.id"
                >{{ fld.name }}</option>
            </select>
            <input v-else
                   class="form-control"
                   @change="emitUpd()"
                   v-model="emailSettings.sender_reply_to"
                   :disabled="!can_edit"
                   :style="textSysStyle"/>
        </div>

        <div class="flex flex--center-v">
            <label>Recipients (email addresses. Use comma, semi-colon or space to separate multiple addresses):</label>
        </div>
        <div class="flex flex--center-v">
            <label style="min-width: 60px">&nbsp;&nbsp;&nbsp;To:&nbsp;</label>
            <select class="form-control"
                    @change="emitUpd()"
                    v-model="emailSettings.recipient_field_id"
                    :disabled="!can_edit"
                    :style="textSysStyle"
            >
                <option :value="null"></option>
                <option v-for="fld in tableMeta._fields"
                        v-if="inArray(fld.f_type, ['Email'])"
                        :value="fld.id"
                >{{ fld.name }}</option>
            </select>
            <label>&nbsp;&nbsp;&nbsp;and&nbsp;</label>
            <input class="form-control"
                   @change="emitUpd()"
                   v-model="emailSettings.recipient_email"
                   :disabled="!can_edit"
                   :style="textSysStyle"/>
        </div>
        <div class="flex flex--center-v">
            <label style="min-width: 60px">&nbsp;&nbsp;&nbsp;Cc:&nbsp;</label>
            <select class="form-control"
                    @change="emitUpd()"
                    v-model="emailSettings.cc_recipient_field_id"
                    :disabled="!can_edit"
                    :style="textSysStyle"
            >
                <option :value="null"></option>
                <option v-for="fld in tableMeta._fields"
                        v-if="inArray(fld.f_type, ['Email'])"
                        :value="fld.id"
                >{{ fld.name }}</option>
            </select>
            <label>&nbsp;&nbsp;&nbsp;and&nbsp;</label>
            <input class="form-control"
                   @change="emitUpd()"
                   v-model="emailSettings.cc_recipient_email"
                   :disabled="!can_edit"
                   :style="textSysStyle"/>
        </div>
        <div class="form-group flex flex--center-v">
            <label style="min-width: 60px">&nbsp;&nbsp;&nbsp;Bcc:&nbsp;</label>
            <select class="form-control"
                    @change="emitUpd()"
                    v-model="emailSettings.bcc_recipient_field_id"
                    :disabled="!can_edit"
                    :style="textSysStyle"
            >
                <option :value="null"></option>
                <option v-for="fld in tableMeta._fields"
                        v-if="inArray(fld.f_type, ['Email'])"
                        :value="fld.id"
                >{{ fld.name }}</option>
            </select>
            <label>&nbsp;&nbsp;&nbsp;and&nbsp;</label>
            <input class="form-control"
                   @change="emitUpd()"
                   v-model="emailSettings.bcc_recipient_email"
                   :disabled="!can_edit"
                   :style="textSysStyle"/>
        </div>

        <div class="form-group">
            <label>Email subject:&nbsp;</label>
            <div style="position: relative">
                <input class="form-control"
                       @keyup="recreateFrm('formula_email_subject')"
                       @focus="formula_email_subject = true"
                       v-model="emailSettings.email_subject"
                       :disabled="!can_edit"
                       :style="textSysStyle"/>
                <formula-helper
                        v-if="formula_email_subject"
                        :user="$root.user"
                        :table-meta="tableMeta"
                        :table-row="emailSettings"
                        :header-key="'email_subject'"
                        :no-function="true"
                        :no_prevent="true"
                        :pop_width="'100%'"
                        @close-formula="formula_email_subject = false"
                        @set-formula="emitUpd"
                ></formula-helper>
            </div>
        </div>

        <div class="form-group flex flex--center-v">
            <label>Generate emails for records (row) group:&nbsp;</label>
            <select-block
                :options="rgOpts()"
                :sel_value="emailSettings.limit_row_group_id"
                :style="{ width:'180px', flexShrink: 0, ...textSysStyle }"
                :with_links="true"
                :is_disabled="!can_edit"
                @option-select="optUpdate"
                @link-click="showRow(emailSettings.limit_row_group_id)"
            ></select-block>
            <label>&nbsp;Total {{ total_emails }} records.</label>

            <label>&nbsp;&nbsp;Attachments:&nbsp;</label>
            <select
                style="width: 180px;flex-shrink: 0;"
                class="form-control"
                @change="emitUpd('field_id_attachments')"
                v-model="emailSettings.field_id_attachments"
                :disabled="!can_edit"
                :style="textSysStyle"
            >
                <option :value="null"></option>
                <option v-for="fld in tableMeta._fields" v-if="fld.f_type === 'Attachment'" :value="fld.id">{{ fld.name }}</option>
            </select>
        </div>

        <div class="form-group flex flex--center-v">
            <label>Preview background, header:&nbsp;</label>
            <div class="clr_wrap">
                <tablda-colopicker
                    :init_color="emailSettings.email_background_header"
                    :avail_null="true"
                    :can_edit="can_edit"
                    @set-color="(clr) => {emailSettings.email_background_header = clr; emitUpd('email_background_header')}"
                ></tablda-colopicker>
            </div>

            <label>&nbsp;&nbsp;&nbsp;&nbsp;body:&nbsp;</label>
            <div class="clr_wrap">
                <tablda-colopicker
                    :init_color="emailSettings.email_background_body"
                    :avail_null="true"
                    :can_edit="can_edit"
                    @set-color="(clr) => {emailSettings.email_background_body = clr; emitUpd('email_background_body')}"
                ></tablda-colopicker>
            </div>
        </div>
    </div>
</template>

<script>
    import {SpecialFuncs} from "../../../../../classes/SpecialFuncs";

    import CellStyleMixin from "./../../../../_Mixins/CellStyleMixin.vue";

    import TabldaColopicker from "../../../../CustomCell/InCell/TabldaColopicker";
    import SelectBlock from "../../../../CommonBlocks/SelectBlock.vue";
    import {OptionsHelper} from "../../../../../classes/helpers/OptionsHelper";
    import {eventBus} from "../../../../../app";

    export default {
        name: "EmailServer",
        mixins: [
            CellStyleMixin,
        ],
        components: {
            SelectBlock,
            TabldaColopicker,
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

                google_dots: '',
                sendgrid_dots: '',
                hide_sendgrid: true,
                hide_google: true,

                formula_email_subject: false,
            }
        },
        props:{
            tableMeta: Object,
            emailSettings: Object,
            total_emails: Number,
            can_edit: Boolean|Number,
        },
        computed: {
            OptionsHelper() {
                return OptionsHelper
            }
        },
        methods: {
            inArray(type, array) {
                return array.indexOf(type) > -1;
            },
            setSendgridDots() {
                this.sendgrid_dots = String(this.emailSettings.sendgrid_api_key || '').replace(/./gi, '*');
            },
            setGoogleDots() {
                this.google_dots = String(this.emailSettings.google_app_pass || '').replace(/./gi, '*');
            },
            rgOpts() {
                return OptionsHelper.rowGroup(this.tableMeta, true);
            },
            optUpdate(opt) {
                this.emailSettings.limit_row_group_id = opt.val;
                this.emitUpd('limit_row_group_id');
            },
            showRow(id) {
                eventBus.$emit('show-grouping-settings-popup', this.tableMeta.db_name, 'row', id);
            },
            emitUpd(type) {
                if (!this.can_edit) {
                    return;
                }
                this.formula_email_subject = false;
                this.$emit('save-backend', this.emailSettings, type);
            },
            removeProp(prop) {
                if (!this.can_edit) {
                    return;
                }
                this.emailSettings[prop] = '';
                this.setSendgridDots();
                this.setGoogleDots();
                this.emitUpd();
            },
            //tooltips
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
            //helper
            recreateFrm(param) {
                this[param] = false;
                this.$nextTick(() => {
                    this[param] = true;
                });
            },
        },
        mounted() {
            this.setSendgridDots();
            this.setGoogleDots();
        },
        beforeDestroy() {
        }
    }
</script>

<style lang="scss" scoped>
    .setup_wrapper {
        font-size: 1.1em;
        white-space: nowrap;

        select {
            font-size: 1.1em !important;
        }
        label {
            margin: 0;
        }

        .l-inl-control {
            display: inline-block;
            margin: 0 5px;
            height: auto;
        }
        .top_elem {
        }
        .rest_elem {
            border: 1px solid #CCC;
            padding: 10px;
            border-radius: 10px;
        }

        .divider {
            border-top: 3px solid #666;
            margin: 15px 0;
        }

        .clr_wrap {
            height: 36px;
            width: 72px;
            position: relative;
            border: 1px solid #CCC;
            border-radius: 5px;
        }
    }
</style>