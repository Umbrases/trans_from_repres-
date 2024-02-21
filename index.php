<?php

require_once __DIR__ . '/vendor/autoload.php';


use App\Controller\TulaController;
use MiladRahimi\PhpRouter\Router;
use App\Controller\UfaController;
use App\Controller\DealController;

$router = Router::create();

$router->post('/ufa', [UfaController::class, 'index']);

$router->post('/tula', [TulaController::class, 'index']);

$router->get('/handler', [DealController::class, 'index']);

$router->dispatch();

