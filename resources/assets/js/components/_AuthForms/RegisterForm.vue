<template>
    <div class="modal-form">
        <div class="form-wrap">
            <div class="tablda_logo">
                <img :src="settings.root_url+'/assets/img/TablDA_w_text_full.png'" width="80%" :alt="settings.app_name">
            </div>

            <partial-messages :settings="settings"></partial-messages>

            <form role="form" :action="settings.root_url+'/register'" method="post" id="registration-form" autocomplete="one-time-code">
                <input type="hidden" :value="settings.csrf_token" name="_token">
                <div class="form-group input-icon">
                    <i class="fa fa-at"></i>
                    <input type="email" ref="register_email" name="email" class="form-control" placeholder="Email" v-model="register_email">
                </div>
                <div class="form-group input-icon pwd_wrapper">
                    <i class="fa fa-lock"></i>
                    <input v-model="register_pass" ref="register_pass" type="password" name="password" class="form-control" placeholder="Password">
                    <div class="pwd_info" v-show="show_pass_requirements">
                        <h4>Password must meet following requirements:</h4>
                        <ul>
                            <li :class="[register_pass.match(/[A-z]/) ? 'valid-i' : 'invalid-i']"
                                :style="{background: register_pass.match(/[A-z]/) ? valid_i_bg : in_valid_i_bg}"
                            >At least <strong>one letter.</strong></li>
                            <li :class="[register_pass.match(/[A-Z]/) ? 'valid-i' : 'invalid-i']"
                                :style="{background: register_pass.match(/[A-Z]/) ? valid_i_bg : in_valid_i_bg}"
                            >At least <strong>one capital letter.</strong></li>
                            <li :class="[register_pass.match(/\d/) ? 'valid-i' : 'invalid-i']"
                                :style="{background: register_pass.match(/\d/) ? valid_i_bg : in_valid_i_bg}"
                            >At least <strong>one number.</strong></li>
                            <li :class="[register_pass.match(/[!@#$%^&*()\-=+_.,]/) ? 'valid-i' : 'invalid-i']"
                                :style="{background: register_pass.match(/[!@#$%^&*()\-=+_.,]/) ? valid_i_bg : in_valid_i_bg}"
                            >At least <strong>one special character.</strong></li>
                            <li :class="[register_pass.length > 6 ? 'valid-i' : 'invalid-i']"
                                :style="{background: register_pass.length > 6 ? valid_i_bg : in_valid_i_bg}"
                            >At least <strong>6 characters</strong> in length.</li>
                        </ul>
                    </div>
                    <div class="pwd_info" v-show="show_dont_match" style="top: calc(50% + 20px);">
                        <ul style="margin-bottom: 0">
                            <li class="invalid-i" :style="{background: in_valid_i_bg}">Passwords do not match.</li>
                        </ul>
                    </div>
                </div>
                <div class="form-group input-icon">
                    <i class="fa fa-lock"></i>
                    <input type="password"
                           ref="register_pass_confirm"
                           v-model="register_pass_confirm"
                           name="password_confirmation"
                           class="form-control"
                           placeholder="Confirm password"
                    >
                </div>

                <div class="form-group">
                    <div class="">
                        <input type="checkbox"
                               :disabled="!register_check"
                               :title="!register_check ? 'Please review the Terms of Service.' : ''"
                               name="tos"
                               id="tos"
                               v-model="accept_tos"
                               value="1"/>
                        <label for="tos">
                            <span>I accept</span>
                            <a :href="settings.root_url+'/tos'" target="_blank" @click="register_check = true">Terms of Service</a>
                        </label>
                    </div>
                </div>

                <div v-if="settings.present_promo" class="form-group flex flex--center-v">
                    <input type="text"
                           ref="register_promo_value"
                           v-model="register_promo_value"
                           name="promo_value"
                           class="form-control width-25"
                           placeholder="Enter promo code."
                           @change="updatedPromo"
                    >
                    <div class="width-75 ml10">
                        <label v-if="! promo && promo_completed && register_promo_value" class="no-margin">Code Invalid.</label>
                        <label v-else-if="promo && promo.is_active" class="no-margin">Valid/Active. ${{ promo.credit }} credit will be added to your account.</label>
                        <label v-else-if="promo && ! promo.is_active" class="no-margin">Code Expired.</label>
                    </div>
                </div>

                <div class="form-group">
                    <button type="submit"
                            class="btn btn-success btn-lg btn-block"
                            :title="!accept_tos ? 'Please accept the Terms of Service after reviewing' : ''"
                            :disabled="!accept_tos || !register_check || !register_pass_the_same"
                            @click="(e) => $root.protectFormSubmitByCaptha(e, 'registration-form')"
                    >Register</button>
                </div>

                <div class="form-group have-acc">
                    <a href="javascript:void(0)" @click="$emit('show_login')">Already have an account?</a>
                </div>
            </form>

            <partial-buttons :settings="settings"></partial-buttons>

        </div>
        <div class="row">
            <div class="col-xs-12 footer">
                <p>Copyright © - {{ settings.app_name }} {{ settings.year }}</p>
            </div>
        </div>
    </div>
</template>

<script>
    import PartialMessages from "./PartialMessages";
    import PartialButtons from "./PartialButtons";

    export default {
        name: 'RegisterForm',
        components: {
            PartialButtons,
            PartialMessages,
        },
        data: function () {
            return {
                promo_completed: false,
                promo: null,
                register_email: this.settings.register_old_email || '',
                register_name: this.settings.register_old_username || '',
                register_pass: '',
                register_pass_confirm: '',
                register_promo_value: '',
                register_check: false,
                pass_the_same: false,
                accept_tos: false,

                valid_i_bg: 'url('+this.settings.root_url+'/assets/img/icons/accept.png) no-repeat 0 50%',
                in_valid_i_bg: 'url('+this.settings.root_url+'/assets/img/icons/cross.png) no-repeat 0 50%',
            }
        },
        props: {
            settings: Object,
        },
        computed: {
            register_pass_the_same() {
                return this.register_pass === this.register_pass_confirm;
            },
            show_pass_requirements() {
                return !this.register_pass.match(/[A-z]/)
                    || !this.register_pass.match(/[A-Z]/)
                    || !this.register_pass.match(/\d/)
                    || !this.register_pass.match(/[!$#%@]/)
                    || this.register_pass.length < 6;
            },
            show_dont_match() {
                return this.register_pass
                    && this.register_pass_confirm
                    && this.register_pass !== this.register_pass_confirm
                    && !this.show_pass_requirements;
            },
        },
        methods: {
            updatedPromo() {
                this.promo_completed = false;
                axios.get('/ajax/promo-code', {
                    params: {
                        promo_value: this.register_promo_value,
                    },
                }).then(({data}) => {
                    this.promo = data.code;
                    this.promo_completed = true;
                });
            },
        },
        mounted() {
            this.register_pass = '';
            this.register_pass_confirm = '';
        }
    }
</script>

<style scoped lang="scss">
    @import "ModalForm";

    .pwd_wrapper {
        position: relative;

        .pwd_info {
            position: absolute;
            z-index: 1500;
            top: calc(50% - 100px);
            left: calc(100% + 50px);
            width: 250px;
            padding: 15px;
            background: #fefefe;
            font-size: 0.875em;
            border-radius: 5px;
            box-shadow: 0 1px 3px #ccc;
            border: 1px solid #ddd;

            h4 {
                margin: 0 0 10px 0;
                padding: 0;
                font-weight: normal;
                font-size: 1.1em;
            }
            ul {
                list-style-type: none;
                padding: 0;
            }
        }
        .invalid-i {
            padding-left: 22px;
            line-height: 24px;
            color: #ec3f41;
        }
        .valid-i {
            padding-left: 22px;
            line-height: 24px;
            color: #3a7d34;
        }
    }
</style>