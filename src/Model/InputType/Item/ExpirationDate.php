<?php
declare(strict_types=1);

namespace App\Model\InputType\Item;

use App\Locale\Message;
use App\Model\Entity\FormPatternDisplayType;
use App\Model\InputType\AbstractInputTypeItem;
use App\Model\InputType\Item\Type\CsvInputInterface;
use App\Model\InputType\Item\Type\CsvInputTrait;
use App\Model\InputType\Item\Type\CsvOutputInterface;
use App\Model\InputType\Item\Type\CsvOutputTrait;
use App\Model\InputType\Item\Type\InputInterface;
use App\Model\InputType\Item\Type\InputTrait;
use App\Model\InputType\Item\Type\ListOutputInterface;
use App\Model\InputType\Item\Type\ListOutputTrait;
use App\Model\InputType\Item\Type\MailOutputInterface;
use App\Model\InputType\Item\Type\MailOutputTrait;
use App\Model\InputType\Item\Type\SearchDisplayInterface;
use App\Model\InputType\Item\Type\SearchDisplayTrait;
use App\Utility\DateTimeUtility;
use Cake\Core\Configure;
use Cake\Core\Exception\CakeException;
use Cake\Database\Schema\TableSchemaInterface;
use Cake\ORM\Query;
use Cake\Utility\Hash;
use Cake\Validation\Validator;
use Kuchen\Validation\Validation\Validator as KuchenValidator;

/**
 * ExpirationDate class.
 */
class ExpirationDate extends AbstractInputTypeItem implements
    CsvInputInterface,
    CsvOutputInterface,
    InputInterface,
    ListOutputInterface,
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
     * @var non-empty-string
     */
    protected $delimiter = '～';

    /**
     * @var string
     */
    protected $tableName = 'users';

    /**
     * @var string
     */
    protected $columnName = 'expiration_date';

    /**
     * @inheritDoc
     */
    public function settingDisplayType(FormPatternDisplayType $formPatternDisplayType)
    {
        parent::settingDisplayType($formPatternDisplayType);

        if ($this->isGuestUserDisplayType()) {
            $this->displayType['canInput'] = false;
            $this->displayType['canDisplay'] = false;
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

        return $this->formatFromTo($user->get('expiration_date_from'), $user->get('expiration_date_to'));
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
        $dateSelectValidator = new KuchenValidator();
        $validator
            ->requirePresence($this->getSearchInputKey(), false)
            ->allowEmptyString($this->getSearchInputKey())
            ->addNested($this->getSearchInputKey(), $dateSelectValidator);

        $dateSelectValidator
            ->requirePresence('from', false)
            ->allowEmptyDate('from')
            ->add('from', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'date' => [
                    'rule' => ['date', 'ymd'],
                    'last' => true,
                    'message' => __(Message::ERROR_DATE),
                ],
            ]);

        $dateSelectValidator
            ->requirePresence('to', false)
            ->allowEmptyDate('to')
            ->add('to', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'date' => [
                    'rule' => ['date', 'ymd'],
                    'last' => true,
                    'message' => __(Message::ERROR_DATE),
                ],
                'compareFields' => [
                    'rule' => ['compareDateTimeFields', 'from', '>='],
                    'last' => true,
                    'message' => __(Message::ERROR_OVER_FROM_DATETIME),
                    'on' => function () use ($dateSelectValidator) {
                        return $dateSelectValidator->isValid('from');
                    },
                ],
            ]);

        return $validator;
    }

    /**
     * @inheritDoc
     */
    public function buildSearchQuery(Query $query, array $inputs)
    {
        $dateFrom = Hash::get($inputs, $this->getSearchInputKey() . '.from');
        $dateTo = Hash::get($inputs, $this->getSearchInputKey() . '.to');
        if (isset($dateFrom) && $dateFrom !== '') {
            $fromWhere['OR'][] = function ($expression) use ($dateFrom) {
                $expression->gte(
                    $this->driverExpression()->cast(
                        $this->getTableAlias() . '.' . $this->getColumnName() . '_to',
                        TableSchemaInterface::TYPE_DATE
                    ),
                    $dateFrom
                );

                return $expression;
            };
            $fromWhere['OR'][] = [
                'AND' => [
                    $this->getTableAlias() . '.' . $this->getColumnName() . '_from IS NOT' => null,
                    $this->getTableAlias() . '.' . $this->getColumnName() . '_to IS' => null,
                ],
            ];
        }
        if (isset($dateTo) && $dateTo !== '') {
            $toWhere['OR'][] = function ($expression) use ($dateTo) {
                $expression->lte(
                    $this->driverExpression()->cast(
                        $this->getTableAlias() . '.' . $this->getColumnName() . '_from',
                        TableSchemaInterface::TYPE_DATE
                    ),
                    $dateTo
                );

                return $expression;
            };
            $toWhere['OR'][] = [
                'AND' => [
                    $this->getTableAlias() . '.' . $this->getColumnName() . '_to IS NOT' => null,
                    $this->getTableAlias() . '.' . $this->getColumnName() . '_from IS' => null,
                ],
            ];
        }

        if (!empty($fromWhere)) {
            $where[] = $fromWhere;
        }
        if (!empty($toWhere)) {
            $where[] = $toWhere;
        }

        if (!empty($where)) {
            $query->where(['AND' => $where]);
        }

        return $query;
    }

    /**
     * @inheritDoc
     */
    public function buildFieldsetValidator(Validator $validator)
    {
        $isRequired = $this->getFormItem()->isRequiredItem() && !$this->isAdmin();

        $validator
            ->requirePresence($this->getColumnName() . '_from', $isRequired, __(Message::ERROR_NOT_EMPTY))
            ->allowEmptyString($this->getColumnName() . '_from', __(Message::ERROR_NOT_EMPTY), !$isRequired)
            ->add($this->getColumnName() . '_from', [
                'date' => [
                    'rule' => ['date', 'ymd'],
                    'last' => true,
                    'message' => __(Message::ERROR_DATE),
                ],
            ]);

        $validator
            ->requirePresence($this->getColumnName() . '_to', $isRequired, __(Message::ERROR_NOT_EMPTY))
            ->allowEmptyString($this->getColumnName() . '_to', __(Message::ERROR_NOT_EMPTY), !$isRequired)
            ->add($this->getColumnName() . '_to', [
                'date' => [
                    'rule' => ['date', 'ymd'],
                    'last' => true,
                    'message' => __(Message::ERROR_DATE),
                ],
                'compareFields' => [
                    'rule' => ['compareDateTimeFields', $this->getColumnName() . '_from', '>='],
                    'last' => true,
                    'message' => __(Message::ERROR_OVER_FROM_DATETIME),
                    'on' => function () use ($validator) {
                        if (!($validator instanceof KuchenValidator)) {
                            throw new CakeException();
                        }

                        return $validator->isValid($this->getColumnName() . '_from');
                    },
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
        if (!isset($data)) {
            return null;
        }

        $result = [];
        $dataArray = explode($this->delimiter, $data);
        if (isset($dataArray[0])) {
            $result['users']['expiration_date_from'] = $dataArray[0];
        }

        if (isset($dataArray[1])) {
            $result['users']['expiration_date_to'] = $dataArray[1];
        }

        return $result;
    }

    /**
     * @inheritDoc
     */
    public function getCsvDescription(?array $options = null)
    {
        return Configure::readOrFail('Setting.csv.import.sample.expirationDate');
    }

    /**
     * @inheritDoc
     */
    protected function getCsvErrorMessages(array $errors): array
    {
        return array_merge(
            Hash::get($errors, 'expiration_date_from', []),
            Hash::get($errors, 'expiration_date_to', [])
        );
    }

    /**
     * @inheritDoc
     */
    public function getMailReplaceToken()
    {
        return 'user_expiration_date';
    }

    /**
     * @inheritDoc
     */
    public function getMailOutputValue(array $data, ?array $options = null)
    {
        return $this->formatFromTo(
            Hash::get($data, 'user.expiration_date_from'),
            Hash::get($data, 'user.expiration_date_to')
        );
    }

    /**
     * 登録済の 有効期間FROM を取得する
     *
     * @return string|null
     */
    protected function getExpirationDateFrom(): ?string
    {
        /** @var \App\Model\Table\UsersTable $usersTable */
        $usersTable = $this->getTableLocator()->get('Users');
        /** @var \App\Model\Entity\User $user */
        $user = $usersTable->find('edit')
            ->select('expiration_date_from')
            ->where(['Users.id' => (int)$this->getConfig('userId')])
            ->first();

        if (!isset($user['expiration_date_from'])) {
            return null;
        }
        $expirationDateFrom = DateTimeUtility::convertToDateObject($user['expiration_date_from']);
        if (isset($expirationDateFrom)) {
            return $expirationDateFrom->format('Y/m/d');
        }

        return null;
    }

    /**
     * 登録済の 有効期間TO を取得する
     *
     * @return string|null
     */
    protected function getExpirationDateTo(): ?string
    {
        /** @var \App\Model\Table\UsersTable $usersTable */
        $usersTable = $this->getTableLocator()->get('Users');
        /** @var \App\Model\Entity\User $user */
        $user = $usersTable->find('edit')
            ->select('expiration_date_to')
            ->where(['Users.id' => (int)$this->getConfig('userId')])
            ->first();

        if (!isset($user['expiration_date_to'])) {
            return null;
        }
        $expirationDateTo = DateTimeUtility::convertToDateObject($user['expiration_date_to']);
        if (isset($expirationDateTo)) {
            return $expirationDateTo->format('Y/m/d');
        }

        return null;
    }

    /**
     * FROM～TOの形へフォーマットする
     *
     * @param mixed $from FROMの値
     * @param mixed $to TOの値
     * @return string
     */
    protected function formatFromTo($from, $to)
    {
        $from = DateTimeUtility::convertToDateObject($from);
        $to = DateTimeUtility::convertToDateObject($to);

        $result = '';
        if (isset($from)) {
            $result .= $from->format('Y/m/d');
        }
        if (isset($from) || isset($to)) {
            $result .= $this->delimiter;
        }
        if (isset($to)) {
            $result .= $to->format('Y/m/d');
        }

        return $result;
    }
}
