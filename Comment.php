<?php

class Comment
{

    public function Create($sql_id, $task_message, $file_id, $db, $tasks, $sql_task, $author_id, $method)
    {
        $method_query = getQuery($method, 'task.commentitem.add', [
            'TASKID' => $sql_id,
            'fields' => [
                'AUTHOR_ID' => $author_id,
                'POST_MESSAGE' => '<b>' . $task_message['AUTHOR_NAME'] . '</b>: ' . preg_replace(array('/^\WUSER=\w{2,}\W/', '/\W{2}USER\W/'), '', $task_message['POST_MESSAGE']),
                'UF_FORUM_MESSAGE_DOC' => $file_id,
            ],]);

        $db->query($sql_task, (int)$tasks, (int)$sql_id, (int)$task_message['ID'], (int)$method_query['result']);

        return true;
    }


    public function getComment($method)
    {
        $task_message = getQuery($method, 'task.commentitem.get', [
            'TASKID' => $_REQUEST['data']['FIELDS_AFTER']['TASK_ID'],
            'ITEMID' => $_REQUEST['data']['FIELDS_AFTER']['ID'],
        ]);

        return $task_message;
    }
}