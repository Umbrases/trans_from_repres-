<?php

namespace App\Service;

use App\Model\QueryHelper;
use App\Model\SafeMySQL;
use App\Model\Task;

class TaskService
{
    private QueryHelper $queryHelper;
    private SafeMySQL $safeMySQL;

    public function __construct()
    {
        $this->queryHelper = new QueryHelper;
        $this->safeMySQL = new SafeMySQL;
    }

    public function setTask(Task $task, $sqlDealId, $fileTaskTulaId, $tasks, $responsibleId, $createBy, $method, $sqlUpdateTask)
    {
        $method_query = $this->queryHelper->getQuery($method, 'tasks.task.add', [
            'fields' => [
                'TITLE' => $task->getTitle(),
                'DESCRIPTION' => $task->getDescription(),
                'RESPONSIBLE_ID' => $responsibleId,
                'CREATED_BY' => $createBy,
                'UF_CRM_TASK' => ['D_' . $sqlDealId],
                'START_DATE_PLAN' => $task->getStartDatePlan(),
                'DEADLINE' => $task->getDeadline(),
                'UF_TASK_WEBDAV_FILES' => $fileTaskTulaId,
                'ALLOW_CHANGE_DEADLINE' => $task->getAllowChangeDeadline(),
            ],]);

        $this->safeMySQL->query($sqlUpdateTask, (int)$method_query['result']['task']['id'], (int)$tasks);

        return true;
    }

    public function getTask($method, $tasks): Task
    {
        $responseArray = $this->queryHelper->getQuery($method, 'tasks.task.get', [
            'taskId' => $tasks,
            'select' => [
                'ID', 'TITLE', 'DESCRIPTION', 'UF_CRM_TASK', 'DEADLINE', 'START_DATE_PLAN', 'RESPONSIBLE_ID', 'CHANGED_BY', 'STATUS', 'ALLOW_CHANGE_DEADLINE', 'ufTaskWebdavFiles'
            ],
        ]);

        return $this->buildTaskFromResponseArray($responseArray);
    }

    private function buildTaskFromResponseArray(array $responseArray): Task
    {
        $task = new Task();
        $task->setId($responseArray['result']['task']['ID']);
        $task->setDescription($responseArray['result']['task']['DESCRIPTION']);
        $task->setDealId($responseArray['result']['task']['ufCrmTask'][0]);
        $task->setDeadline($responseArray['result']['task']['DEADLINE']);
        $task->setStartDatePlan($responseArray['result']['task']['START_DATE_PLAN']);
        $task->setResponsibleId($responseArray['result']['task']['RESPONSIBLE_ID']);
        $task->setChangedBy($responseArray['result']['task']['CHANGED_BY']);
        $task->setStatus($responseArray['result']['task']['STATUS']);
        $task->setAllowChangeDeadline($responseArray['result']['task']['ALLOW_CHANGE_DEADLINE']);
        $task->setUfTaskWebdavFiles($responseArray['result']['task']['ufTaskWebdavFiles']);

        return $task;
    }

    public function setFile($method, $folderId, $fileContent, $file)
    {
        return $this->queryHelper->getQuery($method, 'disk.folder.uploadfile', [
            'id' => $folderId,
            'data' => [
                'NAME' => $file['result']['NAME']
            ],
            'fileContent' => [$file['result']['NAME'], base64_encode($fileContent)],
            'generateUniqueName' => true,
        ]);
    }

    public function updateTask($task, $taskMessage, $method, $sqlCityId)
    {
        $this->queryHelper->getQuery($method, 'tasks.task.update', [
            'taskId' => $sqlCityId,
            'fields' => [
                'TITLE' => $task['title'],
                'DESCRIPTION' => $task['description'],
                'STATUS' => $task['status'],
                'IS_TASK_RESULT' => $taskMessage,
                'DEADLINE' => $task['deadline'],
            ]]);

        return true;
    }

}