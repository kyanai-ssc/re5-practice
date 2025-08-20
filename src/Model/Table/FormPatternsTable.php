<?php
declare(strict_types=1);

namespace App\Model\Table;

use App\Locale\Message;
use App\Model\AppTable;
use App\Model\Entity\FormGroup;
use App\Model\Entity\FormItem;
use App\Model\Entity\FormPatternDisplayType;
use App\Model\Entity\SmartLock;
use App\Utility\ArrayUtility;
use App\Utility\SmartLock\SmartLockLinkage;
use Cake\Collection\CollectionInterface;
use Cake\Core\Configure;
use Cake\Datasource\EntityInterface;
use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\Utility\Hash;
use Kuchen\Validation\Validation\Validation;

/**
 * FormPatterns Model
 *
 * @method \App\Model\Entity\FormPattern newEmptyEntity()
 * @method \App\Model\Entity\FormPattern newEntity(array $data, array $options = [])
 * @method \App\Model\Entity\FormPattern[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\FormPattern get($primaryKey, $options = [])
 * @method \App\Model\Entity\FormPattern findOrCreate($search, ?callable $callback = null, $options = [])
 * @method \App\Model\Entity\FormPattern patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\FormPattern[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\FormPattern|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\FormPattern saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\FormPattern[]|\Cake\Datasource\ResultSetInterface|false saveMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\FormPattern[]|\Cake\Datasource\ResultSetInterface saveManyOrFail(iterable $entities, $options = [])
 * @method \App\Model\Entity\FormPattern[]|\Cake\Datasource\ResultSetInterface|false deleteMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\FormPattern[]|\Cake\Datasource\ResultSetInterface deleteManyOrFail(iterable $entities, $options = [])
 */
class FormPatternsTable extends AppTable
{
    public const NAME_MAX = 100;
    public const REMARK_MAX = 1000;

    /**
     * @var mixed|null
     */
    protected $patternValidator = null;

    /**
     * フォームタイプ
     *
     * @var int
     */
    protected $formType = null;

    /**
     * フォーム情報
     *
     * @var null
     */
    protected $forms = null;

    /**
     * @inheritDoc
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->hasMany('Events', [
            'foreignKey' => 'form_pattern_id',
        ]);

        $this->hasMany('FormPatternDisplayTypes', [
            'foreignKey' => 'form_pattern_id',
            'joinType' => Query::JOIN_TYPE_LEFT,
            'saveStrategy' => 'replace',
            'dependent' => true,
        ]);
        $this->hasMany('FormPatternOptions', [
            'foreignKey' => 'form_pattern_id',
            'saveStrategy' => 'replace',
            'dependent' => true,
        ]);

        $this->hasMany('UserAuthorities', [
            'foreignKey' => 'form_pattern_id',
        ]);

        $this->getBehavior('AdminOperationLog')->setConfig([
            'saveOperation' => true,
        ]);
    }

    /**
     * @inheritDoc
     */
    public function buildRules(RulesChecker $rules): RulesChecker
    {
        $rules->add(function ($entity) {
            return $this->checkOptions($entity, $this->forms);
        }, 'checkOptions');

        return $rules;
    }

    /**
     * beforeSave callback.
     *
     * @param \Cake\Event\EventInterface $event イベント
     * @param \Cake\Datasource\EntityInterface $entity The entity that is going to be saved
     * @param \ArrayObject $options The options for the query
     * @return void
     */
    public function beforeSave($event, EntityInterface $entity, $options)
    {
        $originalData = $entity->extractOriginal(['form_pattern_display_types']);
        // 新規登録画面でグループを選択しなかった場合に第1引数がnullになったのでarrayでキャストしてから渡している
        $formItemIds = array_column((array)$originalData['form_pattern_display_types'], 'id', 'form_item_id');

        $smartLock = new SmartLockLinkage();
        // Akerunが有効でない場合でもAkerunユーザーIDの設定が登録されるように$entityにセットする
        if ($this->formType === FormGroup::FORM_TYPE_USER && !$smartLock->useAkerun()) {
            if ($entity->isNew()) {
                /** @var \App\Model\Table\FormItemsTable $formItemsTable */
                $formItemsTable = $this->getTableLocator()->get('FormItems');
                /** @var \App\Model\Table\FormPatternDisplayTypesTable $formPatternDisplayTypesTable */
                $formPatternDisplayTypesTable = $this->getTableLocator()->get('FormPatternDisplayTypes');

                $akerunFormItems = $formItemsTable->find()
                    ->select(['id'])
                    ->where(['smart_lock_type' => SmartLock::TYPE_AKERUN])
                    ->toArray();
                foreach ($akerunFormItems as $akerunFormItem) {
                    if (!isset($akerunFormItem['id'])) {
                        continue;
                    }
                    $formPatternDisplayType = $formPatternDisplayTypesTable->newEntity([
                        'form_item_id' => $akerunFormItem['id'],
                    ]);
                    $formPatternDisplayType->set(
                        'display_type',
                        FormPatternDisplayType::DISPLAY_TYPE_DISPLAY_ONLY_ADMIN
                    );
                    $formPatternDisplayType->set(
                        'app_display_flg',
                        FormPatternDisplayType::APP_DISPLAY_FLG_OFF
                    );
                    $addFormPatternDisplayTypes[] = $formPatternDisplayType;
                }
            } else {
                foreach ($originalData['form_pattern_display_types'] as $formPatternDisplayType) {
                    if (
                        isset($formPatternDisplayType['form_item']['smart_lock_type'])
                        && $formPatternDisplayType['form_item']['smart_lock_type'] === SmartLock::TYPE_AKERUN
                    ) {
                        $addFormPatternDisplayTypes[] = $formPatternDisplayType;
                    }
                }
            }
            if (isset($addFormPatternDisplayTypes)) {
                $formPatternDisplayTypes = array_merge(
                    (array)$entity->get('form_pattern_display_types'),
                    $addFormPatternDisplayTypes
                );
                $entity->set('form_pattern_display_types', $formPatternDisplayTypes);
            }
        }

        foreach ((array)$entity->get('form_pattern_display_types') as $index => $val) {
            if (isset($formItemIds[$val['form_item_id']])) {
                $val->set('id', $formItemIds[$val['form_item_id']]);
                $val->setNew(false);
            }
        }

        $originalData = $entity->extractOriginal(['form_pattern_options']);
        if (is_array($originalData['form_pattern_options'])) {
            $formPatternIds = array_column($originalData['form_pattern_options'], 'id', 'form_item_option_id');
        }

        if ($this->formType === FormGroup::FORM_TYPE_RESERVATION && !empty($entity->get('form_pattern_options'))) {
            $setPatternOptions = null;
            foreach ($entity->get('form_pattern_options') as $index => $val) {
                if (!empty($val['form_item_option_id'])) {
                    if (isset($formPatternIds[$val['form_item_option_id']])) {
                        $val->set(
                            'id',
                            $formPatternIds[$val['form_item_option_id']]
                        );
                        $val->setNew(false);
                    }
                    $setPatternOptions[$index] = $val;
                }
            }
            if (!empty($setPatternOptions)) {
                $entity->set('form_pattern_options', $setPatternOptions);
            }
        }

        // フォーム表示パターンとオプションは別途対象のみをafterSaveで更新するため退避
        // 新規登録画面でグループを選択しなかった場合に第1引数がnullになり
        // afterSave()のデフォルト値登録が飛ばされてしまうのでarrayでキャストしてから渡している
        $options['form_pattern_display_types'] = (array)$entity['form_pattern_display_types'];
        unset($entity['form_pattern_display_types']);
        if (!empty($entity['form_pattern_options']) && !empty($options['targetOptions'])) {
            $options['form_pattern_options'] = $entity['form_pattern_options'];
            unset($entity['form_pattern_options']);
        }

        $entity->set('form_type', $this->formType);
    }

    /**
     * Model.afterSaveイベント
     *
     * @param \Cake\Event\EventInterface $event イベント
     * @param \Cake\Datasource\EntityInterface $entity The entity that is going to be saved
     * @param \ArrayObject $options The options for the query
     * @return void
     */
    public function afterSave($event, EntityInterface $entity, $options)
    {
        /** @var \App\Model\Table\FormPatternDisplayTypesTable $formPatternDisplayTypesTable */
        $formPatternDisplayTypesTable = $this->getTableLocator()->get('FormPatternDisplayTypes');
        /** @var \App\Model\Table\FormPatternOptionsTable $formPatternOptionsTable */
        $formPatternOptionsTable = $this->getTableLocator()->get('FormPatternOptions');
        /** @var \App\Model\Table\FormItemsTable $formItemsTable */
        $formItemsTable = $this->getTableLocator()->get('FormItems');

        $formPatternId = $entity->get('id');

        // フォーム表示パターン更新
        if (isset($options['form_pattern_display_types'])) {
            $insertedFormItemIds = [];

            foreach ($options['form_pattern_display_types'] as $formPatternDisplayType) {
                // 更新
                $query = $formPatternDisplayTypesTable->updateQuery();
                $query->update()
                    ->set([
                        'display_type' => $formPatternDisplayType['display_type'],
                        'app_display_flg' => $formPatternDisplayType['app_display_flg'],
                        'modified' => $this->commonData()->getNowDateTime(),
                    ])
                    ->where([
                        'form_pattern_id' => $formPatternId,
                        'form_item_id' => $formPatternDisplayType['form_item_id'],
                    ]);

                $result = $query->execute();
                if ($result->rowCount() < 1) {
                    // 更新対象が無ければ登録
                    $formPatternDisplayTypesTable
                        ->insertQuery()
                        ->insert(['form_pattern_id', 'form_item_id', 'display_type', 'app_display_flg'])
                        ->values([
                            'form_pattern_id' => $formPatternId,
                            'form_item_id' => $formPatternDisplayType['form_item_id'],
                            'display_type' => $formPatternDisplayType['display_type'],
                            'app_display_flg' => $formPatternDisplayType['app_display_flg'],
                        ])
                        ->execute();
                }

                $insertedFormItemIds[$formPatternDisplayType['form_item_id']] = $formPatternDisplayType['form_item_id'];
            }
            // 登録画面で選択されていなかったグループの項目についての登録。
            // 基本的には未設定にして「表示しない」と同じ扱いにするが、「表示しない」を選択できない項目については特定の値を入れておく
            if ($options['saveOperation']['action'] === 'add') {
                $formItems = $formItemsTable->find()
                    ->contain('FormGroups')
                    ->select(['FormItems.id', 'FormItems.input_type'])
                    ->where([
                        'FormItems.default_flg' => FormItem::DEFAULT_FLG_ON,//「表示しない」を選択できない項目はデフォルト項目のみ
                        'FormGroups.form_type' => $entity->get('form_type'),
                    ])
                    ->disableBufferedResults();

                foreach ($formItems as $formItem) {
                    if (isset($insertedFormItemIds[$formItem->get('id')])) {
                        // 画面から入力されていたらそちらを優先する
                        continue;
                    }

                    // 「表示しない」を選択できない項目の場合は特定の選択肢を選んだものとして扱う
                    $onlyDisplayType = (array)Configure::read('Master.formPattern.inList.displayOnlyItemType');
                    $onlyAdminType = (array)Configure::read('Master.formPattern.inList.onlyAdminItemType');
                    $displayAndOnlyAdminType
                        = (array)Configure::read('Master.formPattern.inList.displayAndOnlyAdminItemType');
                    if (Hash::get($onlyDisplayType, $formItem->get('input_type'), false)) {
                        $query = $formPatternDisplayTypesTable->insertQuery();
                        $query->insert(['form_pattern_id', 'form_item_id', 'display_type', 'app_display_flg'])
                            ->values([
                                'form_pattern_id' => $formPatternId,
                                'form_item_id' => $formItem->get('id'),
                                'display_type' => FormPatternDisplayType::DISPLAY_TYPE_DISPLAY,
                                'app_display_flg' => FormPatternDisplayType::APP_DISPLAY_FLG_OFF,
                            ])
                            ->execute();
                    } elseif (Hash::get($onlyAdminType, $formItem->get('input_type'), false)) {
                        $query = $formPatternDisplayTypesTable->insertQuery();
                        $query->insert(['form_pattern_id', 'form_item_id', 'display_type', 'app_display_flg'])
                            ->values([
                                'form_pattern_id' => $formPatternId,
                                'form_item_id' => $formItem->get('id'),
                                'display_type' => FormPatternDisplayType::DISPLAY_TYPE_DISPLAY_ONLY_ADMIN,
                                'app_display_flg' => FormPatternDisplayType::APP_DISPLAY_FLG_OFF,
                            ])
                            ->execute();
                    } elseif (Hash::get($displayAndOnlyAdminType, $formItem->get('input_type'), false)) {
                        $query = $formPatternDisplayTypesTable->insertQuery();
                        $query->insert(['form_pattern_id', 'form_item_id', 'display_type', 'app_display_flg'])
                            ->values([
                                'form_pattern_id' => $formPatternId,
                                'form_item_id' => $formItem->get('id'),
                                'display_type' => FormPatternDisplayType::DISPLAY_TYPE_DISPLAY,
                                'app_display_flg' => FormPatternDisplayType::APP_DISPLAY_FLG_OFF,
                            ])
                            ->execute();
                    }
                }
            }
        }

        // オプション更新(削除→登録)
        if (!empty($options['form_pattern_options']) && !empty($options['targetOptions'])) {
            // 削除
            $formPatternOptionsTable
                ->deleteQuery()
                ->where([
                    'form_pattern_id' => $formPatternId,
                    'form_item_option_id IN' => $options['targetOptions']])
                ->execute();

            $query = $formPatternOptionsTable->insertQuery();
            foreach ($options['form_pattern_options'] as $formPatternOption) {
                $query->insert(['form_pattern_id', 'form_item_option_id'])
                    ->values([
                        'form_pattern_id' => $formPatternId,
                        'form_item_option_id' => $formPatternOption['form_item_option_id'],
                    ]);
            }
            $query->execute();
        }
    }

    /**
     * @inheritDoc
     */
    protected function buildFieldValueOptions()
    {
        $formPatternDisplayTypeOrigin = Configure::readOrFail('Master.formPattern.displayType');
        $formPattern = FormPatternDisplayType::DISPLAY_TYPE_SET_LIST;
        $formPatternDisplayType = [];

        $formPatternDisplayType['addition'] = [];
        foreach ($formPattern['addition'] as $addition) {
            $formPatternDisplayType['addition'][$addition] = $formPatternDisplayTypeOrigin[$addition];
        }

        $formPatternDisplayType['noAdminEdit'] = [];
        foreach ($formPattern['noAdminEdit'] as $noAdminEdit) {
            $formPatternDisplayType['noAdminEdit'][$noAdminEdit] = $formPatternDisplayTypeOrigin[$noAdminEdit];
        }

        $formPatternDisplayType['number'] = [];
        foreach ($formPattern['number'] as $number) {
            $formPatternDisplayType['number'][$number] = $formPatternDisplayTypeOrigin[$number];
        }

        $formPatternDisplayType['displayOnly'] = [];
        foreach ($formPattern['displayOnly'] as $displayOnly) {
            $formPatternDisplayType['displayOnly'][$displayOnly] = $formPatternDisplayTypeOrigin[$displayOnly];
        }

        $formPatternDisplayType['onlyAdmin'] = [];
        foreach ($formPattern['onlyAdmin'] as $onlyAdmin) {
            $formPatternDisplayType['onlyAdmin'][$onlyAdmin] = $formPatternDisplayTypeOrigin[$onlyAdmin];
        }

        $formPatternDisplayType['displayAndOnlyAdmin'] = [];
        foreach ($formPattern['displayAndOnlyAdmin'] as $displayAndOnlyAdmin) {
            $formPatternDisplayType['displayAndOnlyAdmin'][$displayAndOnlyAdmin]
                = $formPatternDisplayTypeOrigin[$displayAndOnlyAdmin];
        }

        $fieldValueOptions = [
            'displayTypeExample' => Configure::readOrFail('Master.formPattern.displayTypeExample'),
            'displayTypeNumber' => $formPatternDisplayType['number'],
            'displayTypeNoAdminEdit' => $formPatternDisplayType['noAdminEdit'],
            'displayTypeAddition' => $formPatternDisplayType['addition'],
            'displayTypeOnly' => $formPatternDisplayType['displayOnly'],
            'displayTypeOnlyAdmin' => $formPatternDisplayType['onlyAdmin'],
            'displayAndOnlyAdmin' => $formPatternDisplayType['displayAndOnlyAdmin'],
            'displayTypeOnlyItems' => Configure::read('Master.formPattern.inList.displayOnlyItemType'),
            'displayTypeOnlyAdminItems' => Configure::read('Master.formPattern.inList.onlyAdminItemType'),
            'displayAndOnlyAdminItems'
                => Configure::read('Master.formPattern.inList.displayAndOnlyAdminItemType'),
        ];

        return $fieldValueOptions;
    }

    /**
     * フォームタイプをセット
     *
     * @param int $formType フォームタイプ
     * @return bool
     */
    public function setFormType($formType)
    {
        $this->formType = $formType;

        return empty($this->formType);
    }

    /**
     * フォームタイプを取得
     *
     * @return int フォームタイプ
     */
    public function getFormType()
    {
        return $this->formType;
    }

    /**
     * 名称の返却
     *
     * @return mixed
     */
    public function getFormTypeName()
    {
        return Configure::readOrFail('Master.formPattern.formTypeName.' . $this->formType);
    }

    /**
     * URLの返却
     *
     * @return mixed
     */
    public function getFormTypeUrl()
    {
        return Configure::readOrFail('Master.formPattern.formTypeUrl.' . $this->formType);
    }

    /**
     * パターン一覧を取得[id:name]
     *
     * @param int $type フォームタイプ
     * @return array 結果セット
     */
    public function getFormPatternList($type)
    {
        $this->setFormType($type);

        $list = $this->find('list', [
            'keyField' => 'id',
            'valueField' => 'name',
        ])->find('searchList')->select(['id', 'name'])->enableHydration(false)->toArray();

        return $list;
    }

    /**
     * フォーム項目取得用のオプション制定
     *
     * @param array $patternIds ids
     * @return array
     */
    public function getFindOptions(array $patternIds)
    {
        $options['contains'] = [
            'FormGroups',
            'FormItemOptionGroups' => ['FormItemOptions' => 'Options'],
            'FormPatternDisplayTypes' => function (Query $q) use ($patternIds) {
                if (!empty($patternIds)) {
                    return $q->where(['form_pattern_id IN' => $patternIds]);
                }

                return $q;
            },

        ];

        $options['inputs'] = ['form_type' => $this->formType];

        return $options;
    }

    /**
     * Validatorの作成
     *
     * @param mixed $forms フォーム
     * @param bool $together まとめて編集フラグ
     * @param array $formPatterns フォームパターン
     * @return void
     */
    public function createValidatorForPatterns($forms, bool $together = false, array $formPatterns = [])
    {
        $validator = $this->getValidator();
        $this->forms = $forms;

        if (!$together) {
            $validator
                ->requirePresence('name', true, __(Message::ERROR_NOT_EMPTY))
                ->allowEmptyString('name', __(Message::ERROR_NOT_EMPTY), false)
                ->add('name', [
                    'isScalar' => [
                        'rule' => ['isScalar'],
                        'last' => true,
                        'message' => __(Message::ERROR_INVALID_VALUE),
                    ],
                    'maxLength' => [
                        'rule' => ['maxLength', static::NAME_MAX],
                        'last' => true,
                        'message' => __(Message::ERROR_MAX_LENGTH, static::NAME_MAX),
                    ],
                ]);

            $validator
                ->requirePresence('remark', true, __(Message::ERROR_NOT_EMPTY))
                ->allowEmptyString('remark')
                ->add('remark', [
                    'isScalar' => [
                        'rule' => ['isScalar'],
                        'last' => true,
                        'message' => __(Message::ERROR_INVALID_VALUE),
                    ],
                    'maxLength' => [
                        'rule' => ['maxLength', static::REMARK_MAX],
                        'last' => true,
                        'message' => __(Message::ERROR_MAX_LENGTH, static::REMARK_MAX),
                    ],
                ]);
        } else {
            if (count($formPatterns) >= 1) {
                $validator
                    ->requirePresence('id', true, __(Message::ERROR_NOT_EMPTY))
                    ->allowEmptyString('id', __(Message::ERROR_NOT_EMPTY), false)
                    ->add('id', [
                        'isScalar' => [
                            'rule' => ['isScalar'],
                            'last' => true,
                            'message' => __(Message::ERROR_INVALID_VALUE),
                        ],
                        'inList' => [
                            'rule' => ['inList', array_column($formPatterns, 'id')],
                            'last' => true,
                            'message' => __(Message::ERROR_IN_LIST),
                        ],
                    ]);
            }
        }

        $optionIds = [];
        $formItems = [];
        foreach ($forms as $form) {
            $formItems[$form->get('id')] =
                [
                    'default_flg' => $form->get('default_flg'),
                    'input_type' => $form->get('input_type'),
                ];

            if ($form->input_type === FormItem::INPUT_TYPE_RESERVATION_OPTION) {
                foreach ($form->form_item_option_group->form_item_options as $formItemOption) {
                    $optionIds[$formItemOption->get('id')]['id'] = $formItemOption->get('option_id');
                    $optionIds[$formItemOption->get('id')]['group_id'] = $form->form_item_option_group->get('id');
                    $optionIds[$formItemOption->get('id')]['form_item_id'] = $form->get('id');
                }
            }
        }

        $validator
            ->requirePresence('formPatternOptions', false)
            ->allowEmptyArray('formPatternOptions')
            ->array('formPatternOptions', __(Message::ERROR_NOT_EMPTY));

        $validator
            ->requirePresence('formPatternDisplayTypes', false)
            ->allowEmptyArray('formPatternDisplayTypes')
            ->array('formPatternDisplayTypes', __(Message::ERROR_NOT_EMPTY));

        /** @var \App\Model\Table\FormPatternDisplayTypesTable $formPatternDisplayTypesTable */
        $formPatternDisplayTypesTable = $this->getTableLocator()->get('FormPatternDisplayTypes');
        /** @var \App\Model\Table\FormPatternOptionsTable $formPatternOptionsTable */
        $formPatternOptionsTable = $this->getTableLocator()->get('FormPatternOptions');

        $formPatternDisplayTypesTable->setFormItems($formItems);
        $formPatternOptionsTable->setFieldValueOptions([
            'options' => $optionIds,
            'formItems' => $formItems,
        ]);
        $formPatternDisplayTypesTable->setFieldValueOptions(
            $this->getFieldValueOptions()
        );

        $this->setValidator('default', $validator);
    }

    /**
     * @inheritDoc
     */
    protected function searchConfiguration()
    {
        $this->searchManager()
            ->like('name', [
                'before' => true,
                'after' => true,
            ]);
    }

    /**
     * 一覧のファインダー
     *
     * @param \Cake\ORM\Query $query The query to find with
     * @param array $options The options to use for the find
     * @return \Cake\ORM\Query The query builder
     */
    public function findSearchList(Query $query, array $options)
    {
        $query->where(['FormPatterns.form_type' => $this->formType]);
        $query->contain(['UserAuthorities']);

        if (Hash::get($options, 'together', false) === true) {
            $query->contain([
                'FormPatternOptions' => [
                    'fields' => [
                        'id',
                        'form_pattern_id',
                        'form_item_option_id',
                    ],
                ],
                'FormPatternDisplayTypes' => [
                    'fields' => [
                        'id',
                        'form_pattern_id',
                        'form_item_id',
                        'display_type',
                        'app_display_flg',
                    ],
                    'sort' => [
                        'FormItems.sort_no',
                        'FormItems.id',
                    ],
                ],
                'FormPatternDisplayTypes.FormItems' => [
                    'fields' => [
                        'id',
                        'sort_no',
                    ],
                ],
            ]);
        }

        $sort = Hash::get($options, 'inputs.sort', 'id');
        $direction = strtoupper(Hash::get($options, 'inputs.direction', 'asc'));
        $query
            ->order([
                    'FormPatterns.' . $sort => $direction,
                ] + [
                    'FormPatterns.id' => $direction,
                ], true);

        return $this->callFinder('search', $query, ['search' => Hash::get($options, 'inputs', [])]);
    }

    /**
     * オプション設定のチェック
     *
     * @param \Cake\Datasource\EntityInterface $entity 入力情報
     * @param \Cake\ORM\Query|null $forms 項目設定情報
     * @return bool
     */
    public function checkOptions(EntityInterface $entity, $forms)
    {
        if (is_null($forms)) {
            return false;
        }

        // 会員フォームは実施しない
        if ($this->formType === FormGroup::FORM_TYPE_USER) {
            return true;
        }

        /** @var \App\Model\Table\FormPatternOptionsTable $formPatternOptionsTable */
        $formPatternOptionsTable = $this->getTableLocator()->get('FormPatternOptions');

        // オプションチェックボックスに対応するエンティティ（POST時に画面に表示されていた項目のみ）
        $formPatternOptions = $entity->get('form_pattern_options');
        // 上記に対応するオプションID等の情報
        $fieldValueOptions = $formPatternOptionsTable->getFieldValueOptions('options');

        // POST時に画面に表示されていた項目の中に、オプション予約の項目があったかを確認している
        $optionForms = [];
        if ($forms instanceof \Cake\ORM\Query) {
            $formsItems = $forms->toArray();
            $optionForms = array_keys(
                array_column($formsItems, 'input_type'),
                FormItem::INPUT_TYPE_RESERVATION_OPTION
            );
        }

        $options = [];
        $formPatternDisplayTypes = $entity->get('form_pattern_display_types');
        // 新規登録画面でグループを選択しなかった場合に第1引数がnullになったのでarrayでキャストしてから渡している
        $formItemsIndex = array_column((array)$formPatternDisplayTypes, 'form_item_id');

        $success = true;
        if (!empty($optionForms)) {
            // オプション予約の項目のIDをキー側に入れている（POST時に画面に表示されていた項目のみ）
            $formItemIds = array_flip(array_column($fieldValueOptions, 'form_item_id'));

            // 編集時は、POST時に画面に表示されていなかった項目で使用されているオプションも考慮に入れて重複チェックをする
            if (!$entity->isNew()) {
                $query = $formPatternOptionsTable->find('checkedOptions', [
                    'inputs' => [
                        'form_pattern_id' => $entity->get('id'),
                        'exclude_form_item_ids' => array_keys($formItemIds),
                    ],
                ]);
                foreach ($query as $undisplayedFormItemOption) {
                    $options[] = $undisplayedFormItemOption['form_item_option']['option_id'];
                }
            }

            foreach ($formPatternOptions as $index => $formPatternOption) {
                $formItemOptionId = $formPatternOption->get('form_item_option_id');

                // チェックが入っていなかったチェックボックスについては何もしない
                if (empty($formItemOptionId)) {
                    unset($entity['form_pattern_options'][$index]);
                    continue;
                }

                // このループで見ているチェックボックスに対応するオプションIDが既に現れていたら重複エラー
                if (ArrayUtility::arraySearch($fieldValueOptions[$formItemOptionId]['id'], $options) !== false) {
                    $formPatternOption->setError(
                        'form_item_option_id',
                        ['_duplication' => (string)__(Message::ERROR_FORM_PATTERN_OPTION_DUPLICATE)]
                    );
                    $success = false;
                }

                // このループで見ているチェックボックスに対応するオプションIDを保存する
                $options[] = $fieldValueOptions[$formItemOptionId]['id'];

                // この項目はオプションが選択されていたと分かったので、この後に行うオプション未選択の入力チェックをする必要はなくなった
                if (
                    isset($fieldValueOptions[$formItemOptionId])
                    && Validation::notBlank($fieldValueOptions[$formItemOptionId]['group_id'])
                ) {
                    unset($formItemIds[$fieldValueOptions[$formItemOptionId]['form_item_id']]);
                }
            }

            foreach (array_keys($formItemIds) as $key) {
                $entityIndex = ArrayUtility::arraySearch($key, $formItemsIndex);
                if ($entityIndex !== false) {
                    $displayType = $formPatternDisplayTypes[$entityIndex]->get('display_type');
                    if ($displayType !== FormPatternDisplayType::DISPLAY_TYPE_HIDE) {
                        $success = false;
                        $this->setEntityErrors($entity, [
                            'form_pattern_display_types' => [
                                $entityIndex => [
                                    'display_type' => [
                                        '_notEmpty' => __(Message::ERROR_FORM_PATTERN_OPTION_REQUIRE),
                                    ],
                                ],
                            ],
                        ]);
                    }
                }
            }
        }

        return $success;
    }

    /**
     * 編集用のファインダー
     *
     * @param \Cake\ORM\Query $query The query to find with
     * @param array $options The options to use for the find
     * @return \Cake\ORM\Query The query builder
     */
    public function findEdit(Query $query, array $options)
    {
        $query->select([
            'id',
            'form_type',
            'name',
            'remark',
            'default_flg',
        ])->contain([
            'FormPatternOptions' => [
                'fields' => [
                    'id',
                    'form_pattern_id',
                    'form_item_option_id',
                ],
            ],
            'FormPatternDisplayTypes' => [
                'fields' => [
                    'id',
                    'form_pattern_id',
                    'form_item_id',
                    'display_type',
                    'app_display_flg',
                ],
                'sort' => [
                    'FormItems.sort_no',
                    'FormItems.id',
                ],
            ],
            'FormPatternDisplayTypes.FormItems' => [
                'fields' => [
                    'id',
                    'sort_no',
                    'smart_lock_type',
                ],
            ],
        ]);

        $query->formatResults(function (CollectionInterface $results) use ($options) {
            return $results->map(function ($row) use ($options) {
                $row['form_pattern_display_types'] = $this->formatDisplayData($row, $options['formItem']);

                return $row;
            });
        });

        return $query;
    }

    /**
     * 削除用ファインダー
     *
     * @param \Cake\ORM\Query $query The query to find with
     * @param array $options The options to use for the find
     * @return \Cake\ORM\Query The query builder
     */
    public function findDelete(Query $query, array $options)
    {
        $query->select([
            'id',
            'form_type',
            'name',
            'remark',
            'default_flg',
        ])->contain([
            'FormPatternOptions' => [
                'fields' => [
                    'id',
                    'form_pattern_id',
                    'form_item_option_id',
                ],
            ],
            'FormPatternDisplayTypes' => [
                'fields' => [
                    'id',
                    'form_pattern_id',
                    'form_item_id',
                    'display_type',
                ],
                'sort' => [
                    'FormItems.sort_no',
                    'FormItems.id',
                ],
            ],
            'FormPatternDisplayTypes.FormItems' => [
                'fields' => [
                    'id',
                    'sort_no',
                ],
            ],
        ]);

        return $query;
    }

    /**
     * まとめて編集用にEntityをセット
     *
     * @param array $formPatterns パターン
     * @param \Cake\ORM\Query $formItems 項目
     * @return array
     */
    public function createEntities(array $formPatterns, $formItems)
    {
        foreach ($formPatterns as $patternKey => $formPattern) {
            $formPatterns[$patternKey]['form_pattern_display_types'] = $this->formatDisplayData(
                $formPattern,
                $formItems
            );
        }

        return $formPatterns;
    }

    /**
     * form_pattern_display_typesの整形（パターンにない項目をセット）
     *
     * @param mixed $formPattern パターン
     * @param \Cake\ORM\Query $formItems 項目
     * @return mixed
     */
    private function formatDisplayData($formPattern, $formItems)
    {
        $result = Hash::combine($formPattern['form_pattern_display_types'], '{n}.form_item_id', '{n}');

        $formPatternDisplayTypesTable = $this->getTableLocator()->get('FormPatternDisplayTypes');
        $items = [];
        foreach ($formItems as $key => $formItem) {
            if (!empty($result[$formItem->get('id')])) {
                $items[$key] = $result[$formItem->get('id')];
            } else {
                $displayEntity = $formPatternDisplayTypesTable->newEmptyEntity();
                $displayEntity->set('form_item_id', $formItem->get('id'));
                $displayEntity->set('form_item', $formItem);
                $items[$key] = $displayEntity;
            }
        }

        return $items;
    }

    /**
     * チェック状態の取得
     *
     * @param \Cake\Datasource\EntityInterface $entity entity
     * @return array
     */
    public function getOptionChecked(EntityInterface $entity)
    {
        // 会員フォームはオプション無し
        if ($this->formType === FormGroup::FORM_TYPE_USER) {
            return [];
        }

        $formPatternOptions = $entity->get('form_pattern_options');
        $checked = [];

        if (is_array($formPatternOptions)) {
            foreach ($formPatternOptions as $formPatternOption) {
                $formItemOptionId = $formPatternOption->get('form_item_option_id');
                $checked[$formItemOptionId] = $formItemOptionId;
            }
        }

        return $checked;
    }
}
