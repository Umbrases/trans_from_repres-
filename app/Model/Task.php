<?php

namespace App\Model;

use App\Service\DealService;
use App\Service\TaskService;
use App\Model\QueryHelper;
use App\Controller\DealController;
use App\Controller\CommentsController;

class Task
{
    public function setOnTask($classFrom, $classBefore, $folderId, $taskId): void
    {
        $safeMySQL = new SafeMySQL;
        $taskService = new TaskService;

        //Вывод задачи
        $task = $taskService->getTask($classFrom, $taskId);

        //sql запросы
        $sqlBeforeId = $safeMySQL->getRow("SELECT `task_box` FROM det_task where task_cloud = ?i", (int)$taskId);
        $sqlDealBeforeId = $safeMySQL->getRow("SELECT `new_id` FROM deals where old_id = ?i", $task['ufCrmTask']);
        $sqlTask = "INSERT INTO det_task SET deal_box = ?i , deal_cloud = ?i, task_cloud = ?i";
        $sqlUpdateTask = "UPDATE det_task SET task_box = ?i WHERE task_cloud = ?i";
        $sqlCount = "SELECT * FROM det_task where task_cloud = ?i";
        $sqlFile = "INSERT INTO `det_file` SET `file_cloud_id` = ?i, `file_box_id` = ?i";

        $fileTaskIds = [];
        //Проверка на файл в задаче
        if (!empty($task['ufTaskWebdavFiles'])) {
            foreach ($task['ufTaskWebdavFiles'] as $taskFile) {
                $sqlFileSearch = $safeMySQL
                    ->getRow("SELECT `file_box_id` FROM `det_file` WHERE `file_cloud_id` = ?i", $taskFile);

                //Вывод файла
                $fileTask = QueryHelper::getQuery($classFrom, 'disk.attachedObject.get', [
                    'id' => $taskFile,
                ]);
                //Считывание файла в строку
                $fileTaskContent = file_get_contents(str_replace(' ', '%20', $fileTask['result']['DOWNLOAD_URL']));
                if (empty($sqlFileSearch)) {
                    //Запись файла в битрикс
                    $fileUploadTask = $taskService->setFile(
                        $classBefore,
                        $folderId,
                        $fileTaskContent,
                        $fileTask
                    );

                    $safeMySQL->query($sqlFile, $taskFile, $fileUploadTask['result']['ID']);

                    //Добавить id в переменную
                    $fileTaskIds[] .= 'n' . $fileUploadTask['result']['ID'];
                } else {
                    //Добавить id в переменную
                    $fileTaskIds[] .= 'n' . $sqlFileSearch['file_box_id'];
                }
            }
        }

        if (empty($sqlDealBeforeId)) {
            $dealController = new DealController();
            $dealController->store($task['ufCrmTask']);
            $sqlDealBeforeId = $safeMySQL->getRow("SELECT `old_id` FROM deals where new_id = ?i", $task['ufCrmTask']);
        }

        $columnResponsibleId = QueryHelper::getQuery($classBefore,
            'crm.deal.get', [
                'ID' => $sqlDealBeforeId['new_id'],
            ])['result']['ASSIGNED_BY_ID'];

        $columnCreateBy = $safeMySQL->getRow("SELECT 'new_id' FROM users where 'old_id' = ?i", $task['changedBy']);
        $columnCreateBy = !empty($columnCreateBy) ? $columnCreateBy : 1;

        //Проверка на пустоту записи сделки в бд
        if (!empty($sqlBeforeId)) {
            $taskService->updateTask(
                $task,
                $classBefore,
                $sqlBeforeId['task_box'],
                $fileTaskIds
            );
        } else {
            $sqlCityCount = $safeMySQL->getAll($sqlCount, (int)$taskId);

            if (count($sqlCityCount) != 0) return;
            $safeMySQL->query($sqlTask, $sqlDealBeforeId['new_id'], $task['ufCrmTask'], (int)$taskId);

            $taskBox = $taskService->setTask(
                $task,
                $classBefore,
                $sqlDealBeforeId['new_id'],
                $fileTaskIds,//Ошибка
                $taskId,
                $columnResponsibleId,
                $columnCreateBy,
                $sqlUpdateTask
            );
        }
    }

    public function setTaskVip($classBefore, $taskId)
    {
        $taskService = new TaskService;

        $task = $taskService->getTask($classBefore, $taskId);

        if (!empty($task['ufCrmTask'])) {
            $dealId = $task['ufCrmTask'];
        } else {
            return;
        }

        $deal = $taskService->getDealVip($classBefore, $dealId);

        if($deal['UF_CRM_1565694387'] == 1397 || str_contains(strtolower($deal['COMMENTS']), 'vip')){
            $taskService->updateFieldVip($classBefore, $taskId);
        }
    }

    public function updateOnTask($classFrom, $classBefore, $taskId)
    {
        $columnFolderId = 502137;
        $safeMySQL = new SafeMySQL;
        $taskService = new TaskService;

        $commentController = new CommentsController();
        $commentController->storeBox($taskId);

        //Вывод задачи
        $taskBox = $taskService->getTask($classFrom, $taskId);

        $taskCloudId = $safeMySQL->getRow("SELECT `task_cloud` FROM det_task where task_box = ?i", (int)$taskId)['task_cloud']; //ошибка

        $taskCloud = $taskService->getTask($classBefore, $taskCloudId);

        $taskDeviations = $taskService->getDeviations($taskBox, $taskCloud);


        if (empty($taskDeviations)) {
            $result = [
                'status' => 'success',
            ];
            echo json_encode($result, JSON_UNESCAPED_UNICODE);
            return;
        }

        $sqlFile = "INSERT INTO `det_file` SET `file_cloud_id` = ?i, `file_box_id` = ?i";
        $fileTaskIds = [];

        //Проверка на файл в задаче
        if (!empty($taskCloud['UFTASKWEBDAVFILES'])) {
            foreach ($taskCloud['UFTASKWEBDAVFILES'] as $taskFile) {
                $sqlFileSearch = $safeMySQL
                    ->getRow("SELECT `file_box_id` FROM `det_file` WHERE `file_cloud_id` = ?i", $taskFile);

                //Вывод файла
                $fileTask = QueryHelper::getQuery($classBefore, 'disk.attachedObject.get', [
                    'id' => $taskFile,
                ]);
                //Считывание файла в строку
                $fileTaskContent = file_get_contents(str_replace(' ', '%20', $fileTask['result']['DOWNLOAD_URL']));
                if (empty($sqlFileSearch)) {
                    //Запись файла в битрикс
                    $fileUploadTask = $taskService->setFile(
                        $classFrom,
                        $columnFolderId,
                        $fileTaskContent,
                        $fileTask
                    );

                    $safeMySQL->query($sqlFile, $taskFile, $fileUploadTask['result']['ID']);

                    //Добавить id в переменную
                    $fileTaskIds[] .= 'n' . $fileUploadTask['result']['ID'];
                } else {
                    //Добавить id в переменную
                    $fileTaskIds[] .= 'n' . $sqlFileSearch['file_box_id'];
                }
            }
        }

        $taskService->updateTask($taskDeviations, $classFrom, $taskId, $fileTaskIds);
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