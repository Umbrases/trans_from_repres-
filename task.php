<?php

$task_add = getQueryUfa('tasks.task.add', [
    'fields' => [
        'TITLE' => 'Задача',
        'DESCRIPTION' => 'Задача',
        'UF_CRM_TASK' => ['D_56054' . $deal_add['result']],
        'RESPONSIBLE_ID' => 17950,
    ],
]);
