<?php
declare(strict_types=1);

namespace App\Utility\CommonData;

/**
 * CommonDataFactory Class.
 */
class CommonDataFactory
{
    /**
     * @var \App\Utility\CommonData\CommonData|null
     */
    protected static $instance = null;

    /**
     * 共通データの単一インスタンスを取得
     *
     * @return \App\Utility\CommonData\CommonData 共通データ
     */
    public static function getInstance()
    {
        if (!isset(static::$instance)) {
            static::$instance = new CommonData();
        }

        return static::$instance;
    }
}
