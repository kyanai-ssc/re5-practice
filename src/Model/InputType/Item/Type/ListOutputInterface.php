<?php
declare(strict_types=1);

namespace App\Model\InputType\Item\Type;

/**
 * ListOutput interface.
 */
interface ListOutputInterface
{
    /**
     * ソートの可否を判定
     *
     * @return bool 判定結果
     */
    public function canSort();

    /**
     * ソートキーを取得
     *
     * @return string|null ソートキー
     */
    public function getSortKey();

    /**
     * 一覧にセットするclassを取得
     *
     * @return string
     */
    public function getListClass();

    /**
     * 一覧の表示内容を取得
     *
     * @param array|null $options オプション引数
     * @return mixed 表示内容
     */
    public function getListValue(?array $options = null);
}
