<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Laravel\Sanctum\Sanctum;
use App\Models\User;
use App\Models\Lote;
use App\Policies\LotePolicy;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Lote::class => LotePolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        // Configuración para Sanctum
        Sanctum::ignoreMigrations();

        // Definir gates personalizados si es necesario
        Gate::define('ver-informes', function (User $user) {
            return $user->hasRole('administrador');
        });

        Gate::define('gestionar-usuarios', function (User $user) {
            return $user->hasRole('administrador');
        });

        // Gate para verificar si un usuario puede acceder a un lote específico
        Gate::define('acceder-lote', function (User $user, $loteId) {
            return $user->tieneLote($loteId);
        });
    }
}
