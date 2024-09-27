<?php

namespace App\Service;

use App\Model\QueryHelper;
use App\Model\Task;
use App\Model\SafeMySQL;

class TaskService
{

    public function setTask(Task $task, $classBefore, $sqlDealId, $fileTaskId, $tasks, $responsibleId, $createBy, $sqlUpdateTask)
    {
        $safeMySQL = new SafeMySQL;

        $methodQuery = QueryHelper::getQuery($classBefore, 'tasks.task.add', [
            'fields' => [
                'TITLE' => $task->getTitle(),
                'DESCRIPTION' => $task->getDescription(),
                'RESPONSIBLE_ID' => $responsibleId,
                'CREATED_BY' => $createBy,
                'UF_CRM_TASK' => ['D_' . $sqlDealId],
                'START_DATE_PLAN' => $task->getStartDatePlan(),
                'DEADLINE' => $task->getDeadline(),
                'UF_TASK_WEBDAV_FILES' => $fileTaskId,  //Ошибка
                'ALLOW_CHANGE_DEADLINE' => $task->getAllowChangeDeadline(),
            ],]);

        $safeMySQL->query($sqlUpdateTask, (int)$methodQuery['result']['task']['id'], (int)$tasks);
    }

    public function getTask($class, $tasks): Task
    {
        $responseArray = QueryHelper::getQuery($class, 'tasks.task.get', [
            'taskId' => $tasks,
            'select' => [
                'ID', 'TITLE', 'DESCRIPTION', 'UF_CRM_TASK', 'DEADLINE', 'START_DATE_PLAN', 'RESPONSIBLE_ID', 'CREATED_BY', 'CHANGED_BY', 'STATUS', 'ALLOW_CHANGE_DEADLINE'
            ],
        ]);

        return $this->buildTaskFromResponseArray($responseArray);
    }

    private function buildTaskFromResponseArray(array $responseArray): Task
    {
        $task = new Task();

        $task->setId($responseArray['result']['task']['id']);
        if (!empty($responseArray['result']['task']['ufCrmTask'][0])) {
            $task->setDealId($responseArray['result']['task']['ufCrmTask'][0]);
        } else {
            $task->setDealId(null);
        }
        $task->setResponsibleId($responseArray['result']['task']['responsibleId']);
        $task->setCreatedBy($responseArray['result']['task']['createdBy']);
        if (!empty($responseArray['result']['task']['ufTaskWebdavFiles'])) {
            $task->setTaskFile($responseArray['result']['task']['ufTaskWebdavFiles']);
        }
        $task->setDescription($responseArray['result']['task']['description']);
        $task->setDeadline($responseArray['result']['task']['deadline']);
        $task->setStartDatePlan($responseArray['result']['task']['startDatePlan']);
        $task->setChangedBy($responseArray['result']['task']['changedBy']);
        $task->setStatus($responseArray['result']['task']['status']);
        $task->setAllowChangeDeadline($responseArray['result']['task']['allowChangeDeadline']);
        $task->setTitle($responseArray['result']['task']['title']);

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

    public function updateTask(Task $task, $classBefore, $sqlCityId, $fileTaskIds): void
    {
        $methodQuery = QueryHelper::getQuery($classBefore, 'tasks.task.update', [
            'taskId' => $sqlCityId,
            'fields' => [
                'TITLE' => $task->getTitle(),
                'DESCRIPTION' => $task->getDescription(),
                'STATUS' => $task->getStatus(),
                'UF_TASK_WEBDAV_FILES' => $fileTaskIds,  //Ошибка
                'DEADLINE' => $task->getDeadline(),
            ]]);
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