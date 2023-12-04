<?
require_once (__DIR__ .'/crestUfa.php');
require_once (__DIR__ .'/crest.php');



    function getQuery($method, $params)
    {
        $result = CRest::call($method, $params);

        if (!empty($result['error']) && $result['error'] == 'QUERY_LIMIT_EXCEEDED') {
            sleep(1);
            getQuery($method, $params);
        }

        return $result;
    }

    function getQueryUfa($method, $params)
    {
        $result = CRestUfa::call($method, $params);

        if (!empty($result['error']) && $result['error'] == 'QUERY_LIMIT_EXCEEDED') {
            sleep(1);
            getQuery($method, $params);
        }

        return $result;
    }



//function getQueryBatch($batch_list){
//    if ($_REQUEST['DOMAIN'] != 'b24-e77y0j.bitrix24.ru') {
//        $batch_result = CRest::callBatch($batch_list);
//    } else {
//        $batch_result = CRestUfa::callBatch($batch_list);
//    }
//    if (!empty($batch_result['error']) && $batch_result['error'] == 'QUERY_LIMIT_EXCEEDED'){
//        sleep(1);
//        getQueryBatch($batch_list);
//    }
//
//    return $batch_result;
//}