
require('./functions');

window._ = require('lodash');
window.Vue = require('vue').default;
window.uuidv4 = require('uuid/v4');
window.Color = require('color-js');
window.ConvertUnit = require('convert-units');
window.Popper = require('popper.js').default;
window.Cookies = require('js-cookie');
window.Swal = require('sweetalert2');
window.moment = require('moment-timezone');
window.html2canvas = require('html2canvas');
window.IntlTelInput = require('intl-tel-input');
window.twilioDevice = require('@twilio/voice-sdk').Device;
window.jsPDF = require('jspdf').jsPDF;

window.$ = window.jQuery = require('jquery');
//bootstrap
require('bootstrap');
//jquery UI
require('jquery.easing');
require('jquery-mousewheel');
require('jquery-ui/ui/widgets/slider.js');
//jquery plugins
require('./libs/jsvalidation');
require('./libs/bootstrap-datetimepicker');
require('./libs/jquery.bracket');//Tournament addon
//imports-loader?define=>false,module=>false! - disable AMD and CommonJS for global import
require('imports-loader?define=>false,module=>false!jstree');
require('imports-loader?define=>false,module=>false!gasparesganga-jquery-loading-overlay');
require('imports-loader?define=>false,module=>false!./libs/select2');


window.axios = require('axios');
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
let token = document.head.querySelector('meta[name="csrf-token"]');
if (token) {
    window.axios.defaults.headers.common['X-CSRF-TOKEN'] = token.content;
} else {
    console.error('CSRF token not found: https://laravel.com/docs/csrf#csrf-x-csrf-token');
}


//window.Highcharts = require('highcharts/highcharts');
window.Highcharts = require('highcharts/highcharts-gantt');
require('highcharts/modules/data')(window.Highcharts);
require('highcharts/modules/exporting')(window.Highcharts);
require('highcharts/modules/xrange')(window.Highcharts);
require('highcharts/modules/draggable-points')(window.Highcharts);
