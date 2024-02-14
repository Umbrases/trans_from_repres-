<?php

namespace App\Controller;

use App\Model\CRestTula;
use App\Model\CRestUfa;
use App\Model\Webhooks;
use App\Service\CommentService;

(new UfaController)->index();

class UfaController
{
    private Webhooks $webhooks;
    private CommentService $commentService;

    public function __construct()
    {
        $this->webhooks = new Webhooks;
        $this->commentService = new CommentService;
    }
    public function index()
    {
        $event = $_REQUEST['event'];
        $method = CRestUfa::class;
        $method_tula = CRestTula::class;
        $folder_id = 54657;
        $city = "ufa";

        if ($event == 'ONTASKADD' || $event == 'ONTASKUPDATE') {
            $taskId = $_REQUEST['data']['FIELDS_AFTER']['ID'];
        } elseif ($event == 'ONTASKCOMMENTADD') {
            $taskId = $_REQUEST['data']['FIELDS_AFTER']['TASK_ID'];
            $item_id = $_REQUEST['data']['FIELDS_AFTER']['ID'];
            $task_message = $this->commentService->getComment($method, $taskId, $item_id);
        }

        $this->webhooks->setOnTask($event, $method, $method_tula, $folder_id, $taskId, $task_message, $city);

        return true;
    }
}

