<?php
declare(strict_types=1);

namespace App\Form\Admin\Reservations;

use App\Form\Common\Reservations\ContinuousForm as CommonContinuousForm;

/**
 * 連続予約フォーム
 */
class ContinuousForm extends CommonContinuousForm
{
    use ContinuousTrait;

    /**
     * @var bool
     */
    protected $adminFlg = true;
}
