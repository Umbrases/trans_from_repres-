<?php

require_once (__DIR__ .'/crest_tula.php');
require_once (__DIR__ .'/crest_ufa.php');
require_once (__DIR__ .'/getQuery.php');
require_once (__DIR__ .'/SafeMySQL.php');

$db = new SafeMySQL();

$event = $_REQUEST['event'];

writeToLog($_REQUEST);


if ($event == 'ONTASKADD' || $event == 'ONTASKUPDATE') {
	$tasks = $_REQUEST['data']['FIELDS_AFTER']['ID'];
} elseif ($event == 'ONTASKCOMMENTADD') {
	$tasks = $_REQUEST['data']['FIELDS_AFTER']['TASK_ID'];
}

$task = getQuery('CRestUfa', 'tasks.task.get', [
			'taskId' => $tasks,
			'select' => [
				'TITLE', 'DESCRIPTION', 'UF_CRM_TASK', 'DEADLINE', 'START_DATE_PLAN', 'RESPONSIBLE_ID', 'CHANGED_BY', 'STATUS', 'UF_TASK_WEBDAV_FILES', 'ALLOW_CHANGE_DEADLINE'
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


if (!empty($task['result']['task']['ufTaskWebdavFiles'])){
	foreach ($task['result']['task']['ufTaskWebdavFiles'] as $taskFile){
		$file_task_ufa = getQuery('CRestUfa', 'disk.attachedObject.get',[
			'id' => $taskFile,
			]);

		$file_task = file_get_contents(str_replace(' ', '%20', $file_task_ufa['result']['DOWNLOAD_URL']));

		$file_tula = getQuery('CRestTula', 'disk.folder.uploadfile',[
			'id' => 54657,
			'data' => [
				'NAME' => $file_task_ufa['result']['NAME']
			  ],
			'fileContent' => [$file_task_ufa['result']['NAME'], base64_encode($file_task)],
			'generateUniqueName' => true,
			]);
		$file_task_tula_id[] .= 'n' . $file_tula['result']['ID'];
	}
}

if(!empty($task_message['result']['ATTACHED_OBJECTS'])){
	foreach ($task_message['result']['ATTACHED_OBJECTS'] as $attached){
		$file_ufa = getQuery('CRestUfa', 'disk.file.get',[
			'id' => $attached['FILE_ID'],
			]);

		$file = file_get_contents(str_replace(' ', '%20', $file_ufa['result']['DOWNLOAD_URL']));

		$file_tula = getQuery('CRestTula', 'disk.folder.uploadfile',[
			'id' => 54657,
			'data' => [
				'NAME' => $attached['NAME']
			  ],
			'fileContent' => [$attached['NAME'], base64_encode($file)],
			'generateUniqueName' => true,
			]);
		$file_tula_id[] .= 'n' . $file_tula['result']['ID'];
	}
}


if ($event == 'ONTASKCOMMENTADD' || $event == 'ONTASKADD'|| $event == 'ONTASKUPDATE'){
	$sql_ufa = $db->getRow("SELECT * FROM det_comment where comment_ufa = ?i", (int)$task_message['result']['ID']);
	$sql_ufa_id = $db->getRow("SELECT * FROM det_task where task_ufa = ?i", (int)$tasks);
	$sql_deal_ufa_id = $db->getRow("SELECT * FROM det_deal where deal_ufa = ?i", (int)$deal_id);

	// writeToLog($sql_ufa);
	// writeToLog($sql_ufa_id);
	// writeToLog($sql_deal_ufa_id);


	if ($event == 'ONTASKADD') {
		if (empty($sql_ufa_id)){
			if ($task['result']['task']['responsibleId'] == 23286){
				$RESPONSIBLE_ID = getQuery('CRestTula', 'crm.deal.get', [
					'ID' => $sql_deal_ufa_id['deal_tula'],
				]);

				$method_query = getQuery('CRestTula', 'tasks.task.add', [
					'fields' => [
						'TITLE' => $task['result']['task']['title'],
						'DESCRIPTION' => $task['result']['task']['description'],
						'RESPONSIBLE_ID' => $RESPONSIBLE_ID['result']['ASSIGNED_BY_ID'],
						'CREATED_BY' => 1125,
						'UF_CRM_TASK' => ['D_' . $sql_deal_ufa_id['deal_tula']],
						'START_DATE_PLAN' => $task['result']['task']['start_date_plan'],
						'DEADLINE' => $task['result']['task']['deadline'],
						'UF_TASK_WEBDAV_FILES' => $file_task_tula_id,
						'ALLOW_CHANGE_DEADLINE' => $task['result']['task']['allowChangeDeadline'],
					],]);
				$sql_task = "INSERT INTO det_task SET deal_tula = ?i, deal_ufa = ?i, task_tula = ?i, task_ufa = ?i";
				$db->query($sql_task, (int)$sql_deal_ufa_id['deal_tula'], (int)$deal_id, (int)$method_query['result']['task']['id'], (int)$tasks);
			}
		}
	} elseif ($event == 'ONTASKCOMMENTADD') {
		if (empty($sql_ufa)) {
			if (!empty($sql_ufa_id['task_tula'])){
				if (strpos($task_message['result']['POST_MESSAGE'], 'вы добавлены наблюдателем') == false  || strpos($task_message['result']['POST_MESSAGE'], 'вы назначены ответственным') == false){	
					$method_query = getQuery('CRestTula', 'task.commentitem.add',[
						'TASKID' => $sql_ufa_id['task_tula'],
						'fields' => [
							'AUTHOR_ID' => 1125,
							'POST_MESSAGE' => '<b>' . $task_message['result']['AUTHOR_NAME'] . '</b>: ' . preg_replace(array('/^\WUSER=\w{2,}\W/', '/\W{2}USER\W/'), '', $task_message['result']['POST_MESSAGE']),
							'UF_FORUM_MESSAGE_DOC' => $file_tula_id,
						],]);
					$sql_task = "INSERT INTO det_comment SET task_tula = ?i, task_ufa = ?i, comment_tula = ?i, comment_ufa = ?i";
					$db->query($sql_task, (int)$sql_ufa_id['task_tula'], (int)$tasks, (int)$method_query['result'], (int)$task_message['result']['ID']);	
				}
			}
		} 
	} elseif ($event == 'ONTASKUPDATE') {
		// if ($task['result']['task']['changedBy'] != 17950){

			if(!empty($sql_ufa_id['task_ufa'])){
				$method_query = getQuery('CRestTula', 'tasks.task.update', [
					'taskId' => $sql_ufa_id['task_tula'],
					'fields' => [
						'TITLE' => $task['result']['task']['title'],
						'DESCRIPTION' => $task['result']['task']['description'],
						'STATUS' => $task['result']['task']['status'],
						'IS_TASK_RESULT' => $task_message['result']['is_task_result'],
						'DEADLINE' => $task['result']['task']['deadline'],
					]]);
			} else {
				if ($task['result']['task']['responsibleId'] == 23286){
					if (empty($sql_ufa_id['task_tula'])){
						//
					}
				}
			}
		// }
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