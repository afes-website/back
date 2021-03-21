<?php
namespace Tests;

use Illuminate\Support\Facades\Artisan;
use Laravel\Lumen\Testing\DatabaseTransactions;

abstract class TestCase extends \Laravel\Lumen\Testing\TestCase {

    use DatabaseTransactions;

    private static $initialized = false;

    /**
     * Creates the application.
     *
     * @return \Laravel\Lumen\Application
     */
    public function createApplication() {
        return require __DIR__.'/../bootstrap/app.php';
    }

    public function setUp(): void {
        parent::setUp();

        if (static::$initialized === false) {
            static::$initialized = true;
            if (env('FRESH_DB', true)) Artisan::call('migrate:fresh');
            else                       Artisan::call('migrate');
            Artisan::call('db:seed', [
                '--force' => true,
            ]);
        }
    }
}
