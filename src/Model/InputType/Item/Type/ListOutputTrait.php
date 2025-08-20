<?php
declare(strict_types=1);

namespace App\Model\InputType\Item\Type;

/**
 * ListOutput trait.
 */
trait ListOutputTrait
{
    /**
     * ソートの可否を判定
     *
     * @return bool 判定結果
     */
    public function canSort()
    {
        return false;
    }

    /**
     * ソートキーを取得
     *
     * @return string|null ソートキー
     */
    public function getSortKey()
    {
        return null;
    }

    /**
     * 一覧にセットするclassを取得
     *
     * @return string
     */
    public function getListClass()
    {
        return '';
    }
}
