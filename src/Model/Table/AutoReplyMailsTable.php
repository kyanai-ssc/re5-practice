<?php
declare(strict_types=1);

namespace App\Model\Table;

use App\Locale\Message;
use App\Model\AppTable;
use App\Model\Entity\AutoReplyMail;
use App\Model\Entity\AutoReplyMailStatus;
use App\Model\Entity\ReservationStatus;
use App\Model\Entity\UserAuthority;
use App\Utility\ArrayUtility;
use App\Validation\CustomValidation;
use Cake\Core\Configure;
use Cake\Core\Exception\CakeException;
use Cake\Datasource\EntityInterface;
use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\Utility\Hash;
use Cake\Validation\Validator;
use Kuchen\Validation\Validation\Validator as KuchenValidator;

/**
 * AutoReplyMails Model
 *
 * @method \App\Model\Entity\AutoReplyMail newEmptyEntity()
 * @method \App\Model\Entity\AutoReplyMail newEntity(array $data, array $options = [])
 * @method \App\Model\Entity\AutoReplyMail[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\AutoReplyMail get($primaryKey, $options = [])
 * @method \App\Model\Entity\AutoReplyMail findOrCreate($search, ?callable $callback = null, $options = [])
 * @method \App\Model\Entity\AutoReplyMail patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\AutoReplyMail[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\AutoReplyMail|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\AutoReplyMail saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\AutoReplyMail[]|\Cake\Datasource\ResultSetInterface|false saveMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\AutoReplyMail[]|\Cake\Datasource\ResultSetInterface saveManyOrFail(iterable $entities, $options = [])
 * @method \App\Model\Entity\AutoReplyMail[]|\Cake\Datasource\ResultSetInterface|false deleteMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\AutoReplyMail[]|\Cake\Datasource\ResultSetInterface deleteManyOrFail(iterable $entities, $options = [])
 */
class AutoReplyMailsTable extends AppTable
{
    public const FROM_NAME_MAX = 100;
    public const SUBJECT_MAX = 100;
    public const BODY_MAX = 50000;
    public const MAIL_MAX = 254;

    /**
     * @inheritDoc
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->belongsTo('UserAuthorities', [
            'foreignKey' => 'user_authority_id',
        ]);
        $this->belongsTo('Labels', [
            'foreignKey' => 'label_id',
        ]);
        $this->hasMany('AutoReplyMailHistories', [
            'foreignKey' => 'auto_reply_mail_id',
        ]);
        $this->hasMany('AutoReplyMailStatuses', [
            'foreignKey' => 'auto_reply_mail_id',
            'saveStrategy' => 'replace',
            'dependent' => true,
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
        $rules->add(function (EntityInterface $entity, $options) {
            $types = [
                AutoReplyMail::TYPE_RESERVE_ADD,
                AutoReplyMail::TYPE_RESERVE_CANCEL,
                AutoReplyMail::TYPE_RESERVE_REMINDER,
                AutoReplyMail::TYPE_STATUS_UPDATE,
                AutoReplyMail::TYPE_RESERVE_REMINDER_CLOSE,
            ];
            if (!in_array($entity->get('type'), $types, true)) {
                return true;
            }

            $statuses = [];
            foreach ((array)$entity->get('auto_reply_mail_statuses') as $autoReplyMailStatus) {
                if (
                    $entity->get('type') === AutoReplyMail::TYPE_RESERVE_ADD
                    && (string)$autoReplyMailStatus->get('type') === (string)AutoReplyMail::TYPE_RESERVE_ADD
                ) {
                    $statuses[$autoReplyMailStatus->get('reservation_status_to_id')] = true;
                } elseif (
                    $entity->get('type') === AutoReplyMail::TYPE_RESERVE_CANCEL
                    && (string)$autoReplyMailStatus->get('type') === (string)AutoReplyMail::TYPE_RESERVE_CANCEL
                ) {
                    $statuses[$autoReplyMailStatus->get('reservation_status_from_id')] = true;
                } elseif (
                    $entity->get('type') === AutoReplyMail::TYPE_RESERVE_REMINDER
                    && (string)$autoReplyMailStatus->get('type') === (string)AutoReplyMail::TYPE_RESERVE_REMINDER
                ) {
                    $statuses[$autoReplyMailStatus->get('reservation_status_from_id')] = true;
                } elseif (
                    $entity->get('type') === AutoReplyMail::TYPE_RESERVE_REMINDER_CLOSE
                    && (string)$autoReplyMailStatus->get('type') === (string)AutoReplyMail::TYPE_RESERVE_REMINDER_CLOSE
                ) {
                    $statuses[$autoReplyMailStatus->get('reservation_status_from_id')] = true;
                } elseif (
                    $entity->get('type') === AutoReplyMail::TYPE_STATUS_UPDATE
                    && (string)$autoReplyMailStatus->get('type') === (string)AutoReplyMail::TYPE_STATUS_UPDATE
                ) {
                    $statuses[$autoReplyMailStatus->get('reservation_status_to_id')] = true;
                }
            }
            unset($statuses[AutoReplyMailStatus::STATUS_EMPTY]);

            if (empty($statuses)) {
                $entity->setError('auto_reply_mail_statuses', (string)__(Message::ERROR_NOT_EMPTY_SELECT));

                return false;
            }

            return true;
        }, 'reservationStatusNotEmpty');

        $rules->add(function (EntityInterface $entity, $options) {
            $success = true;
            $inList = [];
            $types = [
                AutoReplyMail::TYPE_RESERVE_ADD,
                AutoReplyMail::TYPE_RESERVE_CANCEL,
                AutoReplyMail::TYPE_RESERVE_REMINDER,
                AutoReplyMail::TYPE_STATUS_UPDATE,
                AutoReplyMail::TYPE_RESERVE_REMINDER_CLOSE,
            ];

            if (!in_array($entity->get('type'), $types, true)) {
                return true;
            }

            foreach ($entity->get('auto_reply_mail_statuses') as $autoReplyMailStatus) {
                if ((string)$entity->get('type') === (string)$autoReplyMailStatus->get('type')) {
                    if ($entity->get('type') === AutoReplyMail::TYPE_RESERVE_ADD) {
                        continue;
                    }
                    if ($autoReplyMailStatus->get('reservation_status_from_id') === AutoReplyMailStatus::STATUS_EMPTY) {
                        continue;
                    }

                    if ($entity->get('type') === AutoReplyMail::TYPE_RESERVE_CANCEL) {
                        $inList = array_keys($this->getFieldValueOptions('reservationStatusCancel'));
                    } elseif (
                        $entity->get('type') === AutoReplyMail::TYPE_RESERVE_REMINDER
                        || $entity->get('type') === AutoReplyMail::TYPE_RESERVE_REMINDER_CLOSE
                    ) {
                        $inList = array_keys($this->getFieldValueOptions('reservationStatusReminder'));
                    } elseif ($entity->get('type') === AutoReplyMail::TYPE_STATUS_UPDATE) {
                        $inList = array_keys($this->getFieldValueOptions('reservationStatusUpdate'));
                    }

                    if (
                        !CustomValidation::inList(
                            $autoReplyMailStatus->get('reservation_status_from_id'),
                            $inList
                        )
                    ) {
                        $autoReplyMailStatus->setError(
                            'reservation_status_from_id',
                            (string)__(Message::ERROR_IN_LIST)
                        );
                        $success = false;
                    }
                }
            }

            return $success;
        }, 'reservationStatusFromIdInList');

        $rules->add(function (EntityInterface $entity, $options) {
            $success = true;
            $inList = [];
            $types = [
                AutoReplyMail::TYPE_RESERVE_ADD,
                AutoReplyMail::TYPE_RESERVE_CANCEL,
                AutoReplyMail::TYPE_RESERVE_REMINDER,
                AutoReplyMail::TYPE_STATUS_UPDATE,
                AutoReplyMail::TYPE_RESERVE_REMINDER_CLOSE,
            ];

            if (!in_array($entity->get('type'), $types, true)) {
                return true;
            }

            $autoReplyMailStatuses = $entity->get('auto_reply_mail_statuses');
            foreach ($autoReplyMailStatuses as $autoReplyMailStatus) {
                if (empty($autoReplyMailStatus->get('reservation_status_to_id'))) {
                    continue;
                }

                if ((string)$entity->get('type') === (string)$autoReplyMailStatus->get('type')) {
                    if (
                        $entity->get('type') === AutoReplyMail::TYPE_RESERVE_CANCEL
                        || $entity->get('type') === AutoReplyMail::TYPE_RESERVE_REMINDER
                        || $entity->get('type') === AutoReplyMail::TYPE_RESERVE_REMINDER_CLOSE
                    ) {
                        continue;
                    }

                    if ($entity->get('type') === AutoReplyMail::TYPE_RESERVE_ADD) {
                        $inList = array_keys($this->getFieldValueOptions('reservationStatusAdd'));
                    } elseif ($entity->get('type') === AutoReplyMail::TYPE_STATUS_UPDATE) {
                        $status = Hash::get(
                            $this->getFieldValueOptions('reservationStatusUpdate'),
                            $autoReplyMailStatus->get('reservation_status_from_id')
                        );

                        if (empty($autoReplyMailStatus->get('reservation_status_from_id'))) {
                            continue;
                        }

                        $inList = array_keys($status);
                    }
                    $inList = array_map('strval', $inList);

                    if (!CustomValidation::inList($autoReplyMailStatus->get('reservation_status_to_id'), $inList)) {
                        $autoReplyMailStatus->setError('reservation_status_to_id', (string)__(Message::ERROR_IN_LIST));
                        $success = false;
                    }
                }
            }

            return $success;
        }, 'reservationStatusToIdInList');

        $rules->add(function (EntityInterface $entity, $options) {
            $type = $entity->get('type');
            $success = true;

            switch ($type) {
                case AutoReplyMail::TYPE_RESERVE_ADD:
                case AutoReplyMail::TYPE_RESERVE_CANCEL:
                case AutoReplyMail::TYPE_RESERVE_REMINDER:
                case AutoReplyMail::TYPE_STATUS_UPDATE:
                case AutoReplyMail::TYPE_RESERVE_REMINDER_CLOSE:
                    /** @var \App\Model\Table\AutoReplyMailStatusesTable $autoReplyMailStatusesTable */
                    $autoReplyMailStatusesTable = $this->getTableLocator()->get('AutoReplyMailStatuses');
                    foreach ($entity->get('auto_reply_mail_statuses') as $autoReplyMailStatus) {
                        if ((string)$entity->get('type') === (string)$autoReplyMailStatus->get('type')) {
                            if (
                                $entity->get('type') === AutoReplyMail::TYPE_RESERVE_CANCEL
                                || $entity->get('type') === AutoReplyMail::TYPE_RESERVE_REMINDER
                                || $entity->get('type') === AutoReplyMail::TYPE_RESERVE_REMINDER_CLOSE
                            ) {
                                if (
                                    !$autoReplyMailStatusesTable->checkDuplicationStatus(
                                        $entity,
                                        $autoReplyMailStatus->get('reservation_status_from_id')
                                    )
                                ) {
                                    $autoReplyMailStatus->setError(
                                        'reservation_status_from_id',
                                        (string)__(Message::ERROR_ALREADY_SAVE)
                                    );
                                    $success = false;
                                }
                            } elseif (
                                $entity->get('type') === AutoReplyMail::TYPE_RESERVE_ADD
                                || $entity->get('type') === AutoReplyMail::TYPE_STATUS_UPDATE
                            ) {
                                if (
                                    !$autoReplyMailStatusesTable->checkDuplicationStatus(
                                        $entity,
                                        $autoReplyMailStatus->get('reservation_status_to_id'),
                                        $autoReplyMailStatus->get('reservation_status_from_id')
                                    )
                                ) {
                                    $autoReplyMailStatus->setError(
                                        'reservation_status_to_id',
                                        (string)__(Message::ERROR_ALREADY_SAVE)
                                    );
                                    $success = false;
                                }
                            }
                        }
                    }
                    break;
                default:
                    if (!$this->checkDuplicationType($type, $entity->toArray())) {
                        $entity->setError('type', (string)__(Message::ERROR_ALREADY_SAVE));
                        $success = false;
                    }
                    break;
            }

            return $success;
        }, 'checkDuplicationStatus');

        return $rules;
    }

    /**
     * @inheritDoc
     */
    protected function buildFieldValueOptions()
    {
        /** @var \App\Model\Table\UserAuthoritiesTable $userAuthorities */
        $userAuthorities = $this->getTableLocator()->get('UserAuthorities');
        $userAuthorityLists = $userAuthorities->getSelectList();

        $option = $this->formatReserveStatus();

        $fieldValueOptions = [
            'common' => Configure::readOrFail('Master.common.flg'),
            'type' => $this->getTypeFieldValueOptions(),
            'contentType' => Configure::readOrFail('Master.common.mailFormatName'),
            'userAuthorityId' => $userAuthorityLists,
            'reservationStatusAdd' => $option['add'],
            'reservationStatusCancel' => $option['cancel'],
            'reservationStatusReminder' => $option['reminder'],
            'reservationStatusUpdate' => $option['update'],
            'reservationStatusUpdateAll' => $option['updateAll'],
            'cancelStatusId' => $option['cancelStatusId'],
            'canSetting' => Configure::readOrFail('Master.autoReplyMail.canSetting'),
        ];

        /** @var \App\Model\Table\AutoReplyMailsTable $autoReplyMailStatusesTable */
        $autoReplyMailStatusesTable = $this->getTableLocator()->get('AutoReplyMailStatuses');
        $autoReplyMailStatusesTable->setFieldValueOptions($fieldValueOptions);

        return $fieldValueOptions;
    }

    /**
     * beforeSave
     *
     * @param \Cake\Event\EventInterface $event イベント
     * @param \Cake\Datasource\EntityInterface $entity エンティティ
     * @param \ArrayObject $options オプション
     * @return void
     */
    public function beforeSave($event, $entity, $options)
    {
        if ($entity->get('user_authority_id') === UserAuthority::SELECT_ALL) {
            $entity->set('user_authority_id', null);
        }

        if ($entity->get('admin_operation_mail_flg') != AutoReplyMail::ADMIN_OPERATION_MAIL_FLG_ON) {
            $entity->set('header_admin', null);
            $entity->set('contents_admin', null);
            $entity->set('footer_admin', null);
        }

        $type = $entity->get('type');
        $saveData = null;
        if (
            $type === AutoReplyMail::TYPE_RESERVE_ADD
            || $type === AutoReplyMail::TYPE_RESERVE_CANCEL
            || $type === AutoReplyMail::TYPE_RESERVE_REMINDER
            || $type === AutoReplyMail::TYPE_STATUS_UPDATE
            || $type === AutoReplyMail::TYPE_RESERVE_REMINDER_CLOSE
        ) {
            foreach ($entity->get('auto_reply_mail_statuses') as $autoReplyMailStatus) {
                if (
                    (string)$type === (string)$autoReplyMailStatus->get('type')
                    && (
                        $autoReplyMailStatus->get('reservation_status_from_id') !== AutoReplyMailStatus::STATUS_EMPTY
                        && $autoReplyMailStatus->get('reservation_status_to_id') !== AutoReplyMailStatus::STATUS_EMPTY
                    )
                ) {
                    $saveData[] = $autoReplyMailStatus;
                }
            }
        } else {
            $saveData = null;
        }

        //会員権限の設定が不要な項目はnull
        $canSetting = $this->getFieldValueOptions('canSetting');
        if (ArrayUtility::arraySearch($type, $canSetting['userAuthority']) === false) {
            $entity->set('user_authority_id', null);
        }

        //ラベルの設定が不要な項目はnull
        if (ArrayUtility::arraySearch($type, $canSetting['label']) === false) {
            $entity->set('label_id', null);
            $entity->set('except_sub_label_flg', AutoReplyMail::EXCEPT_SUB_LABEL_FLG_OFF);
        }

        $entity->set('auto_reply_mail_statuses', $saveData);
    }

    /**
     * @inheritDoc
     */
    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->requirePresence('type', true, __(Message::ERROR_NOT_EMPTY_SELECT))
            ->allowEmptyString('type', __(Message::ERROR_NOT_EMPTY_SELECT), false)
            ->add('type', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'inList' => [
                    'rule' => [
                        'inList',
                        array_keys($this->getFieldValueOptions('type')),
                    ],
                    'last' => true,
                    'message' => __(Message::ERROR_IN_LIST),
                ],
            ]);

        $validator
            ->requirePresence('user_authority_id', true, __(Message::ERROR_NOT_EMPTY_SELECT))
            ->allowEmptyString('user_authority_id', __(Message::ERROR_NOT_EMPTY_SELECT), false)
            ->add('user_authority_id', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'inList' => [
                    'rule' => [
                        'inList',
                        array_keys($this->getFieldValueOptions('userAuthorityId')),
                    ],
                    'last' => true,
                    'message' => __(Message::ERROR_IN_LIST),
                ],
            ]);

        $validator
            ->requirePresence('label_id', true, __(Message::ERROR_NOT_EMPTY_SELECT))
            ->allowEmptyString('label_id');

        /** @var \App\Model\Table\LabelsTable $labelTable */
        $labelTable = $this->getTableLocator()->get('Labels');
        $validator = $labelTable->addValidateLabelId($validator);

        $validator
            ->requirePresence('except_sub_label_flg', true, __(Message::ERROR_NOT_EMPTY_SELECT))
            ->allowEmptyString('except_sub_label_flg', __(Message::ERROR_NOT_EMPTY_SELECT), false)
            ->add('except_sub_label_flg', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_NOT_EMPTY_SELECT),
                ],
                'inList' => [
                    'rule' => [
                        'inList',
                        $this->getFieldValueOptions('common'),
                    ],
                    'last' => true,
                    'message' => __(Message::ERROR_IN_LIST),
                ],
            ]);

        $validator
            ->requirePresence('from_mail_name', true, __(Message::ERROR_NOT_EMPTY))
            ->allowEmptyString('from_mail_name', __(Message::ERROR_NOT_EMPTY), false)
            ->add('from_mail_name', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'maxLength' => [
                    'rule' => ['maxLength', static::FROM_NAME_MAX],
                    'last' => true,
                    'message' => __(Message::ERROR_MAX_LENGTH, static::FROM_NAME_MAX),
                ],
            ]);

        $validator
            ->requirePresence('from_mail', true, __(Message::ERROR_NOT_EMPTY))
            ->allowEmptyString('from_mail', __(Message::ERROR_NOT_EMPTY), false)
            ->add('from_mail', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'maxLength' => [
                    'rule' => ['maxLength', static::MAIL_MAX],
                    'message' => __(Message::ERROR_MAX_LENGTH, static::MAIL_MAX),
                    'last' => true,
                ],
                'email' => [
                    'rule' => ['email'],
                    'last' => true,
                    'message' => __(Message::ERROR_MAIL_ADDRESS),
                ],
                'isDkimDomain' => [
                    'rule' => ['isDkimDomain'],
                    'last' => true,
                    'message' => __(Message::ERROR_NOT_AVAILABLE_DOMAIN),
                ],
            ]);

        $validator
            ->requirePresence('content_type', true, __(Message::ERROR_NOT_EMPTY_SELECT))
            ->allowEmptyString('content_type', __(Message::ERROR_NOT_EMPTY_SELECT), false)
            ->add('content_type', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_NOT_EMPTY_SELECT),
                ],
                'inList' => [
                    'rule' => [
                        'inList',
                        array_keys($this->getFieldValueOptions('contentType')),
                    ],
                    'last' => true,
                    'message' => __(Message::ERROR_IN_LIST),
                ],
            ]);

        $validator
            ->requirePresence('reply_to', true, __(Message::ERROR_NOT_EMPTY))
            ->allowEmptyString('reply_to')
            ->add('reply_to', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'maxLength' => [
                    'rule' => ['maxLength', static::MAIL_MAX],
                    'message' => __(Message::ERROR_MAX_LENGTH, static::MAIL_MAX),
                    'last' => true,
                ],
                'email' => [
                    'rule' => ['email'],
                    'last' => true,
                    'message' => __(Message::ERROR_MAIL_ADDRESS),
                ],
            ]);

        $validator
            ->requirePresence('subject', true, __(Message::ERROR_NOT_EMPTY))
            ->allowEmptyString('subject', __(Message::ERROR_NOT_EMPTY), false)
            ->add('subject', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'maxLength' => [
                    'rule' => ['maxLength', static::SUBJECT_MAX],
                    'last' => true,
                    'message' => __(Message::ERROR_MAX_LENGTH, static::SUBJECT_MAX),
                ],
            ]);

        $validator
            ->requirePresence('header', true, __(Message::ERROR_NOT_EMPTY))
            ->allowEmptyString('header')
            ->add('header', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'maxLength' => [
                    'rule' => ['maxLength', static::BODY_MAX],
                    'last' => true,
                    'message' => __(Message::ERROR_MAX_LENGTH, static::BODY_MAX),
                ],
            ]);

        $validator
            ->requirePresence('contents', true, __(Message::ERROR_NOT_EMPTY))
            ->allowEmptyString('contents', __(Message::ERROR_NOT_EMPTY), false)
            ->add('contents', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'maxLength' => [
                    'rule' => ['maxLength', static::BODY_MAX],
                    'last' => true,
                    'message' => __(Message::ERROR_MAX_LENGTH, static::BODY_MAX),
                ],
            ]);

        $validator
            ->requirePresence('footer', true, __(Message::ERROR_NOT_EMPTY))
            ->allowEmptyString('footer')
            ->add('footer', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'maxLength' => [
                    'rule' => ['maxLength', static::BODY_MAX],
                    'last' => true,
                    'message' => __(Message::ERROR_MAX_LENGTH, static::BODY_MAX),
                ],
            ]);
        $validator
            ->requirePresence('admin_operation_mail_flg', true, __(Message::ERROR_NOT_EMPTY_SELECT))
            ->allowEmptyString('admin_operation_mail_flg')
            ->add('admin_operation_mail_flg', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_NOT_EMPTY_SELECT),
                ],
                'inList' => [
                    'rule' => [
                        'inList',
                        $this->getFieldValueOptions('common'),
                    ],
                    'last' => true,
                    'message' => __(Message::ERROR_IN_LIST),
                ],
            ]);

        $validator
            ->requirePresence('header_admin', true, __(Message::ERROR_NOT_EMPTY))
            ->allowEmptyString('header_admin')
            ->add('header_admin', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'maxLength' => [
                    'rule' => ['maxLength', static::BODY_MAX],
                    'last' => true,
                    'message' => __(Message::ERROR_MAX_LENGTH, static::BODY_MAX),
                ],
            ]);

        $validator
            ->requirePresence('contents_admin', true, __(Message::ERROR_NOT_EMPTY))
            ->allowEmptyString(
                'contents_admin',
                __(Message::ERROR_NOT_EMPTY),
                function ($context) use ($validator) {
                    if (!($validator instanceof KuchenValidator)) {
                        throw new CakeException();
                    }

                    if (
                        $validator->isValid('admin_operation_mail_flg')
                        && (string)Hash::get($context['data'], 'admin_operation_mail_flg')
                        === (string)AutoReplyMail::ADMIN_OPERATION_MAIL_FLG_ON
                    ) {
                        return false;
                    }

                    return true;
                }
            )
            ->add('contents_admin', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'maxLength' => [
                    'rule' => ['maxLength', static::BODY_MAX],
                    'last' => true,
                    'message' => __(Message::ERROR_MAX_LENGTH, static::BODY_MAX),
                ],
            ]);

        $validator
            ->requirePresence('footer_admin', true, __(Message::ERROR_NOT_EMPTY))
            ->allowEmptyString('footer_admin')
            ->add('footer_admin', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'maxLength' => [
                    'rule' => ['maxLength', static::BODY_MAX],
                    'last' => true,
                    'message' => __(Message::ERROR_MAX_LENGTH, static::BODY_MAX),
                ],
            ]);

        $validator
            ->requirePresence('auto_reply_mail_statuses', true, __(Message::ERROR_INVALID_VALUE))
            ->array('auto_reply_mail_statuses', __(Message::ERROR_NOT_EMPTY_SELECT))
            ->allowEmptyArray('auto_reply_mail_statuses');

        return $validator;
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
            'type',
            'user_authority_id',
            'label_id',
            'except_sub_label_flg',
            'from_mail_name',
            'from_mail',
            'reply_to',
            'content_type',
            'subject',
            'header',
            'contents',
            'footer',
            'admin_operation_mail_flg',
            'header_admin',
            'contents_admin',
            'footer_admin',
        ])->contain([
            'AutoReplyMailStatuses' =>
                [
                    'fields' => [
                        'id',
                        'auto_reply_mail_id',
                        'reservation_status_from_id',
                        'reservation_status_to_id',
                    ],
                ],
        ]);

        // フィールド、条件、関連が構築済であると仮定します。
        $query->formatResults(function (\Cake\Collection\CollectionInterface $results) {
            return $results->map(function ($row) {
                $format = [];
                foreach ($row['auto_reply_mail_statuses'] as $statuses) {
                    $key = $statuses['reservation_status_from_id'] . '-' . $statuses['reservation_status_to_id'];
                    if ((int)$row['type'] === AutoReplyMail::TYPE_RESERVE_REMINDER) {
                        $key = $key . '-' . AutoReplyMail::TYPE_RESERVE_REMINDER;
                    } elseif ((int)$row['type'] === AutoReplyMail::TYPE_RESERVE_REMINDER_CLOSE) {
                        $key = $key . '-' . AutoReplyMail::TYPE_RESERVE_REMINDER_CLOSE;
                    }

                    $format[$key] = $statuses;
                }
                $row['auto_reply_mail_statuses'] = $format;

                return $row;
            });
        });

        return $query;
    }

    /**
     * 重複チェック時のファインダー
     *
     * @param \Cake\ORM\Query $query The query to find with
     * @param array $options The options to use for the find
     * @return \Cake\ORM\Query The query builder
     */
    public function findDuplicateCheck(Query $query, array $options)
    {
        $query->select(['id', 'type']);

        $data = Hash::get($options, 'inputs', []);
        $type = Hash::get($options, 'type');

        $canSetting = $this->getFieldValueOptions('canSetting');
        //権限の設定が可能
        if (ArrayUtility::arraySearch($type, $canSetting['userAuthority']) !== false) {
            $userAuthority = Hash::get($data, 'user_authority_id');
            if ($userAuthority !== UserAuthority::SELECT_ALL) {
                $query->where(['AutoReplyMails.user_authority_id' => Hash::get($data, 'user_authority_id')]);
            } else {
                $query->where(['AutoReplyMails.user_authority_id IS' => null]);
            }
        }

        //ラベルの設定が可能
        if (ArrayUtility::arraySearch($type, $canSetting['label']) !== false) {
            if (Hash::get($data, 'label_id')) {
                $query->where(['AutoReplyMails.label_id' => Hash::get($data, 'label_id')]);

                // 「カテゴリー」を設定できないタイプや、「カテゴリー」を空にしているデータで、
                // 「このカテゴリーのみ」のチェックの有無のみが異なるデータが別物と判定されて両方とも登録されてはいけない。
                // 「このカテゴリーのみ」のチェックの有無まで重複チェックに入れるのは、「カテゴリー」を設定する場合だけにする。
                $query->where(['AutoReplyMails.except_sub_label_flg' => Hash::get($data, 'except_sub_label_flg')]);
            } else {
                $query->where(['AutoReplyMails.label_id IS ' => null]);
            }
        }

        if (Hash::get($data, 'id')) {
            $query->where(['AutoReplyMails.id != ' => Hash::get($data, 'id')]);
        }

        $query->where([
            'AutoReplyMails.type' => Hash::get($data, 'type'),
        ]);

        return $query;
    }

    /**
     * 自動返信メール送信時のファインダー
     *
     * @param \Cake\ORM\Query $query The query to find with
     * @param array $options The options to use for the find
     * @return \Cake\ORM\Query The query builder
     */
    public function findSendAutoReplyMail(Query $query, array $options)
    {
        $query->select([
            'id',
            'type',
            'label_id',
            'from_mail_name',
            'from_mail',
            'reply_to',
            'content_type',
            'subject',
            'header',
            'contents',
            'footer',
            'admin_operation_mail_flg',
            'header_admin',
            'contents_admin',
            'footer_admin',
        ]);

        return $query;
    }

    /**
     * タイプ重複チェック
     *
     * @param int $type タイプ
     * @param array $values 入力値
     * @return bool
     */
    public function checkDuplicationType($type, $values)
    {
        $query = $this->find('duplicateCheck', [
            'inputs' => $values,
            'type' => $type,
        ]);

        if ($query->count() < 1) {
            return true;
        }

        return false;
    }

    /**
     * 予約ステータスの選択肢を作成
     *
     * @return mixed
     */
    public function formatReserveStatus()
    {
        /** @var \App\Model\Table\ReservationStatusesTable $reservationStatusesTable */
        $reservationStatusesTable = $this->getTableLocator()->get('ReservationStatuses');
        $statusLists = $reservationStatusesTable->getGroupingStatusType();

        $add = array_merge(
            $statusLists[ReservationStatus::STATUS_TYPE_FIXED],
            $statusLists[ReservationStatus::STATUS_TYPE_TENTATIVE]
        );
        $option['add'] = Hash::combine($add, '{n}.id', '{n}.name');

        $cancel = array_merge(
            $statusLists[ReservationStatus::STATUS_TYPE_FIXED],
            $statusLists[ReservationStatus::STATUS_TYPE_TENTATIVE],
            $statusLists[ReservationStatus::STATUS_TYPE_VISIT],
            $statusLists[ReservationStatus::STATUS_TYPE_ABSENCE]
        );
        $option['cancel'] = Hash::combine($cancel, '{n}.id', '{n}.name');
        $option['cancelStatusId'] = key(array_slice($statusLists[ReservationStatus::STATUS_TYPE_CANCEL], 0, 1, true));

        $reminder = array_merge(
            $statusLists[ReservationStatus::STATUS_TYPE_FIXED],
            $statusLists[ReservationStatus::STATUS_TYPE_TENTATIVE],
            $statusLists[ReservationStatus::STATUS_TYPE_VISIT]
        );
        $option['reminder'] = Hash::combine($reminder, '{n}.id', '{n}.name');

        $update = array_merge(
            $statusLists[ReservationStatus::STATUS_TYPE_FIXED],
            $statusLists[ReservationStatus::STATUS_TYPE_TENTATIVE],
            $statusLists[ReservationStatus::STATUS_TYPE_VISIT],
            $statusLists[ReservationStatus::STATUS_TYPE_ABSENCE]
        );
        $option['updateAll'] = Hash::combine($update, '{n}.id', '{n}.name');
        foreach ($update as $key => $status) {
            $option['update'][$status->get('id')] = Hash::combine(
                Hash::remove($update, (string)$key),
                '{n}.id',
                '{n}.name'
            );
        }

        return $option;
    }

    /**
     * @inheritDoc
     */
    protected function searchConfiguration()
    {
        $this->searchManager()
            ->value('type')
            ->callback('user_authority_id', [
                'callback' => function ($query, $args) {
                    $userAuthorities = Hash::get($args, 'user_authority_id');
                    $selectAll = ArrayUtility::arraySearch(UserAuthority::SELECT_ALL, $userAuthorities);

                    if ($selectAll !== false) {
                        unset($userAuthorities[$selectAll]);

                        $orWhere = [];
                        if (!empty($userAuthorities)) {
                            $orWhere = ['user_authority_id IN' => $userAuthorities];
                        }

                        $query->where([
                            'OR' => [
                                ['user_authority_id IS NULL'],
                                $orWhere,
                            ],
                        ]);
                    } else {
                        $query->where(['user_authority_id IN' => $userAuthorities]);
                    }
                },
            ])
            ->like('from_mail_name', [
                'before' => true,
                'after' => true,
            ])
            ->like('from_mail', [
                'before' => true,
                'after' => true,
            ])->like('subject', [
                'before' => true,
                'after' => true,
            ])->like('contents', [
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
        /** @var \App\Model\Table\LabelsTable $labelsTable */
        $labelsTable = $this->getTableLocator()->get('Labels');

        $query->contain(['Labels' => ['fields' => ['id', 'parent_id', 'name']]]);

        $query->join([
            'Labels' => [
                'table' => 'labels',
                'type' => 'LEFT',
                'conditions' => 'Labels.id = AutoReplyMails.label_id',
            ],
        ]);
        $labelsTable->joinQuery($query, 'Labels');

        $labelId = Hash::get($options, 'inputs.label_id');
        if (isset($labelId) && $labelId !== '') {
            $labelsTable->addNestWhere($query, (int)$labelId);
        }

        $sort = Hash::get($options, 'inputs.sort', 'type');
        $direction = strtoupper(Hash::get($options, 'inputs.direction', 'asc'));

        $query
            ->order([
                    'AutoReplyMails.' . $sort => $direction,
                ] + [
                    'AutoReplyMails.type' => $direction,
                    'AutoReplyMails.id' => $direction,
                ], true);

        return $this->callFinder('search', $query, ['search' => Hash::get($options, 'inputs', [])]);
    }

    /**
     * 送信対象取得時のファインダー
     *
     * @param \Cake\ORM\Query $query The query to find with
     * @param array $options The options to use for the find
     * @return \Cake\ORM\Query The query builder
     */
    public function findSendTarget(Query $query, array $options)
    {
        /** @var \App\Model\Table\LabelsTable $labelsTable */
        $labelsTable = $this->getTableLocator()->get('Labels');

        $query->select([
            'id',
            'from_mail_name',
            'from_mail',
            'reply_to',
            'content_type',
            'subject',
            'header',
            'contents',
            'footer',
            'admin_operation_mail_flg',
            'header_admin',
            'contents_admin',
            'footer_admin',
        ]);

        // タイプ
        $type = Hash::get($options, 'inputs.type');
        $query->where([
            'AutoReplyMails.type' => $type,
        ]);

        // 会員権限
        $userAuthorityId = Hash::get($options, 'inputs.user_authority_id');
        if (((string)$userAuthorityId) !== '') {
            $query->where([
                'OR' => [
                    'AutoReplyMails.user_authority_id' => $userAuthorityId,
                    'AutoReplyMails.user_authority_id IS NULL',
                ],
            ]);

            $query->order([
                'AutoReplyMails.user_authority_id' => 'ASC',
            ]);
        } else {
            $query->where([
                'AutoReplyMails.user_authority_id IS NULL',
            ]);
        }

        // ラベル
        $labelId = Hash::get($options, 'inputs.label_id');
        if (((string)$labelId) !== '' && $type !== AutoReplyMail::TYPE_REPEAT_RESERVATION) {
            $limit = Configure::readOrFail('Setting.label.depth') - 1;

            $query->join([
                'table' => 'labels',
                'alias' => 'labels_0',
                'type' => 'LEFT',
                'conditions' => [
                    'labels_0.id' => $labelId,
                ],
            ]);
            $query = $labelsTable->joinQuery($query, 'labels_0');

            $labelColumn = [];
            for ($i = 0; $i < $limit; ++$i) {
                $labelColumn[] = 'labels_' . ($i + 1) . '.id';
            }
            $query->where([
                'OR' => [
                    'AutoReplyMails.label_id' => $labelId,
                    [
                        'AutoReplyMails.label_id IN (' . implode(',', $labelColumn) . ')',
                        'AutoReplyMails.except_sub_label_flg' => AutoReplyMail::EXCEPT_SUB_LABEL_FLG_OFF,
                    ],
                    'AutoReplyMails.label_id IS NULL',
                ],
            ]);

            // 優先順位でソート
            $query->order([
                'AutoReplyMails.except_sub_label_flg' => 'DESC',
            ]);
            $query->order(function ($queryExpression, $query) use ($labelId, $limit) {
                $order = 0;
                $case = $query->newExpr()->case();
                $case
                    ->when(['AutoReplyMails.label_id' => $labelId])
                    ->then($order++);
                for ($i = 0; $i < $limit; ++$i) {
                    $case
                        ->when(['AutoReplyMails.label_id = labels_' . ($i + 1) . '.id'])
                        ->then($order++);
                }
                $queryExpression->add($case);

                return $queryExpression;
            });
        } else {
            $query->where([
                'AutoReplyMails.label_id IS NULL',
            ]);
        }

        // ステータス
        $statusFrom = Hash::get($options, 'inputs.reservation_status_from_id');
        $statusTo = Hash::get($options, 'inputs.reservation_status_to_id');
        if (
            (((string)$statusFrom) !== '' || ((string)$statusTo) !== '' ) &&
            $type !== AutoReplyMail::TYPE_REPEAT_RESERVATION
        ) {
            $query->innerJoinWith('AutoReplyMailStatuses', function ($statusQuery) use ($statusFrom, $statusTo) {
                if (((string)$statusFrom) !== '') {
                    $statusQuery->where([
                        'AutoReplyMailStatuses.reservation_status_from_id' => $statusFrom,
                    ]);
                } else {
                    $statusQuery->where([
                        'AutoReplyMailStatuses.reservation_status_from_id IS NULL',
                    ]);
                }
                if (((string)$statusTo) !== '') {
                    $statusQuery->where([
                        'AutoReplyMailStatuses.reservation_status_to_id' => $statusTo,
                    ]);
                } else {
                    $statusQuery->where([
                        'AutoReplyMailStatuses.reservation_status_to_id IS NULL',
                    ]);
                }

                return $statusQuery;
            });
        }

        $query->order([
            'AutoReplyMails.id' => 'ASC',
        ]);

        $query->limit(1);

        return $query;
    }

    /**
     * メールアドレスの初期設定
     *
     * @return void
     */
    public function initializeAddress()
    {
        $this->updateAll([
            'from_mail' => $this->commonData()->getDefaultFromAddress(),
            'reply_to' => null,
        ], []);
    }

    /**
     * メール種別のタイプ設定
     *
     * @return array
     */
    public function getTypeFieldValueOptions()
    {
        /** @var \App\Model\Table\AdminsTable $adminsTable */
        $adminsTable = $this->getTableLocator()->get('Admins');
        if (!$adminsTable->checkLabelIdAdmin()) {
            $type = 'type';
        } else {
            $type = 'adminLabelIdType';
        }

        return Configure::readOrFail('Master.autoReplyMail.' . $type);
    }
}
