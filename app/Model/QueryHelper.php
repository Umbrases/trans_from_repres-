<?php

namespace App\Model;

use App\Model\CRestBox;
use App\Model\CRestCloud;

class QueryHelper {
    public static function getQuery($class, $method, $params)
    {
        $result = $class::call($method, $params);

        if (!empty($result['error']) && $result['error'] == 'QUERY_LIMIT_EXCEEDED') {
            sleep(1);
            self::getQuery($class, $method, $params);
        }

        return $result;
    }

    public static function getQueryBatch($class, $batch_list)
    {
        $batch_result = $class::callBatch($class, $batch_list);
        if (!empty($batch_result['error']) && $batch_result['error'] == 'QUERY_LIMIT_EXCEEDED') {
            sleep(1);
            self::getQueryBatch($class, $batch_list);
        }

        return $batch_result;
    }
}
