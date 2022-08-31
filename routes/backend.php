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

$router->group(['prefix' => 'v1'], function ($app) {
    $app->group(['prefix' => 'auth'], function($app) {
        $app->post('login', 'AuthController@login');
        
    });

    $app->group(['prefix' => 'categories','middleware' => 'auth'], function($app) {
        $app->get('/',  ['uses' => 'CategoryController@index']);
        $app->get('{id}',['uses' =>'CategoryController@showOneCategory']);
       
        $app->post('create', 'CategoryController@create');
        $app->put('{id}', 'CategoryController@update');
        $app->delete('{id}', 'CategoryController@delete');
    
    });
    $app->group(['prefix' => 'user','middleware' => 'auth'], function($app) {
        $app->get('list',  ['uses' => 'UserController@list']);
        $app->post('create',  ['uses' => 'UserController@create']);
        $app->put('{id}', 'UserController@update');
        $app->delete('{id}', 'UserController@delete');
    });

    $app->group(['middleware' => 'auth'], function($app) {
        $app->get('terms/list','TermsController@index');
        $app->put('terms/{id}','TermsController@update');
   });
});

