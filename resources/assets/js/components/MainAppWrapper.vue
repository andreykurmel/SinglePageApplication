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
            }
        },
        props: {
            app_debug: Boolean|Number,
            app_name: String,
            vue_labels: Object,
            init_user: Object,
            flash_message: String,
            jsValidatorFunctions: Array
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
                    Swal('', getErrors(errors));
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

            //redirect for mobiles (available only homepage and /dcr/, /view/)
            if (
                window.screen.width < 768
                && window.location.pathname != '/'
                && window.location.pathname.substr(0,5).toLowerCase() != '/dcr/'
                && window.location.pathname.substr(0,6).toLowerCase() != '/view/'
            ) {
                window.location.href = '/';
            }

            this.loadSettings();

            //set authorized user
            this.$root.user = this.init_user;
            this.$root.user.timezone = this.$root.user.timezone ? this.$root.user.timezone : moment.tz.guess();
            console.log('User', this.$root.user, 'size about: ', JSON.stringify(this.$root.user).length);

            if (this.flash_message) {
                Swal('', this.flash_message, 'info');
            }

            setInterval(() => {
                if (!this.$root.debug && this.$root.user.id) {
                    //check if user was logout from another terminal
                    axios.post('/ping', {
                        app_user: {
                            id: this.$root.user.id,
                            vendor_script: this.$root.user._vendor_script,
                            app_script: this.$root.user._app_script,
                        },
                        pathname: window.location.pathname,
                    }).then(({ data }) => {
                        if (data.error) {
                            if (data.error_code == 1) {
                                window.location = '/logout';
                            } else {
                                let $msg = data.error;
                                if ($msg) {
                                    this.$root.debug = true;
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
                        let $msg = Cookies.get('ping_message');
                        if ($msg) {
                            this.$root.debug = true;
                            Swal('', $msg.replace(/\+/gi, ' ')).then(() => {
                                //Cookies.set('sync_page_reload', 1);
                                window.location.reload();
                            });
                        } else {
                            this.$root.debug = true;
                            Swal('Server ping error');
                            //window.location = '/logout';
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