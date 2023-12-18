<?php

require_once (__DIR__ .'/crest_tula.php');
require_once (__DIR__ .'/crest_ufa.php');
require_once (__DIR__ .'/getQuery.php');
require_once (__DIR__ .'/SafeMySQL.php');

$db = new SafeMySQL();

$event = $_REQUEST['event'];

if ($event == 'ONTASKCOMMENTADD') {
	$tasks = $_REQUEST['data']['FIELDS_AFTER']['TASK_ID'];
}
$task = getQuery('CRestUfa', 'tasks.task.get', [
			'taskId' => $tasks,
			'select' => [
				'TITLE', 'DESCRIPTION', 'UF_CRM_TASK'
			],
]);

$deal_id = trim($task['result']['task']['ufCrmTask'][0], 'D_');
$deal = getQuery('CRestUfa', 'crm.deal.get',[
			'ID' => $deal_id,
			]
);

$task_message = getQuery('CRestUfa', 'task.commentitem.get', [
	'TASKID' => $_REQUEST['data']['FIELDS_AFTER']['TASK_ID'],
	'ITEMID' => $_REQUEST['data']['FIELDS_AFTER']['ID'],
]);



if (!empty($deal['result']['UF_CRM_1627447542'])){
	if ($event == 'ONTASKCOMMENTADD' || $event == 'ONTASKADD'|| $event == 'ONTASKUPDATE'){
			$sql_ufa = $db->getRow("SELECT * FROM det_comment where comment_ufa = ?i", (int)$task_message['result']['ID']);
			$sql_ufa_id = $db->getRow("SELECT * FROM det_task where task_ufa = ?i", (int)$tasks);

            if (empty($sql_ufa)) {
				if (!empty($sql_ufa_id['task_tula'])){
					$method_query = getQuery('CRestTula', 'task.commentitem.add',[
						'TASKID' => $sql_ufa_id['task_tula'],
						'fields' => [
							'AUTHOR_ID' => 1125,
							'POST_MESSAGE' => $task_message['result']['POST_MESSAGE'],
						],]);
					$sql_task = "INSERT INTO det_comment SET task_tula = ?i, task_ufa = ?i, comment_tula = ?i, comment_ufa = ?i";
					$db->query($sql_task, (int)$sql_ufa_id['task_tula'], (int)$tasks, (int)$method_query['result'], (int)$task_message['result']['ID']);	
				}
            }
    }
}


