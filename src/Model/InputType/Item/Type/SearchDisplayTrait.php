<?php
declare(strict_types=1);

namespace App\Model\InputType\Item\Type;

use Cake\Core\Exception\CakeException;

/**
 * SearchDisplay trait.
 */
trait SearchDisplayTrait
{
    /**
     * 検索条件のキー名を取得
     *
     * @return string キー名
     */
    public function getSearchInputKey()
    {
        $fieldsetInputKey = $this->getFieldsetInputKey();
        if (is_null($fieldsetInputKey)) {
            throw new CakeException();
        }

        $searchInputKey = preg_replace('/\\./', '_', $fieldsetInputKey);
        if (!is_scalar($searchInputKey)) {
            throw new CakeException();
        }

        return $searchInputKey;
    }
}
