<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Contracts\Auth\Factory as Auth;
use Symfony\Component\HttpKernel\Exception\HttpException;

class Authenticate
{
    /**
     * The authentication guard factory instance.
     *
     * @var \Illuminate\Contracts\Auth\Factory
     */
    protected $auth;

    /**
     * Create a new middleware instance.
     *
     * @param  \Illuminate\Contracts\Auth\Factory  $auth
     * @return void
     */
    public function __construct(Auth $auth)
    {
        $this->auth = $auth;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string[]  ...$perms
     * @return mixed
     * @throws Symfony\Component\HttpKernel\Exception\HttpException
     */
    public function handle($request, Closure $next, ...$perms)
    {
        if ($this->auth->guard()->guest()) {
            throw new HttpException(401, 'Unauthorized.');
        }

        $user = $request->user();
        $passed = false;
        if (count($perms) !== 0) {
            foreach ($perms as $val) {
                if ($user->has_permission(trim($val))) {
                    $passed = true;
                    break;
                }
            }
        } else {
            $passed = true;
        }

        if (!$passed)
            throw new HttpException(403, 'Forbidden.');

        return $next($request);
    }
}
