<?php

namespace App\Service;

use App\Model\QueryHelper;
use App\Model\Task;
use App\Model\SafeMySQL;
use App\Model\CRestTula;
use App\Model\CRestUfa;

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
        $methodQuery = $this->queryHelper->getQuery($method, 'tasks.task.add', [
            'fields' => [
                'TITLE' => $task->getTitle(),
                'DESCRIPTION' => $task->getDescription(),
                'RESPONSIBLE_ID' => $responsibleId,
                'CREATED_BY' => $createBy,
                'UF_CRM_TASK' => ['D_' . $sqlDealId],
                'START_DATE_PLAN' => $task->getStartDatePlan(),
                'DEADLINE' => $task->getDeadline(),
                'UF_TASK_WEBDAV_FILES' => $fileTaskTulaId,  //Ошибка
                'ALLOW_CHANGE_DEADLINE' => $task->getAllowChangeDeadline(),
            ],]);

        $this->safeMySQL->query($sqlUpdateTask, (int)$methodQuery['result']['task']['id'], (int)$tasks);

        return true;
    }

    public function getTask($method, $tasks): Task
    {
        $responseArray = $this->queryHelper->getQuery($method, 'tasks.task.get', [
            'taskId' => $tasks,
            'select' => [
                'ID', 'TITLE', 'DESCRIPTION', 'UF_CRM_TASK', 'DEADLINE', 'START_DATE_PLAN', 'RESPONSIBLE_ID', 'CHANGED_BY', 'STATUS', 'ALLOW_CHANGE_DEADLINE'
            ],
        ]);

        return $this->buildTaskFromResponseArray($responseArray);
    }

    private function buildTaskFromResponseArray(array $responseArray): Task
    {
        $task = new Task();
        $task->setId($responseArray['result']['task']['id']);
        $task->setDealId($responseArray['result']['task']['ufCrmTask'][0]);
        $task->setResponsibleId($responseArray['result']['task']['responsibleId']);
        $task->setTaskFile($responseArray['result']['task']['ufTaskWebdavFiles']);
        $task->setDescription($responseArray['result']['task']['description']);
        $task->setDeadline($responseArray['result']['task']['deadline']);
        $task->setStartDatePlan($responseArray['result']['task']['startDatePlan']);
        $task->setChangedBy($responseArray['result']['task']['changedBy']);
        $task->setStatus($responseArray['result']['task']['status']);
        $task->setAllowChangeDeadline($responseArray['result']['task']['allowChangeDeadline']);
        $task->setTitle($responseArray['result']['task']['title']);

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

    public function updateTask(Task $task, $taskMessage, $method, $sqlCityId)
    {
        
        $methodQuery = $this->queryHelper->getQuery($method, 'tasks.task.update', [
            'taskId' => $sqlCityId,
            'fields' => [
                'TITLE' => $task->getTitle(),
                'DESCRIPTION' => $task->getDescription(),
                'STATUS' => $task->getStatus(),
                'IS_TASK_RESULT' => $taskMessage,
                'DEADLINE' => $task->getDeadline(),
            ]]);
            // writeToLog($methodQuery);
        return true;
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