<?php
declare(strict_types=1);

namespace App\Model\InputType\Item;

use App\Locale\Message;
use App\Model\Entity\AutoReplyMail;
use App\Model\Entity\FormPatternDisplayType;
use App\Model\InputType\AbstractInputTypeItem;
use App\Model\InputType\Item\Type\CsvInputInterface;
use App\Model\InputType\Item\Type\CsvInputTrait;
use App\Model\InputType\Item\Type\CsvOutputInterface;
use App\Model\InputType\Item\Type\CsvOutputTrait;
use App\Model\InputType\Item\Type\InputInterface;
use App\Model\InputType\Item\Type\InputTrait;
use App\Model\InputType\Item\Type\MailOutputInterface;
use App\Model\InputType\Item\Type\MailOutputTrait;
use App\Utility\ArrayUtility;
use App\Validation\PasswordValidation;
use Cake\Core\Configure;
use Cake\Utility\Hash;
use Cake\Validation\Validator;

/**
 * Password class.
 */
class Password extends AbstractInputTypeItem implements
    CsvInputInterface,
    CsvOutputInterface,
    InputInterface,
    MailOutputInterface
{
    use CsvInputTrait;
    use CsvOutputTrait;
    use InputTrait;
    use MailOutputTrait;

    /**
     * @var string
     */
    protected $tableName = 'users';

    /**
     * @var string
     */
    protected $columnName = 'password';

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
            $this->displayType['canDisplay'] = true;
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
            $isRequired = function ($context) {
                if (!$context['newRecord']) {
                    return false;
                }

                return $this->getFormItem()->isRequiredItem();
            };

            $passwordValidator = PasswordValidation::getCommonValidator(
                Configure::readOrFail('Setting.auth.user.password')
            );

            $validator
                ->requirePresence($this->getColumnName(), $isRequired, __(Message::ERROR_NOT_EMPTY))
                ->allowEmptyString(
                    $this->getColumnName(),
                    __(Message::ERROR_NOT_EMPTY),
                    function ($context) use ($isRequired) {
                        return !call_user_func($isRequired, $context);
                    }
                )
                ->add($this->getColumnName(), $passwordValidator['password']);
        }

        return $validator;
    }

    /**
     * @inheritDoc
     */
    public function getCsvOutputValue(?array $options = null)
    {
        return null;
    }

    /**
     * @inheritDoc
     */
    public function formatCsvInputData(?string $data = null)
    {
        $result = [];
        $result['users']['password'] = $data;

        return $result;
    }

    /**
     * @inheritDoc
     */
    public function getCsvDescription(?array $options = null)
    {
        return Configure::readOrFail('Setting.csv.import.sample.values');
    }

    /**
     * @inheritDoc
     */
    protected function getCsvErrorMessages(array $errors): array
    {
        return Hash::get($errors, 'password', []);
    }

    /**
     * @inheritDoc
     */
    public function canOutputMailValue(int $autoReplyMailType, bool $oldType = false)
    {
        $types = [
            AutoReplyMail::TYPE_USER_ADD,
            AutoReplyMail::TYPE_USER_EDIT,
        ];
        if (!ArrayUtility::inArray($autoReplyMailType, $types) || $oldType) {
            return false;
        }

        return true;
    }

    /**
     * @inheritDoc
     */
    public function getMailReplaceToken()
    {
        return 'user_password';
    }

    /**
     * @inheritDoc
     */
    public function getMailOutputValue(array $data, ?array $options = null)
    {
        return Hash::get($data, 'user.password', '********');
    }
}
