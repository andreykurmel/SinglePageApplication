<template>
    <div class="full-height setup_wrapper flex flex--col">
        <div class="top_elem form-group">
            <div class="flex flex--center">
                <label>Select STMP Server&nbsp;</label>
                <select class="form-control" v-model="emailSettings.server_type" @change="emitUpd()">
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
                        :style="textStyle"
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
                            :style="textStyle"
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
                       :style="textStyle"/>
                <label>App Password</label>
                <input class="form-control l-inl-control"
                       @click="hide_google = false"
                       @change="emitUpd();setGoogleDots();hide_google = true;"
                       v-model="hide_google ? google_dots : emailSettings.google_app_pass"
                       :style="textStyle"/>
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
                        :style="textStyle"
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
                            :style="textStyle"
                            style="width: 120px;padding: 0;"
                    >
                        <option :value="null">No API Key</option>
                        <option v-for="(kkey,kk) in $root.user._sendgrid_api_keys" :value="kkey.id">#{{ kk+1 }}</option>
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
                       :style="textStyle"/>
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
                   :style="textStyle"/>
            <template v-if="emailSettings.server_type === 'sendgrid'">
                <label>&nbsp;&nbsp;&nbsp;Email:&nbsp;</label>
                <span class="indeterm_check__wrap">
                    <span class="indeterm_check" @click="emailSettings.sender_email_isdif = !emailSettings.sender_email_isdif;emitUpd()">
                        <i v-if="emailSettings.sender_email_isdif" class="glyphicon glyphicon-ok group__icon"></i>
                    </span>
                </span>
                <label>&nbsp;by record&nbsp;</label>
                <select v-if="emailSettings.sender_email_isdif" class="form-control" @change="emitUpd()" v-model="emailSettings.sender_email_fld_id" :style="textStyle">
                    <option v-for="fld in tableMeta._fields" :value="fld.id">{{ fld.name }}</option>
                </select>
                <input v-else="" class="form-control" @change="emitUpd()" v-model="emailSettings.sender_email" :style="textStyle"/>
            </template>
            <label>&nbsp;&nbsp;&nbsp;Reply To Email:&nbsp;</label>
            <span class="indeterm_check__wrap">
                <span class="indeterm_check" @click="emailSettings.sender_reply_to_isdif = !emailSettings.sender_reply_to_isdif;emitUpd()">
                    <i v-if="emailSettings.sender_reply_to_isdif" class="glyphicon glyphicon-ok group__icon"></i>
                </span>
            </span>
            <label>&nbsp;by record&nbsp;</label>
            <select v-if="emailSettings.sender_reply_to_isdif" class="form-control" @change="emitUpd()" v-model="emailSettings.sender_reply_to_fld_id" :style="textStyle">
                <option v-for="fld in tableMeta._fields" :value="fld.id">{{ fld.name }}</option>
            </select>
            <input v-else="" class="form-control" @change="emitUpd()" v-model="emailSettings.sender_reply_to" :style="textStyle"/>
        </div>

        <div class="form-group flex flex--center">
            <label>Recipient email address:&nbsp;</label>
            <select class="form-control" @change="emitUpd()" v-model="emailSettings.recipient_field_id" :style="textStyle">
                <option v-for="fld in tableMeta._fields" :value="fld.id">{{ fld.name }}</option>
            </select>
            <label>&nbsp;and&nbsp;</label>
            <input class="form-control"
                   @change="emitUpd()"
                   v-model="emailSettings.recipient_email"
                   :style="textStyle"/>
        </div>

        <div class="form-group">
            <label>Email subject:&nbsp;</label>
            <div style="position: relative">
                <input class="form-control"
                       @keyup="recreateFrm('formula_email_subject')"
                       @focus="formula_email_subject = true"
                       v-model="emailSettings.email_subject"
                       :style="textStyle"/>
                <formula-helper
                        v-if="formula_email_subject"
                        :user="$root.user"
                        :table-meta="tableMeta"
                        :table-row="emailSettings"
                        :header-key="'email_subject'"
                        :no-function="true"
                        :no_prevent="true"
                        :pop_width="'100%'"
                        @set-formula="emitUpd"
                ></formula-helper>
            </div>
        </div>

        <div class="form-group flex flex--center" style="width: 50%">
            <label>Generate emails for records (row) group:&nbsp;</label>
            <select class="form-control" @change="emitUpd('limit_row_group_id')" v-model="emailSettings.limit_row_group_id" :style="textStyle">
                <option :value="null"></option>
                <option v-for="rowgr in tableMeta._row_groups" :value="rowgr.id">{{ rowgr.name }}</option>
            </select>
            <label>&nbsp;Total {{ total_emails }} records.</label>
        </div>
    </div>
</template>

<script>
    import CellStyleMixin from "./../../../../_Mixins/CellStyleMixin.vue";

    import FormulaHelper from "../../../../CustomCell/InCell/FormulaHelper";

    export default {
        name: "EmailServer",
        mixins: [
            CellStyleMixin,
        ],
        components: {
            FormulaHelper,
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
        },
        computed: {
        },
        methods: {
            setSendgridDots() {
                this.sendgrid_dots = String(this.emailSettings.sendgrid_api_key || '').replace(/./gi, '*');
            },
            setGoogleDots() {
                this.google_dots = String(this.emailSettings.google_app_pass || '').replace(/./gi, '*');
            },
            emitUpd(type) {
                this.formula_email_subject = false;
                this.$emit('save-backend', type);
            },
            removeProp(prop) {
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
    }
</style>