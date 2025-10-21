<?php

declare(strict_types=1);

require __DIR__ . '/../app/bootstrap.php';

use App\Controllers\ApiController;
use App\Controllers\HomeController;
use App\Controllers\RiddleController;
use App\Controllers\StaticController;

$router = new Router();

$router->get('/', function (): string {
    return (new HomeController())();
});
$router->get('/riddles/{slug}', function (string $slug): string {
    return (new RiddleController())->show($slug);
});
$router->get('/pages/{slug}', function (string $slug): string {
    return (new StaticController())->show($slug);
});
$router->post('/api/vote', function (): void {
    (new ApiController())->vote();
});

$router->dispatch();
