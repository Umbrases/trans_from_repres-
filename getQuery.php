<?

function getQuery($class, $method, $params)
{
    $result = $class::call($method, $params);

    if (!empty($result['error']) && $result['error'] == 'QUERY_LIMIT_EXCEEDED') {
        sleep(1);
        getQuery($method, $params);
    }

    return $result;
}

function getQueryBatch($class, $batch_list){
    $batch_result = $class::callBatch($batch_list);
    if (!empty($batch_result['error']) && $batch_result['error'] == 'QUERY_LIMIT_EXCEEDED'){
        sleep(1);
        getQueryBatch($batch_list);
    }

    return $batch_result;
}