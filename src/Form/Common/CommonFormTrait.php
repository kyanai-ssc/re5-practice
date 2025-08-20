<?php
declare(strict_types=1);

namespace App\Form\Common;

/**
 * CommonForm trait.
 */
trait CommonFormTrait
{
    /**
     * @var bool
     */
    protected $adminFlg = null;

    /**
     * 管理者側フラグを取得
     *
     * @return bool 管理者側フラグ
     */
    public function isAdmin()
    {
        return $this->adminFlg;
    }
}
