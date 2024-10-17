<?php

namespace App\Model;

use App\Controller\TasksController;
use App\Model\QueryHelper;
use App\Model\CRestTula;
use App\Model\CRest;
use App\Service\TaskService;
use App\Controller\CommentsController;

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
        $userId = $this->getCheckBoxUser($classBefore, $authorId);
        $pregReplace = preg_replace(array('/^\WUSER=\w{2,}\W/', '/\W{2}USER\W/'), '', $taskMessage['POST_MESSAGE']);
        $methodQuery = QueryHelper::getQuery($classBefore, 'task.commentitem.add', [
            'TASKID' => $sqlId,
            'fields' => [
                'AUTHOR_ID' => $userId,
                'POST_MESSAGE' => '<b>' . $taskMessage['AUTHOR_NAME'] . '</b>: ' . $pregReplace,
                'UF_FORUM_MESSAGE_DOC' => $fileId,
            ],]);

        writeToLog($methodQuery);
        $safeMySQL->query($sqlTask, (int)$tasks, (int)$sqlId, (int)$taskMessage['ID'], (int)$methodQuery['result']);
    }

    public static function getCheckBoxUser($classBefore, $userId)
    {
        $user = QueryHelper::getQuery($classBefore, 'user.get', ['ID' => $userId]);
//        print_r($user);
        if($user['result'][0]['ACTIVE'] == true && !empty($user['result'][0]['ACTIVE'])
            || $user['result']['ACTIVE'] == true && !empty($user['result']['ACTIVE'])){
            return $userId;
        } else {
            return 1;
        }
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

        $sqlFrom = $safeMySQL->getRow("SELECT * FROM det_comment where `comment_cloud` = ?i", (int)$taskMessage['ID']);
        $sqlBeforeId = $safeMySQL->getRow("SELECT `task_box` FROM det_task where `task_cloud` = ?i", (int)$taskId)['task_box'];
        $sqlTaskComment = "INSERT INTO det_comment SET task_cloud = ?i, task_box = ?i, comment_cloud = ?i, comment_box = ?i";
        $columnAuthorId = $safeMySQL
            ->getRow("SELECT `new_id` FROM users where `old_id` = ?i", $taskMessage['AUTHOR_ID'])['new_id'];

        if (empty($sqlBeforeId)) {
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

    public function updateOnTask($classFrom, $classBefore, $taskId)
    {
        $safeMySQL = new SafeMySQL;

        $taskCloudId = $safeMySQL->getRow("SELECT `task_cloud` FROM det_task where task_box = ?i", (int)$taskId)['task_cloud'];

        $commentCloudList = $this->getCommentList($classBefore, $taskCloudId);

        foreach ($commentCloudList as $commentCloud) {
            $commentBoxId = $safeMySQL->getRow("SELECT `comment_box` FROM det_comment where `comment_cloud` = ?i", $commentCloud['ID'])['comment_box'];

            if (empty($commentBoxId)){
                $commentController = new CommentsController();
                $commentController->store($taskCloudId, $commentCloud['ID']);
            } else {
                QueryHelper::getQuery($classBefore, 'task.commentitem.update', [
                    'TASKID' => $taskId,
                    'ITEMID' => $commentBoxId,
                    'fields' => $commentCloud]);
            }
        }
    }

    public function getCommentList($class, $taskId)
    {
        $safeMySQL = new SafeMySQL;

        $methodQuery = QueryHelper::getQuery($class, 'task.commentitem.getlist', [
            'TASKID' => $taskId,
        ]);

        foreach ($methodQuery['result'] as $keyResult => $result) {
            foreach ($result as $key => $value) {
                $comment[$keyResult][strtoupper($key)] = match ($key) {
                    'AUTHOR_ID' => $class == 'App\Model\CRestCloud' ? $safeMySQL->getRow("SELECT `new_id` FROM users where `old_id` = ?i", $value)['new_id'] : $value,
                    default => $value,
                };
            }
        }

        return $comment;
    }


}
