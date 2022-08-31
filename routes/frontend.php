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
    $app->get('header', 'HeaderController@index');
    $app->get('footer', 'FooterController@index');
    $app->get('meta', 'MetaController@index');
    $app->get('videos', 'VideoController@index');
    $app->get('series','DynamicPageController@index');

    //Widget
    $app->get('widget','WidgetController@index');
    $app->get('terms','TermsController@index');
    $app->get('powerranking','PowerrankingController@index');

    //Publication
    $app->get('publication','PublicationController@index');

    //Static
    $app->get('about','AboutController@index');
    $app->get('contact','ContactController@index');
    $app->get('terms-of-use','TermsOfUseController@index');
  
    //Category
    $app->get('categories','CategoryController@index');
    $app->get('category/tab','CategoryController@tab');
    $app->get('category/details','CategoryController@details');
    
    //Sitemap
    $app->get('sitemap.xml','SitemapController@index');
    $app->get('sitemap','SitemapController@index');
    $app->get('story', 'StoryController@detail');

    //Brand
    $app->get('activebrands', 'BrandController@active');
    $app->get('franchise-research','BrandController@franchiseResearch');
    $app->get('info', 'BrandInfoController@info');
    $app->get('pdf','BrandInfoController@pdf');
    $app->get('why-i-bought','BrandInfoController@whyIBought');
    $app->get('executive','BrandInfoController@executive');
    $app->get('available-market','BrandInfoController@availableMarket');
    $app->get('financial','BrandController@financial');
    $app->post('contact','BrandFormController@contact');
    //Author
    $app->get('author','AuthorController@details');
    $app->get('{slug:[-\w]+}','BrandController@details');
    $app->get('{slug:[-\w]+}/info-tab','BrandInfoController@tab');

    //Newsletter
    $app->group(['prefix' => 'newsletter'], function($app) {
        $app->post('signup','NewsletterController@signup');
    });

    $app->group(['prefix' => 'articles'], function($app) {
        $app->get('{type}', ['middleware' => 'article', 'uses' => 'StoryController@list']);
    });
    $app->group(['prefix' => 'cover'], function($app) {
        $app->get('{type}', ['middleware' => 'cover', 'uses' => 'CoverController@index']);
    });
});

