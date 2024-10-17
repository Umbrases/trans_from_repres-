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

    public function storeBox($taskId)
    {
        $classFrom = CRestBox::class;
        $classBefore = CRestCloud::class;

        $this->comment->updateOnTask($classFrom, $classBefore, $taskId);
    }
}