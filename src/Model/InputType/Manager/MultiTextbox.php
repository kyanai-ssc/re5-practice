<?php
declare(strict_types=1);

namespace App\Model\InputType\Manager;

use App\Model\Entity\FormGroup;
use App\Model\InputType\AbstractInputTypeManager;
use App\Model\InputType\Manager\Type\TextTypeTrait;
use Cake\Validation\Validator;

/**
 * MultiTextbox class.
 */
class MultiTextbox extends AbstractInputTypeManager
{
    use TextTypeTrait;

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
     * @var int|null
     */
    protected $formItemDetailNumber = null;

    /**
     * @var array
     */
    protected $formItemDetailColumns = [
        'front_word',
        'back_word',
        'text_lower_limit',
        'text_upper_limit',
        'text_input_translate',
        'text_input_check',
    ];

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

        return $this->validationTextFormItemDetail($validator);
    }
}
