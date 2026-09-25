<?php

namespace App\Providers;

use Carbon\CarbonImmutable;
use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureDefaults();
        $this->configureAuthEvents();
        $this->configureAuthorization();
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }

    /**
     * Flash a success toast on login (both Fortify email/password and Google OAuth,
     * since both call Auth::login() under the hood).
     *
     * 🔧 Wala nang Logout listener dito — ang session()->invalidate() na tinatawag
     * agad pagkatapos ng logout ay nagbubura ng flash bago pa ito maabot ng next
     * request, kaya client-side na lang ang logout toast (see UserMenuContent.vue).
     */
    protected function configureAuthEvents(): void
    {
        Event::listen(Login::class, function (Login $event) {
            Inertia::flash('toast', [
                'type' => 'success',
                'message' => "Welcome back, {$event->user->name}!",
            ]);
        });
    }

    /**
     * Every permission slug in the permissions table works as a Gate ability,
     * e.g. ->middleware('can:clients.create') or Gate::authorize('clients.delete').
     *
     * Returning null (not false) when the user lacks the permission lets
     * any existing policies still run.
     */
    protected function configureAuthorization(): void
    {
        Gate::before(function ($user, string $ability) {
            return $user->hasPermission($ability) ?: null;
        });
    }
}
