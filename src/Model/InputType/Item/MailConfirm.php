<?php
declare(strict_types=1);

namespace App\Model\InputType\Item;

use App\Locale\Message;
use App\Model\Entity\FormPatternDisplayType;
use App\Model\InputType\AbstractInputTypeItem;
use App\Model\InputType\Item\Type\InputInterface;
use App\Model\InputType\Item\Type\InputTrait;
use Cake\Validation\Validator;

/**
 * MailConfirm class.
 */
class MailConfirm extends AbstractInputTypeItem implements InputInterface
{
    use InputTrait;

    /**
     * @var string
     */
    protected $tableName = 'users';

    /**
     * @var string
     */
    protected $columnName = 'mail_confirm';

    /**
     * @inheritDoc
     */
    public function settingDisplayType(FormPatternDisplayType $formPatternDisplayType)
    {
        parent::settingDisplayType($formPatternDisplayType);

        /** @var \App\Model\Table\SiteSettingsTable $siteSettingsTable */
        $siteSettingsTable = $this->getTableLocator()->get('SiteSettings');
        $siteSetting = $siteSettingsTable->getData();

        $userId = $this->getConfig('userId');
        if (
            (!isset($userId) && !$this->isAdmin() && $siteSetting->isUseFlgOn('optin_flg'))
            || (isset($userId) && !$this->isAdmin() && $siteSetting->isUseFlgOn('mail_edit_optin_flg'))
        ) {
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
                        'rule' => ['maxLength', Mail::MAIL_VALUE_MAX],
                        'last' => true,
                        'message' => __(Message::ERROR_MAX_LENGTH, Mail::MAIL_VALUE_MAX),
                    ],
                    'compareWith' => [
                        'rule' => ['compareWith', 'mail'],
                        'last' => true,
                        'message' => __(Message::ERROR_NOT_SAME),
                    ],
                ]);
        }

        return $validator;
    }
}
