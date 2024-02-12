<?php

use App\Controller\TulaController;
use MiladRahimi\PhpRouter\Router;
use App\Controller\UfaController;
use App\Controller\DealController;

$router = Router::create();

$router->get('/ufa', UfaController::class);

$router->get('/tula', TulaController::class);

$router->get('/handler', DealController::class);

$router->dispatch();