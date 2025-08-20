<?php
declare(strict_types=1);

namespace App\Form\User\Reservations;

use App\Form\Common\Reservations\CalendarPopupForm as CommonCalendarPopupForm;

/**
 * カレンダーポップアップフォーム
 */
class CalendarPopupForm extends CommonCalendarPopupForm
{
    /**
     * @var bool
     */
    protected $adminFlg = false;
}
