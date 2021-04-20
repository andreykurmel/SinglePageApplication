
window._ = require('lodash');
window.moment = require('moment-timezone');
window.Vue = require('vue');

Vue.component('moment-timezones', require('./components/MomentTimezones.vue'));
Vue.component('tablda-colopicker', require('./components/CustomCell/InCell/TabldaColopicker.vue'));

window.addEventListener("load", function(event) {

    const p_timezone = new Vue({
        el: '#vanguard-timezone',
        data: function () {
            return {
            }
        },
        methods: {
        },
    });

    const p_colorpicker = new Vue({
        el: '#vanguard-colorpicker',
        data: function () {
            return {
            }
        },
        methods: {
        },
    });

});