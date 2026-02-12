<template>
    <div class="modal-form">
        <div class="form-wrap">
            <div class="tablda_logo">
                <img :src="settings.root_url+'/assets/img/TablDA_w_text_full.png'" width="80%" :alt="settings.app_name">
            </div>

            <partial-messages :settings="settings"></partial-messages>

            <form role="form" :action="settings.root_url+'/login'" method="POST" id="login-form" autocomplete="off" novalidate="novalidate">
                <input type="hidden" :value="settings.csrf_token" name="_token">
                <input type="hidden" :value="cururl" name="cur_path">
                <div class="form-group input-icon has-success">
                    <label class="sr-only">Email</label>
                    <i class="fas fa-envelope"></i>
                    <input type="email" name="username" class="form-control valid" placeholder="Email" aria-invalid="false" aria-describedby="username-error" v-model="login_email">
                    <span id="username-error" class="help-block error-help-block"></span>
                </div>
                <div class="form-group password-field input-icon">
                    <label class="sr-only">Password</label>
                    <i class="fa fa-lock"></i>
                    <input type="password" name="password" class="form-control" placeholder="Password" v-model="login_pass">
                    <a href="javascript:void(0)" @click="$emit('show_remind')" class="forgot">Forgot my password</a>
                </div>
                <div class="form-group remember-row">
                    <input type="checkbox" name="remember" id="remember" value="1">
                    <label for="remember">Remember me?</label>

                    <a href="javascript:void(0)" @click="$emit('show_register')">Don't have an account?</a>
                </div>
                <div class="form-group">
                    <button type="submit"
                            class="btn btn-success btn-lg btn-block"
                            id="btn-login"
                            @click="(e) => $root.protectFormSubmitByCaptha(e, 'login-form')"
                    >Log In</button>
                </div>
            </form>
            <div class="divider-wrapper">
                <hr class="or-divider">
            </div>

            <partial-buttons :settings="settings"></partial-buttons>

        </div>
        <div class="row">
            <div class="col-xs-12" style="text-align: center;font-size: 12px;">
                <p>Copyright © - {{ settings.app_name }} {{ settings.year }}</p>
            </div>
        </div>
    </div>
</template>

<script>
    import PartialMessages from "./PartialMessages";
    import PartialButtons from "./PartialButtons";

    export default {
        name: 'LoginForm',
        components: {
            PartialButtons,
            PartialMessages,
        },
        data: function () {
            return {
                login_email: this.settings.login_old_email || '',
                login_pass: this.settings.login_old_pass || '',
            }
        },
        props: {
            settings: Object,
        },
        computed: {
            cururl() {
                return location.href;
            },
        },
        methods: {
        },
        created() {
        }
    }
</script>

<style scoped lang="scss">
    @import "ModalForm";
</style>