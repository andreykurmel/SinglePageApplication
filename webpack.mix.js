let mix = require('laravel-mix');

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel application. By default, we are compiling the Sass
 | file for the application as well as bundling up all the JS files.
 |
 */

/* VENDOR SCRIPTS */
mix.scripts([
    'public/assets/js/jquery-3.3.1.min.js',
    'public/assets/js/popper.min.js',
    'public/assets/js/bootstrap.min.js',
    'public/assets/js/moment.min.js',
    'public/assets/js/sweetalert.min.js',
    'public/assets/js/delete.handler.js',
    'public/assets/plugins/js-cookie/js.cookie.js',
    'public/vendor/jsvalidation/js/jsvalidation.js',
    'public/assets/plugins/bootstrap-datepicker/bootstrap-datepicker.min.js',
    'public/assets/plugins/croppie/croppie.js'
], 'public/assets/js/vendor.js');


/* VENDOR STYLES */
mix.styles([
    'public/assets/fontawesome/css/all.min.css',
    'public/assets/plugins/bootstrap-datepicker/bootstrap-datepicker.min.css',
    'public/assets/plugins/croppie/croppie.css',
    'node_modules/jquery-loading/plugins/croppie/croppie.css',
],  'public/assets/css/vendor.css');


/* STIM 3D APP */
mix.babel([
    'resources/assets/js/Applications/StimWid/3D/lib/threejs/three.js',
    'resources/assets/js/Applications/StimWid/3D/lib/threejs/Projector.js',
    'resources/assets/js/Applications/StimWid/3D/lib/threejs/three.combined.camera.js',
    'resources/assets/js/Applications/StimWid/3D/lib/threejs/controls/OrbitControls.js',
    'resources/assets/js/Applications/StimWid/3D/lib/threejs/loaders/OBJLoader.js',
    'resources/assets/js/Applications/StimWid/3D/lib/threejs/loaders/MTLLoader.js',
    'resources/assets/js/Applications/StimWid/3D/lib/threejs/loaders/TGALoader.js',
    'resources/assets/js/Applications/StimWid/3D/customized/functions4drawMembers.js',
    'resources/assets/js/Applications/StimWid/3D/customized/setMaterial.js',
    'resources/assets/js/Applications/StimWid/3D/customized/dictionary.js',
    'resources/assets/js/Applications/StimWid/3D/customized/arrow.js',
    'resources/assets/js/Applications/StimWid/3D/customized/skybox.js',
    'resources/assets/js/Applications/StimWid/3D/customized/TerrainPlatter.js',
], 'public/assets/js/three-3d-lib.js').version();
mix.babel([
    'resources/assets/js/Applications/StimWid/3D/customized/drawAISCmembers.js',
    'resources/assets/js/Applications/StimWid/3D/webgl.js',
], 'public/assets/js/three-webgl.js').version();


/* TABLDA APP */
mix.js('resources/assets/js/vendor.js', 'public/assets/js/tablda/vendor.js')
    .js('resources/assets/js/app.js', 'public/assets/js/tablda/app.js')
    .js('resources/assets/js/vanguard.js', 'public/assets/js')
    .sass('resources/assets/sass/app.scss', 'public/assets/css')
    .sass('resources/assets/sass/tablda-app.scss', 'public/assets/css')
    .version();