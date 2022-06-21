
require('./functions');
window._ = require('lodash');
window.moment = require('moment-timezone');
window.Vue = require('vue');
window.Swal = require('sweetalert2');
//AXIOS should not be imported!

Vue.component('vanguard-timezone', require('./VanguardTimezone.vue'));

import {SpecialFuncs} from './classes/SpecialFuncs';

import AutologoutMixin from './global_mixins/AutologoutMixin.vue';

export const eventBus = new Vue();

window.addEventListener("load", function(event) {

    const vang = new Vue({
        el: '#vang',
        mixins: [
            AutologoutMixin,
        ],
        data() {
            return {
                user: {},
            };
        },
        created() {
            try {
                let cur_us = $('#cur_user').attr('content');
                this.user = JSON.parse(cur_us);
            } catch (e) {}
            //auto logout on front-end
            this.refreshAutologout();
            setInterval(() => {
                this.checkAutologout();
            }, this.autologout_delay);
            eventBus.$on('global-click', (e) => {
                this.refreshAutologout();
            });
            //-----
            document.addEventListener('mousedown', (e) => {
                eventBus.$emit('global-click', e);
            });
            document.addEventListener('contextmenu', (e) => {
                eventBus.$emit('global-click', e);
            });
        },
    });

});