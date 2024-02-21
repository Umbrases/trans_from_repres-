<?php

namespace App\Model;

use App\Service\CommentService;
use App\Service\TaskService;

class Webhooks
{

    private TaskService $taskService;
    private CommentService $commentService;
    private QueryHelper $queryHelper;
    private SafeMySQL $safeMySQL;

    public function __construct()
    {
        $this->taskService = new TaskService;
        $this->queryHelper = new QueryHelper;
        $this->commentServiяce = new CommentService;
        $this->safeMySQL = new SafeMySQL;
    }

    public function setOnTask($event, $methodFrom, $methodBefore, $folderId, $taskId, $taskMessage, $city): void
    {
        //Вывод задачи
        $task = $this->taskService->getTask($methodFrom, $taskId);

        //Вывод сделки
        $dealId = $task->getDealId();


        $fileTaskIds = [];
        $fileMessageId = [];

        //Проверка на файл в задаче
        if (!empty($task->getTaskFile())) {
            foreach ($task->getTaskFile() as $taskFile) {
                //Вывод файла
                $fileTask = $this->queryHelper->getQuery($methodFrom, 'disk.attachedObject.get', [
                    'id' => $taskFile,
                ]);

                //Считывание файла в строку
                $fileTaskContent = file_get_contents(str_replace(' ', '%20', $fileTask['result']['DOWNLOAD_URL']));

                //Запись файла в битрикс
                $fileUploadTask = $this->taskService->setFile(
                    $methodBefore,
                    $folderId,
                    $fileTaskContent,
                    $fileTask
                );

                //Добавить id в переменную
                $fileTaskIds[] .= 'n' . $fileUploadTask['result']['ID'];
            }
        }

        //Проверка на файл в коментарии
        if (!empty($taskMessage['result']['ATTACHED_OBJECTS'])) {
            foreach ($taskMessage['result']['ATTACHED_OBJECTS'] as $attached) {
                //Вывод файла
                $fileMessage = $this->queryHelper->getQuery($methodFrom, 'disk.file.get', [
                    'id' => $attached['FILE_ID'],
                ]);

                //Считывание файла в строку
                $fileMessageContent = file_get_contents(str_replace(' ', '%20', $fileMessage['result']['DOWNLOAD_URL']));

                //Запись файла в битрикс
                $fileUploadMessage = $this->taskService->setFile(
                    $methodBefore,
                    $folderId,
                    $fileMessageContent,
                    $fileMessage
                );

                //Добавить id в переменную
                $fileMessageId[] .= 'n' . $fileUploadMessage['result']['ID'];
            }
        }

        //Проверка на то, какой метод используется
        if (!in_array($event, ['ONTASKCOMMENTADD', 'ONTASKADD', 'ONTASKUPDATE'], true)) return;

        //Проверка на город и запись в переменную
        $columnSelectTask = $city == "tula" ? 'task_ufa' : 'task_tula';
        $columnWhereTask = $city == "tula" ? 'task_tula' : 'task_ufa';
        $columnSelectDeal = $city == "tula" ? 'deal_ufa' : 'deal_tula';
        $columnWhereDeal = $city == "tula" ? 'deal_tula' : 'deal_ufa';
        $columnSelectComment = $city == "tula" ? 'comment_ufa' : 'comment_tula';
        $columnWhereComment = $city == "tula" ? 'comment_tula' : 'comment_ufa';

        //sql запросы
        $sqlFrom = $this->safeMySQL->getRow("SELECT * FROM det_comment where {$columnWhereComment} = ?i", (int)$taskMessage['result']['ID']);
        $sqlBeforeId = $this->safeMySQL->getRow("SELECT {$columnSelectTask} FROM det_task where {$columnWhereTask} = ?i", (int)$taskId);
        $sqlDealBeforeId = $this->safeMySQL->getRow("SELECT {$columnSelectDeal} FROM det_deal where {$columnWhereDeal} = ?i", $dealId);
        $sqlTask = "INSERT INTO det_task SET {$columnSelectDeal} = ?i, {$columnWhereDeal} = ?i, {$columnWhereTask} = ?i";
        $sqlTaskComment = "INSERT INTO det_comment SET {$columnWhereTask} = ?i, {$columnSelectTask} = ?i, {$columnWhereComment} = ?i, {$columnSelectComment} = ?i";
        $sqlUpdateTask = "UPDATE det_task SET {$columnSelectTask} = ?i WHERE {$columnWhereTask} = ?i";
        $sqlCount = "SELECT * FROM det_task where {$columnWhereTask} = ?i";

        $columnResponsibleId = $city == "tula" ? 13348 : $this->queryHelper->getQuery($methodBefore,
            'crm.deal.get', [
                'ID' => $sqlDealBeforeId,
            ])['result']['ASSIGNED_BY_ID'];
        $columnCreateBy = $city == "tula" ? 23286 : 1125;
        $columnTaskResponsibleID = $city == "tula" ? 1125 : 23286;
        $columnAuthorId = $city == "tula" ? 23286 : 1125;

        //Проверка на пустоту записи сделки в бд
        if (empty($sqlDealBeforeId)) return;
        if ($event == 'ONTASKADD') {
            if (!empty($sqlBeforeId)) if ($task->getResponsibleId() !== $columnTaskResponsibleID) return;

            $sqlCityCount = $this->safeMySQL->getAll($sqlCount, (int)$taskId);

            if (count($sqlCityCount) != 0) return;

            $this->safeMySQL->query($sqlTask, (int)$sqlDealBeforeId[$columnSelectDeal] , $dealId, (int)$taskId);
            $this->taskService->setTask( 
                $task,
                $sqlDealBeforeId['deal_ufa'],
                $fileTaskIds,//Ошибка
                $taskId,
                $columnResponsibleId,
                $columnCreateBy,
                $methodBefore,
                $sqlUpdateTask
            );
        } elseif ($event == 'ONTASKCOMMENTADD') {
            writeToLog(1);
            if (empty($sqlBeforeId)) if (!empty($sqlFrom)) {
                $messageObserver = strpos($taskMessage['result']['POST_MESSAGE'], 'вы добавлены наблюдателем');
                $messageResponible = strpos($taskMessage['result']['POST_MESSAGE'], 'вы назначены ответственным');

                if ($messageObserver !== false || $messageResponible !== false) return;//Ошибка
            }
            writeToLog($sqlBeforeId);
            writeToLog($taskMessage['result']);
            writeToLog($fileMessageId);
            writeToLog($taskId);
            writeToLog($sqlTaskComment);
            writeToLog($columnAuthorId);
            writeToLog($methodBefore);
            $this->commentService->setComment(//Ошибка
                $sqlBeforeId,
                $taskMessage['result'],
                $fileMessageId,
                $taskId,
                $sqlTaskComment,
                $columnAuthorId,
                $methodBefore
            );
        } elseif ($event == 'ONTASKUPDATE') {
            // if ($task->getChangedBy() == $columnTaskResponsibleID) return;
            if (!empty($sqlBeforeId)) {
                $this->taskService->updateTask(
                    $task,
                    $taskMessage['result']['is_task_result'],
                    $methodBefore,
                    $sqlBeforeId[$columnSelectTask]
                );
            } else {
                if ($task->getResponsibleId() !== $columnTaskResponsibleID) return;

                $sqlUfaCount = $this->safeMySQL->getAll($sqlCount, (int)$taskId);

                if (count($sqlUfaCount) !== 0) return; 

                $this->safeMySQL->query($sqlTask, $dealId, (int)$sqlDealBeforeId, (int)$taskId);
                $this->taskService->setTask(
                    $task,
                    $sqlDealBeforeId,
                    $fileTaskIds,
                    $taskId,
                    $columnResponsibleId,
                    $columnCreateBy,
                    $methodBefore,
                    $sqlUpdateTask
                );

            }

        }
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