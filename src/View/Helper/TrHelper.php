<?php
declare(strict_types=1);

namespace App\View\Helper;

use Cake\View\Helper;

/**
 * TrHelper class. TranslateHelper
 */
class TrHelper extends Helper
{
    /**
     * 翻訳
     *
     * @param string $code 翻訳コード
     * @return string
     */
    public function t(string $code)
    {
        return __($code);
    }

    /**
     * 翻訳＋エスケープ
     *
     * @param string $code 翻訳コード
     * @return string
     */
    public function h(string $code)
    {
        return h($this->t($code));
    }

    /**
     * 翻訳＋エスケープ(nl2br)
     *
     * @param string $code 翻訳コード
     * @return string
     */
    public function nl2br(string $code)
    {
        return nl2br($this->h($code));
    }
}
