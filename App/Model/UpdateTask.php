<?php

namespace App\Model;

use App\Model\Query;

class UpdateTask
{

    public function Update($task, $task_message, $method, $sql_city_id)
    {
        Query::getQuery($method, 'tasks.task.update', [
            'taskId' => $sql_city_id,
            'fields' => [
                'TITLE' => $task['title'],
                'DESCRIPTION' => $task['description'],
                'STATUS' => $task['status'],
                'IS_TASK_RESULT' => $task_message,
                'DEADLINE' => $task['deadline'],
            ]]);

        return true;
    }
}