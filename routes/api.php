<?php
declare(strict_types=1);

use App\Controllers\Api\AiApiController;
use App\Controllers\Api\ArticleApiController;
use App\Controllers\Api\ConsentApiController;
use App\Controllers\Api\EventApiController;
use App\Controllers\Api\HealthController;
use App\Controllers\Api\SimulationApiController;
use App\Controllers\Api\ToolApiController;

$router->get('/health', [HealthController::class, 'health']);
$router->get('/ready', [HealthController::class, 'ready']);

$router->get('/api/v1/articles/search', [ArticleApiController::class, 'search'], ['rate_limit:api']);
$router->get('/api/v1/events', [EventApiController::class, 'index'], ['rate_limit:api']);
$router->post('/api/v1/consents', [ConsentApiController::class, 'store'], ['csrf', 'rate_limit:api']);
$router->post('/api/v1/tools/password-strength', [ToolApiController::class, 'passwordStrength'], ['auth', 'tenant', 'rate_limit:api']);
$router->post('/api/v1/tools/url-analyze', [ToolApiController::class, 'urlAnalyze'], ['auth', 'tenant', 'rate_limit:api']);
$router->post('/api/v1/ai/analyze-text', [AiApiController::class, 'analyzeText'], ['auth', 'tenant', 'rate_limit:api']);
$router->get('/api/v1/simulations/{id}/report', [SimulationApiController::class, 'report'], ['auth', 'tenant', 'subscription', 'rate_limit:api']);
