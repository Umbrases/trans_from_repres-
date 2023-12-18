<?php

require_once (__DIR__ .'/crest_tula.php');
require_once (__DIR__ .'/crest_ufa.php');
require_once (__DIR__ .'/getQuery.php');
require_once (__DIR__ .'/SafeMySQL.php');

$db = new SafeMySQL();

$event = $_REQUEST['event'];

if ($event == 'ONTASKADD' || $event == 'ONTASKUPDATE') {
	$tasks = $_REQUEST['data']['FIELDS_AFTER']['ID'];
} elseif ($event == 'ONTASKCOMMENTADD') {
	$tasks = $_REQUEST['data']['FIELDS_AFTER']['TASK_ID'];
}

$task = getQuery('CRestTula', 'tasks.task.get', [
			'taskId' => $tasks,
			'select' => [
				'TITLE', 'DESCRIPTION', 'UF_CRM_TASK'
			],
]);

$deal_id = trim($task['result']['task']['ufCrmTask'][0], 'D_');
$deal = getQuery('CRestTula', 'crm.deal.get',[
			'ID' => $deal_id,
			]
);

$task_message = getQuery('CRestTula', 'task.commentitem.get', [
	'TASKID' => $_REQUEST['data']['FIELDS_AFTER']['TASK_ID'],
	'ITEMID' => $_REQUEST['data']['FIELDS_AFTER']['ID'],
]);


if (!empty($deal['result']['UF_CRM_1664374736018'])){
	if ($event == 'ONTASKCOMMENTADD' || $event == 'ONTASKADD'|| $event == 'ONTASKUPDATE'){
		$sql_ufa = $db->getRow("SELECT * FROM det_comment where comment_tula = ?i", (int)$task_message['result']['ID']);
		$sql_tula_id = $db->getRow("SELECT * FROM det_task where task_tula = ?i", (int)$tasks);
		$sql_deal_tula_id = $db->getRow("SELECT * FROM det_deal where deal_tula = ?i", (int)$deal_id);

		if(!empty($sql_deal_tula_id)){
			if ($event == 'ONTASKADD') {
				$method_query = getQuery('CRestUfa', 'tasks.task.add', [
						'fields' => [
							'TITLE' => $task['result']['task']['title'],
							'DESCRIPTION' => $task['result']['task']['description'],
							'RESPONSIBLE_ID' => 23286,
							'CREATED_BY' => 23286,
							'UF_CRM_TASK' => ['D_' . $sql_deal_tula_id['deal_ufa']],
							'START_DATE_PLAN' => $task['result']['task']['start_date_plan'],
							'END_DATE_PLAN' => $task['result']['task']['end_date_plan'],
						],]);
						
				$sql_task = "INSERT INTO det_task SET deal_tula = ?i, deal_ufa = ?i, task_tula = ?i, task_ufa = ?i";
				$db->query($sql_task, (int)$deal_id, (int)$sql_deal_tula_id['deal_ufa'], (int)$tasks, (int)$method_query['result']['task']['id']);
			} elseif ($event == 'ONTASKCOMMENTADD') {
				if (!empty($sql_tula_id['task_ufa'])){
					$sql_tula = $db->getRow("SELECT * FROM det_comment where comment_tula = ?i", (int)$task_message['result']['ID']);
					if (empty($sql_ufa)){
					$method_query = getQuery('CRestUfa', 'task.commentitem.add',[
							'TASKID' => $sql_tula_id['task_ufa'],
							'fields' => [
								'AUTHOR_ID' => 23286,
								'POST_MESSAGE' => $task_message['result']['POST_MESSAGE'],
						],]);
					$sql_task = "INSERT INTO det_comment SET task_tula = ?i, task_ufa = ?i, comment_tula = ?i, comment_ufa = ?i";
					$db->query($sql_task, (int)$tasks, (int)$sql_tula_id['task_ufa'], (int)$task_message['result']['ID'], (int)$method_query['result']);
					}
			}
			} elseif ($event == 'ONTASKUPDATE') {
				$method_query = getQuery('CRestUfa', 'tasks.task.update', [
									'taskId' => $sql_tula_id['task_ufa'],
									'fields' => [
										'TITLE' => $task['result']['task']['title'],
										'DESCRIPTION' => $task['result']['task']['description'],
										'STATUS' => $task['result']['task']['status'],
										'IS_TASK_RESULT' => $task_message['result']['is_task_result'],
									]]);
			}		
		}
	}
}

