<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

use Illuminate\Http\Request;

$router->get('/', function () use ($router) {
    return $router->app->version();
});

$router->post('/admin/login', ['uses'=>'AdminAuthController@authenticate', 'middleware'=>'throttle:5,1']); // throttled 5 requests/1 min
$router->get('/admin/user', ['uses'=>'AdminAuthController@user_info', 'middleware'=>'auth:admin']);

$router->get('/blog/revisions/{id}', ['uses' => 'BlogController@get_revision']);
$router->post('/blog/revisions/', ['uses' => 'BlogController@post_revision']);
$router->get('/blog/articles/{id}', ['uses' => 'BlogController@get_article']);
$router->options('{path:.*}', function(){}); // any path
