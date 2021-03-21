<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\DB;

class AppServiceProvider extends ServiceProvider {
    public function boot() {
        if (DB::getDriverName() == 'sqlite') {
            fputs(STDERR, "sqlite is no longer supported. use other database such as MySQL.\n");
            exit(1);
        }
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register() {
        //
    }
}
