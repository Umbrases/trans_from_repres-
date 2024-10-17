<?php

namespace App\Service;

use App\Model\QueryHelper;
use App\Model\Task;
use App\Model\SafeMySQL;

class TaskService
{

    public function setTask($task, $classBefore, $sqlDealId, $fileTaskId, $tasks, $responsibleId, $createBy, $sqlUpdateTask)
    {
        $safeMySQL = new SafeMySQL;

        $methodQuery = QueryHelper::getQuery($classBefore, 'tasks.task.add', [
            'fields' => [
                'TITLE' => $task['title'],
                'DESCRIPTION' => $task['description'],
                'RESPONSIBLE_ID' => $responsibleId,
                'CREATED_BY' => $createBy,
                'UF_CRM_TASK' => ['D_' . $sqlDealId],
                'START_DATE_PLAN' => $task['startDatePlan'],
                'DEADLINE' => $task['deadline'],
                'UF_TASK_WEBDAV_FILES' => $fileTaskId,  //Ошибка
                'ALLOW_CHANGE_DEADLINE' => $task['allowChangeDeadline'],
            ],]);

        $safeMySQL->query($sqlUpdateTask, (int)$methodQuery['result']['task']['id'], (int)$tasks);
    }

    public function getTask($class, $tasks)
    {
        $responseArray = QueryHelper::getQuery($class, 'tasks.task.get', [
            'taskId' => $tasks,
            'select' => [
                'ID', 'TITLE', 'DESCRIPTION', 'UF_CRM_TASK', 'DEADLINE', 'START_DATE_PLAN', 'RESPONSIBLE_ID', 'CREATED_BY', 'CHANGED_BY', 'STATUS', 'ALLOW_CHANGE_DEADLINE'
            ],
        ]);

        return $this->buildTaskFromResponseArray($responseArray);
    }

    private function buildTaskFromResponseArray(array $responseArray)
    {
        $task = [];

        foreach ($responseArray['result']['task'] as $key => $value) {
            $task[strtoupper($key)] = match ($key) {
                'ufCrmTask' => trim($value[0], 'D_'),
                default => $value,
            };
        }

        return $task;
    }

    public function setFile($classBefore, $folderId, $fileContent, $file)
    {
        return QueryHelper::getQuery($classBefore, 'disk.folder.uploadfile', [
            'id' => $folderId,
            'data' => [
                'NAME' => $file['result']['NAME']
            ],
            'fileContent' => [$file['result']['NAME'], base64_encode($fileContent)],
            'generateUniqueName' => true,
        ]);
    }

    public function updateTask($task, $classBefore, $sqlCityId, $fileTaskIds)
    {
        $fields = [];
//        writeToLog($task);
        foreach ($task as $key => $value) {
//            writeToLog($value);
            if (!empty($key) || !empty($value)) $fields[$key] = $value;
        }
        $fields['UF_TASK_WEBDAV_FILES'] = $fileTaskIds;
//        writeToLog($fields);

        $methodQuery = QueryHelper::getQuery($classBefore, 'tasks.task.update', [
            'taskId' => $sqlCityId,
            'fields' => $fields
        ]);
//        writeToLog($methodQuery);
        if (!empty($methodQuery['error'])) {
            echo json_encode($this->error('has'), JSON_UNESCAPED_UNICODE);
        } else {
            echo json_encode($this->error('success'), JSON_UNESCAPED_UNICODE);
        }
    }

    public function getDealVip($classBefore, $dealId)
    {
        return QueryHelper::getQuery($classBefore,
            'crm.deal.get', [
                'ID' => $dealId,
            ])['result'];
    }

    public function updateFieldVip($classBefore, $taskId)
    {
        QueryHelper::getQuery($classBefore, 'tasks.task.update', [
            'taskId' => $taskId,
            'fields' => [
                'UF_AUTO_561710307937' => 1
            ]]);
    }

    public function getDeviations($taskBox, $taskCloud)
    {
        $response = [];
        foreach ($taskBox as $keyBox => $valueBox) {
            foreach ($taskCloud as $keyCloud => $valueCloud) {
//                if ($keyBox === 'id'
//                    || $keyBox === 'responsibleId'
//                    || $keyBox === 'createdBy'
//                    || $keyBox === 'ufCrmTask'
//                    || $keyBox === 'creator'
//                    || $keyBox === 'responsible'
//                    || $keyBox === 'changedBy') continue;
                if ($keyCloud === $keyBox && $valueBox != $valueCloud
                    ) $response[$keyBox] = $valueCloud;
            }
        }

        return $response;
    }

    private function error($code): array
    {
        $result = [];

        switch ($code) {
            case 'has':
                $result = [
                    'status' => 'error',
                    'message' => 'Поля пусты'
                ];
                break;
            case 'success':
                $result = [
                    'status' => 'success',
                ];
        }

        return $result;
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