<script>
    import {eventBus} from './../app';

    import LayoutData from './MainApp/LayoutData';
    import NavbarData from './NavbarData';

    export default {
        name: 'MainAppWrapper',
        components: {
            LayoutData,
            NavbarData
        },
        data: function () {
            return {
                stimapptop: false,
                user: this.init_user,
                present_activity: true,
                show_login: 0,
                show_register: 0,
                ping_errors: 0,
            }
        },
        props: {
            app_debug: Boolean|Number,
            app_name: String,
            vue_labels: Object,
            init_user: Object,
            flash_message: String,
            jsValidatorFunctions: Array,
            no_settings: Number,
            recaptcha_key: String,
        },
        computed: {
        },
        methods: {
            loadSettings() {
                axios.post('/ajax/get-settings', {
                }).then(({ data }) => {
                    this.setIntl();

                    _.each(data, (val,key) => {
                        this.$set(this.$root.settingsMeta, key, val);
                    });
                    this.$root.settingsMeta.is_loaded = true;
                    console.log('SettingMeta', this.$root.settingsMeta, 'size about: ', JSON.stringify(this.$root.settingsMeta).length);

                    let sync = this.$root.settingsMeta.app_settings['app_max_backend_sync_delay'];
                    this.$root.version_hash_delay = sync && sync.val ? Number(sync.val)*1000 : 3000;

                    this.checkInactiveClouds();
                }).catch(errors => {
                    Swal('Info', getErrors(errors) || 'Server Error');
                });
            },
            checkInactiveClouds() {
                if (location.href.indexOf('/data/') > -1) {
                    let inactive = false;
                    _.each(this.$root.settingsMeta.user_clouds_data, (el) => {
                        if (el.msg_to_user && el.msg_to_user.indexOf('</a>') > -1) {
                            inactive = true;
                        }
                    });
                    if (inactive) {
                        Swal('Info','You have inactive Cloud Connections!');
                    }
                }
            },
            setIntl() {
                //Create instance of IntTelinput for formatting in different places
                let intl = document.getElementById('virtual_phone');
                if (intl) {
                    this.$root.intlTelInput = IntlTelInput(intl, {
                        utilsScript: '/assets/js/utils.js',
                    });
                }
            },
            preventRobotAndProceed() {
                if (this.$root.captchaSkipped()) {
                    this.createdHandler();
                    return;
                }

                window.grecaptcha.ready(() => {
                    window.grecaptcha.execute(this.$root.recaptcha_key, {action: 'submit'}).then((token) => {
                        this.$root.user.captcha_checked = true;
                        this.createdHandler();
                    });
                });
            },
            createdHandler() {
                if (this.flash_message) {
                    Swal('Info', this.flash_message, 'info');
                }

                if (this.$root.user.view_all && window.location.href.match(/\/mrv\//gi)) {
                    this.$root.is_mrv_page = this.$root.user.view_all.id;
                }

                if (this.$root.user.view_all && !this.$root.user.view_all.is_active) {
                    let type = 'View';
                    if (window.location.href.match(/\/mrv\//gi)) {
                        type = 'MRV';
                    }
                    if (window.location.href.match(/\/srv\//gi)) {
                        type = 'SRV';
                    }
                    Swal('Info','The '+type+' is not accessible!').then(() => {
                        window.location.href = '/';
                    });
                    $('body').append('<div style="position: fixed;top: 0;right: 0;left: 0;bottom: 0;background: white;"></div>');
                    return;
                }

                //load settings
                if (this.no_settings != 1) {
                    this.loadSettings();
                } else {
                    this.$root.settingsMeta.is_loaded = true;
                }

                setInterval(() => {
                    if (this.$root.user.see_view) {
                        return;
                    }
                    if (!localStorage.getItem('no_ping')) {
                        //check if user was logout from another terminal
                        axios.post('/ping', {
                            app_user: {
                                id: this.$root.user.id || 0,
                                vendor_script: this.$root.user._vendor_script,
                                app_script: this.$root.user._app_script,
                            },
                            pathname: window.location.pathname,
                        }).then(({ data }) => {

                            if (data.user_changed && !this.$root.user.is_force_guested) {
                                this.$root.debug = true;
                                window.location.reload();
                            }
                            if (data.sync_reloading != this.$root.user.sync_reloading) {
                                window.location.reload();
                            }

                            this.ping_errors = 0;
                            if (data.error) {
                                if (data.error_code == 1) {
                                    window.location = '/logout';
                                } else {
                                    let $msg = data.error;
                                    if ($msg) {
                                        Swal('Info', $msg.replace(/\+/gi, ' ')).then(() => {
                                            window.location.reload();
                                        });
                                    } else {
                                        window.location.reload();
                                    }
                                }
                            }
                            if (data && data.memutree_hash && data.memutree_hash !== this.$root.user.memutree_hash) {
                                this.$root.user.memutree_hash = data.memutree_hash;
                                eventBus.$emit('event-reload-menu-tree');
                            }
                        }).catch((err) => {
                            this.ping_errors++;
                            let $msg = Cookies.get('ping_message');
                            if ($msg) {
                                Swal('Info', $msg.replace(/\+/gi, ' ')).then(() => {
                                    window.location.reload();
                                });
                            }
                            let can_ignore = window.location.href.match('/srv/')
                                || window.location.href.match('/mrv/')
                                || window.location.href.match('/dcr/')
                                || window.location.href.match('/folderview/')
                            if (!can_ignore && this.ping_errors >= 3 && err && err.message !== 'Network Error') {
                                window.location = '/logout';
                            }
                        });
                    }
                }, this.$root.ping_delay);

                _.each(this.jsValidatorFunctions, (fn_name) => {
                    if (window[fn_name]) {
                        (window[fn_name])();
                    }
                });
            },
            watchDocsUrl() {
                if (location.href.search('//docs.') >= 0) {
                    this.$nextTick(() => {
                        let iframe = $('#docs-iframe');
                        if (iframe) {
                            iframe.attr('src', iframe.data('init-path'));
                        }

                        eventBus.$on('global-docs-path-updated', (path) => {
                            history.pushState('', '', path);
                        });
                    });
                }
            },
        },
        created() {
            this.$root.recaptcha_key = this.recaptcha_key;
            this.$root.debug = this.app_debug;
            this.$root.app_name = this.app_name;
            this.$root.labels = this.vue_labels;
            this.$root.isSafari = /^((?!chrome|android).)*safari/i.test(navigator.userAgent);
            this.$root.isIphone = navigator.platform.indexOf("iPhone") > -1;

            //set authorized user
            this.$root.user = this.init_user;
            this.$root.user.timezone = this.$root.user.timezone ? this.$root.user.timezone : moment.tz.guess();
            window.rootUser = this.$root.user;
            console.log('User', this.$root.user, 'size about: ', JSON.stringify(this.$root.user).length);

            //redirect for mobiles (available only homepage and /dcr/, /mrv/, /srv/)
            if (
                window.innerWidth < 768
                && window.location.pathname != '/'
                && window.location.pathname.substr(0,5).toLowerCase() != '/dcr/'
                && window.location.pathname.substr(0,5).toLowerCase() != '/mrv/'
                && window.location.pathname.substr(0,5).toLowerCase() != '/srv/'
            ) {
                window.location.href = '/';
                return;
            }

            this.preventRobotAndProceed();
            this.watchDocsUrl();
        },
        mounted() {
            //Show missed Twilio calls
            let defTwAc = _.find(this.$root.user._twilio_api_keys, {is_active: 1});
            let callHist = this.$root.user._twilio_test_history ? this.$root.user._twilio_test_history.call : [];
            let hasMissed = _.find(callHist, {missed: 1});
            if (defTwAc && hasMissed) {
                let time = setInterval(() => {
                    if (window.twilio_test_pop_ready) {
                        eventBus.$emit('show-twilio-test-popup', defTwAc.id, 'tw_phone');
                        clearInterval(time);
                    }
                }, 500);
            }
        }
    }
</script>