<?php
declare(strict_types=1);

namespace App\Form\Admin\Zip;

use App\Form\Common\Zip\SearchForm as CommonSearchForm;

/**
 * 住所検索フォーム
 */
class SearchForm extends CommonSearchForm
{
    /**
     * @var bool
     */
    protected $adminFlg = true;
}
