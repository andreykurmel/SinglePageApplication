<?php

namespace Vanguard\Providers;

use Illuminate\Contracts\Auth\Access\Gate as GateContract;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Vanguard\Models\AppTheme;
use Vanguard\Models\DataSetPermissions\CondFormat;
use Vanguard\Models\Pages\Pages;
use Vanguard\Models\StaticPage;
use Vanguard\Models\Table\TableAlert;
use Vanguard\Models\User\UserGroup;
use Vanguard\Models\Folder\Folder;
use Vanguard\Models\Table\TableData;
use Vanguard\Models\Table\TableView;
use Vanguard\Policies\AppThemePolicy;
use Vanguard\Policies\CondFormatPolicy;
use Vanguard\Policies\FolderPolicy;
use Vanguard\Policies\PagesPolicy;
use Vanguard\Policies\StaticPagePolicy;
use Vanguard\Policies\TableAlertPolicy;
use Vanguard\Policies\TableDataPolicy;
use Vanguard\Policies\TableViewPolicy;
use Vanguard\Policies\UserGroupPolicy;
use Vanguard\User;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        TableData::class => TableDataPolicy::class,
        TableView::class => TableViewPolicy::class,
        TableAlert::class => TableAlertPolicy::class,
        Folder::class => FolderPolicy::class,
        UserGroup::class => UserGroupPolicy::class,
        CondFormat::class => CondFormatPolicy::class,
        StaticPage::class => StaticPagePolicy::class,
        AppTheme::class => AppThemePolicy::class,
        Pages::class => PagesPolicy::class,
    ];

    /**
     * Register any application authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        \Blade::directive('role', function ($expression) {
            return "<?php if (\\Auth::user()->hasRole({$expression})) : ?>";
        });

        \Blade::directive('endrole', function ($expression) {
            return "<?php endif; ?>";
        });

        \Blade::directive('permission', function ($expression) {
            return "<?php if (\\Auth::user()->hasPermission({$expression})) : ?>";
        });
        
        \Blade::directive('endpermission', function ($expression) {
            return "<?php endif; ?>";
        });

        \Gate::define('manage-session', function (User $user, $session) {
            if ($user->hasPermission('users.manage')) {
                return true;
            }

            return (int) $user->id === (int) $session->user_id;
        });
    }
}
