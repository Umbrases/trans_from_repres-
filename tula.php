<?php
require_once(__DIR__ . '/crest_tula.php');
require_once(__DIR__ . '/crest_ufa.php');
require_once(__DIR__ . '/getQuery.php');
require_once(__DIR__ . '/SafeMySQL.php');
require_once(__DIR__ . '/Task.php');
require_once(__DIR__ . '/UpdateTask.php');
require_once(__DIR__ . '/Comment.php');

$db = new SafeMySQL();


$tula = new Tula();
$tula->Event($db);

class Tula
{

    public function Event($db)
    {
        $Task = new Task();
        $Comment = new Comment();
        $UpdateTask = new UpdateTask();
        $event = $_REQUEST['event'];
        $method = 'CRestTula';
        $method_ufa = 'CRestUfa';
        $folder_id = 1740432;

        if ($event == 'ONTASKADD' || $event == 'ONTASKUPDATE') {
            $tasks = $_REQUEST['data']['FIELDS_AFTER']['ID'];
        } elseif ($event == 'ONTASKCOMMENTADD') {
            $tasks = $_REQUEST['data']['FIELDS_AFTER']['TASK_ID'];
        }

        $task = $Task->getTask($method, $tasks);

        $deal_id = trim($task['result']['task']['ufCrmTask'][0], 'D_');

        $task_message = $Comment->getComment($method);

        if (!empty($task['result']['task']['ufTaskWebdavFiles'])) {
            foreach ($task['result']['task']['ufTaskWebdavFiles'] as $taskFile) {
                $file_task = getQuery($method, 'disk.attachedObject.get', [
                    'id' => $taskFile,
                ]);

                $file_task = file_get_contents(str_replace(' ', '%20', $file_task['result']['DOWNLOAD_URL']));

                $file_upload_task = $Task->uploadFile($method_ufa, $folder_id, $file_task);
                $file_task_id[] .= 'n' . $file_upload_task['result']['ID'];
            }
        }

        if (!empty($task_message['result']['ATTACHED_OBJECTS'])) {
            foreach ($task_message['result']['ATTACHED_OBJECTS'] as $attached) {
                $file_message = getQuery($method, 'disk.file.get', [
                    'id' => $attached['FILE_ID'],
                ]);

                $file_message = file_get_contents(str_replace(' ', '%20', $file_message['result']['DOWNLOAD_URL']));

                $file_upload_message = $Task->uploadFile($method_ufa, $folder_id, $file_message);
                $file_message_id[] .= 'n' . $file_upload_message['result']['ID'];
            }
        }

        if ($event == 'ONTASKCOMMENTADD' || $event == 'ONTASKADD' || $event == 'ONTASKUPDATE') {
            $sql_ufa = $db->getRow("SELECT * FROM det_comment where comment_tula = ?i", (int)$task_message['result']['ID']);
            $sql_tula_id = $db->getRow("SELECT * FROM det_task where task_tula = ?i", (int)$tasks);
            $sql_deal_tula_id = $db->getRow("SELECT * FROM det_deal where deal_tula = ?i", (int)$deal_id);
            $responsible_id = 13348;
            $create_by = 23286;

            if (!empty($sql_deal_tula_id)) {
                if ($event == 'ONTASKADD') {
                    $sql_task = "INSERT INTO det_task SET deal_ufa = ?i, deal_tula = ?i, task_tula = ?i";
                    $sql_update_task = "UPDATE det_task SET task_ufa = ?i WHERE task_tula = ?i";

                    if (empty($sql_tula_id)) {
                        if ($task['result']['task']['responsibleId'] == 1125) {
                            $sql_tula_count = $db->getAll("SELECT * FROM det_task where task_tula = ?i", (int)$tasks);

                            if (count($sql_tula_count) == 0) {
                                $db->query($sql_task, (int)$deal_id, (int)$sql_deal_tula_id['deal_ufa'], (int)$tasks);
                                $Task->Create($task['result']['task'], $sql_deal_tula_id['deal_ufa'], $file_task_id, $db, $tasks, $responsible_id, $create_by, $method_ufa, $sql_update_task);
                            }
                        }
                    }
                } elseif ($event == 'ONTASKCOMMENTADD') {
                    $sql_task = "INSERT INTO det_comment SET task_tula = ?i, task_ufa = ?i, comment_tula = ?i, comment_ufa = ?i";
                    $author_id = 23286;

                    if (!empty($sql_tula_id['task_ufa'])) {
                        if (empty($sql_ufa)) {
                            if (strpos($task_message['result']['POST_MESSAGE'], 'вы добавлены наблюдателем') == false || strpos($task_message['result']['POST_MESSAGE'], 'вы назначены ответственным') == false) {
                                $Comment->Create($sql_tula_id['task_ufa'], $task_message['result'], $file_message_id, $db, $tasks, $sql_task, $author_id, $method_ufa);
                            }
                        }
                    }
                } elseif ($event == 'ONTASKUPDATE') {
                    $sql_insert_task = "INSERT INTO det_task SET deal_tula = ?i, deal_ufa = ?i, task_tula = ?i";
                    $sql_update_task = "UPDATE det_task SET task_ufa = ?i WHERE task_tula = ?i";
                    $changed_by = 1125;

                    if ($task['result']['task']['changedBy'] != $changed_by) {
                        if (!empty($sql_city_id)) {
                        $UpdateTask->Update($task['result']['task'], $task_message['result']['is_task_result'], $method, $sql_tula_id['task_ufa']);
                        } else {
                            if ($task['result']['task']['responsibleId'] == $changed_by) {
                                $sql_tula_count = $db->getAll("SELECT * FROM det_task where task_tula = ?i", (int)$tasks);

                                if (count($sql_tula_count) == 0) {
                                    $db->query($sql_insert_task, (int)$deal_id, (int)$sql_deal_tula_id['deal_ufa'], (int)$tasks);
                                    $Task->Create($task['result']['task'], $sql_deal_tula_id['deal_ufa'], $file_task_id, $db, $tasks, $responsible_id, $create_by, $method_ufa, $sql_update_task);
                                }
                            }
                        }
                    }
                }
            }
        }
    }

}


// function writeToLog($data) {
//     $log = "\n------------------------\n";
//     $log .= date("Y.m.d G:i:s") . "\n";
//     $log .= print_r($data, 1);
//     $log .= "\n------------------------\n";
//     file_put_contents(getcwd() . '/hook.log', $log, FILE_APPEND);
//     return true;
// }