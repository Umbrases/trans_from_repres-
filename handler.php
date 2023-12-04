<?php

if($_REQUEST['DOMAIN'] != 'b24-e77y0j.bitrix24.ru') die();

function tula()
{
    require (__DIR__ .'/crest.php');
    require (__DIR__ .'/getQuery.php');

    $deal_id = $_REQUEST['deal_id'];

    global $deal, $contact, $tasks;
    $deal = getQuery('crm.deal.get', [
        'ID' => $deal_id,
    ]);

    $contact = getQuery('crm.contact.get', [
        'ID' => $deal['result']['CONTACT_ID'],
    ]);

    $tasks = getQuery('tasks.task.list', [
        'filter' => [
            'UF_CRM_TASK' => 'D_' . $deal['result']['ID'],
        ]
    ]);

}
tula();
// foreach($tasks['result']['tasks'] as $task){
//     var_dump($task);
// }
//var_dump($deal);
ufa($deal, $contact, $tasks);
//file_put_contents(__DIR__ . '/log.txt', json_decode($deal) . PHP_EOL, FILE_APPEND);
//file_put_contents(__DIR__ . '/log.txt', json_decode($contact) . PHP_EOL, FILE_APPEND);

function ufa($deal, $contact, $tasks)
{
    $_REQUEST['DOMAIN'] = 'stopzaym.bitrix24.ru';

    require (__DIR__ .'/crestUfa.php');
    require (__DIR__ .'/getQuery.php');


    $contact_add = getQueryUfa('crm.contact.add', [
        'fields' => [
            'NAME' => $contact['result']['NAME'],
            'SECOND_NAME' => $contact['result']['SECOND_NAME'],
            'LAST_NAME' => $contact['result']['LAST_NAME'],
            'PHONE' => $contact['result']['PHONE'],
            'BIRTHDATE' => $contact['result']['BIRTHDATE'],
            'ADDRESS' => $contact['result']['ADDRESS'],
        ],
    ]);


    $deal_add = getQueryUfa('crm.deal.add', [
        'fields' => [
            'TITLE' => $deal['result']['TITLE'],
            'CONTACT_ID' => $contact_add['result'],
            'CATEGORY_ID' => 58,
            'ASSIGNED_BY_ID' => 17950,
            'OBSERVER' => 17950,
            'UF_CRM_1653545949629' => $deal['result']['UF_CRM_6333543A7DBA0'],
            'COMMENTS' => $deal['result']['COMMENTS'],
            'UF_CRM_5D53E58571DB8' => $deal['result']['UF_CRM_6333543AAB9A1'],
            'UF_CRM_1627447542' => $deal['result']['UF_CRM_1664374736018'],
            'UF_CRM_1650372775123' => $deal['result']['UF_CRM_1664373248467'],
            'UF_CRM_1654154788530' => $deal['result']['UF_CRM_1664374644067'],
            'UF_CRM_625D560433A58' => 6182,
            'UF_CRM_1621386904' => 1,
            'TYPE_ID' => 'UC_M0M7LA',
            'SOURCE_ID' => 'UC_5IIS3U',
        ],
    ]);

// var_dump($deal_add);
sleep(2);
    foreach($tasks['result']['tasks'] as $task){
        $task_add = getQueryUfa('tasks.task.add', [
           'fields' => [
               'TITLE' => $task['title'],
               'DESCRIPTION' => $task['description'],
               'UF_CRM_TASK' => 'D_' . $deal_add['result'],
               'RESPONSIBLE_ID' => 17950,
           ],
        ]);
        var_dump($task_add);
    }
}




?>
