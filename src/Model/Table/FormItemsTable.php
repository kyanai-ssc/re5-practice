<?php
declare(strict_types=1);

namespace App\Model\Table;

use App\Locale\Message;
use App\Model\AppTable;
use App\Model\Entity\FormGroup;
use App\Model\Entity\FormItem;
use App\Model\InputType\InputTypeManagerTrait;
use App\Model\InputType\Item\Type\CsvInputInterface;
use App\Model\InputType\Item\Type\CsvOutputInterface;
use App\Model\InputType\Item\Type\ListOutputInterface;
use App\Model\InputType\Item\Type\SearchDisplayInterface;
use App\Model\InputType\Manager\DateSelect;
use App\Model\InputType\Manager\ReservationOption;
use App\Utility\SmartLock\SmartLockLinkage;
use Cake\Core\Configure;
use Cake\Core\Exception\CakeException;
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\ORM\Query;
use Cake\Utility\Hash;
use Cake\Validation\Validator;

/**
 * FormItems Model
 *
 * @method \App\Model\Entity\FormItem newEmptyEntity()
 * @method \App\Model\Entity\FormItem newEntity(array $data, array $options = [])
 * @method \App\Model\Entity\FormItem[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\FormItem get($primaryKey, $options = [])
 * @method \App\Model\Entity\FormItem findOrCreate($search, ?callable $callback = null, $options = [])
 * @method \App\Model\Entity\FormItem patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\FormItem[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\FormItem|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\FormItem saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\FormItem[]|\Cake\Datasource\ResultSetInterface|false saveMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\FormItem[]|\Cake\Datasource\ResultSetInterface saveManyOrFail(iterable $entities, $options = [])
 * @method \App\Model\Entity\FormItem[]|\Cake\Datasource\ResultSetInterface|false deleteMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\FormItem[]|\Cake\Datasource\ResultSetInterface deleteManyOrFail(iterable $entities, $options = [])
 */
class FormItemsTable extends AppTable
{
    use InputTypeManagerTrait;

    public const CSV_COLUMN_USER_ID = -1;
    public const CSV_COLUMN_USER_AUTHORITY = -2;
    public const CSV_COLUMN_LOGIN_ID = -3;
    public const CSV_COLUMN_PASSWORD = -4;
    public const CSV_COLUMN_MAIL = -5;
    public const CSV_COLUMN_GUEST_FLG = -6;
    public const CSV_COLUMN_WITHDRAWAL_FLG = -7;
    public const CSV_COLUMN_USER_CREATED = -8;
    public const CSV_COLUMN_USER_MODIFIED = -9;
    public const CSV_COLUMN_RESERVATION_ID = -10;
    public const CSV_COLUMN_RESERVATION_STATUS_ID = -11;
    public const CSV_COLUMN_LABEL = -12;
    public const CSV_COLUMN_EVENT_NAME = -13;
    public const CSV_COLUMN_USAGE_DATE = -14;
    public const CSV_COLUMN_USAGE_TIME = -15;
    public const CSV_COLUMN_RESERVATION_TIME = -16;
    public const CSV_COLUMN_EVENT_PLANS = -17;
    public const CSV_COLUMN_RESERVATION_NUMBER = -18;
    public const CSV_COLUMN_CHARGE = -19;
    public const CSV_COLUMN_PAYMENT_METHOD = -20;
    public const CSV_COLUMN_PAYMENT_STATUS = -21;
    public const CSV_COLUMN_RESERVATION_CREATED = -22;
    public const CSV_COLUMN_RESERVATION_MODIFIED = -23;
    public const CSV_COLUMN_USER_ADDITION = -24;
    public const CSV_COLUMN_RESERVATION_ADDITION = -25;
    public const CSV_COLUMN_MAIL_DELIVERY_STATUS = -26;
    public const CSV_COLUMN_RECEPTION_STATUS_ID = -27;
    public const CSV_COLUMN_EXPIRATION_DATE = -28;
    public const CSV_COLUMN_AKERUN_USER_ID = -29;
    public const CSV_COLUMN_SMART_LOCK_PIN = -30;
    public const CSV_COLUMN_SMART_LOCK_KEY_URL = -31;
    public const CSV_COLUMN_SMART_LOCK_UNIVERSAL_ACCESS_KEY = -32;

    public const ITEM_NAME_MAX = 100;
    public const ITEM_DESCRIPTION_MAX = 10000;

    /**
     * @var mixed|null
     */
    protected $formItems = null;

    /**
     * @inheritDoc
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->belongsTo('FormGroups', [
            'foreignKey' => 'form_group_id',
            'joinType' => 'INNER',
        ]);
        $this->hasOne('FormItemOptionGroups', [
            'foreignKey' => 'form_item_id',
        ]);
        $this->hasMany('EventRemarks', [
            'foreignKey' => 'form_item_id',
        ]);
        $this->hasMany('FormItemChoices', [
            'foreignKey' => 'form_item_id',
            'dependent' => false,
            'saveStrategy' => 'append',
            'sort' => [
                'FormItemChoices.sort_no' => 'ASC',
                'FormItemChoices.id' => 'ASC',
            ],
        ]);
        $this->hasMany('FormItemDetails', [
            'foreignKey' => 'form_item_id',
            'dependent' => false,
            'saveStrategy' => 'append',
            'sort' => [
                'FormItemDetails.sort_no' => 'ASC',
                'FormItemDetails.id' => 'ASC',
            ],
        ]);
        $this->hasMany('FormPatternDisplayTypes', [
            'foreignKey' => 'form_item_id',
        ]);
        $this->hasMany('ReservationAdditions', [
            'foreignKey' => 'form_item_id',
        ]);
        $this->hasMany('ReservationOptions', [
            'foreignKey' => 'form_item_id',
        ]);
        $this->hasMany('UserAdditions', [
            'foreignKey' => 'form_item_id',
        ]);
        $this->hasMany('UserAuthorities', [
            'foreignKey' => 'login_name_form_item_id',
        ]);
    }

    /**
     * @inheritDoc
     */
    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->requirePresence('input_type', true, __(Message::ERROR_NOT_EMPTY_SELECT))
            ->allowEmptyString('input_type', __(Message::ERROR_NOT_EMPTY_SELECT), false)
            ->add('input_type', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'inList' => [
                    'rule' => ['inList', array_keys($this->getFieldValueOptions('inputType'))],
                    'last' => true,
                    'message' => __(Message::ERROR_IN_LIST),
                ],
                'equalTo' => [
                    'rule' => function ($value, $context) {
                        $formItem = null;
                        try {
                            /** @throws \Cake\Datasource\Exception\RecordNotFoundException */
                            $formItem = $this->get(Hash::get($context['data'], 'id'));
                        } catch (RecordNotFoundException $e) {
                            return false;
                        }

                        if (((string)$value) !== (string)$formItem->get('input_type')) {
                            return false;
                        }

                        return true;
                    },
                    'last' => true,
                    'message' => __(Message::ERROR_IN_LIST),
                    'on' => function ($context) {
                        if ($context['newRecord']) {
                            return false;
                        }

                        return true;
                    },
                ],
            ]);

        $validator
            ->requirePresence('name', false)
            ->allowEmptyString('name')
            ->add('name', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'maxLength' => [
                    'rule' => ['maxLength', static::ITEM_NAME_MAX],
                    'last' => true,
                    'message' => __(Message::ERROR_MAX_LENGTH, static::ITEM_NAME_MAX),
                ],
            ]);

        $validator
            ->requirePresence('required_flg', true, __(Message::ERROR_NOT_EMPTY_SELECT))
            ->allowEmptyString('required_flg', __(Message::ERROR_NOT_EMPTY_SELECT), false)
            ->add('required_flg', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'inList' => [
                    'rule' => ['inList', array_keys($this->getFieldValueOptions('requiredFlg'))],
                    'last' => true,
                    'message' => __(Message::ERROR_IN_LIST),
                ],
                'equalToOn' => [
                    'rule' => ['inList', [Configure::readOrFail('Master.common.flg.on')]],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                    'on' => function ($context) {
                        $inputType = Hash::get($context['data'], 'input_type');
                        if (!$this->validateInputType($inputType)) {
                            return false;
                        }

                        if ($this->inputTypeManager((int)$inputType, true)->canSelectRequired()) {
                            return false;
                        }

                        if (!$this->inputTypeManager((int)$inputType, true)->required()) {
                            return false;
                        }

                        return true;
                    },
                ],
                'equalToOff' => [
                    'rule' => ['inList', [Configure::readOrFail('Master.common.flg.off')]],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                    'on' => function ($context) {
                        $inputType = Hash::get($context['data'], 'input_type');
                        if (!$this->validateInputType($inputType)) {
                            return false;
                        }

                        if ($this->inputTypeManager((int)$inputType, true)->canSelectRequired()) {
                            return false;
                        }

                        if ($this->inputTypeManager((int)$inputType, true)->required()) {
                            return false;
                        }

                        return true;
                    },
                ],
            ]);

        $validator
            ->requirePresence('reservation_display_flg', true, __(Message::ERROR_NOT_EMPTY_SELECT))
            ->allowEmptyString('reservation_display_flg', __(Message::ERROR_NOT_EMPTY_SELECT), false)
            ->add('reservation_display_flg', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'inList' => [
                    'rule' => ['inList', array_keys($this->getFieldValueOptions('reservationDisplayFlg'))],
                    'last' => true,
                    'message' => __(Message::ERROR_IN_LIST),
                ],
                'equalToOff' => [
                    'rule' => ['inList', [Configure::readOrFail('Master.common.flg.off')]],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                    'on' => function ($context) {
                        $inputType = Hash::get($context['data'], 'input_type');
                        if (!$this->validateInputType($inputType)) {
                            return false;
                        }
                        if ($this->inputTypeManager((int)$inputType, true)->canReservationDisplay()) {
                            return false;
                        }

                        return true;
                    },
                ],
            ]);

        $validator
            ->requirePresence('description', false)
            ->allowEmptyString('description')
            ->add('description', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'maxLength' => [
                    'rule' => ['maxLength', static::ITEM_DESCRIPTION_MAX],
                    'last' => true,
                    'message' => __(Message::ERROR_MAX_LENGTH, static::ITEM_DESCRIPTION_MAX),
                ],
            ]);

        $formItemChoicesRequired = function ($context) {
            $inputType = Hash::get($context['data'], 'input_type');
            if (!$this->validateInputType($inputType)) {
                return false;
            }
            if (!$this->inputTypeManager((int)$inputType, true)->hasFormItemChoices()) {
                return false;
            }

            return true;
        };
        $validator
            ->requirePresence('form_item_choices', function ($context) use ($formItemChoicesRequired) {
                return $formItemChoicesRequired($context);
            }, __(Message::ERROR_ONE_OR_MORE))
            ->allowEmptyArray(
                'form_item_choices',
                __(Message::ERROR_ONE_OR_MORE),
                function ($context) use ($formItemChoicesRequired) {
                    return !$formItemChoicesRequired($context);
                }
            )
            ->add('form_item_choices', [
                'isArray' => [
                    'rule' => ['isArray'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
            ]);

        $formItemDetailsRequired = function ($context) {
            $inputType = Hash::get($context['data'], 'input_type');
            if (!$this->validateInputType($inputType)) {
                return false;
            }
            if (!$this->inputTypeManager((int)$inputType, true)->hasFormItemDetails()) {
                return false;
            }

            return true;
        };
        $validator
            ->requirePresence('form_item_details', function ($context) use ($formItemDetailsRequired) {
                return $formItemDetailsRequired($context);
            }, __(Message::ERROR_ONE_OR_MORE))
            ->allowEmptyArray(
                'form_item_details',
                __(Message::ERROR_ONE_OR_MORE),
                function ($context) use ($formItemDetailsRequired) {
                    return !$formItemDetailsRequired($context);
                }
            )
            ->add('form_item_details', [
                'isArray' => [
                    'rule' => ['isArray'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
            ]);

        $formItemOptionGroupRequired = function ($context) {
            $inputType = Hash::get($context['data'], 'input_type');
            if (!$this->validateInputType($inputType)) {
                return false;
            }
            if (!$this->inputTypeManager((int)$inputType, true)->hasFormItemOptionGroups()) {
                return false;
            }

            return true;
        };
        $validator
            ->requirePresence('form_item_option_group', function ($context) use ($formItemOptionGroupRequired) {
                return $formItemOptionGroupRequired($context);
            }, __(Message::ERROR_ONE_OR_MORE))
            ->allowEmptyArray(
                'form_item_option_group',
                __(Message::ERROR_ONE_OR_MORE),
                function ($context) use ($formItemOptionGroupRequired) {
                    return !$formItemOptionGroupRequired($context);
                }
            )
            ->add('form_item_option_group', [
                'isArray' => [
                    'rule' => ['isArray'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
            ]);

        return $validator;
    }

    /**
     * @inheritDoc
     */
    protected function buildFieldValueOptions()
    {
        /** @var \App\Model\Table\FormItemChoicesTable $formItemChoicesTable */
        $formItemChoicesTable = $this->getAssociation('FormItemChoices')->getTarget();
        /** @var \App\Model\Table\FormItemDetailsTable $formItemDetailsTable */
        $formItemDetailsTable = $this->getAssociation('FormItemDetails')->getTarget();
        /** @var \App\Model\Table\FormItemOptionGroupsTable $formItemOptionGroupsTable */
        $formItemOptionGroupsTable = $this->getAssociation('FormItemOptionGroups')->getTarget();

        $fieldValueOptions = [
            'inputType' => Configure::readOrFail('Master.form.inputType'),
            'requiredFlg' => Configure::readOrFail('Master.form.requiredFlg'),
            'reservationDisplayFlg' => Configure::readOrFail('Master.form.reservationDisplayFlg'),
            'formItemChoices' => $formItemChoicesTable->getFieldValueOptions(),
            'formItemDetails' => $formItemDetailsTable->getFieldValueOptions(),
            'formItemOptionGroups' => $formItemOptionGroupsTable->getFieldValueOptions(),
        ];

        return $fieldValueOptions;
    }

    /**
     * @inheritDoc
     */
    protected function buildDefaultFieldValues()
    {
        $defaultFieldValues = [
            'input_type' => FormItem::INPUT_TYPE_TEXT,
        ];

        return $defaultFieldValues;
    }

    /**
     * 編集時のファインダー
     *
     * @param \Cake\ORM\Query $query The query to find with
     * @param array $options The options to use for the find
     * @return \Cake\ORM\Query The query builder
     */
    public function findEdit(Query $query, array $options)
    {
        $smartLock = new SmartLockLinkage();
        if (!$smartLock->useAkerun()) {
            $query->where([
                'FormItems.smart_lock_type IS' => null,
            ]);
        }
        $query->contain([
            'FormItemDetails' => [],
            'FormItemChoices' => [],
            'FormItemOptionGroups' => [],
            'FormItemOptionGroups.FormItemOptions' => [],
        ], true);

        return $query;
    }

    /**
     * 項目取得時のファインダー
     *
     * @param \Cake\ORM\Query $query The query to find with
     * @param array $options The options to use for the find
     * @return \Cake\ORM\Query The query builder
     */
    public function findItems(Query $query, array $options)
    {
        $smartLock = new SmartLockLinkage();
        if (!$smartLock->useAkerun()) {
            $query->where([
                'FormItems.smart_lock_type IS' => null,
            ]);
        }
        $query->contain([
            'FormItemDetails' => [],
            'FormItemChoices' => [],
        ], true);

        $query->order([
            'FormItems.sort_no' => 'ASC',
            'FormItems.id' => 'ASC',
        ], true);

        $inputType = Hash::get($options, 'inputs.input_type');
        if (!empty($inputType)) {
            $query->where(['FormItems.input_type' => $inputType]);
        }

        $formGroupId = Hash::get($options, 'inputs.form_group_id');
        if (!empty($formGroupId)) {
            $query->where(['FormItems.form_group_id' => $formGroupId]);
        }

        $contains = Hash::get($options, 'contains');
        if (isset($contains) && $contains !== '' && count((array)$contains) > 0) {
            $query->contain($contains, true);

            $formType = Hash::get($options, 'inputs.form_type');
            if (isset($formType) && $formType !== '') {
                $query->where(['FormGroups.form_type' => $formType]);
            }
            $query->order([
                'FormGroups.sort_no' => 'ASC',
                'FormItems.sort_no' => 'ASC',
                'FormItems.id' => 'ASC',
            ], true);
        }

        return $query;
    }

    /**
     * 注釈タイプ取得時のファインダー
     *
     * @param \Cake\ORM\Query $query The query to find with
     * @param array $options The options to use for the find
     * @return \Cake\ORM\Query The query builder
     */
    public function findRemarkList(Query $query, array $options)
    {
        $query = $this->callFinder('items', $query, [
            'inputs' => [
                'input_type' => FormItem::INPUT_TYPE_EVENT_REMARK,
            ],
        ]);
        $query = $this->callFinder('list', $query, [
            'keyField' => 'id',
            'valueField' => 'name',
        ]);
        $query->enableHydration(false);

        return $query;
    }

    /**
     * 入力タイプを検証
     *
     * @param mixed $inputType フォーム種別
     * @return bool 検証結果
     */
    public function validateInputType($inputType)
    {
        $validator = new Validator();
        $validator
            ->requirePresence('input_type', true)
            ->allowEmptyString('input_type', null, false)
            ->add('input_type', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                ],
                'inList' => [
                    'rule' => ['inList', array_keys($this->getFieldValueOptions('inputType'))],
                    'last' => true,
                ],
            ]);

        $errors = $validator->validate(['input_type' => $inputType]);
        if (count($errors) > 0) {
            return false;
        }

        return true;
    }

    /**
     * フォーム項目一覧を取得
     *
     * @param int|null $formType フォーム種別
     * @return array フォーム項目一覧
     */
    public function getFormItems(?int $formType = null)
    {
        if (!isset($this->formItems)) {
            /** @var \App\Model\Table\FormGroupsTable $formGroupsTable */
            $formGroupsTable = $this->getAssociation('FormGroups')->getTarget();

            foreach ($formGroupsTable->getFormGroups() as $formGroup) {
                $formItems = $formGroup->get('form_items');
                if (is_array($formItems)) {
                    foreach ($formItems as $formItem) {
                        $this->formItems[''][$formItem->get('id')] = $formItem;
                        $this->formItems[$formGroup->get('form_type')][$formItem->get('id')] = $formItem;
                    }
                }
            }
        }

        return $this->formItems[$formType];
    }

    /**
     * フォーム項目を取得
     *
     * @param int $formItemId フォーム項目ID
     * @return \App\Model\Entity\FormItem|null フォーム項目
     */
    public function getFormItem(int $formItemId)
    {
        return Hash::get($this->getFormItems(), (string)$formItemId);
    }

    /**
     * 入力タイプからフォーム項目を取得
     *
     * @param int $inputType 入力タイプ
     * @return array フォーム項目
     */
    public function getFormItemByInputType(int $inputType)
    {
        $formItems = [];
        foreach ($this->getFormItems() as $formItem) {
            if ((string)$formItem->get('input_type') === ((string)$inputType)) {
                $formItems[$formItem->get('id')] = $formItem;
            }
        }

        return $formItems;
    }

    /**
     * 検索可能なフォーム項目一覧を取得
     *
     * @param int|null $formType フォーム種別
     * @return array フォーム項目一覧
     */
    public function getSearchableFormItems(?int $formType = null)
    {
        $formItems = [];
        foreach ($this->getFormItems($formType) as $formItem) {
            /** @var \App\Model\InputType\AbstractInputTypeItem $inputTypeItem */
            $inputTypeItem = $formItem->getInputTypeItem();

            if ($inputTypeItem instanceof SearchDisplayInterface) {
                $formItems[$formItem->getInputTypeItem()->getFormType()][$formItem->get('id')] = $formItem;
            }
        }

        return $formItems;
    }

    /**
     * 一覧画面で表示可能なフォーム項目一覧を取得
     *
     * @param int|null $formType フォーム種別
     * @return array フォーム項目一覧
     */
    public function getListDisplayableFormItems(?int $formType = null)
    {
        $formItems = [];
        foreach ($this->getFormItems($formType) as $formItem) {
            $inputTypeItem = $formItem->getInputTypeItem();
            if ($inputTypeItem instanceof ListOutputInterface) {
                $formItems[$formItem->getInputTypeItem()->getFormType()][$formItem->get('id')] = $formItem;
            }
        }

        return $formItems;
    }

    /**
     * CSVの出力項目を生成
     *
     * @param string $ioType 種別
     * @param array $columns カラム
     * @param int|null $formType フォームタイプ
     * @return array 出力項目
     */
    public function generateCsvItems(string $ioType, array $columns, ?int $formType = null)
    {
        $formItems = [];
        $formItemsByInputType = [];
        foreach ($this->getFormItems($formType) as $formItem) {
            $inputTypeItem = $formItem->getInputTypeItem();

            if (
                ($ioType === 'output' && $inputTypeItem instanceof CsvOutputInterface && $inputTypeItem->canCsvOutput())
                || ($ioType === 'input' && $inputTypeItem instanceof CsvInputInterface)
            ) {
                $formItems[$formItem->get('id')] = $formItem;
                if (!isset($formItemsByInputType[$formItem->get('input_type')])) {
                    $formItemsByInputType[$formItem->get('input_type')] = $formItem;
                }
            }
        }

        $csvItemsWithoutAddition = [];
        foreach ($columns as $column) {
            $inputType = Configure::read('Setting.csv.common.headerInputType.' . $column);
            if (isset($inputType)) {
                $csvItemsWithoutAddition[$column] = $formItemsByInputType[$inputType];
                unset($formItems[$formItemsByInputType[$inputType]->get('id')]);
            } else {
                $csvItemsWithoutAddition[$column] = $column;
            }
        }

        $csvItems = [];
        $additionFormTypes = [
            static::CSV_COLUMN_USER_ADDITION => FormGroup::FORM_TYPE_USER,
            static::CSV_COLUMN_RESERVATION_ADDITION => FormGroup::FORM_TYPE_RESERVATION,
        ];
        foreach ($csvItemsWithoutAddition as $column => $item) {
            if (isset($additionFormTypes[$column])) {
                foreach ($formItems as $formItem) {
                    $inputTypeItem = $formItem->getInputTypeItem();
                    if ((string)$inputTypeItem->getFormType() === ((string)$additionFormTypes[$column])) {
                        $csvItems[$column][$formItem->get('id')] = $formItem;
                    }
                }
            } else {
                $csvItems[$column] = $item;
            }
        }

        return $csvItems;
    }

    /**
     * CSVのヘッダを生成
     *
     * @param array $csvItems 出力項目
     * @param array $headerTemplate ヘッダテンプレート
     * @return array ヘッダ
     */
    public function generateCsvHeader(array $csvItems, array $headerTemplate)
    {
        $headerKey = function ($column, $item) {
            $additions = [
                static::CSV_COLUMN_USER_ADDITION => true,
                static::CSV_COLUMN_RESERVATION_ADDITION => true,
            ];
            $key = null;
            if ($item instanceof FormItem && isset($additions[$column])) {
                $key = $column . '_' . $item->get('id');
            } else {
                $key = $column;
            }

            return $key;
        };

        $header = [];
        foreach ($csvItems as $column => $csvItem) {
            $headerValue = Hash::get($headerTemplate, $column);
            if (is_array($csvItem)) {
                foreach ($csvItem as $item) {
                    $key = call_user_func($headerKey, $column, $item);
                    $header[$key] = $this->csvFormat()->csvHeader($headerValue, $item);
                }
            } else {
                $key = call_user_func($headerKey, $column, $csvItem);
                $header[$key] = $this->csvFormat()->csvHeader($headerValue, $csvItem);
            }
        }

        return $header;
    }

    /**
     * CSVへ出力するデータを生成
     *
     * @param array $csvItems 出力項目
     * @param callable $callback コールバック
     * @param array $options オプション
     * @return array データ
     */
    public function generateCsvData(array $csvItems, $callback, array $options = [])
    {
        $headerKey = function ($column, $item) {
            $additions = [
                static::CSV_COLUMN_USER_ADDITION => true,
                static::CSV_COLUMN_RESERVATION_ADDITION => true,
            ];
            $key = null;
            if ($item instanceof FormItem && isset($additions[$column])) {
                $key = $column . '_' . $item->get('id');
            } else {
                $key = $column;
            }

            return $key;
        };
        $replace = function ($column, $item, $callback, $options) {
            $value = null;
            if ($item instanceof FormItem) {
                $inputTypeItem = $item->getInputTypeItem();
                if (!($inputTypeItem instanceof CsvOutputInterface)) {
                    throw new CakeException();
                }
                $value = $inputTypeItem->getCsvOutputValue($options);
            } else {
                $value = call_user_func($callback, $item, $options);
            }

            return $value;
        };

        $data = [];
        foreach ($csvItems as $column => $csvItem) {
            if (is_array($csvItem)) {
                foreach ($csvItem as $item) {
                    $key = call_user_func($headerKey, $column, $item);
                    $data[$key] = call_user_func($replace, $column, $item, $callback, $options);
                }
            } else {
                $key = call_user_func($headerKey, $column, $csvItem);
                $data[$key] = call_user_func($replace, $column, $csvItem, $callback, $options);
            }
        }

        return $data;
    }

    /**
     * CSVへ出力する説明文を生成
     *
     * @param array $csvItems 出力項目
     * @param callable $callback コールバック
     * @param array $options オプション
     * @return array データ
     */
    public function generateCsvDescription(array $csvItems, $callback, array $options = [])
    {
        $headerKey = function ($column, $item) {
            $additions = [
                static::CSV_COLUMN_USER_ADDITION => true,
                static::CSV_COLUMN_RESERVATION_ADDITION => true,
            ];
            $key = null;
            if ($item instanceof FormItem && isset($additions[$column])) {
                $key = $column . '_' . $item->get('id');
            } else {
                $key = $column;
            }

            return $key;
        };
        $replace = function ($column, $item, $callback, $options) {
            $value = null;
            if ($item instanceof FormItem) {
                $inputTypeItem = $item->getInputTypeItem();
                if (!($inputTypeItem instanceof CsvInputInterface)) {
                    throw new CakeException();
                }
                $value = $inputTypeItem->getCsvDescription($options);
            } else {
                $value = call_user_func($callback, $item, $options);
            }

            return $value;
        };

        $data = [];
        foreach ($csvItems as $column => $csvItem) {
            if (is_array($csvItem)) {
                foreach ($csvItem as $item) {
                    $key = call_user_func($headerKey, $column, $item);
                    $data[$key] = call_user_func($replace, $column, $item, $callback, $options);
                }
            } else {
                $key = call_user_func($headerKey, $column, $csvItem);
                $data[$key] = call_user_func($replace, $column, $csvItem, $callback, $options);
            }
        }

        return $data;
    }

    /**
     * 表示順を割り当てる
     *
     * @param array $entities エンティティー配列
     * @return void
     */
    public function assignSortNo(array $entities)
    {
        $sortNo = 0;
        foreach ($entities as $entity) {
            $sortNo += 1;
            $entity->set('sort_no', $sortNo);
        }

        /** @var \App\Model\Table\FormItemChoicesTable $formItemChoicesTable */
        $formItemChoicesTable = $this->getAssociation('FormItemChoices')->getTarget();
        /** @var \App\Model\Table\FormItemDetailsTable $formItemDetailsTable */
        $formItemDetailsTable = $this->getAssociation('FormItemDetails')->getTarget();
        /** @var \App\Model\Table\FormItemOptionGroupsTable $formItemOptionGroupsTable */
        $formItemOptionGroupsTable = $this->getAssociation('FormItemOptionGroups')->getTarget();

        foreach ($entities as $entity) {
            if ($entity->has('form_item_choices')) {
                $formItemChoicesTable->assignSortNo($entity->get('form_item_choices'));
            }
            if ($entity->has('form_item_details')) {
                $formItemDetailsTable->assignSortNo($entity->get('form_item_details'));
            }
            if ($entity->has('form_item_option_group')) {
                $formItemOptionGroupsTable->assignSortNo($entity->get('form_item_option_group'));
            }
        }
    }

    /**
     * フォーム種別を指定して削除を行う
     *
     * @param int $formType フォーム種別
     * @param array|null $excludeEntities エンティティー配列
     * @return void
     */
    public function deleteByFormType(int $formType, ?array $excludeEntities = null)
    {
        /** @var \App\Model\Table\FormItemChoicesTable $formItemChoicesTable */
        $formItemChoicesTable = $this->getAssociation('FormItemChoices')->getTarget();
        /** @var \App\Model\Table\FormItemDetailsTable $formItemDetailsTable */
        $formItemDetailsTable = $this->getAssociation('FormItemDetails')->getTarget();
        /** @var \App\Model\Table\FormItemOptionGroupsTable $formItemOptionGroupsTable */
        $formItemOptionGroupsTable = $this->getAssociation('FormItemOptionGroups')->getTarget();
        /** @var \App\Model\Table\FormPatternDisplayTypesTable $formPatternDisplayTypesTable */
        $formPatternDisplayTypesTable = $this->getAssociation('FormPatternDisplayTypes')->getTarget();
        /** @var \App\Model\Table\EventRemarksTable $eventRemarksTable */
        $eventRemarksTable = $this->getAssociation('EventRemarks')->getTarget();
        /** @var \App\Model\Table\UserAdditionsTable $userAdditionsTable */
        $userAdditionsTable = $this->getAssociation('UserAdditions')->getTarget();
        /** @var \App\Model\Table\ReservationAdditionsTable $reservationAdditionsTable */
        $reservationAdditionsTable = $this->getAssociation('ReservationAdditions')->getTarget();
        /** @var \App\Model\Table\ReservationOptionsTable $reservationOptionsTable */
        $reservationOptionsTable = $this->getAssociation('ReservationOptions')->getTarget();

        $formItemId = [];
        $formItemChoices = [];
        $formItemDetails = [];
        $formItemOptionGroups = [];
        if (isset($excludeEntities)) {
            foreach ($excludeEntities as $entity) {
                if ($entity->has('id')) {
                    $formItemId[] = $entity->get('id');
                }
                if ($entity->has('form_item_choices')) {
                    foreach ($entity->get('form_item_choices') as $formItemChoice) {
                        $formItemChoices[] = $formItemChoice;
                    }
                }
                if ($entity->has('form_item_details')) {
                    foreach ($entity->get('form_item_details') as $formItemDetail) {
                        $formItemDetails[] = $formItemDetail;
                    }
                }
                if ($entity->has('form_item_option_group')) {
                    $formItemOptionGroups[] = $entity->get('form_item_option_group');
                }
            }
        }

        // 予約追加情報削除
        $reservationAdditionsTable->deleteByFormType($formType, $formItemId);

        // 予約オプション削除
        $reservationOptionsTable->deleteByFormType($formType, $formItemId);

        // 会員追加情報削除
        $userAdditionsTable->deleteByFormType($formType, $formItemId);

        // 予約枠備考削除
        $eventRemarksTable->deleteByFormType($formType, $formItemId);

        // フォームパターン表示タイプ削除
        $formPatternDisplayTypesTable->deleteByFormType($formType, $formItemId);

        // フォーム項目関連データ削除
        $formItemChoicesTable->deleteByFormType($formType, $formItemChoices);
        $formItemDetailsTable->deleteByFormType($formType, $formItemDetails);
        $formItemOptionGroupsTable->deleteByFormType($formType, $formItemOptionGroups);

        $formGroupsQuery = $this->getAssociation('FormGroups')->find();
        $formGroupsQuery->select(['FormGroups.id']);
        $formGroupsQuery->where(['FormGroups.form_type' => $formType]);

        $this->deleteAll([
            ['FormItems.form_group_id IN' => $formGroupsQuery],
            $this->excludeQueryByEntities($excludeEntities),
        ]);
    }

    /**
     * Entity取得後の追加チェック
     *
     * @param \Cake\ORM\Entity $entity Entity
     * @return void
     */
    public function afterEntity($entity)
    {
        //オプションの重複チェック
        if ($entity->get('input_type') === FormItem::INPUT_TYPE_RESERVATION_OPTION) {
            $inputTypeItem = $this->inputTypeManager((int)$entity->get('input_type'), true);
            if (!($inputTypeItem instanceof ReservationOption)) {
                throw new CakeException();
            }
            $inputTypeItem->validateDuplication($entity);
        }

        //オプションの重複チェック
        if ($entity->get('input_type') === FormItem::INPUT_TYPE_DATE_SELECT) {
            $inputTypeItem = $this->inputTypeManager((int)$entity->get('input_type'), true);
            if (!($inputTypeItem instanceof DateSelect)) {
                throw new CakeException();
            }
            $inputTypeItem->filterType($entity);
        }
    }

    /**
     * Akerunユーザー名連携する会員項目取得時のファインダー
     *
     * @param \Cake\ORM\Query $query The query to find with
     * @param array $options The options to use for the find
     * @return \Cake\ORM\Query The query builder
     */
    public function findFormItemsForAkerun(Query $query, array $options)
    {
        $query->select([
            'FormItems.id',
            'FormItems.name',
        ])->contain([
            'FormGroups' => [
                'fields' => ['id'],
            ],
        ])->where([
            'FormItems.input_type IN' => [FormItem::INPUT_TYPE_TEXT, FormItem::INPUT_TYPE_FULL_NAME],
            'FormGroups.form_type' => FormGroup::FORM_TYPE_USER,
        ])->order([
            'FormGroups.sort_no' => 'ASC',
            'FormItems.sort_no' => 'ASC',
            'FormItems.id' => 'ASC',
        ], true);

        return $query;
    }

    /**
     * Akerunのスマートロックを有効化する際に，AkerunユーザーIDのデータを取得するファインダー
     *
     * @param \Cake\ORM\Query $query The query to find with
     * @param array $options The options to use for the find
     * @return \Cake\ORM\Query The query builder
     */
    public function findInitialSettingForAkerun(Query $query, array $options): Query
    {
        return $query
            ->select([
                'id',
                'description',
            ])
            ->where(['FormItems.input_type' => FormItem::INPUT_TYPE_AKERUN_USER_ID])
            ->orderAsc('FormItems.id');
    }
}
