<?php


namespace Vanguard\Services\Tablda;


use Proengsoft\JsValidation\JsValidatorFactory;
use Ramsey\Uuid\Uuid;
use Vanguard\Classes\TabldaEncrypter;
use Vanguard\Models\AppSetting;
use Vanguard\Models\AppTheme;
use Vanguard\Models\Table\Table;
use Vanguard\Models\Table\TableView;
use Vanguard\Repositories\Tablda\Permissions\UserGroupRepository;
use Vanguard\Repositories\Tablda\TableViewRepository;
use Vanguard\Repositories\Tablda\UserConnRepository;
use Vanguard\User;

class BladeVariablesService
{
    private $tableService;
    private $userService;
    private $service;

    public $is_app_route = 0;

    protected $table;
    protected $table_google_api = '';
    protected $named_user = '';

    /**
     * BladeVariablesService constructor.
     */
    public function __construct()
    {
        $this->tableService = new TableService();
        $this->userService = new UserService();
        $this->service = new HelperService();
    }

    /**
     * @param Table $table
     * @param User|null $named_user
     * @return $this
     */
    public function setTableObj(Table $table, User $named_user = null)
    {
        //$this->table = $table;
        $this->table_google_api = HelperService::getTableGoogleApi($table);
        $this->named_user = $named_user;
        return $this;
    }

    /**
     * @param User $user
     */
    public function setUserTheme(User $user): void
    {
        $sel_th = AppTheme::where('id', '=', $user->app_theme_id)->first();
        $user->_selected_theme = $sel_th ?: AppTheme::where('obj_type', '=', 'system')->first();
    }

    /**
     * Get variables for blade.
     *
     * @param TableView|null $view
     * @param int $force_guest
     * @param int $lightweight
     * @return array
     */
    public function getVariables(TableView $view = null, int $force_guest = 0, int $lightweight = 0)
    {
        $user = auth()->check() ? auth()->user() : new User();
        $named_user = $this->named_user ?: $user;

        //Load all needed Relations
        $user = $this->getUserLoads($user, $force_guest);

        //Set View parameters
        if ($view) {
            $view->load('_filtering');
        }
        $user->see_view = !!$view;
        $user->view_hash = $view ? $view->hash : '';
        $user->view_all = $view;
        $user->is_force_guested = $force_guest || !!$view;

        $b_owner_or_collab =
            $view
            &&
            TableView::where('id', $view->id)
                ->where(function ($v) use ($user) {
                    $v->orWhere('user_id', $user->id);//or is owner
                    $v->orWhereHas('_table_permissions', function ($tp) {
                        $tp->isActiveForUserOrVisitor();//or is collaborator
                    });
                })
                ->first();

        $user->view_locked = $view && $view->is_locked && !$b_owner_or_collab;
        $user->tables_count = $user->id ? auth()->user()->_tables()->count() : 0;

        //for 'auto-reload page with new scripts' module
        $user->_vendor_script = mix('assets/js/tablda/vendor.js')->toHtml();
        $user->_app_script = mix('assets/js/tablda/app.js')->toHtml();
        $user->_random_hash = Uuid::uuid4();
        $user->_uc_sys_visiting_url = (new TableViewRepository())->getVisitingUrl(55);
        $user->_usr_email_domain = HelperService::usrEmailDomain();

        $result_url = $this->service->getUsersUrl($user, $this->service->cur_subdomain);

        //decrypt
        $this->decryptRelations($user);

        //Ignore DCR ($force_guest)
        $usr_gmap = $named_user ? $named_user->_google_api_keys()->where('is_active','=',1)->first() : '';
        $user->__active_gmap = $usr_gmap ? TabldaEncrypter::decrypt($usr_gmap->key ?? '') : '';

        if ($user->__active_gmap) {
            $gmap_api_key = $user->__active_gmap;
        } else {
            $admin = User::where('role_id', 1)->first();
            $admin_gmap = $admin ? $admin->_google_api_keys->where('is_active','=',1)->first() : null;
            $gmap_api_key = $admin_gmap ? $admin_gmap->key : '';//env('GOOGLE_API_KEY');
        }

        $user->__google_table_api = $this->table_google_api ?: $gmap_api_key;

        return [
            'meta_app_settings' => AppSetting::all()->keyBy('key'),
            'socialProviders' => config('auth.social.providers'),
            'user' => $user,
            'app_url' => $result_url,
            'clear_url' => config('app.url'),
            'app_domain' => config('app.domain'),
            'stripe_key' => config('app.stripe_public'),
            'discourse_start_login' => config('app.discourse_url_login'),
            'paypal_client' => env('PAYPAL_CLIENT_ID'),
            'embed' => $force_guest,
            'tablePermission' => $view ? $view->hash : '',
            'vueJsValidator' => new JsValidatorFactory(app(), ['view' => 'tablda.vue_js_validation.bootstrap']),
            'route_group' => null,
            'lightweight' => $lightweight,
            'is_app_route' => $this->is_app_route,
            'vue_labels' => __('vue'),
            'gmap_api_key' => $user->__google_table_api,
        ];
    }

    /**
     * @param User $user
     * @param int $force_guest
     * @return User
     * @throws \Exception
     */
    protected function getUserLoads(User $user, int $force_guest = 0)
    {
        if (!$force_guest && auth()->id()) {
            if (!$user->personal_hash) {
                $user->personal_hash = Uuid::uuid4();
                $user->save();
            }

            $user->is_admin = $user->isAdmin();
            $this->userService->checkAndSetPlan($user);
            $this->userService->defaultTheme($user);

            $user->load([
                '_available_features',
                '_subscription' => function ($s) {
                    $s->with(['_addons', '_plan']);
                },
                '_next_subscription' => function ($s) {
                    $s->with(['_addons', '_plan']);
                },
                '_invitations',
                '_cards',
                '_subscribed_apps',
                '_google_api_keys',
                '_sendgrid_api_keys',
                '_airtable_api_keys',
                '_extracttable_api_keys',
                '_stripe_payment_keys',
                '_paypal_payment_keys',
                '_google_email_accs',
            ]);
            (new UserGroupRepository())->loadUserGroups($user);

            $user->_ava_themes = AppTheme::where('obj_type', 'system')
                ->get()
                ->merge( $user->_themes()->get() );

            $this->setUserTheme($user);

            //User's SubIcon is related to subdomain
            $user->sub_icon = $this->service->subiconOnSubDomain() ?: $user->sub_icon;
        } else {
            $user->_pre_id = $user->id;
            $user->id = 0;
            $user->is_admin = false;
            $user->subdomain = '';
            $user->_subscription = '{}';
            $user->_user_groups = [];
            $user->_subscribed_apps = [];
            $user->_google_api_keys = collect([]);
            $user->_sendgrid_api_keys = collect([]);
            $user->_airtable_api_keys = collect([]);
            $user->_extracttable_api_keys = collect([]);
            $user->_stripe_payment_keys = collect([]);
            $user->_paypal_payment_keys = collect([]);
            $user->_google_email_accs = collect([]);
            $user->_available_features = '{}';
            $user->sub_icon = $this->service->subiconOnSubDomain() ?: null;
        }
        return $user;
    }

    /**
     * @param User $user
     */
    protected function decryptRelations(User $user)
    {
        foreach ($user->_google_api_keys as $api) {
            $api->key = TabldaEncrypter::decrypt($api->key ?? '');
        }
        foreach ($user->_sendgrid_api_keys as $api) {
            $api->key = TabldaEncrypter::decrypt($api->key ?? '');
        }
        foreach ($user->_extracttable_api_keys as $api) {
            $api->key = TabldaEncrypter::decrypt($api->key ?? '');
        }
        foreach ($user->_airtable_api_keys as $api) {
            $api->key = TabldaEncrypter::decrypt($api->key ?? '');
        }
        foreach ($user->_google_email_accs as $api) {
            $api->app_pass = TabldaEncrypter::decrypt($api->app_pass ?? '');
        }
        $uRepo = new UserConnRepository();
        foreach ($user->_stripe_payment_keys as $paykey) {
            $paykey = $uRepo->userPaymentDecrypt($paykey);
        }
        foreach ($user->_paypal_payment_keys as $paykey) {
            $paykey = $uRepo->userPaymentDecrypt($paykey);
        }
    }
}