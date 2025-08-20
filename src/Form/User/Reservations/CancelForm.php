<?php
declare(strict_types=1);

namespace App\Form\User\Reservations;

use App\Form\Common\Reservations\CancelForm as CommonCancelForm;

/**
 * キャンセルフォーム
 */
class CancelForm extends CommonCancelForm
{
    /**
     * @var bool
     */
    protected $adminFlg = false;
}
