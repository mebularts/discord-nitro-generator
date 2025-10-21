<?php
declare(strict_types=1);

session_start();
require_once __DIR__ . '/../app/bootstrap.php';

use App\Router;

if (isset($_GET['lang'])) {
    $locale = resolve_locale($_GET['lang']);
    setcookie('solveclone_locale', $locale, time() + 31536000, '/', '', isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off', true);
    $uri = $_SERVER['REQUEST_URI'] ?? '/';
    $cleanUri = preg_replace('/([?&])lang=[^&]+(&|$)/', '$1', $uri);
    $cleanUri = rtrim(str_replace(['?&', '&&'], ['?', '&'], $cleanUri), '?&');
    redirect($cleanUri ?: '/');
}

$router = new Router();

$router->get('/', 'HomeController@index');
$router->get('/new', 'HomeController@latest');
$router->get('/riddle/([a-z0-9\-]+)', 'RiddleController@show');
$router->get('/terms', 'StaticController@page');
$router->get('/privacy', 'StaticController@page');
$router->get('/advertising', 'StaticController@page');
$router->get('/contact', 'StaticController@page');
$router->get('/app', 'StaticController@page');
$router->get('/sitemap.xml', 'StaticController@sitemap');
$router->get('/robots.txt', 'StaticController@robots');

$router->post('/api/vote', 'ApiController@vote');
$router->get('/api/riddles', 'ApiController@list');

$router->dispatch();
