<?php

namespace App\Controller;

use App\Model\Webhooks;
use App\Service\CommentService;

(new TulaController)->handle();

class TulaController
{
    private Webhooks $webhooks;

    public function __construct()
    {
        $this->webhooks = new Webhooks;
    }

    public function handle()
    {
        $event = $_REQUEST['event'];
        $method = 'CRestTula';
        $method_ufa = 'CRestUfa';
        $folder_id = 1740432;
        $city = "tula";

        if ($event == 'ONTASKADD' || $event == 'ONTASKUPDATE') {
            $taskId = $_REQUEST['data']['FIELDS_AFTER']['ID'];
        } elseif ($event == 'ONTASKCOMMENTADD') {
            $taskId = $_REQUEST['data']['FIELDS_AFTER']['TASK_ID'];
            $item_id = $_REQUEST['data']['FIELDS_AFTER']['ID'];
            $task_message = (new CommentService)->getComment($method, $taskId, $item_id);
        }

        $this->webhooks->setOnTask($event, $method, $method_ufa, $folder_id, $taskId, $task_message, $city);

        return true;
    }

}


// function writeToLog($data) {
//     $log = "\n------------------------\n";
//     $log .= date("Y.m.d G:i:s") . "\n";
//     $log .= print_r($data, 1);
//     $log .= "\n------------------------\n";
//     file_put_contents(getcwd() . '/hook.log', $log, FILE_APPEND);
//     return true;
// }