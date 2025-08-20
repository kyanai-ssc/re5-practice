<?php
declare(strict_types=1);

namespace App\Model\InputType\Item\Type;

use App\Model\Entity\User;

/**
 * LoginName interface.
 */
interface LoginNameInterface
{
    /**
     * ログイン情報の出力内容を取得
     *
     * @param \App\Model\Entity\User $user 会員
     * @return string|null 出力内容
     */
    public function getLoginNameValue(User $user);
}
