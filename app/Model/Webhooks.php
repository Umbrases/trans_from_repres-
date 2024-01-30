<?php

namespace App\Model;

class Webhooks
{

    public function setOnTask($event, $method_from, $method_before, $folder_id, $tasks, $task_message, $city)
    {
        $db = SafeMySQL::class;
        $task = (new Task)->getTask($method_from, $tasks);

        $deal_id = trim($task['result']['task']['ufCrmTask'][0], 'D_');

        if (!empty($task['result']['task']['ufTaskWebdavFiles'])) {
            foreach ($task['result']['task']['ufTaskWebdavFiles'] as $taskFile) {
                $file_task = (new Query)->getQuery($method_from, 'disk.attachedObject.get', [
                    'id' => $taskFile,
                ]);

                $file_task_content = file_get_contents(str_replace(' ', '%20', $file_task['result']['DOWNLOAD_URL']));

                $file_upload_task = (new Task)->setFile($method_before, $folder_id, $file_task_content, $file_task);

                $file_task_id[] .= 'n' . $file_upload_task['result']['ID'];
            }
        }

        if (!empty($task_message['result']['ATTACHED_OBJECTS'])) {
            foreach ($task_message['result']['ATTACHED_OBJECTS'] as $attached) {
                $file_message = (new Query)->getQuery($method_from, 'disk.file.get', [
                    'id' => $attached['FILE_ID'],
                ]);

                $file_message_content = file_get_contents(str_replace(' ', '%20', $file_message['result']['DOWNLOAD_URL']));

                $file_upload_message = (new Task)->setFile($method_before, $folder_id, $file_message_content, $file_message);
                $file_message_id[] .= 'n' . $file_upload_message['result']['ID'];
            }
        }

        if ($event == 'ONTASKCOMMENTADD' || $event == 'ONTASKADD' || $event == 'ONTASKUPDATE') {
            if ($city == "tula") {
                $sql_from = $db->getRow("SELECT * FROM det_comment where comment_tula = ?i", (int)$task_message['result']['ID']);
                $sql_before_id = $db->getRow("SELECT task_ufa FROM det_task where task_tula = ?i", (int)$tasks);
                $sql_deal_before_id = $db->getRow("SELECT deal_ufa FROM det_deal where deal_tula = ?i", (int)$deal_id);

                $responsible_id = 13348;
                $create_by = 23286;
                $task_reponsible_id = 1125;
                $author_id = 23286;

                $sql_task = "INSERT INTO det_task SET deal_ufa = ?i, deal_tula = ?i, task_tula = ?i";
                $sql_task_comment = "INSERT INTO det_comment SET task_tula = ?i, task_ufa = ?i, comment_tula = ?i, comment_ufa = ?i";
                $sql_update_task = "UPDATE det_task SET task_ufa = ?i WHERE task_tula = ?i";
                $sql_count = "SELECT * FROM det_task where task_tula = ?i";
            } else {
                $sql_from = $db->getRow("SELECT * FROM det_comment where comment_ufa = ?i", (int)$task_message['result']['ID']);
                $sql_before_id = $db->getRow("SELECT task_tula FROM det_task where task_ufa = ?i", (int)$tasks);
                $sql_deal_before_id = $db->getRow("SELECT deal_tula FROM det_deal where deal_ufa = ?i", (int)$deal_id);

                $responsible_id = (new Query)->getQuery($method_before, 'crm.deal.get', [
                    'ID' => $sql_deal_before_id,
                ]);
                $create_by = 1125;
                $task_reponsible_id = 23286;
                $author_id = 1125;

                $sql_task = "INSERT INTO det_task SET deal_ufa = ?i, deal_tula = ?i, task_ufa = ?i";
                $sql_task_comment = "INSERT INTO det_comment SET task_ufa = ?i, task_tula = ?i, comment_ufa = ?i, comment_tula = ?i";
                $sql_update_task = "UPDATE det_task SET task_tula = ?i WHERE task_ufa = ?i";
                $sql_count = "SELECT * FROM det_task where task_ufa = ?i";
            }

            if (!empty($sql_deal_before_id)) {
                if ($event == 'ONTASKADD') {
                    if (empty($sql_before_id)) {
                        if ($task['result']['task']['responsibleId'] == $task_reponsible_id) {
                            $sql_city_count = $db->getAll($sql_count, (int)$tasks);

                            if (count($sql_city_count) == 0) {
                                $db->query($sql_task, (int)$deal_id, (int)$sql_deal_before_id, (int)$tasks);
                                (new Task)->setTask($task['result']['task'], $sql_deal_before_id, $file_task_id, $db, $tasks, $responsible_id, $create_by, $method_before, $sql_update_task);
                            }
                        }
                    }
                } elseif ($event == 'ONTASKCOMMENTADD') {
                    if (!empty($sql_before_id)) {
                        if (empty($sql_from)) {
                            if (strpos($task_message['result']['POST_MESSAGE'], 'вы добавлены наблюдателем') == false || strpos($task_message['result']['POST_MESSAGE'], 'вы назначены ответственным') == false) {
                                (new Comment)->setComment($sql_before_id, $task_message['result'], $file_message_id, $db, $tasks, $sql_task_comment, $author_id, $method_before);
                            }
                        }
                    }
                } elseif ($event == 'ONTASKUPDATE') {
                    if ($task['result']['task']['changedBy'] != $task_reponsible_id) {
                        if (!empty($sql_city_id)) {
                            (new UpdateTask)->updateTask($task['result']['task'], $task_message['result']['is_task_result'], $method_from, $sql_before_id);
                        } else {
                            if ($task['result']['task']['responsibleId'] == $task_reponsible_id) {
                                $sql_ufa_count = $db->getAll($sql_count, (int)$tasks);

                                if (count($sql_ufa_count) == 0) {
                                    $db->query($sql_task, (int)$deal_id, (int)$sql_deal_before_id, (int)$tasks);
                                    (new Task)->setTask($task['result']['task'], $sql_deal_before_id, $file_task_id, $db, $tasks, $responsible_id['result']['ASSIGNED_BY_ID'], $create_by, $method_before, $sql_update_task);
                                }
                            }
                        }
                    }
                }
            }
        }
    }
}