<?php
declare(strict_types=1);

namespace App\Model\InputType\Manager;

use App\Locale\Message;
use App\Model\Entity\FormGroup;
use App\Model\InputType\AbstractInputTypeManager;
use Cake\Utility\Hash;

/**
 * ReservationOption class.
 */
class ReservationOption extends AbstractInputTypeManager
{
    /**
     * @var array
     */
    protected $canCreate = [
        FormGroup::FORM_TYPE_USER => false,
        FormGroup::FORM_TYPE_RESERVATION => true,
    ];

    /**
     * @var bool
     */
    protected $canSelectRequired = true;

    /**
     * @var bool
     */
    protected $hasFormItemOptionGroups = true;

    /**
     * @var bool
     */
    protected $hasFormItemDetailTab = true;

    /**
     * オプショングループの重複チェック
     *
     * @param \Cake\ORM\Entity $entity entity
     * @return void
     */
    public function validateDuplication($entity)
    {
        $formItemOptionGroups = $entity->get('form_item_option_group');
        $formItemOptions = $formItemOptionGroups->get('form_item_options');
        if (!is_array($formItemOptions)) {
            return;
        }

        $inputOptionIds = array_column($formItemOptions, 'option_id');
        $optionIds = array_unique($inputOptionIds);

        $diff = Hash::diff($inputOptionIds, $optionIds);

        if (!empty($diff)) {
            foreach (array_keys($diff) as $key) {
                $formItemOptions[$key]->setError('option_id', __(Message::ERROR_DUPLICATION));
            }

            $formItemOptionGroups->set('form_item_options', $formItemOptions);
        }
    }

    /**
     * 予約存在チェック
     *
     * @param int $formItemId FormItems.id
     * @return bool
     */
    public function isReserve(int $formItemId)
    {
        /** @var \App\Model\Table\ReservationOptionsTable $reservationOptionsTable */
        $reservationOptionsTable = $this->getTableLocator()->get('ReservationOptions');

        return $reservationOptionsTable->checkReserveFormItemId($formItemId);
    }
}
