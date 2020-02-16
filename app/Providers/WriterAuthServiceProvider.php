<?php

namespace App\Providers;

use App\Models\WriterUser;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Lcobucci\JWT\Parser;
use Lcobucci\JWT\ValidationData;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Symfony\Component\HttpKernel\Exception\HttpException;

class WriterAuthServiceProvider extends ServiceProvider {
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register() {
        //
    }

    /**
     * Boot the authentication services for the application.
     *
     * @return void
     */
    public function boot() {
        // Here you may define how you wish users to be authenticated for your Lumen
        // application. The callback which receives the incoming request instance
        // should return either a User instance or null. You're free to obtain
        // the User instance via an API token or any other method necessary.

        $this->app['auth']->viaRequest('writer', function ($request) {
            $token = $request->header('X-BLOG-WRITER-TOKEN');

            if (!$token) return;

            $signer = new Sha256();
            $data = new ValidationData();
            $data->setIssuer(env('APP_URL'));
            $data->setAudience(env('APP_URL'));
            $data->setCurrentTime(Carbon::now()->getTimestamp());

            try {
                $token = (new Parser())->parse((string) $token);

                if (!$token->validate($data))
                    return;

                if (!$token->verify($signer, env('JWT_SECRET')))
                    return;

                $user = WriterUser::findOrFail($token->getClaim('uid'));

            } catch (Exception $e) {
                return;
            }
            return $user;//*/
        });

    }
}
