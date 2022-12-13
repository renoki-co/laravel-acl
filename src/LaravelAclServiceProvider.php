<?php

namespace RenokiCo\LaravelAcl;

use Illuminate\Support\ServiceProvider;

class LaravelAclServiceProvider extends ServiceProvider
{
    /**
     * Boot the service provider.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__.'/../config/acl.php' => config_path('acl.php'),
        ], 'config');

        $this->publishes([
            __DIR__.'/../database/migrations/2022_12_11_161511_create_acl_policies_table.php' => database_path('migrations/2022_12_11_161511_create_acl_policies_table.php'),
        ], 'config');

        $this->mergeConfigFrom(__DIR__.'/../config/acl.php', 'acl');
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
