<?php

/**
 * Authentication
 */

Route::get('login', 'Auth\AuthController@getLogin')->name('login');
Route::post('login', 'Auth\AuthController@postLogin');

Route::get('logout', [
    'as' => 'auth.logout',
    'uses' => 'Auth\AuthController@getLogout'
]);

// Allow registration routes only if registration is enabled.
if (settings('reg_enabled')) {
    Route::get('register', 'Auth\AuthController@getRegister');
    Route::post('register', 'Auth\AuthController@postRegister');
    Route::get('register/confirmation/{token}', [
        'as' => 'register.confirm-email',
        'uses' => 'Auth\AuthController@confirmEmail'
    ]);
}

// Register password reset routes only if it is enabled inside website settings.
if (settings('forgot_password')) {
    Route::get('password/remind', 'Auth\PasswordController@forgotPassword');
    Route::post('password/remind', 'Auth\PasswordController@sendPasswordReminder');
    Route::get('password/reset/{token}', 'Auth\PasswordController@getReset');
    Route::post('password/reset', 'Auth\PasswordController@postReset');
}

/**
 * Two-Factor Authentication
 */
if (settings('2fa.enabled')) {
    Route::get('auth/two-factor-authentication', [
        'as' => 'auth.token',
        'uses' => 'Auth\AuthController@getToken'
    ]);

    Route::post('auth/two-factor-authentication', [
        'as' => 'auth.token.validate',
        'uses' => 'Auth\AuthController@postToken'
    ]);
}

/**
 * Social Login
 */
Route::get('auth/{provider}/login', [
    'as' => 'social.login',
    'uses' => 'Auth\SocialAuthController@redirectToProvider',
    'middleware' => 'social.login'
]);

Route::get('auth/{provider}/callback', 'Auth\SocialAuthController@handleProviderCallback');

Route::get('auth/twitter/email', 'Auth\SocialAuthController@getTwitterEmail');
Route::post('auth/twitter/email', 'Auth\SocialAuthController@postTwitterEmail');

Route::group(['middleware' => 'auth'], function () {

    /**
     * Dashboard
     */

    Route::get('/dashboard', [
        'as' => 'dashboard',
        'uses' => 'DashboardController@index'
    ]);

    /**
     * User Profile
     */

    Route::get('profile', [
        'as' => 'profile',
        'uses' => 'ProfileController@index'
    ]);

    Route::get('profile/activity', [
        'as' => 'profile.activity',
        'uses' => 'ProfileController@activity'
    ]);

    Route::put('profile/details/update', [
        'as' => 'profile.update.details',
        'uses' => 'ProfileController@updateDetails'
    ]);

    Route::post('profile/avatar/update', [
        'as' => 'profile.update.avatar',
        'uses' => 'ProfileController@updateAvatar'
    ]);

    Route::post('profile/avatar/update/external', [
        'as' => 'profile.update.avatar-external',
        'uses' => 'ProfileController@updateAvatarExternal'
    ]);

    Route::put('profile/login-details/update', [
        'as' => 'profile.update.login-details',
        'uses' => 'ProfileController@updateLoginDetails'
    ]);

    Route::post('profile/two-factor/enable', [
        'as' => 'profile.two-factor.enable',
        'uses' => 'ProfileController@enableTwoFactorAuth'
    ]);

    Route::post('profile/two-factor/disable', [
        'as' => 'profile.two-factor.disable',
        'uses' => 'ProfileController@disableTwoFactorAuth'
    ]);

    Route::get('profile/sessions', [
        'as' => 'profile.sessions',
        'uses' => 'ProfileController@sessions'
    ]);

    Route::delete('profile/sessions/{session}/invalidate', [
        'as' => 'profile.sessions.invalidate',
        'uses' => 'ProfileController@invalidateSession'
    ]);

    Route::get('profile/{id}', 'ProfileController@viewProfile')->where(['id' => '\d+']);

    /**
     * User Management
     */
    Route::get('user', [
        'as' => 'user.list',
        'uses' => 'UsersController@index'
    ]);

    Route::get('user/create', [
        'as' => 'user.create',
        'uses' => 'UsersController@create'
    ]);

    Route::post('user/create', [
        'as' => 'user.store',
        'uses' => 'UsersController@store'
    ]);

    Route::get('user/bulk', [
        'as' => 'user.bulk',
        'uses' => 'UsersController@bulk'
    ]);

    Route::post('user/bulk/csv', [
        'as' => 'user.store_bulk_csv',
        'uses' => 'UsersController@storeBulkCsv'
    ]);
    Route::post('user/bulk/paste', [
        'as' => 'user.store_bulk_paste',
        'uses' => 'UsersController@storeBulkPaste'
    ]);

    Route::get('user/{user}/show', [
        'as' => 'user.show',
        'uses' => 'UsersController@view'
    ]);

    Route::get('user/{user}/edit', [
        'as' => 'user.edit',
        'uses' => 'UsersController@edit'
    ]);

    Route::put('user/{user}/update/details', [
        'as' => 'user.update.details',
        'uses' => 'UsersController@updateDetails'
    ]);

    Route::put('user/{user}/update/login-details', [
        'as' => 'user.update.login-details',
        'uses' => 'UsersController@updateLoginDetails'
    ]);

    Route::delete('user/{user}/delete', [
        'as' => 'user.delete',
        'uses' => 'UsersController@delete'
    ]);

    Route::post('user/{user}/update/avatar', [
        'as' => 'user.update.avatar',
        'uses' => 'UsersController@updateAvatar'
    ]);

    Route::post('user/{user}/update/avatar/external', [
        'as' => 'user.update.avatar.external',
        'uses' => 'UsersController@updateAvatarExternal'
    ]);

    Route::get('user/{user}/sessions', [
        'as' => 'user.sessions',
        'uses' => 'UsersController@sessions'
    ]);

    Route::delete('user/{user}/sessions/{session}/invalidate', [
        'as' => 'user.sessions.invalidate',
        'uses' => 'UsersController@invalidateSession'
    ]);

    Route::post('user/{user}/two-factor/enable', [
        'as' => 'user.two-factor.enable',
        'uses' => 'UsersController@enableTwoFactorAuth'
    ]);

    Route::post('user/{user}/two-factor/disable', [
        'as' => 'user.two-factor.disable',
        'uses' => 'UsersController@disableTwoFactorAuth'
    ]);

    /**
     * Roles & Permissions
     */

    Route::get('role', [
        'as' => 'role.index',
        'uses' => 'RolesController@index'
    ]);

    Route::get('role/create', [
        'as' => 'role.create',
        'uses' => 'RolesController@create'
    ]);

    Route::post('role/store', [
        'as' => 'role.store',
        'uses' => 'RolesController@store'
    ]);

    Route::get('role/{role}/edit', [
        'as' => 'role.edit',
        'uses' => 'RolesController@edit'
    ]);

    Route::put('role/{role}/update', [
        'as' => 'role.update',
        'uses' => 'RolesController@update'
    ]);

    Route::delete('role/{role}/delete', [
        'as' => 'role.delete',
        'uses' => 'RolesController@delete'
    ]);


    Route::post('permission/save', [
        'as' => 'permission.save',
        'uses' => 'PermissionsController@saveRolePermissions'
    ]);

    Route::resource('permission', 'PermissionsController');

    /**
     * Settings
     */

    Route::get('settings', [
        'as' => 'settings.general',
        'uses' => 'SettingsController@general',
        'middleware' => 'permission:settings.general'
    ]);

    Route::post('settings/general', [
        'as' => 'settings.general.update',
        'uses' => 'SettingsController@update',
        'middleware' => 'permission:settings.general'
    ]);

    Route::get('settings/auth', [
        'as' => 'settings.auth',
        'uses' => 'SettingsController@auth',
        'middleware' => 'permission:settings.auth'
    ]);

    Route::post('settings/auth', [
        'as' => 'settings.auth.update',
        'uses' => 'SettingsController@update',
        'middleware' => 'permission:settings.auth'
    ]);

// Only allow managing 2FA if AUTHY_KEY is defined inside .env file
    if (env('AUTHY_KEY')) {
        Route::post('settings/auth/2fa/enable', [
            'as' => 'settings.auth.2fa.enable',
            'uses' => 'SettingsController@enableTwoFactor',
            'middleware' => 'permission:settings.auth'
        ]);

        Route::post('settings/auth/2fa/disable', [
            'as' => 'settings.auth.2fa.disable',
            'uses' => 'SettingsController@disableTwoFactor',
            'middleware' => 'permission:settings.auth'
        ]);
    }

    Route::post('settings/auth/registration/captcha/enable', [
        'as' => 'settings.registration.captcha.enable',
        'uses' => 'SettingsController@enableCaptcha',
        'middleware' => 'permission:settings.auth'
    ]);

    Route::post('settings/auth/registration/captcha/disable', [
        'as' => 'settings.registration.captcha.disable',
        'uses' => 'SettingsController@disableCaptcha',
        'middleware' => 'permission:settings.auth'
    ]);

    Route::get('settings/notifications', [
        'as' => 'settings.notifications',
        'uses' => 'SettingsController@notifications',
        'middleware' => 'permission:settings.notifications'
    ]);

    Route::post('settings/notifications', [
        'as' => 'settings.notifications.update',
        'uses' => 'SettingsController@update',
        'middleware' => 'permission:settings.notifications'
    ]);

    /**
     * Activity Log
     */

    Route::get('activity', [
        'as' => 'activity.index',
        'uses' => 'ActivityController@index'
    ]);

    Route::get('activity/user/{user}/log', [
        'as' => 'activity.user',
        'uses' => 'ActivityController@userActivity'
    ]);

});


/**
 * Installation
 */

$router->get('install', [
    'as' => 'install.start',
    'uses' => 'InstallController@index'
]);

$router->get('install/requirements', [
    'as' => 'install.requirements',
    'uses' => 'InstallController@requirements'
]);

$router->get('install/permissions', [
    'as' => 'install.permissions',
    'uses' => 'InstallController@permissions'
]);

$router->get('install/database', [
    'as' => 'install.database',
    'uses' => 'InstallController@databaseInfo'
]);

$router->get('install/start-installation', [
    'as' => 'install.installation',
    'uses' => 'InstallController@installation'
]);

$router->post('install/start-installation', [
    'as' => 'install.installation',
    'uses' => 'InstallController@installation'
]);

$router->post('install/install-app', [
    'as' => 'install.install',
    'uses' => 'InstallController@install'
]);

$router->get('install/complete', [
    'as' => 'install.complete',
    'uses' => 'InstallController@complete'
]);

$router->get('install/error', [
    'as' => 'install.error',
    'uses' => 'InstallController@error'
]);

/** Tablda routes */
Route::domain('blog.'.env('APP_DOMAIN'))->group(function () {
    Route::get('/', 'Tablda\AppController@blog')->name('blog');
});

Route::post('/user_state', 'Tablda\AppController@user_state');
Route::post('/send-mail', 'Tablda\AppController@sendMail');
Route::get('/discourse/sso', 'Tablda\AppController@discourseSSO');
Route::get('/discourse/redirect', 'Tablda\AppController@discourseRedirect')->name('discourse-redirect');
Route::get('/discourse/exit', 'Tablda\AppController@getDiscourseLogout')->name('discourse-exit');
Route::get('/discourse/logout', 'Tablda\AppController@getDiscourseLogout')->name('discourse/logout');
Route::get('/storage/{filepath}', 'Tablda\AppController@protectFile')->where(['filepath' => '.+'])->name('old_protect');
Route::get('/file/{filehash}', 'Tablda\AppController@protectFile')->where(['filehash' => '.+'])->name('protect_file');

//ckeditor
Route::prefix('ckeditor')->group(function () {
    Route::get('/file-browse', 'Tablda\CkeditorController@browseFile');
    Route::post('/file-upload', 'Tablda\CkeditorController@uploadFile');
    Route::delete('/file-delete', 'Tablda\CkeditorController@deleteFile');
});

//Applications
Route::get('/list', 'Tablda\Applications\AllController@listApps')->name('apps.list');
Route::get('/apps/my', 'Tablda\Applications\AllController@myApps')->name('apps');
Route::get('/apps', 'Tablda\Applications\AllController@index');
Route::get('/apps/{apppath}', 'Tablda\Applications\AllController@get')->where(['apppath' => '.+']);
Route::post('/apps/{apppath}', 'Tablda\Applications\AllController@post')->where(['apppath' => '.+']);
Route::put('/apps/{apppath}', 'Tablda\Applications\AllController@put')->where(['apppath' => '.+']);
Route::delete('/apps/{apppath}', 'Tablda\Applications\AllController@delete')->where(['apppath' => '.+']);
//
Route::post('/ajax/apps/toggle', 'Tablda\Applications\AllController@appToggle');
//-------
Route::get('/user-cloud/activate', 'Tablda\UserCloudController@activate');

Route::group(['middleware' => ['subdomains.redirect']], function () {
    Route::get('/', 'Tablda\AppController@home')->name('homepage');
    Route::get('/tos', 'Tablda\AppController@terms')->name('terms');
    Route::get('/privacy', 'Tablda\AppController@privacy')->name('privacy');
    Route::get('/disclaimer', 'Tablda\AppController@disclaimer')->name('disclaimer');

    Route::get('/test-vue', 'Tablda\TestController@testVue');
    Route::get('/test', 'Tablda\TestController@test');
    Route::get('/day-gone', 'Tablda\TestController@dayGone');
    Route::get('/load-account-tables', 'Tablda\TestController@loadAccountTables');

    Route::post('/ping', 'Tablda\AppController@ping')->middleware('cors');
    Route::get('/data', 'Tablda\AppController@index')->name('data');
    Route::get('/data/{table}', 'Tablda\AppController@table')->name('table')->where(['table' => '.+']);
    Route::get('/visiting/{table_id}', 'Tablda\AppController@tableId')->name('visiting')->where(['table_id' => '.+']);
    Route::get('/embed/{view}', 'Tablda\AppController@embed')->name('embed')->where(['view' => '.+']);
    Route::get('/embed-dcr/{code}', 'Tablda\AppController@tableRequest')->name('embed-request')->where(['code' => '.+']);
    Route::get('/srv/{tablehash}', 'Tablda\AppController@singleRecordView')->name('srv')->where(['tablehash' => '.+']);
    Route::get('/mrv/{view}', 'Tablda\AppController@multiRecordView')->name('mrv')->where(['view' => '.+']);
    Route::get('/view/{view}', 'Tablda\AppController@folderView')->name('view')->where(['view' => '.+']);
    Route::get('/dcr/{code}', 'Tablda\AppController@tableRequest')->name('request')->where(['code' => '.+']);

    //static pages
    Route::get('/getstarted', 'Tablda\StaticPageController@getStartedPage')->name('getstarted');
    Route::get('/introduction/{url}', 'Tablda\StaticPageController@getIntroduction')->name('introduction')->where(['url' => '.+']);
    Route::get('/tutorials/{url}', 'Tablda\StaticPageController@getTutorial')->name('tutorials')->where(['url' => '.+']);
    Route::get('/templates/{url}', 'Tablda\StaticPageController@getTemplate')->name('templates')->where(['url' => '.+']);
    Route::get('/applications/{url}', 'Tablda\StaticPageController@getApplications')->name('applications')->where(['url' => '.+']);
});

Route::prefix('ajax')->group(function () {
    Route::get('/menu-tree', 'Tablda\FolderController@getMenuTree');
    Route::get('/correspondence-app-tables', 'Tablda\TableController@getCorrespondenceTables');
    Route::post('/correspondence-used-fields', 'Tablda\TableController@getCorrespondenceUsedFields');
    Route::post('/get-settings', 'Tablda\TableController@settingsMeta');

    //table meta
    Route::prefix('table')->group(function () {
        Route::post('/version_hash', 'Tablda\TableController@versionHash');
        Route::post('/chart', 'Tablda\TableChartController@saveChart');
        Route::post('/realign-charts', 'Tablda\TableChartController@realignCharts');
    });

    //table data
    Route::post('/table-data', 'Tablda\TableDataController@insert');
    Route::put('/table-data', 'Tablda\TableDataController@update');
    Route::put('/table-data/mass', 'Tablda\TableDataController@massUpdate');
    Route::delete('/table-data', 'Tablda\TableDataController@delete');
    Route::prefix('table-data')->group(function () {
        Route::post('/search-on-map', 'Tablda\TableDataController@searchOnMap');
        Route::post('/remove-duplicates', 'Tablda\TableDataController@removeDuplicates');
        Route::post('/batch-uploading', 'Tablda\TableDataController@batchUploading');
        Route::post('/find-replace', 'Tablda\TableDataController@findReplace');
        Route::post('/do-replace', 'Tablda\TableDataController@doReplace');
        Route::post('/info-row', 'Tablda\TableDataController@infoRow');

        Route::post('/get', 'Tablda\TableDataController@get');
        Route::post('/get-map-bounds', 'Tablda\TableDataController@getMapBounds');
        Route::post('/get-map-markers', 'Tablda\TableDataController@getMapMarkers');
        Route::post('/get-dcr-rows', 'Tablda\TableDataController@getDcrRows');
        Route::get('/marker-popup', 'Tablda\TableDataController@getMarkerPopup');
        Route::post('/get-preset', 'Tablda\TableDataController@loadPreset');
        Route::post('/get-headers', 'Tablda\TableDataController@loadHeaders');
        Route::post('/get-tb-fields', 'Tablda\TableDataController@loadJustFields');
        Route::get('/link-src', 'Tablda\TableDataController@loadLinkSrc');
        Route::post('/field/get-rows', 'Tablda\TableDataController@getFieldRows');
        Route::post('/field/get-all-values', 'Tablda\TableDataController@getAllValuesForField');
        Route::get('/available-tables', 'Tablda\TableController@getAvailableTables');
        Route::get('/get-chart-data', 'Tablda\TableDataController@getChartData');
        Route::post('/chart-update-cache', 'Tablda\TableDataController@chartUpdateCache');
        Route::post('/check-row-on-backend', 'Tablda\TableDataController@checkRowOnBackend');
        Route::post('/recalc-all', 'Tablda\TableDataController@formulasTableRecalc');

        Route::get('/field/get-distinctive', 'Tablda\TableDataController@getDistinctiveField');
        Route::post('/delete-selected', 'Tablda\TableDataController@deleteSelected');
        Route::put('/favorite', 'Tablda\TableDataController@favoriteToggleRow');
        Route::put('/favorite/all', 'Tablda\TableDataController@toggleAllFavorites');
        Route::put('/mass-check-box', 'Tablda\TableDataController@toggleMassCheckBoxes');
        Route::post('/mass-copy', 'Tablda\TableDataController@massCopy');

        //reference ddl values
        Route::post('/ddl/get-values', 'Tablda\TableDataController@getDDLvalues');

        //map icons
        Route::get('/field/map-icons', 'Tablda\TableDataController@getMapIcons');
        Route::post('/field/map-icons', 'Tablda\TableDataController@addMapIcon');
        Route::put('/field/map-icons', 'Tablda\TableDataController@updateMapIcon');
        Route::delete('/field/map-icons', 'Tablda\TableDataController@delMapIcon');
    });

    //check pass for table request
    Route::get('/table-data-request/check-pass', 'Tablda\TableDataRequestController@checkPass');
    //table data if filled via table request
    Route::post('/table-request', 'Tablda\TableDataController@requestInsert');
    Route::post('/table-request/row-pass', 'Tablda\TableDataRequestController@requestRowPass');
    Route::put('/table-request', 'Tablda\TableDataController@requestUpdate');
    Route::delete('/table-request', 'Tablda\TableDataController@requestDelete');

    //history data
    Route::get('/history', 'Tablda\HistoryController@get');
    Route::get('/history/all', 'Tablda\HistoryController@getAll');
    Route::post('/history/comment', 'Tablda\HistoryController@postComment');
    Route::delete('/history', 'Tablda\HistoryController@delete');

    //single record views
    Route::get('/srv/row', 'Tablda\SrvController@getSrv');
    Route::post('/srv/row-pass', 'Tablda\SrvController@checkPass');
    Route::post('/srv/bgi-file', 'Tablda\SrvController@insertBgiFile');
    Route::delete('/srv/bgi-file', 'Tablda\SrvController@deleteBgiFile');

    //table views
    Route::post('/table-view', 'Tablda\TableViewController@insert');
    Route::post('/table-view/lock-pass', 'Tablda\TableViewController@lockPass');

    //ddl options
    Route::post('/ddl/add-option', 'Tablda\DDLController@newOption');

    //users
    Route::post('/user/admin-balance', 'Tablda\UserController@setBalance');
    Route::get('/cur-user', 'Tablda\UserController@curUser');
    Route::post('/user/finds', 'Tablda\UserController@findUsersInfo');
    Route::post('/user/get-info', 'Tablda\UserController@getUserOrGroupInfo');
    Route::get('/user/search', 'Tablda\UserController@searchUsers');
    Route::get('/user/search-can-group', 'Tablda\UserController@searchUsersCanGroup');
    Route::get('/user/search-in-groups', 'Tablda\UserController@searchUsersInUserGroups');

    //files
    Route::post('/files', 'Tablda\FileController@insert');
    Route::put('/files', 'Tablda\FileController@update');
    Route::delete('/files', 'Tablda\FileController@delete');

    Route::get('settings/table-fields', 'Tablda\SettingsTableController@getFieldsForTable');
    Route::post('/table/alert/anr_proceed', 'Tablda\TableAlertsController@proceedANR');

    Route::prefix('table')->group(function () {
        Route::prefix('/alert')->group(function () {
            Route::post('/condition', 'Tablda\TableAlertsController@insertCond');
            Route::put('/condition', 'Tablda\TableAlertsController@updateCond');
            Route::delete('/condition', 'Tablda\TableAlertsController@deleteCond');
            Route::post('/anr', 'Tablda\TableAlertsController@insertAnrTable');
            Route::put('/anr', 'Tablda\TableAlertsController@updateAnrTable');
            Route::delete('/anr', 'Tablda\TableAlertsController@deleteAnrTable');
            Route::post('/anr_tmp_to_main', 'Tablda\TableAlertsController@storeAnrChanges');
            Route::prefix('/anr')->group(function () {
                Route::post('/copy', 'Tablda\TableAlertsController@copyAnrFields');
                Route::post('/fld', 'Tablda\TableAlertsController@insertAnrField');
                Route::put('/fld', 'Tablda\TableAlertsController@updateAnrField');
                Route::delete('/fld', 'Tablda\TableAlertsController@deleteAnrField');
            });
            Route::post('/ufv', 'Tablda\TableAlertsController@insertUfvTable');
            Route::put('/ufv', 'Tablda\TableAlertsController@updateUfvTable');
            Route::delete('/ufv', 'Tablda\TableAlertsController@deleteUfvTable');
            Route::prefix('/ufv')->group(function () {
                Route::post('/copy', 'Tablda\TableAlertsController@copyUfvFields');
                Route::post('/fld', 'Tablda\TableAlertsController@insertUfvField');
                Route::put('/fld', 'Tablda\TableAlertsController@updateUfvField');
                Route::delete('/fld', 'Tablda\TableAlertsController@deleteUfvField');
            });
            Route::post('/right', 'Tablda\TableAlertsController@toggleAlertRight');
            Route::delete('/right', 'Tablda\TableAlertsController@delAlertRight');
        });
    });

    Route::group(['middleware' => 'auth'], function () { // AUTH ROUTES ------------------------------------------------
        //static pages
        Route::post('/static-page', 'Tablda\StaticPageController@addPage');
        Route::put('/static-page', 'Tablda\StaticPageController@updatePage');
        Route::delete('/static-page', 'Tablda\StaticPageController@deletePage');
        Route::put('/static-page/move', 'Tablda\StaticPageController@movePage');

        //user's payments
        Route::prefix('payment')->group(function () {
            Route::post('/viaStripeCard', 'Tablda\Applications\PaymentProcessingController@viaStripeCard');
            Route::post('/viaPayPalCard', 'Tablda\Applications\PaymentProcessingController@viaPayPalCard');
        });

        //users
        Route::put('/user', 'Tablda\UserController@updateData');
        Route::prefix('user')->group(function () {
            Route::post('/tos-accepted', 'Tablda\UserController@tosAccepted');
            Route::post('/payAvailCredit', 'Tablda\UserController@payAvailCredit');
            Route::post('/payStripeCard', 'Tablda\UserController@payStripeCard');
            Route::post('/payPayPalCard', 'Tablda\UserController@payPayPalCard');
            Route::post('/payPayPalAccount', 'Tablda\UserController@payPayPalAccount');
            Route::get('/payCompletedPayPalAccount', 'Tablda\UserController@payCompletedPayPalAccount');
            Route::post('/transfer-credits', 'Tablda\UserController@transferCredits');
            Route::post('/link-card', 'Tablda\UserController@linkCard');
            Route::delete('/unlink-card', 'Tablda\UserController@unlinkCard');
            Route::post('/next-subscription', 'Tablda\UserController@nextSubscription');
            Route::get('/invitation', 'Tablda\UserController@reloadInvitations');
            Route::post('/invitation', 'Tablda\UserController@addInvitations');
            Route::put('/invitation', 'Tablda\UserController@updateInvitation');
            Route::delete('/invitation', 'Tablda\UserController@delInvitation');
            Route::post('/send-invitation', 'Tablda\UserController@sendInvitations');
        });

        //table info
        Route::put('/table', 'Tablda\TableController@update');
        Route::delete('/table', 'Tablda\TableController@delete');
        Route::prefix('table')->group(function () {
            Route::get('/views-and-settings', 'Tablda\TableController@getViews');
            Route::post('/statuse', 'Tablda\TableController@saveStatuse');
            Route::post('/link', 'Tablda\TableController@createLink');
            Route::delete('/link', 'Tablda\TableController@deleteLink');
            Route::post('/transfer', 'Tablda\TableController@transfer');
            Route::post('/copy', 'Tablda\TableController@copy');
            Route::post('/copy-embed', 'Tablda\TableController@copyEmbed');
            Route::post('/message', 'Tablda\TableController@addMessage');
            Route::delete('/message', 'Tablda\TableController@deleteMessage');
            Route::put('/move', 'Tablda\TableController@move');
            Route::put('/favorite', 'Tablda\TableController@favorite');
            Route::put('/user-note', 'Tablda\TableController@updateUserNote');
            Route::get('/get-filter-url', 'Tablda\TableController@getFilterUrl');
            Route::post('/backup', 'Tablda\TableBackupsController@insert');
            Route::put('/backup', 'Tablda\TableBackupsController@update');
            Route::delete('/backup', 'Tablda\TableBackupsController@delete');
            Route::post('/alert', 'Tablda\TableAlertsController@insert');
            Route::put('/alert', 'Tablda\TableAlertsController@update');
            Route::delete('/alert', 'Tablda\TableAlertsController@delete');
            Route::prefix('/shared')->group(function () {
                Route::post('/rename', 'Tablda\TableController@renameShared');
            });
            Route::prefix('/chart')->group(function () {
                Route::put('/settings', 'Tablda\TableChartController@saveChartSettings');
                Route::post('/export', 'Tablda\TableChartController@exportChart');
                Route::post('/right', 'Tablda\TableChartController@toggleChartRight');
                Route::delete('/right', 'Tablda\TableChartController@delChartRight');
            });
        });

        //table views
        Route::put('/table-view', 'Tablda\TableViewController@update');
        Route::delete('/table-view', 'Tablda\TableViewController@delete');
        Route::prefix('table-view')->group(function () {
            Route::post('/right', 'Tablda\TableViewController@insertRight');
            Route::delete('/right', 'Tablda\TableViewController@deleteRight');
            Route::post('/filtering', 'Tablda\TableViewController@insertFiltering');
            Route::put('/filtering', 'Tablda\TableViewController@updateFiltering');
            Route::delete('/filtering', 'Tablda\TableViewController@deleteFiltering');
        });

        //table Refers
        Route::post('/refer', 'Tablda\TableReferController@insert');
        Route::put('/refer', 'Tablda\TableReferController@update');
        Route::delete('/refer', 'Tablda\TableReferController@delete');
        Route::prefix('refer')->group(function () {
            Route::post('/corrs', 'Tablda\TableReferController@insertCorrs');
            Route::put('/corrs', 'Tablda\TableReferController@updateCorrs');
            Route::delete('/corrs', 'Tablda\TableReferController@deleteCorrs');
        });

        //table settings
        Route::prefix('settings')->group(function () {
            Route::post('/import-tooltips', 'Tablda\SettingsTableController@importTooltips');
            Route::get('/fees', 'Tablda\SettingsTableController@getFees');
            Route::put('/data', 'Tablda\SettingsTableController@updateSettingsRow');
            Route::put('/mass-data', 'Tablda\SettingsTableController@updateMassSettings');
            Route::put('/show-columns-toggle', 'Tablda\SettingsTableController@showColumnsToggle');
            Route::put('/change-order-column', 'Tablda\SettingsTableController@changeOrderColumn');
            Route::put('/change-row-order', 'Tablda\SettingsTableController@changeRowOrder');
            Route::put('/kanban', 'Tablda\SettingsTableController@updateKanban');
            Route::put('/kanban-column', 'Tablda\SettingsTableController@attachDetachKanbanFld');
            Route::prefix('load')->group(function () {
                Route::get('/cond-formats', 'Tablda\SettingsTableController@loadCondFormats');
            });
            Route::prefix('data')->group(function () {
                Route::post('/just_user_setts', 'Tablda\SettingsTableController@justUserSetts');
                Route::post('/link', 'Tablda\TableFieldLinkController@insertLink');
                Route::put('/link', 'Tablda\TableFieldLinkController@updateLink');
                Route::delete('/link', 'Tablda\TableFieldLinkController@deleteLink');
                Route::prefix('link')->group(function () {
                    Route::post('/param', 'Tablda\TableFieldLinkController@insertLinkParam');
                    Route::put('/param', 'Tablda\TableFieldLinkController@updateLinkParam');
                    Route::delete('/param', 'Tablda\TableFieldLinkController@deleteLinkParam');
                });
                Route::prefix('link')->group(function () {
                    Route::post('/todcr', 'Tablda\TableFieldLinkController@insertLinkToDcr');
                    Route::put('/todcr', 'Tablda\TableFieldLinkController@updateLinkToDcr');
                    Route::delete('/todcr', 'Tablda\TableFieldLinkController@deleteLinkToDcr');
                });
            });
        });

        //table-data-requests
        Route::post('/table-data-request', 'Tablda\TableDataRequestController@insertTableDataRequest');
        Route::put('/table-data-request', 'Tablda\TableDataRequestController@updateTableDataRequest');
        Route::delete('/table-data-request', 'Tablda\TableDataRequestController@deleteTableDataRequest');
        Route::prefix('table-data-request')->group(function () {
            Route::post('/check', 'Tablda\TableDataRequestController@checkPermis');
            Route::post('/copy', 'Tablda\TableDataRequestController@copyPermis');
            Route::put('/default-field', 'Tablda\TableDataRequestController@defaultField');
            Route::post('/column', 'Tablda\TableDataRequestController@updateColumnInTableDataRequest');
            Route::post('/dcr-file', 'Tablda\TableDataRequestController@addDcrFile');
            Route::delete('/dcr-file', 'Tablda\TableDataRequestController@deleteDcrFile');
            Route::post('/linked-table', 'Tablda\TableDataRequestController@insertLinkedTable');
            Route::put('/linked-table', 'Tablda\TableDataRequestController@updateLinkedTable');
            Route::delete('/linked-table', 'Tablda\TableDataRequestController@deleteLinkedTable');
        });

        //------ DATA SETS TAB ------
        //table-permissions
        Route::post('/table-permission', 'Tablda\TablePermissionController@insertTablePermission');
        Route::put('/table-permission', 'Tablda\TablePermissionController@updateTablePermission');
        Route::delete('/table-permission', 'Tablda\TablePermissionController@deleteTablePermission');
        Route::prefix('table-permission')->group(function () {
            Route::post('/copy', 'Tablda\TablePermissionController@copyPermis');
            Route::put('/default-field', 'Tablda\TablePermissionController@defaultField');
            Route::post('/addon-right', 'Tablda\TablePermissionController@insertAddonRight');
            Route::put('/addon-right', 'Tablda\TablePermissionController@updateAddonRight');
            Route::delete('/addon-right', 'Tablda\TablePermissionController@deleteAddonRight');
            Route::post('/column', 'Tablda\TablePermissionController@updateColumnInTablePermission');
            Route::post('/row', 'Tablda\TablePermissionController@updateRowInTablePermission');
            Route::post('/user-group', 'Tablda\TablePermissionController@addUserGroupToTablePermission');
            Route::put('/user-group', 'Tablda\TablePermissionController@updateUserGroupFromTablePermission');
            Route::delete('/user-group', 'Tablda\TablePermissionController@deleteUserGroupFromTablePermission');
            Route::post('/forbid-settings', 'Tablda\TablePermissionController@addForbidSetting');
            Route::delete('/forbid-settings', 'Tablda\TablePermissionController@deleteForbidSetting');
        });

        //table rows groups
        Route::post('/row-group', 'Tablda\TableRowGroupController@insertRowGroup');
        Route::put('/row-group', 'Tablda\TableRowGroupController@updateRowGroup');
        Route::delete('/row-group', 'Tablda\TableRowGroupController@deleteRowGroup');
        Route::prefix('row-group')->group(function () {
            Route::post('/count', 'Tablda\TableRowGroupController@countRowGroup');
            Route::post('/regular-mass', 'Tablda\TableRowGroupController@insertRowGroupRegularMass');
            Route::post('/regular', 'Tablda\TableRowGroupController@insertRowGroupRegular');
            Route::put('/regular', 'Tablda\TableRowGroupController@updateRowGroupRegular');
            Route::delete('/regular', 'Tablda\TableRowGroupController@deleteRowGroupRegular');
        });

        //table columns groups
        Route::post('/column-group', 'Tablda\TableColumnGroupController@insertColGroup');
        Route::put('/column-group', 'Tablda\TableColumnGroupController@updateColGroup');
        Route::delete('/column-group', 'Tablda\TableColumnGroupController@deleteColGroup');
        Route::prefix('column-group')->group(function () {
            Route::post('/field', 'Tablda\TableColumnGroupController@addFieldToColGroup');
            Route::delete('/field', 'Tablda\TableColumnGroupController@deleteFieldFromColGroup');
        });

        //table ref conditions
        Route::post('/ref-condition', 'Tablda\TableRefConditionController@insertRefCondition');
        Route::put('/ref-condition', 'Tablda\TableRefConditionController@updateRefCondition');
        Route::delete('/ref-condition', 'Tablda\TableRefConditionController@deleteRefCondition');
        Route::prefix('ref-condition')->group(function () {
            Route::post('/copy', 'Tablda\TableRefConditionController@copyRefCondition');
            Route::post('/item', 'Tablda\TableRefConditionController@insertRefConditionItem');
            Route::put('/item', 'Tablda\TableRefConditionController@updateRefConditionItem');
            Route::delete('/item', 'Tablda\TableRefConditionController@deleteRefConditionItem');
            Route::post('/incom', 'Tablda\TableRefConditionController@updIncomRef');
        });

        //table columns groups
        Route::post('/cond-format', 'Tablda\TableCondFormatController@insertCondFormat');
        Route::put('/cond-format', 'Tablda\TableCondFormatController@updateCondFormat');
        Route::delete('/cond-format', 'Tablda\TableCondFormatController@deleteCondFormat');
        Route::prefix('cond-format')->group(function () {
            Route::post('/right', 'Tablda\TableCondFormatController@insertCondFormatRight');
            Route::put('/right', 'Tablda\TableCondFormatController@updateCondFormatRight');
            Route::delete('/right', 'Tablda\TableCondFormatController@deleteCondFormatRight');
        });
        //---------------------------

        //USER SETTINGS
        Route::prefix('user')->group(function () {
            Route::put('/set-sel-theme', 'Tablda\UserController@setSelTheme');
            Route::put('/set-ectracttable-terms', 'Tablda\UserController@setEctracttableTerms');
        });

        //User Connections
        Route::post('/user-conn', 'Tablda\UserConnController@insert');
        Route::put('/user-conn', 'Tablda\UserConnController@update');
        Route::delete('/user-conn', 'Tablda\UserConnController@delete');

        //User Api Keys
        Route::post('/user-api-key', 'Tablda\UserConnController@insertApi');
        Route::put('/user-api-key', 'Tablda\UserConnController@updateApi');
        Route::delete('/user-api-key', 'Tablda\UserConnController@deleteApi');

        //User Payment Keys
        Route::post('/user-payment-key', 'Tablda\UserConnController@insertPayment');
        Route::put('/user-payment-key', 'Tablda\UserConnController@updatePayment');
        Route::delete('/user-payment-key', 'Tablda\UserConnController@deletePayment');

        //User Email Accounts
        Route::post('/user-email-acc', 'Tablda\UserConnController@insertEmailAcc');
        Route::put('/user-email-acc', 'Tablda\UserConnController@updateEmailAcc');
        Route::delete('/user-email-acc', 'Tablda\UserConnController@deleteEmailAcc');

        //Addon Email Settings
        Route::post('/addon-email-sett', 'Tablda\TableEmailAddonController@insert');
        Route::put('/addon-email-sett', 'Tablda\TableEmailAddonController@update');
        Route::delete('/addon-email-sett', 'Tablda\TableEmailAddonController@delete');
        Route::post('/addon-email-sett/copy', 'Tablda\TableEmailAddonController@copyAdn');
        Route::post('/addon-email-sett/cancel', 'Tablda\TableEmailAddonController@cancelEmail');
        Route::post('/addon-email-sett/send', 'Tablda\TableEmailAddonController@sendEmail');
        Route::post('/addon-email-sett/preview', 'Tablda\TableEmailAddonController@previewEmail');
        Route::get('/addon-email-sett/status', 'Tablda\TableEmailAddonController@emailStatus');
        Route::delete('/addon-email-sett/history', 'Tablda\TableEmailAddonController@clearHistory');

        //User Clouds
        Route::post('/user-cloud', 'Tablda\UserCloudController@insert');
        Route::put('/user-cloud', 'Tablda\UserCloudController@update');
        Route::delete('/user-cloud', 'Tablda\UserCloudController@delete');
        Route::delete('/user-cloud/set-inactive', 'Tablda\UserCloudController@setInactive');

        //User Groups
        Route::post('/user-group', 'Tablda\UserGroupController@insertUserGroup');
        Route::put('/user-group', 'Tablda\UserGroupController@updateUserGroup');
        Route::delete('/user-group', 'Tablda\UserGroupController@deleteUserGroup');
        Route::prefix('user-group')->group(function () {
            Route::post('/reload', 'Tablda\UserGroupController@reloadUserGroups');
            Route::post('/user', 'Tablda\UserGroupController@addUserToUserGroup');
            Route::put('/user', 'Tablda\UserGroupController@updateUserInUserGroup');
            Route::delete('/user', 'Tablda\UserGroupController@deleteUserFromUserGroup');
            Route::post('/condition', 'Tablda\UserGroupController@insertUserGroupCondition');
            Route::put('/condition', 'Tablda\UserGroupController@updateUserGroupCondition');
            Route::delete('/condition', 'Tablda\UserGroupController@deleteUserGroupCondition');
            Route::post('/cond-to-user', 'Tablda\UserGroupController@changeCondToUsers');
        });

        //DDL
        Route::post('/ddl', 'Tablda\DDLController@insertDDL');
        Route::put('/ddl', 'Tablda\DDLController@updateDDL');
        Route::delete('/ddl', 'Tablda\DDLController@deleteDDL');
        Route::prefix('ddl')->group(function () {
            Route::post('/fill-from-field', 'Tablda\DDLController@fillFromField');
            Route::post('/parse-options', 'Tablda\DDLController@parseOptions');
            Route::post('/copy-from-table', 'Tablda\DDLController@copyDDLfromTable');
            Route::post('/item', 'Tablda\DDLController@insertDDLItem');
            Route::put('/item', 'Tablda\DDLController@updateDDLItem');
            Route::delete('/item', 'Tablda\DDLController@deleteDDLItem');
            Route::post('/reference', 'Tablda\DDLController@insertDDLReference');
            Route::put('/reference', 'Tablda\DDLController@updateDDLReference');
            Route::delete('/reference', 'Tablda\DDLController@deleteDDLReference');
            Route::prefix('reference')->group(function () {
                Route::post('/color', 'Tablda\DDLController@addDDLReferenceColor');
                Route::put('/color', 'Tablda\DDLController@updateDDLReferenceColor');
                Route::delete('/color', 'Tablda\DDLController@deleteDDLReferenceColor');
                Route::prefix('color')->group(function () {
                    Route::post('/create-and-load', 'Tablda\DDLController@createAndLoadRefColors');
                });
            });
        });

        //folders
        Route::get('/folder', 'Tablda\FolderController@getSettings');
        Route::post('/folder', 'Tablda\FolderController@insert');
        Route::put('/folder', 'Tablda\FolderController@update');
        Route::delete('/folder', 'Tablda\FolderController@delete');
        Route::prefix('folder')->group(function () {
            Route::post('/link', 'Tablda\FolderController@createLink');
            //folder-permissions
            Route::post('/copy', 'Tablda\FolderController@copyTo');
            Route::prefix('permission')->group(function () {
                //checked tables for folder-permissions
                Route::post('/tables', 'Tablda\FolderPermissionController@setFolderPermissions');
            });
            //---
            //folder-views
            Route::post('/view', 'Tablda\FolderViewController@insertFolderView');
            Route::put('/view', 'Tablda\FolderViewController@updateFolderView');
            Route::delete('/view', 'Tablda\FolderViewController@deleteFolderView');
            Route::prefix('view')->group(function () {
                //checked tables for folder-views
                Route::get('/checked-table', 'Tablda\FolderViewController@getFolderViewTable');
                Route::put('/checked-table', 'Tablda\FolderViewController@setFolderViewTable');
                Route::post('/tables', 'Tablda\FolderViewController@setFolderViews');
            });
            //---
            Route::post('/transfer', 'Tablda\FolderController@transfer');
            Route::put('/move', 'Tablda\FolderController@move');
            Route::put('/favorite', 'Tablda\FolderController@favorite');

            Route::post('/icon', 'Tablda\FolderController@addIcon');
            Route::delete('/icon', 'Tablda\FolderController@delIcon');
        });

        //table import module
        Route::prefix('import')->group(function () {
            Route::post('/presave-column', 'Tablda\ImportController@presaveColumn');
            Route::post('/create-table', 'Tablda\ImportController@createTable');
            Route::post('/modify-table', 'Tablda\ImportController@modifyTable');
            Route::delete('/delete-table', 'Tablda\ImportController@deleteTable');

            Route::post('/get-fields/csv', 'Tablda\ImportController@getFieldsFromCSV');
            Route::post('/get-fields/mysql', 'Tablda\ImportController@getFieldsFromMySQL');
            Route::post('/get-fields/paste', 'Tablda\ImportController@getFieldsFromPaste');
            Route::post('/get-fields/g-sheet', 'Tablda\ImportController@getFieldsFromGSheet');
            Route::post('/get-fields/web-scrap', 'Tablda\ImportController@getScrapWeb');
            Route::post('/get-fields/airtable', 'Tablda\ImportController@getAirtable');

            Route::post('/airtable/col-values', 'Tablda\ImportController@getAirtableColValues');

            Route::post('/ocr/check-key', 'Tablda\ImportController@ocrCheckKey');
            Route::post('/ocr/parse-image', 'Tablda\ImportController@ocrParseImage');

            Route::post('/google-drive/all-files', 'Tablda\ImportController@allGoogleFiles');
            Route::post('/google-drive/sheets-for-table', 'Tablda\ImportController@sheetsForGoogleTable');
            Route::post('/google-drive/store-file', 'Tablda\ImportController@storeGoogleFile');

            Route::post('/dropbox/all-files', 'Tablda\ImportController@allDropboxFiles');
            Route::post('/dropbox/store-file', 'Tablda\ImportController@storeDropboxFile');

            Route::post('/one-drive/all-files', 'Tablda\ImportController@allOneDriveFiles');
            Route::post('/one-drive/store-file', 'Tablda\ImportController@storeOneDriveFile');

            Route::post('/xls-sheets', 'Tablda\ImportController@getXlsSheets');
            Route::get('/status', 'Tablda\ImportController@getImportStatus');
            Route::get('/remote-dbs', 'Tablda\ImportController@getRemoteDBS');
            Route::get('/remote-tables', 'Tablda\ImportController@getRemoteTables');
            Route::post('/direct-call', 'Tablda\ImportController@directImportData');
        });

        //plans module
        Route::post('/plan', 'Tablda\TablePlansController@addPlan');
        Route::put('/plan', 'Tablda\TablePlansController@updatePlan');
        Route::post('/addon', 'Tablda\TablePlansController@addAddon');
        Route::put('/addon', 'Tablda\TablePlansController@updateAddon');
        Route::put('/function', 'Tablda\TablePlansController@updateFunction');

        //Downloader
        Route::post('/download', 'Tablda\DownloadController@download')->name('downloader');
        Route::post('/download/chart', 'Tablda\DownloadController@downloadChart')->name('dwn_chart');

        //App paths
        Route::put('/app/settings', 'Tablda\App\AppSettingsController@updateAppSett');
        Route::put('/app/theme', 'Tablda\App\AppThemeController@updateTheme');
    });


    /////APPLICATIONS
    //Stim Wid
    Route::post('/wid_view', 'Tablda\Applications\StimWidController@insertAppView');
    Route::put('/wid_view', 'Tablda\Applications\StimWidController@updateAppView');
    Route::delete('/wid_view', 'Tablda\Applications\StimWidController@deleteAppView');
    Route::prefix('wid_view')->group(function () {
        Route::post('/state', 'Tablda\Applications\StimWidController@stateAppView');
        Route::post('/feedback', 'Tablda\Applications\StimWidController@insertAppViewFeedback');
        Route::put('/feedback', 'Tablda\Applications\StimWidController@updateAppViewFeedback');
        Route::delete('/feedback', 'Tablda\Applications\StimWidController@deleteAppViewFeedback');
        Route::prefix('feedback')->group(function () {
            Route::post('/check-pass', 'Tablda\Applications\StimWidController@checkAppViewFeedbackPass');
            Route::post('/email', 'Tablda\Applications\StimWidController@sendFeedbackEmail');
            Route::post('/result', 'Tablda\Applications\StimWidController@insertAppViewFeedbackResult');
            Route::put('/result', 'Tablda\Applications\StimWidController@updateAppViewFeedbackResult');
            Route::delete('/result', 'Tablda\Applications\StimWidController@deleteAppViewFeedbackResult');
        });
    });
});

