<?php
declare(strict_types=1);

namespace App\Model\InputType\Item;

use App\Locale\Message;
use App\Model\Entity\FormPatternDisplayType;
use App\Model\InputType\AbstractInputTypeItem;
use App\Model\InputType\Item\Type\InputInterface;
use App\Model\InputType\Item\Type\InputTrait;
use Cake\Core\Configure;
use Cake\Validation\Validator;

/**
 * PasswordConfirm class.
 */
class PasswordConfirm extends AbstractInputTypeItem implements InputInterface
{
    use InputTrait;

    /**
     * @var string
     */
    protected $tableName = 'users';

    /**
     * @var string
     */
    protected $columnName = 'password_confirm';

    /**
     * @inheritDoc
     */
    public function settingDisplayType(FormPatternDisplayType $formPatternDisplayType)
    {
        parent::settingDisplayType($formPatternDisplayType);

        $userId = $this->getConfig('userId');
        if ($this->isGuestUserDisplayType()) {
            $this->displayType['canDisplay'] = false;
            $this->displayType['canInput'] = false;
        } elseif (!$this->isAdmin() && isset($userId)) {
            $this->displayType['canDisplay'] = false;
            $this->displayType['canInput'] = false;
        }
    }

    /**
     * @inheritDoc
     */
    public function buildFieldsetValidator(Validator $validator)
    {
        $isConfirm = $this->getConfig('isConfirm', false);
        if (!$isConfirm) {
            $isRequired = $this->getFormItem()->isRequiredItem() && !$this->isAdmin();
            $passwordLengthMax = Configure::readOrFail('Setting.auth.user.password.length.max');

            $validator
                ->requirePresence($this->getColumnName(), $isRequired, __(Message::ERROR_NOT_EMPTY))
                ->allowEmptyString($this->getColumnName(), __(Message::ERROR_NOT_EMPTY), !$isRequired)
                ->add($this->getColumnName(), [
                    'isScalar' => [
                        'rule' => ['isScalar'],
                        'last' => true,
                        'message' => __(Message::ERROR_INVALID_VALUE),
                    ],
                    'maxLength' => [
                        'rule' => ['maxLength', $passwordLengthMax],
                        'last' => true,
                        'message' => __(Message::ERROR_MAX_LENGTH, $passwordLengthMax),
                    ],
                    'compareWith' => [
                        'rule' => ['compareWith', 'password'],
                        'last' => true,
                        'message' => __(Message::ERROR_NOT_SAME),
                    ],
                ]);
        }

        return $validator;
    }
}
