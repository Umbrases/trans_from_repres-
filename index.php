<?php

require_once __DIR__ . '/vendor/autoload.php';


use App\Controller\CommentsController;
use App\Controller\TasksController;
use App\Controller\LeadController;
use App\Controller\DealController;
use App\Controller\ContactController;
use MiladRahimi\PhpRouter\Router;

$router = Router::create();

$router->post('/taskcloudtobox/task', function (){
    $event = $_REQUEST['event'];

    //Проверка на то, какой метод используется
    if (!in_array($event, ['ONTASKADD', 'ONTASKUPDATE'], true)) return;
    $taskId = $_REQUEST['data']['FIELDS_AFTER']['ID'];

    $taskController = new TasksController();
    $taskController->store($taskId);
});

$router->post('/taskcloudtobox/comment', function (){
    $event = $_REQUEST['event'];

    //Проверка на то, какой метод используется
    if ($event != 'ONTASKCOMMENTADD') return true;

    $taskId = $_REQUEST['data']['FIELDS_AFTER']['TASK_ID'];
    $itemId = $_REQUEST['data']['FIELDS_AFTER']['ID'];

    $commentController = new CommentsController();
    $commentController->store($taskId, $itemId);
});

$router->get('/taskcloudtobox/deal', function (){
//    $event = $_REQUEST['event'];

    //Проверка на то, какой метод используется
//    if ($event != 'ONTASKCOMMENTADD') return true;
//    $dealId = (int)$_REQUEST['data']['FIELDS']['ID'];
    $dealId = 63629;

    $dealController = new DealController();
    $dealController->store($dealId);
});

$router->get('/taskcloudtobox/lead', function (){
//    $event = $_REQUEST['event'];

    //Проверка на то, какой метод используется
//    if ($event != 'ONTASKCOMMENTADD') return true;
//    $leadId = (int)$_REQUEST['data']['FIELDS']['ID'];
    $leadId = 949334;

    $leadController = new LeadController();
    $leadController->create($leadId);
});

$router->get('/taskcloudtobox/contact', function (){
//    $event = $_REQUEST['event'];

    //Проверка на то, какой метод используется
//    if ($event != 'ONTASKCOMMENTADD') return true;
//    $leadId = (int)$_REQUEST['data']['FIELDS']['ID'];
    $contactId = 31674;

    $contactController = new ContactController();
    $contactController->store($contactId);
});

$router->dispatch();
function writeToLog($data)
{
    $log = "\n------------------------\n";
    $log .= date("Y.m.d G:i:s") . "\n";
    $log .= print_r($data, 1);
    $log .= "\n------------------------\n";
    file_put_contents(getcwd() . '/hook.log', $log, FILE_APPEND);
    return true;
}