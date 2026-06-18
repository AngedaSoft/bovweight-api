<?php

namespace App\Providers;

use App\Models\AccesoCompartido;
use App\Models\Animal;
use App\Models\Finca;
use App\Models\Notificacion;
use App\Models\Pesaje;
use App\Models\Rebano;
use App\Policies\AccesoCompartidoPolicy;
use App\Policies\AnimalPolicy;
use App\Policies\FincaPolicy;
use App\Policies\NotificacionPolicy;
use App\Policies\PesajePolicy;
use App\Policies\RebanoPolicy;
use App\Services\Ml\HttpMlEstimacionClient;
use App\Services\Ml\MlEstimacionClient;
use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(MlEstimacionClient::class, function ($app) {
            return new HttpMlEstimacionClient(
                http: $app->make(HttpFactory::class),
                baseUrl: config('services.ml.base_url'),
                apiKey: config('services.ml.api_key'),
                timeoutSeg: (int) config('services.ml.timeout', 30),
            );
        });
    }

    public function boot(): void
    {
        Gate::policy(Finca::class, FincaPolicy::class);
        Gate::policy(Animal::class, AnimalPolicy::class);
        Gate::policy(Pesaje::class, PesajePolicy::class);
        Gate::policy(Rebano::class, RebanoPolicy::class);
        Gate::policy(Notificacion::class, NotificacionPolicy::class);
        Gate::policy(AccesoCompartido::class, AccesoCompartidoPolicy::class);
    }
}
