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
            "SELECT deal_box FROM det_deal where deal_cloud = ?i",
            $dealId
        )['deal_box'];
        $sqlDeal = "INSERT INTO det_deal SET deal_box = ?i, deal_cloud = ?i";

        $deal = $dealService->getDeal($classFrom, $dealId, $sqlBeforeId, $classBefore);

        // Если сделка в коробке отсутствует, то создаем сделку
        if (empty($sqlBeforeId)) {
            $dealService->setDeal($classBefore, $deal, $sqlDeal);
        } else {
            // ... иначе обновляем ее
            // Обновление сделки в коробке если она есть
            $dealService->updateDeal($classBefore, $deal, $sqlBeforeId);
        }
    }
}
