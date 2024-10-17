<?php

require_once __DIR__ . '/vendor/autoload.php';


use App\Controller\CommentsController;
use App\Controller\TasksController;
use App\Controller\TasksVipController;
use App\Controller\LeadController;
use App\Controller\DealController;
use App\Controller\ContactController;
use App\Controller\FilesController;
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

$router->post('/taskcloudtobox/deal', function (){
    $event = $_REQUEST['event'];
//    writeToLog($event);
    //Проверка на то, какой метод используется
    if ($event == 'ONCRMDEALADD' || $event == 'ONCRMDEALUPDATE') {
        $dealId = (int)$_REQUEST['data']['FIELDS']['ID'];

        $dealController = new DealController();
        $dealController->store($dealId);
    }
});

$router->post('/taskcloudtobox/deal/observer', function (){
    $event = $_REQUEST['event'];

    //Проверка на то, какой метод используется
    if ($event != 'DEALOBSERVER') return true;
    $dealId = (int)$_REQUEST['deal_id'];
    $observers = $_REQUEST['observer'];

    $dealController = new DealController();
    $dealController->observerStore($dealId, $observers);
});

$router->post('/taskcloudtobox/lead', function (){
    $event = $_REQUEST['event'];

    //Проверка на то, какой метод используется
    if ($event != 'ONCRMLEADADD' || $event != 'ONCRMLEADUPDATE') return true;
    $leadId = (int)$_REQUEST['data']['FIELDS']['ID'];

    $leadController = new LeadController();
    $leadController->create($leadId);
});

$router->post('/taskcloudtobox/contact', function (){
    $event = $_REQUEST['event'];

    //Проверка на то, какой метод используется
    if ($event != 'ONCRMCONTACTADD' || $event != 'ONCRMCONTACTUPDATE') return true;
    $contactId = (int)$_REQUEST['data']['FIELDS']['ID'];

    $contactController = new ContactController();
    $contactController->store($contactId);
});

$router->post('/taskcloudtobox/file', function (){
    $fileId = (int)$_REQUEST['fileId'];

    $fileController = new FilesController();
    $fileController->store($fileId);
});

$router->post('/taskcloudtobox/task/vip', function (){
    $event = $_REQUEST['event'];

    //Проверка на то, какой метод используется
    if (!in_array($event, ['ONTASKADD', 'ONTASKUPDATE'], true)) return;
    $taskId = $_REQUEST['data']['FIELDS_AFTER']['ID'];

    $taskController = new TasksVipController();
    $taskController->store($taskId);
});


$router->get('/taskcloudtobox/task/update', function (){
    $taskId = $_REQUEST['taskId'];

    $taskController = new TasksController();
    $taskController->storeBox($taskId);
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