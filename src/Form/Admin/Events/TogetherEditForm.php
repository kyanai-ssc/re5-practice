<?php
declare(strict_types=1);

namespace App\Form\Admin\Events;

use App\Form\AppForm;
use App\Locale\Message;
use App\Model\Entity\Event as EventEntity;
use App\Utility\ArrayUtility;
use App\Utility\SmartLock\SmartLockLinkage;
use Cake\Core\Exception\CakeException;
use Cake\Form\Schema;
use Cake\Utility\Hash;
use Cake\Validation\Validator as CakeValidator;
use Kuchen\Validation\Validation\Validator;

/**
 * まとめて編集フォーム
 */
class TogetherEditForm extends AppForm
{
    /**
     * @var array
     */
    protected $notIncludedUpdateFields = [];

    /**
     * @inheritDoc
     */
    public function initialize()
    {
        $this->notIncludedUpdateFields = ['update'];
    }

    /**
     * @inheritDoc
     */
    protected function buildFieldValueOptions()
    {
        /** @var \App\Model\Table\EventsTable $eventsTable */
        $eventsTable = $this->getTableLocator()->get('Events');

        return $eventsTable->getFieldValueOptions();
    }

    /**
     * @inheritDoc
     */
    protected function _buildSchema(Schema $schema): Schema
    {
        $schema
            ->addField('update', 'array')
            ->addField('name', 'string')
            ->addField('label_id', 'integer')
            ->addField('charge', 'integer')
            ->addField('public_from', 'datetime')
            ->addField('public_to', 'datetime')
            ->addField('public_flg', 'integer')
            ->addField('reservation_limit_future', 'integer')
            ->addField('reservation_limit_month', 'integer')
            ->addField('reservation_limit_day', 'integer')
            ->addField('reservation_limit_all', 'integer')
            ->addField('reception_period_time', '\Cake\I18n\FrozenTime')
            ->addField('reception_period_number', 'integer')
            ->addField('registration_deadline_number', 'integer')
            ->addField('registration_deadline_type', 'integer')
            ->addField('registration_deadline_time', '\Cake\I18n\FrozenTime')
            ->addField('editing_deadline_number', 'integer')
            ->addField('editing_deadline_type', 'integer')
            ->addField('editing_deadline_time', '\Cake\I18n\FrozenTime')
            ->addField('cancellation_deadline_number', 'integer')
            ->addField('cancellation_deadline_type', 'integer')
            ->addField('cancellation_deadline_time', '\Cake\I18n\FrozenTime')
            ->addField('reservation_status_id', 'integer')
            ->addField('waiting_cancellation_flg', 'integer')
            ->addField('duplication_check_flg', 'integer')
            ->addField('background_color_type', 'integer')
            ->addField('usage_time_notation', 'integer')
            ->addField('color_chip_id', 'integer')
            ->addField('stock_display_type', 'integer')
            ->addField('background_color_replace_front', 'json')
            ->addField('background_color_replace_admin', 'json')
            ->addField('format_type_display', 'json')
            ->addField('form_pattern_id', 'string')
            ->addField('stock_unit', 'string')
            ->addField('description', 'string')
            ->addField('event_images', 'array')
            ->addField('event_remarks', 'array')
            ->addField('event_tags', 'array')
            ->addField('event_stock_marks', 'array')
            ->addField('qr_code_flg', 'integer')
            ->addField('registration_deadline_criterion', 'integer')
            ->addField('editing_deadline_criterion', 'integer')
            ->addField('cancellation_deadline_criterion', 'integer')
            ->addField('event_smart_lock', 'array');

        return $schema;
    }

    /**
     * @inheritDoc
     */
    public function validationDefault(CakeValidator $validator): CakeValidator
    {
        /** @var \App\Model\Table\EventsTable $eventsTable */
        $eventsTable = $this->getTableLocator()->get('Events');

        $validator
            ->requirePresence('update', true, __(Message::ERROR_ONE_OR_MORE_SELECT))
            ->allowEmptyArray('update', __(Message::ERROR_ONE_OR_MORE_SELECT), false)
            ->array('update', __(Message::ERROR_ONE_OR_MORE_SELECT));

        $validator = $eventsTable->validationTogether($validator);

        $stockMarksValidator = $this->getTableLocator()->get('EventStockMarks')->validationDefault(
            new Validator()
        );
        $tagsValidator = $this->getTableLocator()->get('EventTags')->validationDefault(new Validator());
        $imagesValidator = $this->getTableLocator()->get('EventImages')->validationDefault(new Validator());
        $remarksValidator = $this->getTableLocator()->get('EventRemarks')->validationDefault(new Validator());
        $updateValidator = $this->addValidateUpdate(new Validator());
        $smartLocksValidator = $this->getTableLocator()->get('EventSmartLocks')->validationDefault(new Validator());

        $smartLock = new SmartLockLinkage();
        if ($smartLock->useAkerun()) {
            $smartLocksValidator
                ->requirePresence('smart_lock_key_url_flg', false, __(Message::ERROR_NOT_EMPTY_SELECT))
                ->allowEmptyString(
                    'smart_lock_key_url_flg',
                    __(Message::ERROR_NOT_EMPTY_SELECT),
                    function ($context) use ($validator) {
                        if (!($validator instanceof Validator)) {
                            throw new CakeException();
                        }

                        return !isset($validator->data['update']['event_smart_lock']['smart_lock_key_url_flg']);
                    }
                );
        }

        $validator->addNestedMany('event_stock_marks', $stockMarksValidator);
        $validator->addNestedMany('event_tags', $tagsValidator);
        $validator->addNestedMany('event_images', $imagesValidator);
        $validator->addNestedMany('event_remarks', $remarksValidator);
        $validator->addNested('update', $updateValidator);
        $validator->addNested('event_smart_lock', $smartLocksValidator);

        return $validator;
    }

    /**
     * @inheritDoc
     */
    public function execute(array $data, array $options = []): bool
    {
        /** @var \App\Model\Table\EventsTable $eventsTable */
        $eventsTable = $this->getTableLocator()->get('Events');

        $data = $eventsTable->clearUnnecessaryInputs($data);
        $success = parent::execute($data);

        if ($success) {
            $eventRemarks = Hash::get($data, 'event_remarks', []);
            if (!empty($eventRemarks)) {
                $formItems = [];
                $errors = [];
                foreach ($eventRemarks as $index => $eventRemark) {
                    if (ArrayUtility::arraySearch($eventRemark['form_item_id'], $formItems) !== false) {
                        $errors['event_remarks'][$index]['form_item_id']['_duplication'] = __(
                            Message::ERROR_DUPLICATION
                        );
                        $success = false;
                    }
                    $formItems[] = $eventRemark['form_item_id'];
                }
                $this->setErrors($errors);
            }
        }

        return $success;
    }

    /**
     * まとめて更新用に入力値をEntityに合わせる
     *
     * @param array $entities 予約枠リスト
     * @param array $inputs 入力値
     * @return array
     */
    public function setTogetherEntities(array $entities, array $inputs)
    {
        $return = [];
        foreach ($entities as $key => $entity) {
            $return[$key] = $inputs;
            $return[$key]['id'] = $entity->get('id');
        }

        return $return;
    }

    /**
     * 「編集する」チェックボックスのバリデータを生成
     *
     * @param \Cake\Validation\Validator $validator バリデータ
     * @return \Cake\Validation\Validator $validator バリデータ
     */
    public function addValidateUpdate(CakeValidator $validator)
    {
        /** @var \App\Model\Table\SystemSettingsTable $systemSettingsTable */
        $systemSettingsTable = $this->getTableLocator()->get('SystemSettings');

        $validator
            ->requirePresence('name', false)
            ->allowEmptyString('name', __(Message::ERROR_NOT_EMPTY_SELECT), false)
            ->add('name', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'inList' => [
                    'rule' => ['inList', [EventEntity::COMMON_FLG_ON]],
                    'last' => true,
                    'message' => __(Message::ERROR_IN_LIST),
                ],
            ]);

        $validator
            ->requirePresence('label', false)
            ->allowEmptyString('label', __(Message::ERROR_NOT_EMPTY_SELECT), false)
            ->add('label', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'inList' => [
                    'rule' => ['inList', [EventEntity::COMMON_FLG_ON]],
                    'last' => true,
                    'message' => __(Message::ERROR_IN_LIST),
                ],
            ]);

        $validator
            ->requirePresence('tag', false)
            ->allowEmptyString('tag', __(Message::ERROR_NOT_EMPTY_SELECT), false)
            ->add('tag', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'inList' => [
                    'rule' => ['inList', [EventEntity::COMMON_FLG_ON]],
                    'last' => true,
                    'message' => __(Message::ERROR_IN_LIST),
                ],
            ]);

        if ($systemSettingsTable->getData()->useSmartLock()) {
            $smartLocksValidator = new Validator();

            $smartLocksValidator
                ->requirePresence('smart_lock_device_key', false)
                ->allowEmptyString('smart_lock_device_key', __(Message::ERROR_NOT_EMPTY_SELECT), false)
                ->add('smart_lock_device_key', [
                    'isScalar' => [
                        'rule' => ['isScalar'],
                        'last' => true,
                        'message' => __(Message::ERROR_INVALID_VALUE),
                    ],
                    'inList' => [
                        'rule' => ['inList', [EventEntity::COMMON_FLG_ON]],
                        'last' => true,
                        'message' => __(Message::ERROR_IN_LIST),
                    ],
                ]);

            $smartLock = new SmartLockLinkage();
            if ($smartLock->useAkerun()) {
                $smartLocksValidator
                    ->requirePresence('smart_lock_key_url_flg', false)
                    ->allowEmptyString('smart_lock_key_url_flg', __(Message::ERROR_NOT_EMPTY_SELECT), false)
                    ->add('smart_lock_key_url_flg', [
                        'isScalar' => [
                            'rule' => ['isScalar'],
                            'last' => true,
                            'message' => __(Message::ERROR_INVALID_VALUE),
                        ],
                        'inList' => [
                            'rule' => ['inList', [EventEntity::COMMON_FLG_ON]],
                            'last' => true,
                            'message' => __(Message::ERROR_IN_LIST),
                        ],
                    ]);
            }

            $validator->addNested('event_smart_lock', $smartLocksValidator);
        }

        $validator
            ->requirePresence('stock_unit', false)
            ->allowEmptyString('stock_unit', __(Message::ERROR_NOT_EMPTY_SELECT), false)
            ->add('stock_unit', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'inList' => [
                    'rule' => ['inList', [EventEntity::COMMON_FLG_ON]],
                    'last' => true,
                    'message' => __(Message::ERROR_IN_LIST),
                ],
            ]);

        $validator
            ->requirePresence('stock_display_type', false)
            ->allowEmptyString('stock_display_type', __(Message::ERROR_NOT_EMPTY_SELECT), false)
            ->add('stock_display_type', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'inList' => [
                    'rule' => ['inList', [EventEntity::COMMON_FLG_ON]],
                    'last' => true,
                    'message' => __(Message::ERROR_IN_LIST),
                ],
            ]);

        $validator
            ->requirePresence('charge', false)
            ->allowEmptyString('charge', __(Message::ERROR_NOT_EMPTY_SELECT), false)
            ->add('charge', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'inList' => [
                    'rule' => ['inList', [EventEntity::COMMON_FLG_ON]],
                    'last' => true,
                    'message' => __(Message::ERROR_IN_LIST),
                ],
            ]);

        $validator
            ->requirePresence('waiting_cancellation_flg', false)
            ->allowEmptyString('waiting_cancellation_flg', __(Message::ERROR_NOT_EMPTY_SELECT), false)
            ->add('waiting_cancellation_flg', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'inList' => [
                    'rule' => ['inList', [EventEntity::COMMON_FLG_ON]],
                    'last' => true,
                    'message' => __(Message::ERROR_IN_LIST),
                ],
            ]);

        $validator
            ->requirePresence('reservation_status_id', false)
            ->allowEmptyString('reservation_status_id', __(Message::ERROR_NOT_EMPTY_SELECT), false)
            ->add('reservation_status_id', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'inList' => [
                    'rule' => ['inList', [EventEntity::COMMON_FLG_ON]],
                    'last' => true,
                    'message' => __(Message::ERROR_IN_LIST),
                ],
            ]);

        $validator
            ->requirePresence('form_pattern_id', false)
            ->allowEmptyString('form_pattern_id', __(Message::ERROR_NOT_EMPTY_SELECT), false)
            ->add('form_pattern_id', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'inList' => [
                    'rule' => ['inList', [EventEntity::COMMON_FLG_ON]],
                    'last' => true,
                    'message' => __(Message::ERROR_IN_LIST),
                ],
            ]);

        $validator
            ->requirePresence('reception_period_number', false)
            ->allowEmptyString('reception_period_number', __(Message::ERROR_NOT_EMPTY_SELECT), false)
            ->add('reception_period_number', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'inList' => [
                    'rule' => ['inList', [EventEntity::COMMON_FLG_ON]],
                    'last' => true,
                    'message' => __(Message::ERROR_IN_LIST),
                ],
            ]);

        $validator
            ->requirePresence('registration_deadline', false)
            ->allowEmptyString('registration_deadline', __(Message::ERROR_NOT_EMPTY_SELECT), false)
            ->add('registration_deadline', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'inList' => [
                    'rule' => ['inList', [EventEntity::COMMON_FLG_ON]],
                    'last' => true,
                    'message' => __(Message::ERROR_IN_LIST),
                ],
            ]);

        $validator
            ->requirePresence('editing_deadline', false)
            ->allowEmptyString('editing_deadline', __(Message::ERROR_NOT_EMPTY_SELECT), false)
            ->add('editing_deadline', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'inList' => [
                    'rule' => ['inList', [EventEntity::COMMON_FLG_ON]],
                    'last' => true,
                    'message' => __(Message::ERROR_IN_LIST),
                ],
            ]);

        $validator
            ->requirePresence('cancellation_deadline', false)
            ->allowEmptyString('cancellation_deadline', __(Message::ERROR_NOT_EMPTY_SELECT), false)
            ->add('cancellation_deadline', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'inList' => [
                    'rule' => ['inList', [EventEntity::COMMON_FLG_ON]],
                    'last' => true,
                    'message' => __(Message::ERROR_IN_LIST),
                ],
            ]);

        $validator
            ->requirePresence('reservation_limit_future', false)
            ->allowEmptyString('reservation_limit_future', __(Message::ERROR_NOT_EMPTY_SELECT), false)
            ->add('reservation_limit_future', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'inList' => [
                    'rule' => ['inList', [EventEntity::COMMON_FLG_ON]],
                    'last' => true,
                    'message' => __(Message::ERROR_IN_LIST),
                ],
            ]);

        $validator
            ->requirePresence('reservation_limit_month', false)
            ->allowEmptyString('reservation_limit_month', __(Message::ERROR_NOT_EMPTY_SELECT), false)
            ->add('reservation_limit_month', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'inList' => [
                    'rule' => ['inList', [EventEntity::COMMON_FLG_ON]],
                    'last' => true,
                    'message' => __(Message::ERROR_IN_LIST),
                ],
            ]);

        $validator
            ->requirePresence('reservation_limit_day', false)
            ->allowEmptyString('reservation_limit_day', __(Message::ERROR_NOT_EMPTY_SELECT), false)
            ->add('reservation_limit_day', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'inList' => [
                    'rule' => ['inList', [EventEntity::COMMON_FLG_ON]],
                    'last' => true,
                    'message' => __(Message::ERROR_IN_LIST),
                ],
            ]);

        $validator
            ->requirePresence('reservation_limit_all', false)
            ->allowEmptyString('reservation_limit_all', __(Message::ERROR_NOT_EMPTY_SELECT), false)
            ->add('reservation_limit_all', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'inList' => [
                    'rule' => ['inList', [EventEntity::COMMON_FLG_ON]],
                    'last' => true,
                    'message' => __(Message::ERROR_IN_LIST),
                ],
            ]);

        $validator
            ->requirePresence('duplication_check_flg', false)
            ->allowEmptyString('duplication_check_flg', __(Message::ERROR_NOT_EMPTY_SELECT), false)
            ->add('duplication_check_flg', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'inList' => [
                    'rule' => ['inList', [EventEntity::COMMON_FLG_ON]],
                    'last' => true,
                    'message' => __(Message::ERROR_IN_LIST),
                ],
            ]);

        $validator
            ->requirePresence('background_color_type', false)
            ->allowEmptyString('background_color_type', __(Message::ERROR_NOT_EMPTY_SELECT), false)
            ->add('background_color_type', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'inList' => [
                    'rule' => ['inList', [EventEntity::COMMON_FLG_ON]],
                    'last' => true,
                    'message' => __(Message::ERROR_IN_LIST),
                ],
            ]);

        $validator
            ->requirePresence('background_color_replace_front', false)
            ->allowEmptyString('background_color_replace_front', __(Message::ERROR_NOT_EMPTY_SELECT), false)
            ->add('background_color_replace_front', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'inList' => [
                    'rule' => ['inList', [EventEntity::COMMON_FLG_ON]],
                    'last' => true,
                    'message' => __(Message::ERROR_IN_LIST),
                ],
            ]);

        $validator
            ->requirePresence('background_color_replace_admin', false)
            ->allowEmptyString('background_color_replace_admin', __(Message::ERROR_NOT_EMPTY_SELECT), false)
            ->add('background_color_replace_admin', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'inList' => [
                    'rule' => ['inList', [EventEntity::COMMON_FLG_ON]],
                    'last' => true,
                    'message' => __(Message::ERROR_IN_LIST),
                ],
            ]);

        $validator
            ->requirePresence('format_type_display', false)
            ->allowEmptyString('format_type_display', __(Message::ERROR_NOT_EMPTY_SELECT), false)
            ->add('format_type_display', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'inList' => [
                    'rule' => ['inList', [EventEntity::COMMON_FLG_ON]],
                    'last' => true,
                    'message' => __(Message::ERROR_IN_LIST),
                ],
            ]);

        $validator
            ->requirePresence('usage_time_notation', false)
            ->allowEmptyString('usage_time_notation', __(Message::ERROR_NOT_EMPTY_SELECT), false)
            ->add('usage_time_notation', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'inList' => [
                    'rule' => ['inList', [EventEntity::COMMON_FLG_ON]],
                    'last' => true,
                    'message' => __(Message::ERROR_IN_LIST),
                ],
            ]);

        $validator
            ->requirePresence('public_from_to', false)
            ->allowEmptyString('public_from_to', __(Message::ERROR_NOT_EMPTY_SELECT), false)
            ->add('public_from_to', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'inList' => [
                    'rule' => ['inList', [EventEntity::COMMON_FLG_ON]],
                    'last' => true,
                    'message' => __(Message::ERROR_IN_LIST),
                ],
            ]);

        $validator
            ->requirePresence('public_flg', false)
            ->allowEmptyString('public_flg', __(Message::ERROR_NOT_EMPTY_SELECT), false)
            ->add('public_flg', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'inList' => [
                    'rule' => ['inList', [EventEntity::COMMON_FLG_ON]],
                    'last' => true,
                    'message' => __(Message::ERROR_IN_LIST),
                ],
            ]);

        $validator
            ->requirePresence('qr_code_flg', false)
            ->allowEmptyString('qr_code_flg', __(Message::ERROR_NOT_EMPTY_SELECT), false)
            ->add('qr_code_flg', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'inList' => [
                    'rule' => ['inList', [EventEntity::COMMON_FLG_ON]],
                    'last' => true,
                    'message' => __(Message::ERROR_IN_LIST),
                ],
            ]);

        $validator
            ->requirePresence('event_images', false)
            ->allowEmptyString('event_images', __(Message::ERROR_NOT_EMPTY_SELECT), false)
            ->add('event_images', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'inList' => [
                    'rule' => ['inList', [EventEntity::COMMON_FLG_ON]],
                    'last' => true,
                    'message' => __(Message::ERROR_IN_LIST),
                ],
            ]);

        $validator
            ->requirePresence('event_remarks', false)
            ->allowEmptyString('event_remarks', __(Message::ERROR_NOT_EMPTY_SELECT), false)
            ->add('event_remarks', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'inList' => [
                    'rule' => ['inList', [EventEntity::COMMON_FLG_ON]],
                    'last' => true,
                    'message' => __(Message::ERROR_IN_LIST),
                ],
            ]);

        $validator
            ->requirePresence('description', false)
            ->allowEmptyString('description', __(Message::ERROR_NOT_EMPTY_SELECT), false)
            ->add('description', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'inList' => [
                    'rule' => ['inList', [EventEntity::COMMON_FLG_ON]],
                    'last' => true,
                    'message' => __(Message::ERROR_IN_LIST),
                ],
            ]);

        return $validator;
    }
}
