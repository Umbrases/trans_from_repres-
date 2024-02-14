<?php

namespace App\Service;

use App\Model\QueryHelper;

class CommentService
{
    private QueryHelper $queryHelper;

    public function __construct()
    {
        $this->queryHelper = new QueryHelper;
    }

    public function setComment($sqlId, $taskMessage, $fileId, $db, $tasks, $sqlTask, $authorId, $method)
    {
        $pregReplace = preg_replace(array('/^\WUSER=\w{2,}\W/', '/\W{2}USER\W/'), '', $taskMessage['POST_MESSAGE']);
        $methodQuery = $this->queryHelper->getQuery($method, 'task.commentitem.add', [
            'TASKID' => $sqlId,
            'fields' => [
                'AUTHOR_ID' => $authorId,
                'POST_MESSAGE' => '<b>' . $taskMessage['AUTHOR_NAME'] . '</b>: ' . $pregReplace,
                'UF_FORUM_MESSAGE_DOC' => $fileId,
            ],]);

        $db->query($sqlTask, (int)$tasks, (int)$sqlId, (int)$taskMessage['ID'], (int)$methodQuery['result']);

        return true;
    }


    public function getComment($method, $taskId, $itemId)
    {
        return $this->queryHelper->getQuery($method, 'task.commentitem.get', [
            'TASKID' => $taskId,
            'ITEMID' => $itemId,
        ]);
    }
}