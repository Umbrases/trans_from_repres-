<?php

namespace App\Controller;

use App\Model\Comment;
use App\Model\Webhooks;

(new UfaController)->index();

class UfaController
{

    public function index()
    {
        $event = $_REQUEST['event'];
        $method = 'CRestUfa';
        $method_tula = 'CRestTula';
        $folder_id = 54657;
        $city = "ufa";

        if ($event == 'ONTASKADD' || $event == 'ONTASKUPDATE') {
            $tasks = $_REQUEST['data']['FIELDS_AFTER']['ID'];
        } elseif ($event == 'ONTASKCOMMENTADD') {
            $tasks = $_REQUEST['data']['FIELDS_AFTER']['TASK_ID'];
            $item_id = $_REQUEST['data']['FIELDS_AFTER']['ID'];
            $task_message = (new Comment)->getComment($method, $tasks, $item_id);
        }

        (new Webhooks)->setOnTask($event, $method, $method_tula, $folder_id, $tasks, $task_message, $city);

        return true;
    }
}

