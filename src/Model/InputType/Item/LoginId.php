<?php
declare(strict_types=1);

namespace App\Model\InputType\Item;

use App\Locale\Message;
use App\Model\Entity\FormPatternDisplayType;
use App\Model\Entity\Reservation;
use App\Model\Entity\User;
use App\Model\InputType\AbstractInputTypeItem;
use App\Model\InputType\Item\Type\CalendarOutputInterface;
use App\Model\InputType\Item\Type\CsvInputInterface;
use App\Model\InputType\Item\Type\CsvInputTrait;
use App\Model\InputType\Item\Type\CsvOutputInterface;
use App\Model\InputType\Item\Type\CsvOutputTrait;
use App\Model\InputType\Item\Type\InputInterface;
use App\Model\InputType\Item\Type\InputTrait;
use App\Model\InputType\Item\Type\ListOutputInterface;
use App\Model\InputType\Item\Type\ListOutputTrait;
use App\Model\InputType\Item\Type\LoginNameInterface;
use App\Model\InputType\Item\Type\MailOutputInterface;
use App\Model\InputType\Item\Type\MailOutputTrait;
use App\Model\InputType\Item\Type\SearchDisplayInterface;
use App\Model\InputType\Item\Type\SearchDisplayTrait;
use Cake\Core\Configure;
use Cake\ORM\Query;
use Cake\Utility\Hash;
use Cake\Validation\Validator;

/**
 * LoginId class.
 */
class LoginId extends AbstractInputTypeItem implements
    CalendarOutputInterface,
    CsvInputInterface,
    CsvOutputInterface,
    InputInterface,
    ListOutputInterface,
    LoginNameInterface,
    MailOutputInterface,
    SearchDisplayInterface
{
    use CsvInputTrait;
    use CsvOutputTrait;
    use InputTrait;
    use ListOutputTrait;
    use MailOutputTrait;
    use SearchDisplayTrait;

    public const LOGIN_ID_SEARCH_VALUE_MAX = 1000;

    /**
     * @var string
     */
    protected $tableName = 'users';

    /**
     * @var string
     */
    protected $columnName = 'login_id';

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
            $this->displayType['canInput'] = false;
        }
    }

    /**
     * @inheritDoc
     */
    public function getDetailValue(?array $options = null)
    {
        if (is_null($options)) {
            return null;
        }

        $user = Hash::get($options, 'user');
        if (!isset($user)) {
            return null;
        }

        return $user->get('login_id');
    }

    /**
     * @inheritDoc
     */
    public function canSort()
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    public function getSortKey()
    {
        return 'Users.login_id';
    }

    /**
     * @inheritDoc
     */
    public function getListValue(?array $options = null)
    {
        return $this->getDetailValue($options);
    }

    /**
     * @inheritDoc
     */
    public function buildSearchValidator(Validator $validator)
    {
        $validator
            ->requirePresence($this->getSearchInputKey(), false)
            ->allowEmptyString($this->getSearchInputKey())
            ->add($this->getSearchInputKey(), [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'maxLength' => [
                    'rule' => ['maxLength', static::LOGIN_ID_SEARCH_VALUE_MAX],
                    'last' => true,
                    'message' => __(Message::ERROR_MAX_LENGTH, static::LOGIN_ID_SEARCH_VALUE_MAX),
                ],
                'custom' => [
                    'rule' => ['custom', Configure::readOrFail('Setting.auth.user.loginId.character')],
                    'last' => true,
                    'message' => __(Message::ERROR_LOGIN_ID_CHARACTER),
                ],
            ]);

        return $validator;
    }

    /**
     * @inheritDoc
     */
    public function buildSearchQuery(Query $query, array $inputs)
    {
        $value = Hash::get($inputs, $this->getSearchInputKey());
        if (isset($value) && $value !== '') {
            $parameter = $this->driverExpression()->escapeLike($value);
            $query->where([
                $this->getTableAlias() . '.' . $this->getColumnName() . ' LIKE' => '%' . $parameter . '%',
            ]);
        }

        return $query;
    }

    /**
     * @inheritDoc
     */
    public function buildFieldsetValidator(Validator $validator)
    {
        $isRequired = $this->getFormItem()->isRequiredItem();
        $loginIdLength = Configure::readOrFail('Setting.auth.user.loginId.length');

        $validator
            ->requirePresence($this->getColumnName(), $isRequired, __(Message::ERROR_NOT_EMPTY))
            ->allowEmptyString($this->getColumnName(), __(Message::ERROR_NOT_EMPTY), !$isRequired)
            ->add($this->getColumnName(), [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'minLength' => [
                    'rule' => ['minLength', $loginIdLength['min']],
                    'last' => true,
                    'message' => __(Message::ERROR_MIN_LENGTH, $loginIdLength['min']),
                ],
                'maxLength' => [
                    'rule' => ['maxLength', $loginIdLength['max']],
                    'last' => true,
                    'message' => __(Message::ERROR_MAX_LENGTH, $loginIdLength['max']),
                ],
                'custom' => [
                    'rule' => ['custom', Configure::readOrFail('Setting.auth.user.loginId.character')],
                    'last' => true,
                    'message' => __(Message::ERROR_LOGIN_ID_CHARACTER),
                ],
            ]);

        return $validator;
    }

    /**
     * @inheritDoc
     */
    public function getCsvOutputValue(?array $options = null)
    {
        return $this->getDetailValue($options);
    }

    /**
     * @inheritDoc
     */
    public function formatCsvInputData(?string $data = null)
    {
        $result = [];
        $result['users']['login_id'] = $data;

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
        return Hash::get($errors, 'login_id', []);
    }

    /**
     * @inheritDoc
     */
    public function getMailReplaceToken()
    {
        return 'user_login_id';
    }

    /**
     * @inheritDoc
     */
    public function getMailOutputValue(array $data, ?array $options = null)
    {
        return Hash::get($data, 'user.login_id');
    }

    /**
     * @inheritDoc
     */
    public function getCalendarOutputValue(Reservation $reservation)
    {
        return $this->getDetailValue([
            'user' => $reservation->get('user'),
            'reservation' => $reservation,
        ]);
    }

    /**
     * @inheritDoc
     */
    public function getLoginNameValue(User $user)
    {
        return $this->getDetailValue([
            'user' => $user,
        ]);
    }
}
