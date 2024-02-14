<?php

namespace App\Model;

use App\Service\DealService;

class TransferDeal
{
    public const RESTRUCTURING_DEBT = 'debt_restructuring';
    public const RESTRUCTURING_PROPERTY = 'property_restructuring';
    private SafeMySQL $safeMySQL;
    private  DealService $dealService;

    public function __construct()
    {
        $this->safeMySQL = new SafeMySQL;
        $this->dealService = new DealService;
    }

    public function saveDeal(int $dealId): void
    {
        writeToLog(123);
        $deal = $this->dealService->getDeal($dealId);

        // Ищем id уфимской сделки
        $sqlDealId = $this->safeMySQL->getRow(
            "SELECT deal_ufa FROM det_deal where deal_tula = ?i",
            $dealId
        );

        // Если сделка в Уфе отсутствует, то создаем сделку
        if (empty($sqlDealId)) {
            $this->dealService->setDeal($deal, $dealId);
        } else {
            // ... иначе обновляем ее
            // Устанавливаем новое состояние сделки
            $stage = match ($_REQUEST['stage']) {
                self::RESTRUCTURING_DEBT => 'C58:PREPARATION',
                self::RESTRUCTURING_PROPERTY => 'C58:PREPAYMENT_INVOIC',
                default => '',
            };

            // Обновление сделки в уфе если она есть
            $this->dealService->updateDeal($sqlDealId, $stage);
        }
    }
}
function writeToLog($data) {
    $log = "\n------------------------\n";
    $log .= date("Y.m.d G:i:s") . "\n";
    $log .= print_r($data, 1);
    $log .= "\n------------------------\n";
    file_put_contents(getcwd() . '/hook.log', $log, FILE_APPEND);
    return true;
}