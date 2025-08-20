<?php
declare(strict_types=1);

namespace App\Model\EventCalendar;

/**
 * PaginateType interface.
 */
interface PaginateTypeInterface
{
    /**
     * ページネーションの値リストを取得
     *
     * @return array
     */
    public function paginateValueOptions();

    /**
     * ページネーションのデフォルト値を取得
     *
     * @return array
     */
    public function paginateDefaultValues();

    /**
     * ページネーションの最大件数を取得
     *
     * @return int
     */
    public function paginateMaxLimit();
}
