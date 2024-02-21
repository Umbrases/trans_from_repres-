<?php

namespace App\Service;

use App\Model\QueryHelper;
use App\Model\SafeMySQL;
use App\Model\CRestTula;
use App\Model\CRestUfa;

class CommentService
{
    private QueryHelper $queryHelper;
    private SafeMySQL $safeMySQL;

    public function __construct()
    {
        $this->queryHelper = new QueryHelper;
        $this->safeMySQL = new SafeMySQL;
    }

    public function setComment($sqlId, $taskMessage, $fileId, $tasks, $sqlTask, $authorId, $method)
    {
        writeToLog($sqlId);
        writeToLog($taskMessage);
        writeToLog($fileId);
        writeToLog($tasks);
        writeToLog($sqlTask);
        writeToLog($authorId);
        writeToLog($method);
        $pregReplace = preg_replace(array('/^\WUSER=\w{2,}\W/', '/\W{2}USER\W/'), '', $taskMessage['POST_MESSAGE']);
        $methodQuery = $this->queryHelper->getQuery($method, 'task.commentitem.add', [
            'TASKID' => $sqlId,
            'fields' => [
                'AUTHOR_ID' => $authorId,
                'POST_MESSAGE' => '<b>' . $taskMessage['AUTHOR_NAME'] . '</b>: ' . $pregReplace,
                'UF_FORUM_MESSAGE_DOC' => $fileId,
            ],]);

        $this->safeMySQL->query($sqlTask, (int)$tasks, (int)$sqlId, (int)$taskMessage['ID'], (int)$methodQuery['result']);

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
function writeToLog($data) {
    $log = "\n------------------------\n";
    $log .= date("Y.m.d G:i:s") . "\n";
    $log .= print_r($data, 1);
    $log .= "\n------------------------\n";
    file_put_contents(getcwd() . '/hook.log', $log, FILE_APPEND);
    return true;
}