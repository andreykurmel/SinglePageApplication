<script>
    /**
     *  Must be present:
     *
     *  this.user: Object
     *  */
    import {SpecialFuncs} from './../classes/SpecialFuncs';

    export default {
        data: function () {
            return {
                autologout_delay: 2000,
                auto_logout_last_active: 0,
            }
        },
        methods: {
            //autologout functions
            refreshAutologout() {
                this.auto_logout_last_active = Date.now();
                //sync between tabs
                SpecialFuncs.set_cookie('auto_logout_timer', Date.now());
                SpecialFuncs.set_cookie('sync_page_autologout', 0);
            },
            checkAutologout() {
                let last_active = to_float(Cookies.get('auto_logout_timer')) || to_float(this.auto_logout_last_active);
                if (!last_active) {
                    this.refreshAutologout();
                }
                else
                if (this.user.id && !this.user.is_force_guested) {
                    let logout_period = to_float(this.user.auto_logout || 30) * 60 * 1000;//ms
                    if (logout_period < 60*1000) {
                        logout_period = 60*1000;//min is 1minute
                    }
                    let last_act_time = to_float(last_active + logout_period);
                    let timeOut = last_act_time - Date.now();
                    if (last_act_time > 0 && timeOut < 0) {
                        SpecialFuncs.set_cookie('sync_page_autologout', 1);
                        Swal('Info','logout');
                        window.location = '/logout';
                    }
                }

                let should_reload = Number(Cookies.get('sync_page_autologout'));
                if (should_reload && this.user && this.user.id) {
                    setTimeout(() => {
                        SpecialFuncs.set_cookie('sync_page_autologout', 0);
                        window.location.reload();
                    }, this.autologout_delay+500);
                }
            },
        },
    }
</script>