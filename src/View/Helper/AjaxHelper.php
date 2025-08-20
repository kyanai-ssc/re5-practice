<?php
declare(strict_types=1);

namespace App\View\Helper;

use Cake\View\Helper;

/**
 * AjaxHelper class.
 */
class AjaxHelper extends Helper
{
    /**
     * データをJSON文字列へ変換
     *
     * @param mixed $data データ
     * @return string JSON文字列
     */
    public function json($data)
    {
        $json = json_encode($data);
        if ($json === false) {
            return '';
        }

        return $json;
    }
}
