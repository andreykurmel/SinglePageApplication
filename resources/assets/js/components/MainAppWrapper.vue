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
        },
        computed: {
        },
        methods: {
            loadSettings() {
                $.LoadingOverlay('show');
                axios.post('/ajax/get-settings', {
                }).then(({ data }) => {
                    _.each(data, (val,key) => {
                        this.$set(this.$root.settingsMeta, key, val);
                    });
                    this.$root.settingsMeta.is_loaded = true;
                    console.log('SettingMeta', this.$root.settingsMeta, 'size about: ', JSON.stringify(this.$root.settingsMeta).length);

                    /*if (this.$root.settingsMeta.table_backups) {
                        let fld = _.find(this.$root.settingsMeta.table_backups._fields, {field: 'time'});
                        fld.name += ' (TZ: ' + this.$root.user.timezone + ')'; //moment().tz( this.$root.user.timezone ).format('Z')
                    }*/

                    this.checkInactiveClouds();
                }).catch(errors => {
                    Swal('', getErrors(errors) || 'Server Error');
                }).finally(() => $.LoadingOverlay('hide'));
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
                        Swal('You have inactive Cloud Connections!');
                    }
                }
            },
        },
        created() {
            this.$root.debug = this.app_debug;
            this.$root.app_name = this.app_name;
            this.$root.labels = this.vue_labels;
            this.$root.isSafari = /^((?!chrome|android).)*safari/i.test(navigator.userAgent);
            this.$root.isIphone = navigator.platform.indexOf("iPhone") > -1;

            //redirect for mobiles (available only homepage and /dcr/, /mrv/, /srv/)
            if (
                window.screen.width < 768
                && window.location.pathname != '/'
                && window.location.pathname.substr(0,5).toLowerCase() != '/dcr/'
                && window.location.pathname.substr(0,6).toLowerCase() != '/mrv/'
                && window.location.pathname.substr(0,6).toLowerCase() != '/srv/'
            ) {
                window.location.href = '/';
            }

            if (this.no_settings != 1) {
                this.loadSettings();
            }

            //set authorized user
            this.$root.user = this.init_user;
            this.$root.user.timezone = this.$root.user.timezone ? this.$root.user.timezone : moment.tz.guess();
            console.log('User', this.$root.user, 'size about: ', JSON.stringify(this.$root.user).length);

            if (this.flash_message) {
                Swal('', this.flash_message, 'info');
            }

            setInterval(() => {
                if (this.$root.user.id && !localStorage.getItem('no_ping')) {
                    //check if user was logout from another terminal
                    axios.post('/ping', {
                        app_user: {
                            id: this.$root.user.id,
                            vendor_script: this.$root.user._vendor_script,
                            app_script: this.$root.user._app_script,
                        },
                        pathname: window.location.pathname,
                    }).then(({ data }) => {
                        this.ping_errors = 0;
                        if (data.error) {
                            if (data.error_code == 1) {
                                window.location = '/logout';
                            } else {
                                let $msg = data.error;
                                if ($msg) {
                                    Swal('', $msg.replace(/\+/gi, ' ')).then(() => {
                                        //Cookies.set('sync_page_reload', 1);
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
                            Swal('', $msg.replace(/\+/gi, ' ')).then(() => {
                                //Cookies.set('sync_page_reload', 1);
                                window.location.reload();
                            });
                        }
                        if (this.ping_errors >= 3 && err && err.message !== 'Network Error') {
                            //Swal('Server ping error');
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

        }
    }
</script>