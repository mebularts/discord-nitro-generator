<?php
declare(strict_types=1);
session_start();
require_once __DIR__.'/../app/bootstrap.php';

use App\Router;
$router = new Router();

$router->get('/', 'HomeController@index');
$router->get('/new', 'HomeController@latest');
$router->get('/riddle/([a-z0-9\-]+)', 'RiddleController@show');

$router->get('/terms', 'StaticController@page');
$router->get('/privacy', 'StaticController@page');
$router->get('/advertising', 'StaticController@page');
$router->get('/contact', 'StaticController@page');
$router->get('/app', 'StaticController@page');

$router->post('/api/vote', 'ApiController@vote');

$router->dispatch();
