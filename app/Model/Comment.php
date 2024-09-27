<?php

namespace App\Model;

use App\Controller\TasksController;
use App\Model\QueryHelper;
use App\Model\CRestTula;
use App\Model\CRest;
use App\Service\TaskService;

class Comment
{

    private TaskService $taskService;

    public function __construct()
    {
        $this->taskService = new TaskService;
    }

    public function setComment($sqlId, $taskMessage, $classBefore, $fileId, $tasks, $sqlTask, $authorId): void
    {
        $safeMySQL = new SafeMySQL;

        $pregReplace = preg_replace(array('/^\WUSER=\w{2,}\W/', '/\W{2}USER\W/'), '', $taskMessage['POST_MESSAGE']);
        $methodQuery = QueryHelper::getQuery($classBefore, 'task.commentitem.add', [
            'TASKID' => $sqlId,
            'fields' => [
                'AUTHOR_ID' => $authorId,
                'POST_MESSAGE' => '<b>' . $taskMessage['AUTHOR_NAME'] . '</b>: ' . $pregReplace,
                'UF_FORUM_MESSAGE_DOC' => $fileId,
            ],]);
        writeToLog($methodQuery);

        $safeMySQL->query($sqlTask, (int)$tasks, (int)$sqlId, (int)$taskMessage['ID'], (int)$methodQuery['result']);
    }

    public function getComment($classFrom, $taskId, $itemId)
    {
        return QueryHelper::getQuery($classFrom, 'task.commentitem.get', [
            'TASKID' => $taskId,
            'ITEMID' => $itemId,
        ]);
    }

    public function saveComment($classFrom, $classBefore, $taskMessage, $folderId, $taskId): void
    {
        $safeMySQL = new SafeMySQL;
        $taskService = new TaskService;

        $sqlFrom = $safeMySQL->getRow("SELECT * FROM det_comment where comment_cloud = ?i", (int)$taskMessage['ID']);
        $sqlBeforeId = $safeMySQL->getRow("SELECT task_box FROM det_task where task_cloud = ?i", (int)$taskId)['task_box'];
        $sqlTaskComment = "INSERT INTO det_comment SET task_cloud = ?i, task_box = ?i, comment_cloud = ?i, comment_box = ?i";
        $columnAuthorId = $safeMySQL
            ->getRow("SELECT `user_box` FROM det_user where `user_cloud` = ?i", $taskMessage['AUTHOR_ID'])['user_box'];

        if (empty($sqlBeforeId['task_box'])) {
            $taskController = new TasksController();
            $taskController->store($taskId);
        }

        $fileMessageId = [];

        $sqlFile = "INSERT INTO `det_file` SET `file_cloud_id` = ?i, `file_box_id` = ?i";
        //Проверка на файл в коментарии
        if (!empty($taskMessage['ATTACHED_OBJECTS'])) {
            foreach ($taskMessage['ATTACHED_OBJECTS'] as $attached) {
                $sqlFileSearch = $safeMySQL
                    ->getRow("SELECT `file_box_id` FROM `det_file` WHERE `file_cloud_id` = ?i", $attached['FILE_ID']);

                //Вывод файла
                $fileMessage = QueryHelper::getQuery($classFrom, 'disk.file.get', [
                    'id' => $attached['FILE_ID'],
                ]);

                //Считывание файла в строку
                $fileMessageContent = file_get_contents(str_replace(' ', '%20', $fileMessage['result']['DOWNLOAD_URL']));

                if (empty($sqlFileSearch)) {
                    //Запись файла в битрикс
                    $fileUploadMessage = $taskService->setFile(
                        $classBefore,
                        $folderId,
                        $fileMessageContent,
                        $fileMessage
                    );

                    $safeMySQL->query($sqlFile, $attached['FILE_ID'], $fileUploadMessage['result']['ID']);

                    //Добавить id в переменную
                    $fileMessageId[] .= 'n' . $fileUploadMessage['result']['ID'];
                } else {
                    //Добавить id в переменную
                    $fileMessageId[] .= 'n' . $sqlFileSearch['file_box_id'];
                }

                //Добавить id в переменную
                $fileMessageId[] .= 'n' . $fileUploadMessage['result']['ID'];
            }
        }

        $columnAuthorId = !empty($columnAuthorId) ? $columnAuthorId : 1;

        if (empty($sqlBeforeId)) if (!empty($sqlFrom)) {
            $messageObserver = strpos($taskMessage['POST_MESSAGE'], 'вы добавлены наблюдателем');
            $messageResponsible = strpos($taskMessage['POST_MESSAGE'], 'вы назначены ответственным');

            if ($messageObserver || $messageResponsible) return;//Ошибка
        }
        writeToLog(1);
        self::setComment(//Ошибка
            $sqlBeforeId,
            $taskMessage,
            $classBefore,
            $fileMessageId,
            $taskId,
            $sqlTaskComment,
            $columnAuthorId
        );
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