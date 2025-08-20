<?php
declare(strict_types=1);

namespace App\Utility\CommonData;

/**
 * CommonData Trait.
 */
trait CommonDataTrait
{
    /**
     * 共通データを取得
     *
     * @return \App\Utility\CommonData\CommonData 共通データ
     */
    protected function commonData()
    {
        return CommonDataFactory::getInstance();
    }
}
