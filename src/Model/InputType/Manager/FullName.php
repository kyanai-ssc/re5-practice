<?php
declare(strict_types=1);

namespace App\Model\InputType\Manager;

use App\Model\Entity\FormGroup;
use App\Model\InputType\AbstractInputTypeManager;
use App\Model\InputType\Manager\Type\TextTypeTrait;
use Cake\Validation\Validator;

/**
 * FullName class.
 */
class FullName extends AbstractInputTypeManager
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
     * @var int
     */
    protected $formItemDetailNumber = 2;

    /**
     * @var array
     */
    protected $formItemDetailColumns = [
        'front_word',
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
        $validator = $this->validationTextFormItemDetail(parent::validationFormItemDetail($validator));

        $validator->remove('back_word');

        return $validator;
    }
}
