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

$router->post('/auth/login', ['uses'=>'AuthController@authenticate', 'middleware'=>'throttle:5,1']); // throttled 5 requests/1 min
$router->get('/auth/user', ['uses'=>'AuthController@user_info', 'middleware'=>'auth']);
$router->post('/auth/change_password', ['uses'=>'AuthController@change_password', 'middleware'=>'auth']);

$router->get('/blog/articles/', ['uses' => 'BlogArticleController@index']);
$router->get('/blog/articles/{id}', ['uses' => 'BlogArticleController@show']);
$router->patch('/blog/articles/{id}', ['uses' => 'BlogArticleController@update', 'middleware'=>'auth:blogAdmin']);
$router->delete('/blog/articles/{id}', ['uses' => 'BlogArticleController@destroy', 'middleware'=>'auth:blogAdmin']);

$router->get('/blog/revisions/', ['uses' => 'BlogRevisionController@index', 'middleware'=>'auth']); //
$router->post('/blog/revisions/', ['uses' => 'BlogRevisionController@create', 'middleware'=>'auth:blogWriter']);
$router->get('/blog/revisions/{id}', ['uses' => 'BlogRevisionController@show', 'middleware'=>'auth']);
$router->patch('/blog/revisions/{id}/accept', ['uses' => 'BlogRevisionController@accept', 'middleware'=>'auth:blogAdmin']);
$router->patch('/blog/revisions/{id}/reject', ['uses' => 'BlogRevisionController@reject', 'middleware'=>'auth:blogAdmin']);

$router->post('/blog/revisions/contrib/', ['uses' => 'BlogRevisionController@create_contrib']);

$router->get('/blog/categories/', ['uses' => 'BlogController@category_index']);

$router->post('/images', ['uses' => 'ImageController@create', 'middleware'=>'auth:blogWriter']);
$router->get('/images/{id:\\w+}', ['uses' => 'ImageController@show']);

$router->get('/ogimage', ['uses' => 'OGImageController@getImage']);
$router->get('/ogimage/articles/{id}', ['uses' => 'OGImageController@getArticleImage']);
$router->get('/ogimage/preview', ['uses' => 'OGImageController@getPreview']);

$router->get('/online/exhibition', ['uses' => 'ExhibitionController@index']);
$router->get('/online/exhibition/{id}', ['uses' => 'ExhibitionController@show']);
$router->get('/online/exhibition/drafts', ['uses' => 'DraftController@index', 'middleware'=>'auth:exhibition, blogAdmin, teacher']);
$router->get('/online/exhibition/drafts/{id}', ['uses' => 'DraftController@show', 'middleware'=>'auth:exhibition, blogAdmin, teacher']);
$router->post('/online/exhibition/drafts', ['uses' => 'DraftController@create', 'middleware'=>'auth:exhibition, blogAdmin']);
$router->patch('/online/exhibition/drafts/{id}/accept', ['uses' => 'DraftController@accept', 'middleware'=>'auth:blogAdmin, teacher']);
$router->patch('/online/exhibition/drafts/{id}/reject', ['uses' => 'DraftController@reject', 'middleware'=>'auth:blogAdmin, teacher']);
$router->patch('/online/exhibition/drafts/{id}/publish', ['uses' => 'DraftController@publish', 'middleware'=>'auth:blogAdmin']);
$router->post('/online/exhibition/drafts/{id}/comment', ['uses' => 'DraftController@comment', 'middleware'=>'auth:blogAdmin']);


$router->options('{path:.*}', function(){}); // any path
