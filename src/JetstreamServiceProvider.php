<?php

namespace Laravel\Jetstream;

use App\Http\Middleware\HandleInertiaRequests;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Illuminate\View\Compilers\BladeCompiler;
use Inertia\Inertia;
use Laravel\Fortify\Fortify;
use Laravel\Jetstream\Http\Livewire\ApiTokenManager;
use Laravel\Jetstream\Http\Livewire\CreateAccountForm;
use Laravel\Jetstream\Http\Livewire\DeleteAccountForm;
use Laravel\Jetstream\Http\Livewire\DeleteUserForm;
use Laravel\Jetstream\Http\Livewire\LogoutOtherBrowserSessionsForm;
use Laravel\Jetstream\Http\Livewire\NavigationMenu;
use Laravel\Jetstream\Http\Livewire\AccountMemberManager;
use Laravel\Jetstream\Http\Livewire\TwoFactorAuthenticationForm;
use Laravel\Jetstream\Http\Livewire\UpdatePasswordForm;
use Laravel\Jetstream\Http\Livewire\UpdateProfileInformationForm;
use Laravel\Jetstream\Http\Livewire\UpdateAccountNameForm;
use Laravel\Jetstream\Http\Middleware\ShareInertiaData;
use Livewire\Livewire;

class JetstreamServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/jetstream.php', 'jetstream');
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'jetstream');

        Fortify::viewPrefix('auth.');

        $this->configureComponents();
        $this->configurePublishing();
        $this->configureRoutes();
        $this->configureCommands();

        RedirectResponse::macro('banner', function ($message) {
            return $this->with('flash', [
                'bannerStyle' => 'success',
                'banner' => $message,
            ]);
        });

        RedirectResponse::macro('dangerBanner', function ($message) {
            return $this->with('flash', [
                'bannerStyle' => 'danger',
                'banner' => $message,
            ]);
        });

        if (config('jetstream.stack') === 'inertia') {
            $this->bootInertia();
        }

        if (config('jetstream.stack') === 'livewire' && class_exists(Livewire::class)) {
            Livewire::component('navigation-menu', NavigationMenu::class);
            Livewire::component('profile.update-profile-information-form', UpdateProfileInformationForm::class);
            Livewire::component('profile.update-password-form', UpdatePasswordForm::class);
            Livewire::component('profile.two-factor-authentication-form', TwoFactorAuthenticationForm::class);
            Livewire::component('profile.logout-other-browser-sessions-form', LogoutOtherBrowserSessionsForm::class);
            Livewire::component('profile.delete-user-form', DeleteUserForm::class);

            if (Features::hasApiFeatures()) {
                Livewire::component('api.api-token-manager', ApiTokenManager::class);
            }

            if (Features::hasAccountFeatures()) {
                Livewire::component('accounts.create-account-form', CreateAccountForm::class);
                Livewire::component('accounts.update-account-name-form', UpdateAccountNameForm::class);
                Livewire::component('accounts.account-member-manager', AccountMemberManager::class);
                Livewire::component('accounts.delete-account-form', DeleteAccountForm::class);
            }
        }
    }

    /**
     * Configure the Jetstream Blade components.
     *
     * @return void
     */
    protected function configureComponents()
    {
        $this->callAfterResolving(BladeCompiler::class, function () {
            $this->registerComponent('action-message');
            $this->registerComponent('action-section');
            $this->registerComponent('application-logo');
            $this->registerComponent('application-mark');
            $this->registerComponent('authentication-card');
            $this->registerComponent('authentication-card-logo');
            $this->registerComponent('banner');
            $this->registerComponent('button');
            $this->registerComponent('confirmation-modal');
            $this->registerComponent('confirms-password');
            $this->registerComponent('danger-button');
            $this->registerComponent('dialog-modal');
            $this->registerComponent('dropdown');
            $this->registerComponent('dropdown-link');
            $this->registerComponent('form-section');
            $this->registerComponent('input');
            $this->registerComponent('checkbox');
            $this->registerComponent('input-error');
            $this->registerComponent('label');
            $this->registerComponent('modal');
            $this->registerComponent('nav-link');
            $this->registerComponent('responsive-nav-link');
            $this->registerComponent('responsive-switchable-account');
            $this->registerComponent('secondary-button');
            $this->registerComponent('section-border');
            $this->registerComponent('section-title');
            $this->registerComponent('switchable-account');
            $this->registerComponent('validation-errors');
            $this->registerComponent('welcome');
        });
    }

    /**
     * Register the given component.
     *
     * @param  string  $component
     * @return void
     */
    protected function registerComponent(string $component)
    {
        Blade::component('jetstream::components.'.$component, 'jet-'.$component);
    }

    /**
     * Configure publishing for the package.
     *
     * @return void
     */
    protected function configurePublishing()
    {
        if (! $this->app->runningInConsole()) {
            return;
        }

        $this->publishes([
            __DIR__.'/../config/jetstream.php' => config_path('jetstream.php'),
        ], 'jetstream-config');

        $this->publishes([
            __DIR__.'/../resources/views' => resource_path('views/vendor/jetstream'),
        ], 'jetstream-views');

        $this->publishes([
            __DIR__.'/../database/migrations/2014_10_12_000000_create_users_table.php' => database_path('migrations/2014_10_12_000000_create_users_table.php'),
        ], 'jetstream-migrations');

        $this->publishes([
            __DIR__.'/../database/migrations/2020_05_21_100000_create_accounts_table.php' => database_path('migrations/2020_05_21_100000_create_accounts_table.php'),
            __DIR__.'/../database/migrations/2020_05_21_200000_create_account_user_table.php' => database_path('migrations/2020_05_21_200000_create_account_user_table.php'),
            __DIR__.'/../database/migrations/2020_05_21_300000_create_account_invitations_table.php' => database_path('migrations/2020_05_21_300000_create_account_invitations_table.php'),
        ], 'jetstream-account-migrations');

        $this->publishes([
            __DIR__.'/../routes/'.config('jetstream.stack').'.php' => base_path('routes/jetstream.php'),
        ], 'jetstream-routes');

        $this->publishes([
            __DIR__.'/../stubs/inertia/resources/js/Pages/Auth' => resource_path('js/Pages/Auth'),
            __DIR__.'/../stubs/inertia/resources/js/Jetstream/AuthenticationCard.vue' => resource_path('js/Jetstream/AuthenticationCard.vue'),
            __DIR__.'/../stubs/inertia/resources/js/Jetstream/AuthenticationCardLogo.vue' => resource_path('js/Jetstream/AuthenticationCardLogo.vue'),
            __DIR__.'/../stubs/inertia/resources/js/Jetstream/Checkbox.vue' => resource_path('js/Jetstream/Checkbox.vue'),
            __DIR__.'/../stubs/inertia/resources/js/Jetstream/ValidationErrors.vue' => resource_path('js/Jetstream/ValidationErrors.vue'),
        ], 'jetstream-inertia-auth-pages');
    }

    /**
     * Configure the routes offered by the application.
     *
     * @return void
     */
    protected function configureRoutes()
    {
        if (Jetstream::$registersRoutes) {
            Route::group([
                'namespace' => 'Laravel\Jetstream\Http\Controllers',
                'domain' => config('jetstream.domain', null),
                'prefix' => config('jetstream.prefix', config('jetstream.path')),
            ], function () {
                $this->loadRoutesFrom(__DIR__.'/../routes/'.config('jetstream.stack').'.php');
            });
        }
    }

    /**
     * Configure the commands offered by the application.
     *
     * @return void
     */
    protected function configureCommands()
    {
        if (! $this->app->runningInConsole()) {
            return;
        }

        $this->commands([
            Console\InstallCommand::class,
        ]);
    }

    /**
     * Boot any Inertia related services.
     *
     * @return void
     */
    protected function bootInertia()
    {
        $kernel = $this->app->make(Kernel::class);

        $kernel->appendMiddlewareToGroup('web', ShareInertiaData::class);
        $kernel->appendToMiddlewarePriority(ShareInertiaData::class);

        if (class_exists(HandleInertiaRequests::class)) {
            $kernel->appendToMiddlewarePriority(HandleInertiaRequests::class);
        }

        Fortify::loginView(function () {
            return Inertia::render('Auth/Login', [
                'canResetPassword' => Route::has('password.request'),
                'status' => session('status'),
            ]);
        });

        Fortify::requestPasswordResetLinkView(function () {
            return Inertia::render('Auth/ForgotPassword', [
                'status' => session('status'),
            ]);
        });

        Fortify::resetPasswordView(function (Request $request) {
            return Inertia::render('Auth/ResetPassword', [
                'email' => $request->input('email'),
                'token' => $request->route('token'),
            ]);
        });

        Fortify::registerView(function () {
            return Inertia::render('Auth/Register');
        });

        Fortify::verifyEmailView(function () {
            return Inertia::render('Auth/VerifyEmail', [
                'status' => session('status'),
            ]);
        });

        Fortify::twoFactorChallengeView(function () {
            return Inertia::render('Auth/TwoFactorChallenge');
        });

        Fortify::confirmPasswordView(function () {
            return Inertia::render('Auth/ConfirmPassword');
        });
    }
}
