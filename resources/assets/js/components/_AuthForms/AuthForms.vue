<template>
    <div>
        <div id="login" v-show="showLogin">
            <div class="modal-form-background" @click="allHide()"></div>
            <login-form
                    :settings="settings"
                    @show_remind="allHide();showRemind=true;"
                    @show_register="allHide();showRegister=true;"
            ></login-form>
        </div>
        <div id="registar" v-show="showRegister">
            <div class="modal-form-background" @click="allHide()"></div>
            <register-form
                    :settings="settings"
                    @show_login="allHide();showLogin=true;"
            ></register-form>
        </div>
        <div id="remind" v-show="showRemind">
            <div class="modal-form-background" @click="allHide()"></div>
            <remind-form
                    :settings="settings"
                    @show_login="allHide();showLogin=true;"
            ></remind-form>
        </div>
    </div>
</template>

<script>
    import LoginForm from "./LoginForm";
    import RegisterForm from "./RegisterForm";
    import RemindForm from "./RemindForm";

    export default {
        name: 'AuthForms',
        components: {
            RemindForm,
            RegisterForm,
            LoginForm,
        },
        data: function () {
            return {
                showLogin: !!this.settings.session_success,
                showRegister: false,
                showRemind: false,
            }
        },
        props: {
            show_register: Boolean|Number,
            show_login: Boolean|Number,
            settings: {
                type: Object,
                default: function () {
                    return {
                        present_promo: 0,
                        root_url: 'https://tablda.com',
                        app_name: 'TablDA',
                        errors: [],
                        session_success: false,
                        year: 2023,
                        social_provider: false,
                        csrf_token: '',
                        register_old_email: '',
                        register_old_username: '',
                        login_old_email: '',
                        login_old_pass: '',
                    }
                }
            },
        },
        watch: {
            show_login(val) {
                this.showLogin = true;
            },
            show_register(val) {
                this.showRegister = true;
            }
        },
        methods: {
            allHide() {
                this.showLogin = false;
                this.showRegister = false;
                this.showRemind = false;
            }
        },
        created() {
            if (location.search.match(/^\?register/gi)) {
                this.showRegister = true;
            }
            if (location.search.match(/^\?login/gi)) {
                this.showLogin = true;
            }
            console.log('settings', this.settings);
        }
    }
</script>

<style scoped>
    .modal-form-background{
        background-color: black;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        position: fixed;
        opacity: 0.5;
        z-index: 1200;
    }
</style>