<?php

namespace App\Service;

use App\Model\QueryHelper;
use App\Model\Task;

class TaskService
{
    private QueryHelper $queryHelper;

    public function __construct()
    {
        $this->queryHelper = new QueryHelper;
    }

    public function setTask($task, $sqlDealId, $fileTaskTulaId, $db, $tasks, $responsibleId, $createBy, $method, $sqlUpdateTask)
    {
        $method_query = $this->queryHelper->getQuery($method, 'tasks.task.add', [
            'fields' => [
                'TITLE' => $task['title'],
                'DESCRIPTION' => $task['description'],
                'RESPONSIBLE_ID' => $responsibleId,
                'CREATED_BY' => $createBy,
                'UF_CRM_TASK' => ['D_' . $sqlDealId],
                'START_DATE_PLAN' => $task['start_date_plan'],
                'DEADLINE' => $task['deadline'],
                'UF_TASK_WEBDAV_FILES' => $fileTaskTulaId,
                'ALLOW_CHANGE_DEADLINE' => $task['allowChangeDeadline'],
            ],]);
        writeToLog($method_query);

        $db->query($sqlUpdateTask, (int)$method_query['result']['task']['id'], (int)$tasks);

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

function writeToLog($data) {
    $log = "\n------------------------\n";
    $log .= date("Y.m.d G:i:s") . "\n";
    $log .= print_r($data, 1);
    $log .= "\n------------------------\n";
    file_put_contents(getcwd() . '/hook.log', $log, FILE_APPEND);
    return true;
}