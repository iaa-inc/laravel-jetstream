<?php

namespace Laravel\Jetstream\Console;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;
use Symfony\Component\Process\Process;

class InstallCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'jetstream:install {stack : The development stack that should be installed}
                                              {--accounts : Indicates if account support should be installed}
                                              {--composer=global : Absolute path to the Composer binary which should be used to install packages}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Install the Jetstream components and resources';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        // Publish...
        $this->callSilent('vendor:publish', ['--tag' => 'jetstream-config', '--force' => true]);
        $this->callSilent('vendor:publish', ['--tag' => 'jetstream-migrations', '--force' => true]);

        $this->callSilent('vendor:publish', ['--tag' => 'fortify-config', '--force' => true]);
        $this->callSilent('vendor:publish', ['--tag' => 'fortify-support', '--force' => true]);
        $this->callSilent('vendor:publish', ['--tag' => 'fortify-migrations', '--force' => true]);

        // "Home" Route...
        $this->replaceInFile('/home', '/dashboard', app_path('Providers/RouteServiceProvider.php'));

        if (file_exists(resource_path('views/welcome.blade.php'))) {
            $this->replaceInFile('/home', '/dashboard', resource_path('views/welcome.blade.php'));
            $this->replaceInFile('Home', 'Dashboard', resource_path('views/welcome.blade.php'));
        }

        // Fortify Provider...
        $this->installServiceProviderAfter('RouteServiceProvider', 'FortifyServiceProvider');

        // Configure Session...
        $this->configureSession();

        // AuthenticateSession Middleware...
        $this->replaceInFile(
            '// \Illuminate\Session\Middleware\AuthenticateSession::class',
            '\Laravel\Jetstream\Http\Middleware\AuthenticateSession::class',
            app_path('Http/Kernel.php')
        );

        // Install Stack...
        if ($this->argument('stack') === 'livewire') {
            $this->installLivewireStack();
        } elseif ($this->argument('stack') === 'inertia') {
            $this->installInertiaStack();
        }

        // Tests...
        copy(__DIR__.'/../../stubs/tests/AuthenticationTest.php', base_path('tests/Feature/AuthenticationTest.php'));
        copy(__DIR__.'/../../stubs/tests/EmailVerificationTest.php', base_path('tests/Feature/EmailVerificationTest.php'));
        copy(__DIR__.'/../../stubs/tests/PasswordConfirmationTest.php', base_path('tests/Feature/PasswordConfirmationTest.php'));
        copy(__DIR__.'/../../stubs/tests/PasswordResetTest.php', base_path('tests/Feature/PasswordResetTest.php'));
        copy(__DIR__.'/../../stubs/tests/RegistrationTest.php', base_path('tests/Feature/RegistrationTest.php'));
    }

    /**
     * Configure the session driver for Jetstream.
     *
     * @return void
     */
    protected function configureSession()
    {
        if (! class_exists('CreateSessionsTable')) {
            try {
                $this->call('session:table');
            } catch (Exception $e) {
                //
            }
        }

        $this->replaceInFile("'SESSION_DRIVER', 'file'", "'SESSION_DRIVER', 'database'", config_path('session.php'));
        $this->replaceInFile('SESSION_DRIVER=file', 'SESSION_DRIVER=database', base_path('.env'));
        $this->replaceInFile('SESSION_DRIVER=file', 'SESSION_DRIVER=database', base_path('.env.example'));
    }

    /**
     * Install the Livewire stack into the application.
     *
     * @return void
     */
    protected function installLivewireStack()
    {
        // Install Livewire...
        $this->requireComposerPackages('livewire/livewire:^2.0', 'laravel/sanctum:^2.6');

        // Sanctum...
        (new Process(['php', 'artisan', 'vendor:publish', '--provider=Laravel\Sanctum\SanctumServiceProvider', '--force'], base_path()))
                ->setTimeout(null)
                ->run(function ($type, $output) {
                    $this->output->write($output);
                });

        // Update Configuration...
        $this->replaceInFile('inertia', 'livewire', config_path('jetstream.php'));
        // $this->replaceInFile("'guard' => 'web'", "'guard' => 'sanctum'", config_path('auth.php'));

        // NPM Packages...
        $this->updateNodePackages(function ($packages) {
            return [
                '@tailwindcss/forms' => '^0.2.1',
                '@tailwindcss/typography' => '^0.3.0',
                'alpinejs' => '^2.7.3',
                'postcss-import' => '^12.0.1',
                'tailwindcss' => '^2.0.1',
            ] + $packages;
        });

        // Tailwind Configuration...
        copy(__DIR__.'/../../stubs/livewire/tailwind.config.js', base_path('tailwind.config.js'));
        copy(__DIR__.'/../../stubs/livewire/webpack.mix.js', base_path('webpack.mix.js'));

        // Directories...
        (new Filesystem)->ensureDirectoryExists(app_path('Actions/Fortify'));
        (new Filesystem)->ensureDirectoryExists(app_path('Actions/Jetstream'));
        (new Filesystem)->ensureDirectoryExists(app_path('View/Components'));
        (new Filesystem)->ensureDirectoryExists(public_path('css'));
        (new Filesystem)->ensureDirectoryExists(resource_path('css'));
        (new Filesystem)->ensureDirectoryExists(resource_path('markdown'));
        (new Filesystem)->ensureDirectoryExists(resource_path('views/api'));
        (new Filesystem)->ensureDirectoryExists(resource_path('views/auth'));
        (new Filesystem)->ensureDirectoryExists(resource_path('views/layouts'));
        (new Filesystem)->ensureDirectoryExists(resource_path('views/profile'));

        (new Filesystem)->deleteDirectory(resource_path('sass'));

        // Terms Of Service / Privacy Policy...
        copy(__DIR__.'/../../stubs/resources/markdown/terms.md', resource_path('markdown/terms.md'));
        copy(__DIR__.'/../../stubs/resources/markdown/policy.md', resource_path('markdown/policy.md'));

        // Service Providers...
        copy(__DIR__.'/../../stubs/app/Providers/JetstreamServiceProvider.php', app_path('Providers/JetstreamServiceProvider.php'));
        $this->installServiceProviderAfter('FortifyServiceProvider', 'JetstreamServiceProvider');

        // Models...
        copy(__DIR__.'/../../stubs/app/Models/User.php', app_path('Models/User.php'));

        // Actions...
        copy(__DIR__.'/../../stubs/app/Actions/Fortify/CreateNewUser.php', app_path('Actions/Fortify/CreateNewUser.php'));
        copy(__DIR__.'/../../stubs/app/Actions/Fortify/UpdateUserProfileInformation.php', app_path('Actions/Fortify/UpdateUserProfileInformation.php'));
        copy(__DIR__.'/../../stubs/app/Actions/Jetstream/DeleteUser.php', app_path('Actions/Jetstream/DeleteUser.php'));

        // View Components...
        copy(__DIR__.'/../../stubs/livewire/app/View/Components/AppLayout.php', app_path('View/Components/AppLayout.php'));
        copy(__DIR__.'/../../stubs/livewire/app/View/Components/GuestLayout.php', app_path('View/Components/GuestLayout.php'));

        // Layouts...
        (new Filesystem)->copyDirectory(__DIR__.'/../../stubs/livewire/resources/views/layouts', resource_path('views/layouts'));

        // Single Blade Views...
        copy(__DIR__.'/../../stubs/livewire/resources/views/dashboard.blade.php', resource_path('views/dashboard.blade.php'));
        copy(__DIR__.'/../../stubs/livewire/resources/views/navigation-menu.blade.php', resource_path('views/navigation-menu.blade.php'));
        copy(__DIR__.'/../../stubs/livewire/resources/views/terms.blade.php', resource_path('views/terms.blade.php'));
        copy(__DIR__.'/../../stubs/livewire/resources/views/policy.blade.php', resource_path('views/policy.blade.php'));

        // Other Views...
        (new Filesystem)->copyDirectory(__DIR__.'/../../stubs/livewire/resources/views/api', resource_path('views/api'));
        (new Filesystem)->copyDirectory(__DIR__.'/../../stubs/livewire/resources/views/profile', resource_path('views/profile'));
        (new Filesystem)->copyDirectory(__DIR__.'/../../stubs/livewire/resources/views/auth', resource_path('views/auth'));

        // Routes...
        $this->replaceInFile('auth:api', 'auth:sanctum', base_path('routes/api.php'));

        if (! Str::contains(file_get_contents(base_path('routes/web.php')), "'/dashboard'")) {
            (new Filesystem)->append(base_path('routes/web.php'), $this->livewireRouteDefinition());
        }

        // Assets...
        copy(__DIR__.'/../../stubs/public/css/app.css', public_path('css/app.css'));
        copy(__DIR__.'/../../stubs/resources/css/app.css', resource_path('css/app.css'));
        copy(__DIR__.'/../../stubs/livewire/resources/js/app.js', resource_path('js/app.js'));

        // Tests...
        copy(__DIR__.'/../../stubs/tests/livewire/ApiTokenPermissionsTest.php', base_path('tests/Feature/ApiTokenPermissionsTest.php'));
        copy(__DIR__.'/../../stubs/tests/livewire/BrowserSessionsTest.php', base_path('tests/Feature/BrowserSessionsTest.php'));
        copy(__DIR__.'/../../stubs/tests/livewire/CreateApiTokenTest.php', base_path('tests/Feature/CreateApiTokenTest.php'));
        copy(__DIR__.'/../../stubs/tests/livewire/DeleteAccountTest.php', base_path('tests/Feature/DeleteAccountTest.php'));
        copy(__DIR__.'/../../stubs/tests/livewire/DeleteApiTokenTest.php', base_path('tests/Feature/DeleteApiTokenTest.php'));
        copy(__DIR__.'/../../stubs/tests/livewire/ProfileInformationTest.php', base_path('tests/Feature/ProfileInformationTest.php'));
        copy(__DIR__.'/../../stubs/tests/livewire/TwoFactorAuthenticationSettingsTest.php', base_path('tests/Feature/TwoFactorAuthenticationSettingsTest.php'));
        copy(__DIR__.'/../../stubs/tests/livewire/UpdatePasswordTest.php', base_path('tests/Feature/UpdatePasswordTest.php'));

        // Accounts...
        if ($this->option('accounts')) {
            $this->installLivewireAccountStack();
        }

        $this->line('');
        $this->info('Livewire scaffolding installed successfully.');
        $this->comment('Please execute "npm install && npm run dev" to build your assets.');
    }

    /**
     * Install the Livewire account stack into the application.
     *
     * @return void
     */
    protected function installLivewireAccountStack()
    {
        // Directories...
        (new Filesystem)->ensureDirectoryExists(resource_path('views/accounts'));

        // Other Views...
        (new Filesystem)->copyDirectory(__DIR__.'/../../stubs/livewire/resources/views/accounts', resource_path('views/accounts'));

        // Tests...
        copy(__DIR__.'/../../stubs/tests/livewire/CreateAccountTest.php', base_path('tests/Feature/CreateAccountTest.php'));
        copy(__DIR__.'/../../stubs/tests/livewire/DeleteAccountTest.php', base_path('tests/Feature/DeleteAccountTest.php'));
        copy(__DIR__.'/../../stubs/tests/livewire/InviteAccountMemberTest.php', base_path('tests/Feature/InviteAccountMemberTest.php'));
        copy(__DIR__.'/../../stubs/tests/livewire/LeaveAccountTest.php', base_path('tests/Feature/LeaveAccountTest.php'));
        copy(__DIR__.'/../../stubs/tests/livewire/RemoveAccountMemberTest.php', base_path('tests/Feature/RemoveAccountMemberTest.php'));
        copy(__DIR__.'/../../stubs/tests/livewire/UpdateAccountMemberRoleTest.php', base_path('tests/Feature/UpdateAccountMemberRoleTest.php'));
        copy(__DIR__.'/../../stubs/tests/livewire/UpdateAccountNameTest.php', base_path('tests/Feature/UpdateAccountNameTest.php'));

        $this->ensureApplicationIsAccountCompatible();
    }

    /**
     * Get the route definition(s) that should be installed for Livewire.
     *
     * @return string
     */
    protected function livewireRouteDefinition()
    {
        return <<<'EOF'

Route::middleware(['auth:sanctum', 'verified'])->get('/dashboard', function () {
    return view('dashboard');
})->name('dashboard');

EOF;
    }

    /**
     * Install the Inertia stack into the application.
     *
     * @return void
     */
    protected function installInertiaStack()
    {
        // Install Inertia...
        $this->requireComposerPackages('inertiajs/inertia-laravel:^0.3.5', 'laravel/sanctum:^2.6', 'tightenco/ziggy:^1.0');

        // Install NPM packages...
        $this->updateNodePackages(function ($packages) {
            return [
                '@inertiajs/inertia' => '^0.8.4',
                '@inertiajs/inertia-vue3' => '^0.3.5',
                '@inertiajs/progress' => '^0.2.4',
                '@tailwindcss/forms' => '^0.2.1',
                '@tailwindcss/typography' => '^0.3.0',
                'postcss-import' => '^12.0.1',
                'tailwindcss' => '^2.0.1',
                'vue' => '^3.0.5',
                '@vue/compiler-sfc' => '^3.0.5',
                'vue-loader' => '^16.1.2',
            ] + $packages;
        });

        // Sanctum...
        (new Process(['php', 'artisan', 'vendor:publish', '--provider=Laravel\Sanctum\SanctumServiceProvider', '--force'], base_path()))
                ->setTimeout(null)
                ->run(function ($type, $output) {
                    $this->output->write($output);
                });

        // Tailwind Configuration...
        copy(__DIR__.'/../../stubs/inertia/tailwind.config.js', base_path('tailwind.config.js'));
        copy(__DIR__.'/../../stubs/inertia/webpack.mix.js', base_path('webpack.mix.js'));
        copy(__DIR__.'/../../stubs/inertia/webpack.config.js', base_path('webpack.config.js'));

        // Directories...
        (new Filesystem)->ensureDirectoryExists(app_path('Actions/Fortify'));
        (new Filesystem)->ensureDirectoryExists(app_path('Actions/Jetstream'));
        (new Filesystem)->ensureDirectoryExists(public_path('css'));
        (new Filesystem)->ensureDirectoryExists(resource_path('css'));
        (new Filesystem)->ensureDirectoryExists(resource_path('js/Jetstream'));
        (new Filesystem)->ensureDirectoryExists(resource_path('js/Layouts'));
        (new Filesystem)->ensureDirectoryExists(resource_path('js/Pages'));
        (new Filesystem)->ensureDirectoryExists(resource_path('js/Pages/API'));
        (new Filesystem)->ensureDirectoryExists(resource_path('js/Pages/Auth'));
        (new Filesystem)->ensureDirectoryExists(resource_path('js/Pages/Profile'));
        (new Filesystem)->ensureDirectoryExists(resource_path('views'));
        (new Filesystem)->ensureDirectoryExists(resource_path('markdown'));

        (new Filesystem)->deleteDirectory(resource_path('sass'));

        // Terms Of Service / Privacy Policy...
        copy(__DIR__.'/../../stubs/resources/markdown/terms.md', resource_path('markdown/terms.md'));
        copy(__DIR__.'/../../stubs/resources/markdown/policy.md', resource_path('markdown/policy.md'));

        // Service Providers...
        copy(__DIR__.'/../../stubs/app/Providers/JetstreamServiceProvider.php', app_path('Providers/JetstreamServiceProvider.php'));

        $this->installServiceProviderAfter('FortifyServiceProvider', 'JetstreamServiceProvider');

        // Middleware...
        (new Process(['php', 'artisan', 'inertia:middleware', 'HandleInertiaRequests', '--force'], base_path()))
            ->setTimeout(null)
            ->run(function ($type, $output) {
                $this->output->write($output);
            });

        $this->installMiddlewareAfter('SubstituteBindings::class', '\App\Http\Middleware\HandleInertiaRequests::class');

        // Models...
        copy(__DIR__.'/../../stubs/app/Models/User.php', app_path('Models/User.php'));

        // Actions...
        copy(__DIR__.'/../../stubs/app/Actions/Fortify/CreateNewUser.php', app_path('Actions/Fortify/CreateNewUser.php'));
        copy(__DIR__.'/../../stubs/app/Actions/Fortify/UpdateUserProfileInformation.php', app_path('Actions/Fortify/UpdateUserProfileInformation.php'));
        copy(__DIR__.'/../../stubs/app/Actions/Jetstream/DeleteUser.php', app_path('Actions/Jetstream/DeleteUser.php'));

        // Blade Views...
        copy(__DIR__.'/../../stubs/inertia/resources/views/app.blade.php', resource_path('views/app.blade.php'));

        if (file_exists(resource_path('views/welcome.blade.php'))) {
            unlink(resource_path('views/welcome.blade.php'));
        }

        // Inertia Pages...
        copy(__DIR__.'/../../stubs/inertia/resources/js/Pages/Dashboard.vue', resource_path('js/Pages/Dashboard.vue'));
        copy(__DIR__.'/../../stubs/inertia/resources/js/Pages/PrivacyPolicy.vue', resource_path('js/Pages/PrivacyPolicy.vue'));
        copy(__DIR__.'/../../stubs/inertia/resources/js/Pages/TermsOfService.vue', resource_path('js/Pages/TermsOfService.vue'));
        copy(__DIR__.'/../../stubs/inertia/resources/js/Pages/Welcome.vue', resource_path('js/Pages/Welcome.vue'));

        (new Filesystem)->copyDirectory(__DIR__.'/../../stubs/inertia/resources/js/Jetstream', resource_path('js/Jetstream'));
        (new Filesystem)->copyDirectory(__DIR__.'/../../stubs/inertia/resources/js/Layouts', resource_path('js/Layouts'));
        (new Filesystem)->copyDirectory(__DIR__.'/../../stubs/inertia/resources/js/Pages/API', resource_path('js/Pages/API'));
        (new Filesystem)->copyDirectory(__DIR__.'/../../stubs/inertia/resources/js/Pages/Auth', resource_path('js/Pages/Auth'));
        (new Filesystem)->copyDirectory(__DIR__.'/../../stubs/inertia/resources/js/Pages/Profile', resource_path('js/Pages/Profile'));

        // Routes...
        $this->replaceInFile('auth:api', 'auth:sanctum', base_path('routes/api.php'));

        copy(__DIR__.'/../../stubs/inertia/routes/web.php', base_path('routes/web.php'));

        // Assets...
        copy(__DIR__.'/../../stubs/public/css/app.css', public_path('css/app.css'));
        copy(__DIR__.'/../../stubs/resources/css/app.css', resource_path('css/app.css'));
        copy(__DIR__.'/../../stubs/inertia/resources/js/app.js', resource_path('js/app.js'));

        // Flush node_modules...
        // static::flushNodeModules();

        // Tests...
        copy(__DIR__.'/../../stubs/tests/inertia/ApiTokenPermissionsTest.php', base_path('tests/Feature/ApiTokenPermissionsTest.php'));
        copy(__DIR__.'/../../stubs/tests/inertia/BrowserSessionsTest.php', base_path('tests/Feature/BrowserSessionsTest.php'));
        copy(__DIR__.'/../../stubs/tests/inertia/CreateApiTokenTest.php', base_path('tests/Feature/CreateApiTokenTest.php'));
        copy(__DIR__.'/../../stubs/tests/inertia/DeleteAccountTest.php', base_path('tests/Feature/DeleteAccountTest.php'));
        copy(__DIR__.'/../../stubs/tests/inertia/DeleteApiTokenTest.php', base_path('tests/Feature/DeleteApiTokenTest.php'));
        copy(__DIR__.'/../../stubs/tests/inertia/ProfileInformationTest.php', base_path('tests/Feature/ProfileInformationTest.php'));
        copy(__DIR__.'/../../stubs/tests/inertia/TwoFactorAuthenticationSettingsTest.php', base_path('tests/Feature/TwoFactorAuthenticationSettingsTest.php'));
        copy(__DIR__.'/../../stubs/tests/inertia/UpdatePasswordTest.php', base_path('tests/Feature/UpdatePasswordTest.php'));

        // Accounts...
        if ($this->option('accounts')) {
            $this->installInertiaAccountStack();
        }

        $this->line('');
        $this->info('Inertia scaffolding installed successfully.');
        $this->comment('Please execute "npm install && npm run dev" to build your assets.');
    }

    /**
     * Install the Inertia account stack into the application.
     *
     * @return void
     */
    protected function installInertiaAccountStack()
    {
        // Directories...
        (new Filesystem)->ensureDirectoryExists(resource_path('js/Pages/Profile'));

        // Pages...
        (new Filesystem)->copyDirectory(__DIR__.'/../../stubs/inertia/resources/js/Pages/Accounts', resource_path('js/Pages/Accounts'));

        // Tests...
        copy(__DIR__.'/../../stubs/tests/inertia/CreateAccountTest.php', base_path('tests/Feature/CreateAccountTest.php'));
        copy(__DIR__.'/../../stubs/tests/inertia/DeleteAccountTest.php', base_path('tests/Feature/DeleteAccountTest.php'));
        copy(__DIR__.'/../../stubs/tests/inertia/InviteAccountMemberTest.php', base_path('tests/Feature/InviteAccountMemberTest.php'));
        copy(__DIR__.'/../../stubs/tests/inertia/LeaveAccountTest.php', base_path('tests/Feature/LeaveAccountTest.php'));
        copy(__DIR__.'/../../stubs/tests/inertia/RemoveAccountMemberTest.php', base_path('tests/Feature/RemoveAccountMemberTest.php'));
        copy(__DIR__.'/../../stubs/tests/inertia/UpdateAccountMemberRoleTest.php', base_path('tests/Feature/UpdateAccountMemberRoleTest.php'));
        copy(__DIR__.'/../../stubs/tests/inertia/UpdateAccountNameTest.php', base_path('tests/Feature/UpdateAccountNameTest.php'));

        $this->ensureApplicationIsAccountCompatible();
    }

    /**
     * Ensure the installed user model is ready for account usage.
     *
     * @return void
     */
    protected function ensureApplicationIsAccountCompatible()
    {
        // Publish Account Migrations...
        $this->callSilent('vendor:publish', ['--tag' => 'jetstream-account-migrations', '--force' => true]);

        // Configuration...
        $this->replaceInFile('// Features::accounts([\'invitations\' => true])', 'Features::accounts([\'invitations\' => true])', config_path('jetstream.php'));

        // Directories...
        (new Filesystem)->ensureDirectoryExists(app_path('Actions/Jetstream'));
        (new Filesystem)->ensureDirectoryExists(app_path('Events'));
        (new Filesystem)->ensureDirectoryExists(app_path('Policies'));

        // Service Providers...
        copy(__DIR__.'/../../stubs/app/Providers/AuthServiceProvider.php', app_path('Providers/AuthServiceProvider.php'));
        copy(__DIR__.'/../../stubs/app/Providers/JetstreamWithAccountsServiceProvider.php', app_path('Providers/JetstreamServiceProvider.php'));

        // Models...
        copy(__DIR__.'/../../stubs/app/Models/Membership.php', app_path('Models/Membership.php'));
        copy(__DIR__.'/../../stubs/app/Models/Account.php', app_path('Models/Account.php'));
        copy(__DIR__.'/../../stubs/app/Models/AccountInvitation.php', app_path('Models/AccountInvitation.php'));
        copy(__DIR__.'/../../stubs/app/Models/UserWithAccounts.php', app_path('Models/User.php'));

        // Actions...
        copy(__DIR__.'/../../stubs/app/Actions/Jetstream/AddAccountMember.php', app_path('Actions/Jetstream/AddAccountMember.php'));
        copy(__DIR__.'/../../stubs/app/Actions/Jetstream/CreateAccount.php', app_path('Actions/Jetstream/CreateAccount.php'));
        copy(__DIR__.'/../../stubs/app/Actions/Jetstream/DeleteAccount.php', app_path('Actions/Jetstream/DeleteAccount.php'));
        copy(__DIR__.'/../../stubs/app/Actions/Jetstream/DeleteUserWithAccounts.php', app_path('Actions/Jetstream/DeleteUser.php'));
        copy(__DIR__.'/../../stubs/app/Actions/Jetstream/InviteAccountMember.php', app_path('Actions/Jetstream/InviteAccountMember.php'));
        copy(__DIR__.'/../../stubs/app/Actions/Jetstream/RemoveAccountMember.php', app_path('Actions/Jetstream/RemoveAccountMember.php'));
        copy(__DIR__.'/../../stubs/app/Actions/Jetstream/UpdateAccountName.php', app_path('Actions/Jetstream/UpdateAccountName.php'));

        copy(__DIR__.'/../../stubs/app/Actions/Fortify/CreateNewUserWithAccounts.php', app_path('Actions/Fortify/CreateNewUser.php'));

        // Policies...
        (new Filesystem)->copyDirectory(__DIR__.'/../../stubs/app/Policies', app_path('Policies'));

        // Factories...
        copy(__DIR__.'/../../database/factories/UserFactory.php', base_path('database/factories/UserFactory.php'));
        copy(__DIR__.'/../../database/factories/AccountFactory.php', base_path('database/factories/AccountFactory.php'));
    }

    /**
     * Install the service provider in the application configuration file.
     *
     * @param  string  $after
     * @param  string  $name
     * @return void
     */
    protected function installServiceProviderAfter($after, $name)
    {
        if (! Str::contains($appConfig = file_get_contents(config_path('app.php')), 'App\\Providers\\'.$name.'::class')) {
            file_put_contents(config_path('app.php'), str_replace(
                'App\\Providers\\'.$after.'::class,',
                'App\\Providers\\'.$after.'::class,'.PHP_EOL.'        App\\Providers\\'.$name.'::class,',
                $appConfig
            ));
        }
    }

    /**
     * Install the middleware to a group in the application Http Kernel.
     *
     * @param  string  $after
     * @param  string  $name
     * @param  string  $group
     * @return void
     */
    protected function installMiddlewareAfter($after, $name, $group = 'web')
    {
        $httpKernel = file_get_contents(app_path('Http/Kernel.php'));

        $middlewareGroups = Str::before(Str::after($httpKernel, '$middlewareGroups = ['), '];');
        $middlewareGroup = Str::before(Str::after($middlewareGroups, "'$group' => ["), '],');

        if (! Str::contains($middlewareGroup, $name)) {
            $modifiedMiddlewareGroup = str_replace(
                $after.',',
                $after.','.PHP_EOL.'            '.$name.',',
                $middlewareGroup,
            );

            file_put_contents(app_path('Http/Kernel.php'), str_replace(
                $middlewareGroups,
                str_replace($middlewareGroup, $modifiedMiddlewareGroup, $middlewareGroups),
                $httpKernel
            ));
        }
    }

    /**
     * Installs the given Composer Packages into the application.
     *
     * @param  mixed  $packages
     * @return void
     */
    protected function requireComposerPackages($packages)
    {
        $composer = $this->option('composer');

        if ($composer !== 'global') {
            $command = ['php', $composer, 'require'];
        }

        $command = array_merge(
            $command ?? ['composer', 'require'],
            is_array($packages) ? $packages : func_get_args()
        );

        (new Process($command, base_path(), ['COMPOSER_MEMORY_LIMIT' => '-1']))
            ->setTimeout(null)
            ->run(function ($type, $output) {
                $this->output->write($output);
            });
    }

    /**
     * Update the "package.json" file.
     *
     * @param  callable  $callback
     * @param  bool  $dev
     * @return void
     */
    protected static function updateNodePackages(callable $callback, $dev = true)
    {
        if (! file_exists(base_path('package.json'))) {
            return;
        }

        $configurationKey = $dev ? 'devDependencies' : 'dependencies';

        $packages = json_decode(file_get_contents(base_path('package.json')), true);

        $packages[$configurationKey] = $callback(
            array_key_exists($configurationKey, $packages) ? $packages[$configurationKey] : [],
            $configurationKey
        );

        ksort($packages[$configurationKey]);

        file_put_contents(
            base_path('package.json'),
            json_encode($packages, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT).PHP_EOL
        );
    }

    /**
     * Delete the "node_modules" directory and remove the associated lock files.
     *
     * @return void
     */
    protected static function flushNodeModules()
    {
        tap(new Filesystem, function ($files) {
            $files->deleteDirectory(base_path('node_modules'));

            $files->delete(base_path('yarn.lock'));
            $files->delete(base_path('package-lock.json'));
        });
    }

    /**
     * Replace a given string within a given file.
     *
     * @param  string  $search
     * @param  string  $replace
     * @param  string  $path
     * @return void
     */
    protected function replaceInFile($search, $replace, $path)
    {
        file_put_contents($path, str_replace($search, $replace, file_get_contents($path)));
    }
}
