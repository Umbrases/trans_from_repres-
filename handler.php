<?

if(empty($_REQUEST['DOMAIN']) && $_REQUEST['DOMAIN'] != 'b24-e77y0j.bitrix24.ru') die();

require_once (__DIR__ .'/crest_tula.php');
require_once (__DIR__ .'/crest_ufa.php');
require_once (__DIR__ .'/getQuery.php');
require_once (__DIR__ .'/SafeMySQL.php');

$db = new SafeMySQL();

$deal_id = $_REQUEST['deal_id'];

$batch_list = [
	'deal' => [
		'method' => 'crm.deal.get',
		'params' => [
			'ID' => $deal_id
		]
	],
	'contact' => [
		'method' => 'crm.contact.get',
		'params' => [
			'ID' => '$result[deal][CONTACT_ID]'
		]
	],
];

$result = getQueryBatch('CRestTula', $batch_list);

$deal = $result['result']['result']['deal'];
$contact = $result['result']['result']['contact'];

$date = explode(' ', $contact['UF_CRM_6333543A28B78']);

foreach($date as $value){
    $time = strtotime($value);
    if($time == true){
        $time = $value;
        unset($date[array_search($time, $date)]);
    }
}

$date = implode(' ', $date);

if($contact['UF_CRM_62D05D7F42F09'] == 53){
    $contact['UF_CRM_62D05D7F42F09'] = 'Тула';
} elseif ($contact['UF_CRM_62D05D7F42F09'] == 55){
    $contact['UF_CRM_62D05D7F42F09'] = 'Владимир';
} else {
    $contact['UF_CRM_62D05D7F42F09'] = 'Другой (ОНЛАЙН)';
}

$batch_list_ufa = [
	'contact' => [
		'method' => 'crm.contact.add',
		'params' => [
			'fields' => [
				'NAME' => $contact['NAME'],
	            'SECOND_NAME' => $contact['SECOND_NAME'],
	            'LAST_NAME' => $contact['LAST_NAME'],
	            'PHONE' => [[
                    'VALUE' => $contact['PHONE'][0]['VALUE'],
                    'VALUE_TYPE' => $contact['PHONE'][0]['VALUE_TYPE'],
                    ]],
	            'BIRTHDATE' => $contact['BIRTHDATE'],
	            'ADDRESS' => $contact['ADDRESS'],
	            'EMAIL' => [[
                    'VALUE' => $contact['EMAIL'][0]['VALUE'],
                    'VALUE_TYPE' => $contact['EMAIL'][0]['VALUE_TYPE'],
                ]],
	            'UF_CRM_629A1B699D519' => $contact['UF_CRM_62D05D7F42F09'],
	            'UF_CRM_629F51D7AE750' => mb_substr($contact['UF_CRM_6333543A1D22F'], 0, 4),
	            'UF_CRM_629F51D7F1D30' => mb_substr($contact['UF_CRM_6333543A1D22F'], -6, 6),
	            'UF_CRM_629F51D834666' => $time,
	            'UF_CRM_629F51D85F1A7' => $date,
			]
		]
	],
	'deal' => [
		'method' => 'crm.deal.add',
		'params' => [
			'fields' => [
				'TITLE' => $deal['TITLE'],
	            'CONTACT_ID' => '$result[contact]',
	            'CATEGORY_ID' => 58,
	            'ASSIGNED_BY_ID' => 208,
	            'UF_CRM_1701760298' => 1,
	            'UF_CRM_1653545949629' => $deal['UF_CRM_6333543A7DBA0'],
	            'COMMENTS' => $deal['COMMENTS'],
	            'UF_CRM_5D53E58571DB8' => $deal['UF_CRM_6333543AAB9A1'],
	            'UF_CRM_1627447542' => $deal['UF_CRM_1664374736018'],
	            'UF_CRM_1650372775123' => $deal['UF_CRM_1664373248467'],
	            'UF_CRM_1654154788530' => $deal['UF_CRM_1664374644067'],
	            'UF_CRM_625D560433A58' => 6182,
	            'UF_CRM_1621386904' => 1,
	            'TYPE_ID' => 'UC_M0M7LA',
	            'SOURCE_ID' => 'UC_5IIS3U',
			]
		]
	],
];

$ufa = getQueryBatch('CRestUfa', $batch_list_ufa);

$sql_task = "INSERT INTO det_deal SET deal_tula = ?i, deal_ufa = ?i";
$db->query($sql_task, (int)$deal_id, (int)$ufa['result']['result']['deal']);