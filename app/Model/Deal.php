<?php

namespace App\Model;

use App\Service\DealService;

class Deal
{
    public function saveDeal(int $dealId, $classFrom, $classBefore): void
    {
        $safeMySQL = new SafeMySQL;
        $dealService = new DealService;

        // Ищем id коробочной сделки
        $sqlBeforeId = $safeMySQL->getRow(
            "SELECT new_id FROM deals where old_id = ?i",
            $dealId
        )['new_id'];

        $sqlDeal = "INSERT INTO deals SET new_id = ?i, old_id = ?i";

        $deal = $dealService->getDeal($classFrom, $dealId, $sqlBeforeId, $classBefore);
//        writeToLog($deal);

        // Если сделка в коробке отсутствует, то создаем сделку
        if (empty($sqlBeforeId)) {
            $dealService->setDeal($classBefore, $deal, $sqlDeal);
        } else {
            // ... иначе обновляем ее
            // Обновление сделки в коробке если она есть
            $dealService->updateDeal($classBefore, $deal, $sqlBeforeId);
        }
    }

    public function saveObserverDeal($dealId, $observers, $classBefore)
    {
        $safeMySQL = new SafeMySQL;
        $dealService = new DealService;

        // Ищем id коробочной сделки
        $sqlBeforeId = $safeMySQL->getRow(
            "SELECT new_id FROM deals where old_id = ?i",
            $dealId
        )['deal_box'];

        $observers = str_replace("user_", "", $observers);
        $observers = explode("," , $observers);

        foreach ($observers as $key => $observer) {
            $observerBox = $safeMySQL
                ->getRow("SELECT new_id FROM users where old_id = ?i", $observer)['new_id'];

            $dealService->setObserverDeal($classBefore, $observerBox, $sqlBeforeId);
        }
    }
}

function writeToLog($data)
{
    $log = "\n------------------------\n";
    $log .= date("Y.m.d G:i:s") . "\n";
    $log .= print_r($data, 1);
    $log .= "\n------------------------\n";
    file_put_contents(getcwd() . '/hook.log', $log, FILE_APPEND);
    return true;
}