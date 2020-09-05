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

$router->get('/', function () use ($router) {
    return $router->app->version();
});

$router->post('/admin/login', ['uses'=>'AdminAuthController@authenticate', 'middleware'=>'throttle:5,1']); // throttled 5 requests/1 min
$router->get('/admin/user', ['uses'=>'AdminAuthController@user_info', 'middleware'=>'auth:admin']);
$router->post('/admin/change_password', ['uses'=>'AdminAuthController@change_password', 'middleware'=>'auth:admin']);

$router->post('/writer/login', ['uses'=>'WriterAuthController@authenticate', 'middleware'=>'throttle:5,1']); // throttled 5 requests/1 min
$router->get('/writer/user', ['uses'=>'WriterAuthController@user_info', 'middleware'=>'auth:writer']);
$router->post('/writer/change_password', ['uses'=>'WriterAuthController@change_password', 'middleware'=>'auth:writer']);

$router->get('/blog/articles/', ['uses' => 'BlogArticleController@index']);
$router->get('/blog/articles/{id}', ['uses' => 'BlogArticleController@show']);
$router->patch('/blog/articles/{id}', ['uses' => 'BlogArticleController@update', 'middleware'=>'auth:admin']);
$router->delete('/blog/articles/{id}', ['uses' => 'BlogArticleController@destroy', 'middleware'=>'auth:admin']);

$router->get('/blog/revisions/', ['uses' => 'BlogRevisionController@index']); //
$router->post('/blog/revisions/', ['uses' => 'BlogRevisionController@create', 'middleware'=>'auth:writer']);
$router->get('/blog/revisions/{id}', ['uses' => 'BlogRevisionController@show']);
$router->patch('/blog/revisions/{id}/accept', ['uses' => 'BlogRevisionController@accept', 'middleware'=>'auth:admin']);
$router->patch('/blog/revisions/{id}/reject', ['uses' => 'BlogRevisionController@reject', 'middleware'=>'auth:admin']);

$router->post('/blog/revisions/contrib/', ['uses' => 'BlogRevisionController@create_contrib']);

$router->get('/blog/categories/', ['uses' => 'BlogController@category_index']);

$router->post('/images', ['uses' => 'ImageController@create', 'middleware'=>'auth:writer']);
$router->get('/images/{id:\\w+}', ['uses' => 'ImageController@show']);

$router->get('/ogimage', ['uses' => 'OGImageController@getImage']);
$router->get('/ogimage/articles/{id}', ['uses' => 'OGImageController@getArticleImage']);
$router->get('/ogimage/preview', ['uses' => 'OGImageController@getPreview']);

$router->post('/reservation', ['uses' => 'ReservationController@create']);
$router->get('/reservation/search', ['uses' => 'ReservationController@index']);
$router->get('/reservation/{id}', ['uses' => 'ReservationController@show']);

$router->post('/general/enter', ['uses' => 'GuestController@enter']);

$router->options('{path:.*}', function(){}); // any path
