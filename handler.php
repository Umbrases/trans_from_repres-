<?php

require_once (__DIR__ .'/crest.php');
require_once (__DIR__ .'/getQuery.php');

if($_REQUEST['PLACEMENT'] != 'USERFIELD_TYPE' || $_REQUEST['DOMAIN'] != 'b24-e77y0j.bitrix24.ru' || empty($_REQUEST['PLACEMENT_OPTIONS'])) die();

$placement_options = json_decode($_REQUEST['PLACEMENT_OPTIONS']);

if($placement_options->ENTITY_ID != 'CRM_DEAL') die();

$deal_id = $placement_options->ENTITY_VALUE_ID;

$deal = getQuery('crm.deal.get', [
    'id' => $deal_id,
]);

$contact = getQuery('crm.contact.get', [
    'id' => $deal['result']['CONTACT_ID'],
]);


$contact_add = getQuery('', [
    'fields' => [
        'NAME' => $contact['result']['NAME'],
        'SECOND_NAME' => $contact['result']['SECOND_NAME'],
        'LAST_NAME' => $contact['result']['LAST_NAME'],
        'PHONE' => $contact['result']['PHONE'],
        'BIRTHDATE' => $contact['result']['BIRTHDATE'],
        'ADDRESS' => $contact['result']['ADDRESS'],
    ],
]);

$deal_add = getQuery('', [
    'fields' => [
        'TITLE' => $deal['result']['TITLE'],
        'CONTACT_ID' => $contact_add['result']['ID'],
    ],
]);


?>
