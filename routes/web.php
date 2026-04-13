<?php
declare(strict_types=1);

use App\Controllers\ArticleController;
use App\Controllers\AuthController;
use App\Controllers\EventController;
use App\Controllers\HomeController;
use App\Controllers\SimulationController;

$router->get('/', [HomeController::class, 'index']);
$router->get('/login', [AuthController::class, 'showLogin'], ['guest']);
$router->post('/login', [AuthController::class, 'login'], ['guest', 'csrf', 'rate_limit:login']);
$router->post('/logout', [AuthController::class, 'logout'], ['auth', 'csrf']);

$router->get('/dashboard', [HomeController::class, 'dashboard'], ['auth', 'tenant']);

$router->get('/articles', [ArticleController::class, 'index'], ['auth', 'tenant', 'subscription']);
$router->get('/articles/create', [ArticleController::class, 'create'], ['auth', 'tenant', 'rbac:org_admin,editor']);
$router->post('/articles', [ArticleController::class, 'store'], ['auth', 'tenant', 'rbac:org_admin,editor', 'csrf']);
$router->post('/articles/{id}/publish', [ArticleController::class, 'publish'], ['auth', 'tenant', 'rbac:org_admin,editor', 'csrf']);
$router->get('/blog/{slug}', [ArticleController::class, 'show']);

$router->get('/events', [EventController::class, 'index'], ['auth', 'tenant', 'subscription']);
$router->get('/events/create', [EventController::class, 'create'], ['auth', 'tenant', 'rbac:org_admin,editor,analyst']);
$router->post('/events', [EventController::class, 'store'], ['auth', 'tenant', 'rbac:org_admin,editor,analyst', 'csrf']);

$router->get('/simulations', [SimulationController::class, 'index'], ['auth', 'tenant', 'subscription']);
$router->get('/simulations/create', [SimulationController::class, 'create'], ['auth', 'tenant', 'rbac:org_admin,analyst']);
$router->post('/simulations', [SimulationController::class, 'store'], ['auth', 'tenant', 'rbac:org_admin,analyst', 'csrf']);
$router->post('/simulations/{id}/launch', [SimulationController::class, 'launch'], ['auth', 'tenant', 'rbac:org_admin,analyst', 'csrf']);

$router->get('/simulate/{token}', [SimulationController::class, 'landing'], ['rate_limit:simulation']);
$router->post('/simulate/{token}/open', [SimulationController::class, 'open'], ['csrf', 'rate_limit:simulation']);
$router->post('/simulate/{token}/report', [SimulationController::class, 'report'], ['csrf', 'rate_limit:simulation']);
$router->post('/simulate/{token}/complete', [SimulationController::class, 'complete'], ['csrf', 'rate_limit:simulation']);
