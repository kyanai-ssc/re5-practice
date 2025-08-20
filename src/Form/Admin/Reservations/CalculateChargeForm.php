<?php
declare(strict_types=1);

namespace App\Form\Admin\Reservations;

use App\Form\Common\Reservations\CalculateChargeForm as CommonCalculateChargeForm;

/**
 * 料金計算フォーム
 */
class CalculateChargeForm extends CommonCalculateChargeForm
{
    use ContinuousTrait;

    /**
     * @var bool
     */
    protected $adminFlg = true;
}
