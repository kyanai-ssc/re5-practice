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
use Cake\ORM\Query;
use Cake\Utility\Hash;
use Cake\Validation\Validator;

/**
 * UserAuthority class.
 */
class UserAuthority extends AbstractInputTypeItem implements
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

    /**
     * @var string
     */
    protected $tableName = 'users';

    /**
     * @var string
     */
    protected $columnName = 'user_authority_id';

    /**
     * @var array|null
     */
    protected $valueOptions = null;

    /**
     * @inheritDoc
     */
    public function settingDisplayType(FormPatternDisplayType $formPatternDisplayType)
    {
        parent::settingDisplayType($formPatternDisplayType);

        if ($this->isGuestUserDisplayType() || !$this->isAdmin()) {
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

        $valueOptions = $this->getValueOptions();

        return $valueOptions[$user->get('user_authority_id')];
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
        return 'Users.user_authority_id';
    }

    /**
     * @inheritDoc
     */
    public function getListValue(?array $options = null)
    {
        if (is_null($options)) {
            return null;
        }

        $user = Hash::get($options, 'user');
        if (!isset($user)) {
            return null;
        }

        $updateAuthority = false;
        if (
            Hash::get($options, 'mode') === 'user'
            && !Hash::get($options, 'notUpdateAuthrity', false)
        ) {
            $updateAuthority = true;
        }
        $data = [
            'updateAuthority' => $updateAuthority,
            'user' => $user,
        ];

        return $data;
    }

    /**
     * @inheritDoc
     */
    public function buildSearchValidator(Validator $validator)
    {
        $validator
            ->requirePresence($this->getSearchInputKey(), false)
            ->allowEmptyArray($this->getSearchInputKey())
            ->add($this->getSearchInputKey(), [
                'isArray' => [
                    'rule' => ['isArray'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'multiple' => [
                    'rule' => ['multiple', [
                        'in' => array_keys($this->getValueOptions()),
                    ]],
                    'last' => true,
                    'message' => __(Message::ERROR_IN_LIST),
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
        if (is_scalar($value) && ((string)$value !== '') || is_array($value) && !empty($value)) {
            $query->where([
                $this->getTableAlias() . '.' . $this->getColumnName() . ' IN' => (array)$value,
            ]);
        }

        return $query;
    }

    /**
     * @inheritDoc
     */
    public function buildFieldsetValidator(Validator $validator)
    {
        $validator
            ->requirePresence($this->getColumnName(), true, __(Message::ERROR_NOT_EMPTY_SELECT))
            ->allowEmptyString($this->getColumnName(), __(Message::ERROR_NOT_EMPTY_SELECT), false)
            ->add($this->getColumnName(), [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'inList' => [
                    'rule' => ['inList', array_keys($this->getValueOptions(false))],
                    'last' => true,
                    'message' => __(Message::ERROR_IN_LIST),
                ],
            ]);

        return $validator;
    }

    /**
     * @inheritDoc
     */
    public function getCsvOutputValue(?array $options = null)
    {
        if (is_null($options)) {
            return null;
        }

        $user = Hash::get($options, 'user');
        if (!isset($user)) {
            return null;
        }

        $userAutority = $user->get('user_authority');
        if (!isset($userAutority)) {
            return null;
        }

        return $this->csvFormat()->csvForId($userAutority->get('id'), $userAutority->get('name'));
    }

    /**
     * @inheritDoc
     */
    public function formatCsvInputData(?string $data = null)
    {
        $result = [];
        $result['users']['user_authority_id'] = $this->csvFormat()->inputForId($data);

        return $result;
    }

    /**
     * @inheritDoc
     */
    public function getCsvDescription(?array $options = null)
    {
        $valueOptions = $this->getValueOptions(false);

        return $this->csvFormat()->csvForHasManyId(array_keys($valueOptions), $valueOptions);
    }

    /**
     * @inheritDoc
     */
    protected function getCsvErrorMessages(array $errors): array
    {
        return Hash::get($errors, 'user_authority_id', []);
    }

    /**
     * @inheritDoc
     */
    public function getMailReplaceToken()
    {
        return 'user_authority';
    }

    /**
     * @inheritDoc
     */
    public function getMailOutputValue(array $data, ?array $options = null)
    {
        $userAuthorityId = Hash::get($data, 'user.user_authority_id');
        if (((string)$userAuthorityId) === '') {
            return null;
        }

        return Hash::get($this->getValueOptions(), $userAuthorityId);
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

    /**
     * 選択肢を取得
     *
     * @param bool $includeGuest 非会員含有フラグ
     * @return array 選択肢
     */
    public function getValueOptions($includeGuest = true)
    {
        if (!isset($this->valueOptions[$includeGuest])) {
            /** @var \App\Model\Table\UserAuthoritiesTable $userAuthoritiesTable */
            $userAuthoritiesTable = $this->getTableLocator()->get('UserAuthorities');

            $this->valueOptions[$includeGuest] = $userAuthoritiesTable->getSelectList(false, $includeGuest);
        }

        return $this->valueOptions[$includeGuest];
    }
}
