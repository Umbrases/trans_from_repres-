<?php

namespace App\Controller;

use App\Model\Comment;
use App\Model\CRestBox;
use App\Model\CRestCloud;
use App\Model\SafeMySQL;

class CommentsController
{
    private Comment $comment;

    public function __construct()
    {
        $this->comment = new Comment;
    }

    public function store($taskId, $itemId): void
    {
        $classFrom = CRestCloud::class;
        $classBefore = CRestBox::class;

        $columnFolderId = 502137;

        $taskMessage = $this->comment->getComment($classFrom, $taskId, $itemId);

        $this->comment->saveComment($classFrom, $classBefore, $taskMessage['result'], $columnFolderId, $taskId);
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