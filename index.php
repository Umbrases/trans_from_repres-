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

//writeToLog($router);

$router->dispatch();

//function writeToLog($data) {
//    $log = "\n------------------------\n";
//    $log .= date("Y.m.d G:i:s") . "\n";
//    $log .= print_r($data, 1);
//    $log .= "\n------------------------\n";
//    file_put_contents(getcwd() . '/hook.log', $log, FILE_APPEND);
//    return true;
//}