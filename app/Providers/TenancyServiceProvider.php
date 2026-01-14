<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use App\Jobs\CreateTenantDatabase; 
use Stancl\JobPipeline\JobPipeline;
use Stancl\Tenancy\Events;
use Stancl\Tenancy\Jobs;
use Stancl\Tenancy\Listeners;
use Stancl\Tenancy\Middleware;
use App\Jobs\SeedTenantDatabase; // <--- Importa tu nuevo Job
use Livewire\Livewire;

class TenancyServiceProvider extends ServiceProvider
{
    public static string $controllerNamespace = '';

    public function events()
    {
        return [
            // EVENTO DE CREACIÓN: Solo uno y con tu Job personalizado
            Events\TenantCreated::class => [
                JobPipeline::make([
                CreateTenantDatabase::class, // <--- Usa el tuyo
                Jobs\MigrateDatabase::class,
                // Jobs\SeedDatabase::class,
                SeedTenantDatabase::class, // <--- CAMBIA Jobs\SeedDatabase por el TUYO
                ])->send(function (Events\TenantCreated $event) {
                    return $event->tenant;
                })->shouldBeQueued(false),
            ],

            // EVENTO DE ELIMINACIÓN
            Events\TenantDeleted::class => [
                JobPipeline::make([
                    Jobs\DeleteDatabase::class,
                ])->send(function (Events\TenantDeleted $event) {
                    return $event->tenant;
                })->shouldBeQueued(false),
            ],

            // Eventos de Tenancy (Mantenimiento de contexto)
            Events\TenancyInitialized::class => [
                Listeners\BootstrapTenancy::class,
            ],
            Events\TenancyEnded::class => [
                Listeners\RevertToCentralContext::class,
            ],

            // Sincronización de recursos (Si lo usas)
            Events\SyncedResourceSaved::class => [
                Listeners\UpdateSyncedResource::class,
            ],

            // Otros eventos vacíos para mantener compatibilidad
            Events\CreatingTenant::class => [],
            Events\SavingTenant::class => [],
            Events\TenantSaved::class => [],
            Events\UpdatingTenant::class => [],
            Events\TenantUpdated::class => [],
            Events\DeletingTenant::class => [],
            Events\CreatingDomain::class => [],
            Events\DomainCreated::class => [],
            Events\SavingDomain::class => [],
            Events\DomainSaved::class => [],
            Events\UpdatingDomain::class => [],
            Events\DomainUpdated::class => [],
            Events\DeletingDomain::class => [],
            Events\DomainDeleted::class => [],
        ];
    }

    public function register() {
        // Forzamos a Laravel a usar TU modelo siempre que se pida un Tenant
    $this->app->bind(\Stancl\Tenancy\Contracts\Tenant::class, \App\Models\Tenant::class);
    }

    public function boot()
    {
        $this->bootEvents();
        // $this->mapRoutes();
        $this->makeTenancyMiddlewareHighestPriority();
        // ⭐ Configurar Livewire para contexto central también
        $this->configureLivewire();
    }

    protected function bootEvents()
    {
        foreach ($this->events() as $event => $listeners) {
            foreach ($listeners as $listener) {
                if ($listener instanceof JobPipeline) {
                    $listener = $listener->toListener();
                }
                Event::listen($event, $listener);
            }
        }
    }

    protected function mapRoutes()
    {
        $this->app->booted(function () {
            if (file_exists(base_path('routes/tenant.php'))) {
                Route::namespace(static::$controllerNamespace)
                    ->group(base_path('routes/tenant.php'));
            }
        });
    }

    protected function makeTenancyMiddlewareHighestPriority()
    {
        $tenancyMiddleware = [
            Middleware\PreventAccessFromCentralDomains::class,
            Middleware\InitializeTenancyByDomain::class,
            Middleware\InitializeTenancyBySubdomain::class,
            Middleware\InitializeTenancyByDomainOrSubdomain::class,
            Middleware\InitializeTenancyByPath::class,
            Middleware\InitializeTenancyByRequestData::class,
        ];

        foreach (array_reverse($tenancyMiddleware) as $middleware) {
            $this->app[\Illuminate\Contracts\Http\Kernel::class]->prependToMiddlewarePriority($middleware);
        }
    }

    protected function configureLivewire()
    {
        Livewire::setUpdateRoute(function ($handle) {
            return Route::post('/public/livewire/update', $handle)
            ->middleware(
                'web',
                'universal',
                // \Stancl\Tenancy\Middleware\InitializeTenancyByDomain::class, // or whatever tenancy middleware you use
            );
            FilePreviewController::$middleware = ['web', 'universal', InitializeTenancyByDomain::class];
    });
    }
}