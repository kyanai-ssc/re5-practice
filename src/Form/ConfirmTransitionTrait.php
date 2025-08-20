<?php
declare(strict_types=1);

namespace App\Form;

/**
 * ConfirmTransition trait.
 */
trait ConfirmTransitionTrait
{
    /**
     * @var bool
     */
    protected $isConfirm = false;

    /**
     * 確認画面の判定
     *
     * @return bool 判定結果
     */
    public function isConfirm()
    {
        return $this->isConfirm;
    }

    /**
     * 確認画面のフラグを設定
     *
     * @param bool $isConfirm フラグ
     * @return void
     */
    public function setConfirm(bool $isConfirm)
    {
        $this->isConfirm = $isConfirm;
    }
}
