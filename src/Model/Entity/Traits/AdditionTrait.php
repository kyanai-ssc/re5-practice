<?php
declare(strict_types=1);

namespace App\Model\Entity\Traits;

/**
 * Addition trait.
 */
trait AdditionTrait
{
    /**
     * 項目データのアクセサ
     *
     * @param string|null $data 項目データ
     * @return mixed
     */
    protected function _getData($data)
    {
        if (array_key_exists('data', $this->_fields)) {
            return $data;
        }

        /** @var \App\Model\Table\FormItemsTable $formItemsTable */
        $formItemsTable = $this->getTableLocator()->get('FormItems');

        $formItemId = $this->get('form_item_id');
        if (!isset($formItemId)) {
            return $data;
        }
        $formItem = $formItemsTable->getFormItem($formItemId);
        if (!isset($formItem)) {
            return $data;
        }

        $this->_fields['data'] = $formItem->getInputTypeItem()->valueToPHP($this->get('value'));

        return $this->_fields['data'];
    }

    /**
     * 項目データのミューテータ
     *
     * @param mixed $data 項目データ
     * @return mixed
     */
    protected function _setData($data)
    {
        /** @var \App\Model\Table\FormItemsTable $formItemsTable */
        $formItemsTable = $this->getTableLocator()->get('FormItems');

        $formItemId = $this->get('form_item_id');
        if (!isset($formItemId)) {
            return $data;
        }
        $formItem = $formItemsTable->getFormItem($formItemId);
        if (!isset($formItem)) {
            return $data;
        }

        $this->set('value', $formItem->getInputTypeItem()->valueToDatabase($data));

        return $data;
    }
}
