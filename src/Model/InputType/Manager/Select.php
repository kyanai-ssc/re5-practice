<?php
declare(strict_types=1);

namespace App\Model\InputType\Manager;

use App\Model\Entity\FormGroup;
use App\Model\InputType\AbstractInputTypeManager;
use App\Model\InputType\Manager\Type\SelectTypeTrait;
use Cake\Validation\Validator;

/**
 * Select class.
 */
class Select extends AbstractInputTypeManager
{
    use SelectTypeTrait;

    /**
     * @var array
     */
    protected $canCreate = [
        FormGroup::FORM_TYPE_USER => true,
        FormGroup::FORM_TYPE_RESERVATION => true,
    ];

    /**
     * @var bool
     */
    protected $canSelectRequired = true;

    /**
     * @var bool
     */
    protected $canReservationDisplay = true;

    /**
     * @var bool
     */
    protected $hasFormItemDetails = true;

    /**
     * @var int
     */
    protected $formItemDetailNumber = 1;

    /**
     * @var array
     */
    protected $formItemDetailColumns = [
        'front_word',
        'back_word',
    ];

    /**
     * @var bool
     */
    protected $hasFormItemChoices = true;

    /**
     * @var bool
     */
    protected $hasFormItemDetailTab = true;

    /**
     * @inheritDoc
     */
    public function validationFormItemDetail(Validator $validator)
    {
        $validator = parent::validationFormItemDetail($validator);

        return $this->validationSelectFormItemDetail($validator);
    }
}
