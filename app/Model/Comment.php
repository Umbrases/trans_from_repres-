<?php

namespace App\Model;

use App\Model\Query;

class Comment
{

    public function setComment($sql_id, $task_message, $file_id, $db, $tasks, $sql_task, $author_id, $method)
    {
        $method_query = (new Query)->getQuery($method, 'task.commentitem.add', [
            'TASKID' => $sql_id,
            'fields' => [
                'AUTHOR_ID' => $author_id,
                'POST_MESSAGE' => '<b>' . $task_message['AUTHOR_NAME'] . '</b>: ' . preg_replace(array('/^\WUSER=\w{2,}\W/', '/\W{2}USER\W/'), '', $task_message['POST_MESSAGE']),
                'UF_FORUM_MESSAGE_DOC' => $file_id,
            ],]);

        $db->query($sql_task, (int)$tasks, (int)$sql_id, (int)$task_message['ID'], (int)$method_query['result']);

        return true;
    }


    public function getComment($method, $task_id, $item_id)
    {
        $task_message = (new Query)->getQuery($method, 'task.commentitem.get', [
            'TASKID' => $task_id,
            'ITEMID' => $item_id,
        ]);

        return $task_message;
    }
}