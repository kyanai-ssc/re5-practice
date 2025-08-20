<?php
declare(strict_types=1);

namespace App\Form\Admin\Users;

use App\Form\Common\Users\UserForm as CommonUserForm;

/**
 * 会員フォーム
 */
class UserForm extends CommonUserForm
{
    /**
     * @var bool
     */
    protected $adminFlg = true;
}
