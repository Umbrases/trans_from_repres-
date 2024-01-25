<?php

namespace App\Model;

use App\Model\Query;

class Task
{

    public function Create($task, $sql_deal_id, $file_task_tula_id, $db, $tasks, $responsible_id, $create_by, $method, $sql_update_task)
    {
        $method_query = Query::getQuery($method, 'tasks.task.add', [
            'fields' => [
                'TITLE' => $task['title'],
                'DESCRIPTION' => $task['description'],
                'RESPONSIBLE_ID' => $responsible_id,
                'CREATED_BY' => $create_by,
                'UF_CRM_TASK' => ['D_' . $sql_deal_id],
                'START_DATE_PLAN' => $task['start_date_plan'],
                'DEADLINE' => $task['deadline'],
                'UF_TASK_WEBDAV_FILES' => $file_task_tula_id,
                'ALLOW_CHANGE_DEADLINE' => $task['allowChangeDeadline'],
            ],]);

        $db->query($sql_update_task, (int)$method_query['result']['task']['id'], (int)$tasks);

        return true;
    }

    public function getTask($method, $tasks)
    {
        $task = Query::getQuery($method, 'tasks.task.get', [
            'taskId' => $tasks,
            'select' => [
                'TITLE', 'DESCRIPTION', 'UF_CRM_TASK', 'DEADLINE', 'START_DATE_PLAN', 'RESPONSIBLE_ID', 'CHANGED_BY', 'STATUS', 'ALLOW_CHANGE_DEADLINE'
            ],
        ]);

        return $task;
    }

    public function uploadFile($method, $folder_id, $file_content, $file)
    {
        $file_upload_task = Query::getQuery($method, 'disk.folder.uploadfile', [
            'id' => $folder_id,
            'data' => [
                'NAME' => $file['result']['NAME']
            ],
            'fileContent' => [$file['result']['NAME'], base64_encode($file_content)],
            'generateUniqueName' => true,
        ]);

        return $file_upload_task;
    }

}