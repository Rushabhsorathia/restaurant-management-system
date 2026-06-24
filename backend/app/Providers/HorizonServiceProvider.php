<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Laravel\Horizon\Horizon;
use Laravel\Horizon\HorizonApplicationServiceProvider;

class HorizonServiceProvider extends HorizonApplicationServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        parent::boot();

        Horizon::night();
    }

    /**
     * Register the Horizon gate.
     *
     * In local / dev environments allow all access. In production restrict
     * to a configured list of admin emails via HORIZON_ADMINS env (comma list).
     */
    protected function gate(): void
    {
        Gate::define('viewHorizon', function ($user = null) {
            if (app()->environment('local', 'testing')) {
                return true;
            }

            $admins = array_filter(array_map(
                'trim',
                explode(',', (string) env('HORIZON_ADMINS', ''))
            ));

            return $user && in_array($user->email, $admins, true);
        });
    }
}
