<?php

namespace App\Controller;

use App\Model\Comment;
use App\Model\Webhooks;

(new TulaController)->index();

class TulaController
{

    public function index()
    {
        $event = $_REQUEST['event'];
        $method = 'CRestTula';
        $method_ufa = 'CRestUfa';
        $folder_id = 1740432;
        $city = "tula";

        if ($event == 'ONTASKADD' || $event == 'ONTASKUPDATE') {
            $tasks = $_REQUEST['data']['FIELDS_AFTER']['ID'];
        } elseif ($event == 'ONTASKCOMMENTADD') {
            $tasks = $_REQUEST['data']['FIELDS_AFTER']['TASK_ID'];
            $item_id = $_REQUEST['data']['FIELDS_AFTER']['ID'];
            $task_message = (new Comment)->getComment($method, $tasks, $item_id);
        }

        (new Webhooks)->setOnTask($event, $method, $method_ufa, $folder_id, $tasks, $task_message, $city);

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