<?php

namespace App\Model;

use App\Service\DealService;

class Deal
{
    public function saveDeal(int $dealId, $classFrom, $classBefore): void
    {
        $safeMySQL = new SafeMySQL;
        $dealService = new DealService;

        $deal = $dealService->getDeal($classFrom, $dealId);

        // Ищем id коробочной сделки
        $sqlDealId = $safeMySQL->getRow(
            "SELECT deal_box FROM det_deal where deal_cloud = ?i",
            $dealId
        );

        // Если сделка в коробке отсутствует, то создаем сделку
        if (empty($sqlDealId)) {
            $dealService->setDeal($classBefore, $deal, $dealId);
        } else {
            // ... иначе обновляем ее
            // Обновление сделки в коробке если она есть
            $dealService->updateDeal($classBefore, $sqlDealId);
        }
    }
}
