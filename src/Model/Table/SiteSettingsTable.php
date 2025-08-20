<?php
declare(strict_types=1);

namespace App\Model\Table;

use App\Locale\Message;
use App\Model\AppTable;
use App\Model\Entity\SiteSetting;
use App\Validation\CustomValidation;
use ArrayObject;
use Cake\Cache\Cache;
use Cake\Core\Configure;
use Cake\Core\Exception\CakeException;
use Cake\Database\Schema\TableSchemaInterface;
use Cake\Datasource\EntityInterface;
use Cake\Event\EventInterface;
use Cake\I18n\FrozenTime;
use Cake\ORM\Exception\PersistenceFailedException;
use Cake\ORM\Query;
use Cake\Utility\Hash;
use Cake\Validation\Validation;
use Cake\Validation\Validator;
use Kuchen\Validation\Validation\Validator as KuchenValidator;
use SplFileInfo;

/**
 * SiteSettings Model
 *
 * @method \App\Model\Entity\SiteSetting newEmptyEntity()
 * @method \App\Model\Entity\SiteSetting newEntity(array $data, array $options = [])
 * @method \App\Model\Entity\SiteSetting[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\SiteSetting get($primaryKey, $options = [])
 * @method \App\Model\Entity\SiteSetting findOrCreate($search, ?callable $callback = null, $options = [])
 * @method \App\Model\Entity\SiteSetting patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\SiteSetting[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\SiteSetting|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\SiteSetting saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\SiteSetting[]|\Cake\Datasource\ResultSetInterface|false saveMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\SiteSetting[]|\Cake\Datasource\ResultSetInterface saveManyOrFail(iterable $entities, $options = [])
 * @method \App\Model\Entity\SiteSetting[]|\Cake\Datasource\ResultSetInterface|false deleteMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\SiteSetting[]|\Cake\Datasource\ResultSetInterface deleteManyOrFail(iterable $entities, $options = [])
 */
class SiteSettingsTable extends AppTable
{
    public const BASE_ID = 1;
    public const REMINDER_TIME_MIN = 0.5;
    public const REMINDER_TIME_MAX = 24;
    public const REMINDER_DAY_MIN = 1;
    public const REMINDER_DAY_MAX = 30;
    public const NEWS_NEW_TIME_MIN = 0.5;
    public const NEWS_NEW_TIME_MAX = 24;
    public const NEWS_NEW_DAY_MIN = 1;
    public const NEWS_NEW_DAY_MAX = 30;
    public const NEWS_TOP_DISPLAY = 50;
    public const URL_MAX = 1000;
    public const KEYWORD_MAX = 1000;
    public const DESCRIPTION_MAX = 1000;
    public const TOP_INFO_MAX = 100000;

    /**
     * 予約状況表示日初期値：MAX
     */
    public const CALENDER_DATE_DEFAULT = 1000;

    /**
     * 予約状況1ヶ月表示最大件数
     */
    public const CALENDER_MONTH_DISP_LIMIT = 1000;

    /**
     * @var \App\Model\Entity\SiteSetting|null
     */
    protected $cacheData = null;

    /**
     * @var int|null
     */
    protected $editId = null;

    /**
     * @var array
     */
    protected $sameValidate = [
        'user_add_flg',
        'user_edit_flg',
        'mail_edit_optin_flg',
        'optin_flg',
        'id_reminder_flg',
        'password_reminder_flg',
        'reservation_add_user_flg',
        'reservation_add_not_user_flg',
        'reservation_continuous_flg',
        'inquiry_flg',
        'login_display_add_user_flg',
        'top_search_label_flg',
        'top_search_tag_flg',
        'top_search_event_name_flg',
        'calendar_search_label_flg',
        'calendar_search_tag_flg',
        'calendar_search_event_name_flg',
        'terms_flg',
        'user_terms_flg',
        'reservation_terms_flg',
        'sctl_flg',
        'reservation_sctl_flg',
        'charge_breakdown_flg',
        'reservation_edit_not_user_flg',
    ];

    /**
     * 同じ検証項目　メール配信
     *
     * @var array
     */
    protected $sameValidateForMailDeliveryFlg = [
        'reservation_reminder_flg',
        'reservation_close_reminder_flg',
    ];

    /**
     * 同じ検証項目　時間単位
     *
     * @var array
     */
    protected $sameValidateForTimeUnit = [
        [
            'type' => 'reservation_reminder_type',
            'hour' => 'reminder_mail_hour',
            'day' => 'reminder_mail_day',
        ],
        [
            'type' => 'reservation_close_reminder_type',
            'hour' => 'reservation_close_reminder_mail_hour',
            'day' => 'reservation_close_reminder_mail_day',
        ],
    ];

    /**
     * @inheritDoc
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->getBehavior('AdminOperationLog')->setConfig([
            'saveOperation' => true,
        ]);
    }

    /**
     * @inheritDoc
     */
    public function getSchema(): TableSchemaInterface
    {
        $schema = parent::getSchema();
        $schema->setColumnType('site_theme', 'json');

        return $schema;
    }

    /**
     * beforeSave
     *
     * @param \Cake\Event\EventInterface $event イベント
     * @param \Cake\Datasource\EntityInterface $entity エンティティ
     * @param \ArrayObject $options オプション
     * @return void
     */
    public function beforeSave(EventInterface $event, EntityInterface $entity, ArrayObject $options)
    {
        //サイト基本設定での設定の場合のみ
        if (isset($options['save']) && $options['save'] === 'SiteSetting') {
            if ($entity->get('reservation_reminder_type') === SiteSetting::RESERVATION_REMINDER_TYPE_TIME) {
                $entity->set('reservation_reminder_time', $entity->get('reminder_mail_hour'));
            }

            if ($entity->get('reservation_reminder_type') === SiteSetting::RESERVATION_REMINDER_TYPE_DAY) {
                $entity->set('reservation_reminder_time', $entity->get('reminder_mail_day'));
            }

            if ($entity->get('reservation_close_reminder_type') === SiteSetting::RESERVATION_REMINDER_TYPE_TIME) {
                $entity->set('reservation_close_reminder_time', $entity->get('reservation_close_reminder_mail_hour'));
            }

            if ($entity->get('reservation_close_reminder_type') === SiteSetting::RESERVATION_REMINDER_TYPE_DAY) {
                $entity->set('reservation_close_reminder_time', $entity->get('reservation_close_reminder_mail_day'));
            }

            $originalData = $entity->extractOriginal($entity->getVisible());
            //変更不可の値を入れなおす
            foreach (Configure::readOrFail('Master.system.canEdit') as $field => $can) {
                if ($can) {
                    continue;
                }
                $entity->set($field, $originalData[$field]);
            }

            //項目が連携しているものはdirtyを付ける
            $entity->isDirty('reservation_reminder_time');
            $entity->isDirty('reservation_reminder_type');
            $entity->isDirty('calendar_time_from');
            $entity->isDirty('calendar_time_to');
            $entity->isDirty('reservation_close_reminder_time');
            $entity->isDirty('reservation_close_reminder_type');
        }

        if (isset($options['save']) && $options['save'] === 'cms') {
            $color = Configure::read('Master.cms.siteThemeDefaultColor');
            $siteTheme = $entity->get('site_theme');
            foreach ($color as $key => $code) {
                if (is_null(Hash::get($siteTheme, $key))) {
                    $siteTheme[$key] = $code;
                }
            }

            $entity->set('site_theme', $siteTheme);
        }

        //お知らせ表示設定での設定の場合のみ
        if (isset($options['save']) && $options['save'] === 'newsSetting') {
            if ($entity->get('news_new_period_type') === SiteSetting::NEWS_NEW_PERIOD_TYPE_TIME) {
                $entity->set('news_new_period_number', $entity->get('news_new_period_number_hour'));
            }

            if ($entity->get('news_new_period_type') === SiteSetting::NEWS_NEW_PERIOD_TYPE_DAY) {
                $entity->set('news_new_period_number', $entity->get('news_new_period_number_day'));
            }
        }
    }

    /**
     * @inheritDoc
     */
    protected function buildFieldValueOptions()
    {
        $fieldValueOptions = [
            'common' => Configure::readOrFail('Master.system.common'),
            'reservationReminderFlg' => Configure::readOrFail('Master.system.reservationReminderFlg'),
            'reservationEditEventFlg' => Configure::readOrFail('Master.system.reservationEditEventFlg'),
            'tagSearchMethod' => Configure::readOrFail('Master.system.tagSearchMethod'),
            'loginFlg' => Configure::readOrFail('Master.system.loginFlg'),
            'reservationReminderType' => Configure::readOrFail('Master.system.reservationReminderType'),
            'reservationFormTypeFirst' => Configure::readOrFail('Master.system.reservationFormTypeFirst'),
            'calendarTimeDefault' => Configure::readOrFail('Master.system.calendarTimeDefault'),
            'frontPublicFlg' => Configure::readOrFail('Master.system.frontPublicFlg'),
            'calendarType' => Configure::readOrFail('Master.event.calendarType'),
            'reservationReminderTime' => $this->get30minSeparatedTime(),
            'reservationReminderDay' => $this->get1daySeparatedDay(),
            'newsNewPeriodTime' => $this->get30minSeparatedTime(),
            'newsNewPeriodDay' => $this->get1daySeparatedDay(),
            'newsNewPeriodType' => Configure::readOrFail('Master.system.newsNewPeriodType'),
            'calendarRegistrationDeadlineDisplayFlg' => Configure::readOrFail(
                'Master.system.calendarRegistrationDeadlineDisplayFlg'
            ),
            'reservationEditPaymentFlg' => Configure::readOrFail('Master.system.reservationEditPaymentFlg'),
            'siteTheme' => Configure::readOrFail('Master.cms.siteTheme'),
        ];

        return $fieldValueOptions;
    }

    /**
     * 基本設定のバリデータ
     *
     * @param \Cake\Validation\Validator $validator Validator
     * @return \Cake\Validation\Validator
     */
    public function validationSiteSetting(Validator $validator)
    {
        foreach ($this->sameValidate as $field) {
            $validator
                ->requirePresence($field, true, __(Message::ERROR_NOT_EMPTY_SELECT))
                ->allowEmptyString($field, __(Message::ERROR_NOT_EMPTY_SELECT), false)
                ->add($field, [
                    'isScalar' => [
                        'rule' => ['isScalar'],
                        'last' => true,
                        'message' => __(Message::ERROR_INVALID_VALUE),
                    ],
                    'inList' => [
                        'rule' => [
                            'inList',
                            array_keys($this->getFieldValueOptions('common')),
                        ],
                        'last' => true,
                        'message' => __(Message::ERROR_IN_LIST),
                    ],
                ]);
        }

        $validator
            ->requirePresence('login_use_flg', true, __(Message::ERROR_NOT_EMPTY_SELECT))
            ->allowEmptyString('login_use_flg', __(Message::ERROR_NOT_EMPTY_SELECT), false)
            ->add('login_use_flg', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'inList' => [
                    'rule' => [
                        'inList',
                        array_keys($this->getFieldValueOptions('loginFlg')),
                    ],
                    'last' => true,
                    'message' => __(Message::ERROR_IN_LIST),
                ],
            ]);

        foreach ($this->sameValidateForMailDeliveryFlg as $field) {
            $validator
                ->requirePresence($field, true, __(Message::ERROR_NOT_EMPTY))
                ->allowEmptyString($field, __(Message::ERROR_NOT_EMPTY), false)
                ->add($field, [
                    'isScalar' => [
                        'rule' => ['isScalar'],
                        'last' => true,
                        'message' => __(Message::ERROR_INVALID_VALUE),
                    ],
                    'inList' => [
                        'rule' => [
                            'inList',
                            array_keys($this->getFieldValueOptions('reservationEditEventFlg')),
                        ],
                        'last' => true,
                        'message' => __(Message::ERROR_IN_LIST),
                    ],
                ]);
        }

        $validator->requirePresence('reservation_edit_event_flg', true, __(Message::ERROR_NOT_EMPTY))
            ->allowEmptyString('reservation_edit_event_flg', __(Message::ERROR_NOT_EMPTY), false)
            ->add('reservation_edit_event_flg', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'inList' => [
                    'rule' => [
                        'inList',
                        array_keys($this->getFieldValueOptions('reservationReminderFlg')),
                    ],
                    'last' => true,
                    'message' => __(Message::ERROR_IN_LIST),
                ],
            ]);

        foreach ($this->sameValidateForTimeUnit as $fields) {
            $validator
                ->requirePresence($fields['type'], true, __(Message::ERROR_NOT_EMPTY))
                ->allowEmptyString(
                    $fields['type'],
                    __(Message::ERROR_NOT_EMPTY),
                    function ($context) use ($validator, $fields) {
                        if (!($validator instanceof KuchenValidator)) {
                            throw new CakeException();
                        }

                        if ($this->getData()->canEdit($fields['type'])) {
                            return false;
                        }

                        return true;
                    }
                )
                ->add($fields['type'], [
                    'isScalar' => [
                        'rule' => ['isScalar'],
                        'last' => true,
                        'message' => __(Message::ERROR_INVALID_VALUE),
                    ],
                    'inList' => [
                        'rule' => [
                            'inList',
                            array_keys($this->getFieldValueOptions('reservationReminderType')),
                        ],
                        'last' => true,
                        'message' => __(Message::ERROR_IN_LIST),
                    ],
                ]);

            $validator
                ->requirePresence($fields['hour'], function ($context) use ($validator, $fields) {
                    if (!($validator instanceof KuchenValidator)) {
                        throw new CakeException();
                    }

                    if (
                        ($validator->isValid($fields['type'])
                        && (string)Hash::get($context['data'], $fields['type'])
                        !== (string)SiteSetting::RESERVATION_REMINDER_TYPE_TIME)
                        || !$this->getData()->canEdit($fields['type'])
                    ) {
                        return false;
                    }

                    return true;
                }, __(Message::ERROR_NOT_EMPTY))
                ->allowEmptyString(
                    $fields['hour'],
                    __(Message::ERROR_NOT_EMPTY),
                    function ($context) use ($validator, $fields) {
                        if (!($validator instanceof KuchenValidator)) {
                            throw new CakeException();
                        }

                        if (
                            $validator->isValid($fields['type'])
                            && (string)Hash::get($context['data'], $fields['type'])
                            === (string)SiteSetting::RESERVATION_REMINDER_TYPE_TIME
                        ) {
                            return false;
                        }

                        return true;
                    }
                )
                ->add($fields['hour'], [
                    'isScalar' => [
                        'rule' => ['isScalar'],
                        'last' => true,
                        'message' => __(Message::ERROR_INVALID_VALUE),
                    ],
                    'inList' => [
                        'rule' => [
                            'inList',
                            array_keys($this->getFieldValueOptions('reservationReminderTime')),
                        ],
                        'last' => true,
                        'message' => __(Message::ERROR_IN_LIST),
                    ],
                ]);

            $validator
                ->requirePresence($fields['day'], function ($context) use ($validator, $fields) {
                    if (!($validator instanceof KuchenValidator)) {
                        throw new CakeException();
                    }

                    if (
                        $validator->isValid($fields['type'])
                        && (string)Hash::get($context['data'], $fields['type'])
                        !== (string)SiteSetting::RESERVATION_REMINDER_TYPE_DAY
                    ) {
                        return false;
                    }

                    return true;
                }, __(Message::ERROR_NOT_EMPTY))
                ->allowEmptyString(
                    $fields['day'],
                    __(Message::ERROR_NOT_EMPTY),
                    function ($context) use ($validator, $fields) {
                        if (!($validator instanceof KuchenValidator)) {
                            throw new CakeException();
                        }

                        if (
                            $validator->isValid($fields['type'])
                            && (string)Hash::get($context['data'], $fields['type'])
                            === (string)SiteSetting::RESERVATION_REMINDER_TYPE_DAY
                        ) {
                            return false;
                        }

                        return true;
                    }
                )
                ->add($fields['day'], [
                    'isScalar' => [
                        'rule' => ['isScalar'],
                        'last' => true,
                        'message' => __(Message::ERROR_INVALID_VALUE),
                    ],
                    'inList' => [
                        'rule' => [
                            'inList',
                            array_keys($this->getFieldValueOptions('reservationReminderDay')),
                        ],
                        'last' => true,
                        'message' => __(Message::ERROR_IN_LIST),
                    ],
                ]);
        }

        $validator->requirePresence('calendar_date_default', true, __(Message::ERROR_NOT_EMPTY))
            ->allowEmptyString('calendar_date_default', __(Message::ERROR_NOT_EMPTY), false)
            ->add('calendar_date_default', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'naturalNumber' => [
                    'rule' => ['naturalNumber', true],
                    'last' => true,
                    'message' => __(Message::ERROR_NUMBER),
                ],
                'compareLessOrEqual' => [
                    'rule' => ['comparison', Validation::COMPARE_LESS_OR_EQUAL, static::CALENDER_DATE_DEFAULT],
                    'last' => true,
                    'message' => __(Message::ERROR_OVER_DIGIT, static::CALENDER_DATE_DEFAULT),
                ],
            ]);

        $validator->requirePresence('calendar_month_display_limit', true, __(Message::ERROR_NOT_EMPTY))
            ->allowEmptyString('calendar_month_display_limit', __(Message::ERROR_NOT_EMPTY), false)
            ->add('calendar_month_display_limit', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'number' => [
                    'rule' => ['naturalNumber'],
                    'last' => true,
                    'message' => __(Message::ERROR_NATURAL_NUMBER),
                ],
                'compareLessOrEqual' => [
                    'rule' => ['comparison', Validation::COMPARE_LESS_OR_EQUAL, static::CALENDER_MONTH_DISP_LIMIT],
                    'last' => true,
                    'message' => __(Message::ERROR_OVER_DIGIT, static::CALENDER_MONTH_DISP_LIMIT),
                ],
            ]);

        $validator->requirePresence('calendar_time_from', true, __(Message::ERROR_NOT_EMPTY))
            ->allowEmptyTime('calendar_time_from', __(Message::ERROR_NOT_EMPTY), false)
            ->add('calendar_time_from', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'time' => [
                    'rule' => [
                        'time24h',
                    ],
                    'last' => true,
                    'message' => __(Message::ERROR_TIME),
                ],
            ]);

        $validator->requirePresence('calendar_time_to', true, __(Message::ERROR_NOT_EMPTY))
            ->allowEmptyTime('calendar_time_to', __(Message::ERROR_NOT_EMPTY), false)
            ->add('calendar_time_to', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'time' => [
                    'rule' => [
                        'time24h',
                    ],
                    'last' => true,
                    'message' => __(Message::ERROR_TIME),
                ],
                'compareFields' => [
                    'rule' => function ($value, $context) use ($validator) {
                        if (!($validator instanceof KuchenValidator)) {
                            throw new CakeException();
                        }
                        if (!$validator->isValid('calendar_time_from')) {
                            return true;
                        }

                        $from = new FrozenTime($context['data']['calendar_time_from']);
                        $to = new FrozenTime($value);

                        return $to >= $from;
                    },
                    'last' => true,
                    'message' => __(Message::ERROR_OVER_FROM_TIME),
                    'on' => function ($context) {
                        if ((string)Hash::get($context['data'], 'calendar_time_to') !== '00:00') {
                            return true;
                        }

                        return false;
                    },
                ],
            ]);

        $validator->requirePresence('calendar_time_default', true, __(Message::ERROR_NOT_EMPTY))
            ->allowEmptyTime('calendar_time_default', __(Message::ERROR_NOT_EMPTY), false)
            ->add('calendar_time_default', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'time' => [
                    'rule' => [
                        'time',
                    ],
                    'last' => true,
                    'message' => __(Message::ERROR_TIME),
                ],
            ]);

        $validator->requirePresence('reservation_form_type_first', true, __(Message::ERROR_NOT_EMPTY_SELECT))
            ->allowEmptyString('reservation_form_type_first', __(Message::ERROR_NOT_EMPTY_SELECT), false)
            ->add('reservation_form_type_first', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'inList' => [
                    'rule' => [
                        'inList',
                        array_keys($this->getFieldValueOptions('reservationFormTypeFirst')),
                    ],
                    'last' => true,
                    'message' => __(Message::ERROR_IN_LIST),
                ],
            ]);

        $validator->requirePresence('front_public_flg', true, __(Message::ERROR_NOT_EMPTY_SELECT))
            ->allowEmptyString('front_public_flg', __(Message::ERROR_NOT_EMPTY_SELECT), false)
            ->add('front_public_flg', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'inList' => [
                    'rule' => [
                        'inList',
                        array_keys($this->getFieldValueOptions('frontPublicFlg')),
                    ],
                    'last' => true,
                    'message' => __(Message::ERROR_IN_LIST),
                ],
            ]);

        $validator->requirePresence('admin_calendar_type_default', true, __(Message::ERROR_NOT_EMPTY_SELECT))
            ->allowEmptyString('admin_calendar_type_default', __(Message::ERROR_NOT_EMPTY_SELECT), false)
            ->add('admin_calendar_type_default', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'inList' => [
                    'rule' => [
                        'inList',
                        array_keys($this->getFieldValueOptions('calendarType')),
                    ],
                    'last' => true,
                    'message' => __(Message::ERROR_IN_LIST),
                ],
            ]);

        $validator->requirePresence('tag_search_method', true, __(Message::ERROR_NOT_EMPTY_SELECT))
            ->allowEmptyString('tag_search_method', __(Message::ERROR_NOT_EMPTY_SELECT), false)
            ->add('tag_search_method', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'inList' => [
                    'rule' => [
                        'inList',
                        array_keys($this->getFieldValueOptions('tagSearchMethod')),
                    ],
                    'last' => true,
                    'message' => __(Message::ERROR_IN_LIST),
                ],
            ]);

        $validator
            ->requirePresence('calendar_registration_deadline_display_flg', true, __(Message::ERROR_NOT_EMPTY_SELECT))
            ->allowEmptyString('calendar_registration_deadline_display_flg', __(Message::ERROR_NOT_EMPTY_SELECT), false)
            ->add('calendar_registration_deadline_display_flg', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'inList' => [
                    'rule' => [
                        'inList',
                        array_keys($this->getFieldValueOptions('calendarRegistrationDeadlineDisplayFlg')),
                    ],
                    'last' => true,
                    'message' => __(Message::ERROR_IN_LIST),
                ],
            ]);

        /** @var \App\Model\Table\SystemSettingsTable $systemSettingsTable */
        $systemSettingsTable = $this->getTableLocator()->get('SystemSettings');

        $isRequired = $systemSettingsTable->getData()->usePayment();
        $validator
            ->requirePresence('reservation_edit_payment_flg', $isRequired, __(Message::ERROR_NOT_EMPTY_SELECT))
            ->allowEmptyString('reservation_edit_payment_flg', __(Message::ERROR_NOT_EMPTY_SELECT), !$isRequired)
            ->add('reservation_edit_payment_flg', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'inList' => [
                    'rule' => [
                        'inList',
                        array_keys($this->getFieldValueOptions('reservationEditPaymentFlg')),
                    ],
                    'last' => true,
                    'message' => __(Message::ERROR_IN_LIST),
                ],
            ]);

        return $validator;
    }

    /**
     * Returns the default validator object.
     *
     * @param \Cake\Validation\Validator $validator The validator.
     * @return \Cake\Validation\Validator
     */
    public function validationNewsSetting(Validator $validator)
    {
        $validator->requirePresence('news_new_period_type', true, __(Message::ERROR_NOT_EMPTY_SELECT))
            ->allowEmptyString('news_new_period_type', __(Message::ERROR_NOT_EMPTY_SELECT), false)
            ->add('news_new_period_type', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'inList' => [
                    'rule' => [
                        'inList',
                        array_keys($this->getFieldValueOptions('tagSearchMethod')),
                    ],
                    'last' => true,
                    'message' => __(Message::ERROR_IN_LIST),
                ],
            ]);

        $validator->requirePresence('news_new_period_number_hour', true, __(Message::ERROR_NOT_EMPTY_SELECT))
            ->allowEmptyString('news_new_period_number_hour', __(Message::ERROR_NOT_EMPTY_SELECT), false)
            ->add('news_new_period_number_hour', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'inList' => [
                    'rule' => [
                        'inList',
                        array_keys($this->getFieldValueOptions('newsNewPeriodTime')),
                    ],
                    'last' => true,
                    'message' => __(Message::ERROR_IN_LIST),
                ],
            ]);

        $validator->requirePresence('news_new_period_number_day', true, __(Message::ERROR_NOT_EMPTY_SELECT))
            ->allowEmptyString('news_new_period_number_day', __(Message::ERROR_NOT_EMPTY_SELECT), false)
            ->add('news_new_period_number_day', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'inList' => [
                    'rule' => [
                        'inList',
                        array_keys($this->getFieldValueOptions('newsNewPeriodDay')),
                    ],
                    'last' => true,
                    'message' => __(Message::ERROR_IN_LIST),
                ],
            ]);

        $validator->requirePresence('top_news_number', true, __(Message::ERROR_NOT_EMPTY_SELECT))
            ->allowEmptyString('top_news_number', __(Message::ERROR_NOT_EMPTY_SELECT), false)
            ->add('top_news_number', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'naturalNumber' => [
                    'rule' => ['naturalNumber'],
                    'last' => true,
                    'message' => __(Message::ERROR_NATURAL_NUMBER),
                ],
                'lessThanOrEqual' => [
                    'rule' => ['comparison', Validation::COMPARE_LESS_OR_EQUAL, static::NEWS_TOP_DISPLAY],
                    'last' => true,
                    'message' => __(Message::ERROR_OVER_DIGIT, static::NEWS_TOP_DISPLAY),
                ],
            ]);

        return $validator;
    }

    /**
     * Returns the default validator object.
     *
     * @param \Cake\Validation\Validator $validator The validator.
     * @return \Cake\Validation\Validator
     */
    public function validationCms(Validator $validator)
    {
        $validator->requirePresence('site_theme', true, __(Message::ERROR_NOT_EMPTY_SELECT))
            ->allowEmptyString('site_theme', __(Message::ERROR_NOT_EMPTY_SELECT), false)
            ->add('site_theme', [
                'isArray' => [
                    'rule' => ['isArray'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
            ]);

        //色のバリデーター
        $colorValidator = new KuchenValidator();
        foreach (array_keys($this->getFieldValueOptions('siteTheme')) as $color) {
            $colorValidator->allowEmptyString($color)
                ->add($color, ['hexColor' => [
                    'rule' => [
                        'hexColor',
                    ],
                    'last' => true,
                    'message' => __(Message::ERROR_COLOR_CODE),
                ]]);
        }
        $validator->addNested('site_theme', $colorValidator);

        $validator->requirePresence('header_logo_pc', true, __(Message::ERROR_NOT_EMPTY))
            ->allowEmptyString('header_logo_pc')
            ->add('header_logo_pc', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'alnumSym' => [
                    'rule' => ['alnumSym'],
                    'last' => true,
                    'message' => __(Message::ERROR_ALNUM_SYM),
                ],
                'maxLength' => [
                    'rule' => ['maxLength', static::URL_MAX],
                    'last' => true,
                    'message' => __(Message::ERROR_MAX_LENGTH, static::URL_MAX),
                ],
            ]);

        $validator->requirePresence('header_logo_sp', true, __(Message::ERROR_NOT_EMPTY))
            ->allowEmptyString('header_logo_sp')
            ->add('header_logo_sp', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'alnumSym' => [
                    'rule' => ['alnumSym'],
                    'last' => true,
                    'message' => __(Message::ERROR_ALNUM_SYM),
                ],
                'maxLength' => [
                    'rule' => ['maxLength', static::URL_MAX],
                    'last' => true,
                    'message' => __(Message::ERROR_MAX_LENGTH, static::URL_MAX),
                ],
            ]);

        $validator->requirePresence('key_visual_url', true, __(Message::ERROR_NOT_EMPTY))
            ->allowEmptyString('key_visual_url')
            ->add('key_visual_url', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'alnumSym' => [
                    'rule' => ['alnumSym'],
                    'last' => true,
                    'message' => __(Message::ERROR_ALNUM_SYM),
                ],
                'maxLength' => [
                    'rule' => ['maxLength', static::URL_MAX],
                    'last' => true,
                    'message' => __(Message::ERROR_MAX_LENGTH, static::URL_MAX),
                ],
            ]);

        $validator->requirePresence('meta_keyword', true, __(Message::ERROR_NOT_EMPTY))
            ->allowEmptyString('meta_keyword')
            ->add('meta_keyword', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'maxLength' => [
                    'rule' => ['maxLength', static::KEYWORD_MAX],
                    'last' => true,
                    'message' => __(Message::ERROR_MAX_LENGTH, static::KEYWORD_MAX),
                ],
                'ngWord' => [
                    'rule' => function ($value) {
                        return !CustomValidation::includeString($value, Configure::readOrFail('Setting.word.ngWords'), [
                            'case' => false,
                            'width' => false,
                            'kana' => false,
                        ]);
                    },
                    'last' => true,
                    'message' => __(Message::ERROR_NG_WORD),
                ],
            ]);

        $validator->requirePresence('meta_description', true, __(Message::ERROR_NOT_EMPTY))
            ->allowEmptyString('meta_description')
            ->add('meta_description', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'maxLength' => [
                    'rule' => ['maxLength', static::DESCRIPTION_MAX],
                    'last' => true,
                    'message' => __(Message::ERROR_MAX_LENGTH, static::DESCRIPTION_MAX),
                ],
                'ngWord' => [
                    'rule' => function ($value) {
                        return !CustomValidation::includeString($value, Configure::readOrFail('Setting.word.ngWords'), [
                            'case' => false,
                            'width' => false,
                            'kana' => false,
                        ]);
                    },
                    'last' => true,
                    'message' => __(Message::ERROR_NG_WORD),
                ],
            ]);

        $validator->requirePresence('top_information', true, __(Message::ERROR_NOT_EMPTY))
            ->allowEmptyString('top_information')
            ->add('top_information', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'maxLength' => [
                    'rule' => ['maxLength', static::TOP_INFO_MAX],
                    'last' => true,
                    'message' => __(Message::ERROR_MAX_LENGTH, static::TOP_INFO_MAX),
                ],
            ]);

        return $validator;
    }

    /**
     * IDを取得する
     *
     * @param int|null $id id
     * @return int id
     */
    public function getId($id)
    {
        if ($id === null) {
            $id = static::BASE_ID;
        }

        $this->editId = $id;

        return $id;
    }

    /**
     * デフォルトのファインダー
     *
     * @param \Cake\ORM\Query $query The query to find with
     * @param array $options The options to use for the find
     * @return \Cake\ORM\Query The query builder
     */
    public function findDefault(Query $query, array $options)
    {
        $query->select([
            'login_flg',
            'login_required_flg',
            'user_add_flg',
            'user_edit_flg',
            'mail_edit_optin_flg',
            'optin_flg',
            'id_reminder_flg',
            'password_reminder_flg',
            'reservation_add_user_flg',
            'reservation_add_not_user_flg',
            'reservation_continuous_flg',
            'reservation_reminder_flg',
            'reservation_reminder_type',
            'reservation_reminder_time',
            'inquiry_flg',
            'front_public_flg',
            'site_theme',
            'key_visual_url',
            'top_information',
            'header_logo_pc',
            'header_logo_sp',
            'login_display_add_user_flg',
            'top_news_number',
            'top_search_label_flg',
            'top_search_tag_flg',
            'tag_search_method',
            'top_search_label_flg',
            'top_search_tag_flg',
            'top_search_event_name_flg',
            'calendar_search_label_flg',
            'calendar_search_tag_flg',
            'calendar_search_event_name_flg',
            'calendar_date_default',
            'calendar_time_from',
            'calendar_time_to',
            'calendar_time_default',
            'calendar_month_display_limit',
            'reservation_form_type_first',
            'reservation_edit_event_flg',
            'terms_flg',
            'user_terms_flg',
            'reservation_terms_flg',
            'sctl_flg',
            'reservation_sctl_flg',
            'charge_breakdown_flg',
            'news_new_period_type',
            'news_new_period_number',
            'meta_keyword',
            'meta_description',
            'admin_calendar_type_default',
            'reservation_edit_not_user_flg',
            'reservation_close_reminder_flg',
            'reservation_close_reminder_type',
            'reservation_close_reminder_time',
            'reservation_edit_payment_flg',
            'calendar_registration_deadline_display_flg',
            'modified',
        ]);

        return $query;
    }

    /**
     * 基本設定データ取得ファインダー
     *
     * @param \Cake\ORM\Query $query The query to find with
     * @param array $options The options to use for the find
     * @return \Cake\ORM\Query The query builder
     */
    public function findSystem(Query $query, array $options)
    {
        $query->select([
            'id',
            'login_flg',
            'login_required_flg',
            'user_add_flg',
            'user_edit_flg',
            'mail_edit_optin_flg',
            'optin_flg',
            'id_reminder_flg',
            'password_reminder_flg',
            'login_display_add_user_flg',
            'reservation_add_user_flg',
            'reservation_add_not_user_flg',
            'reservation_continuous_flg',
            'reservation_reminder_flg',
            'reservation_reminder_type',
            'reservation_reminder_time',
            'inquiry_flg',
            'front_public_flg',
            'top_information',
            'tag_search_method',
            'top_search_label_flg',
            'top_search_tag_flg',
            'top_search_event_name_flg',
            'calendar_search_label_flg',
            'calendar_search_tag_flg',
            'calendar_search_event_name_flg',
            'calendar_date_default',
            'calendar_time_from',
            'calendar_time_to',
            'calendar_time_default',
            'calendar_month_display_limit',
            'reservation_form_type_first',
            'reservation_edit_event_flg',
            'terms_flg',
            'user_terms_flg',
            'reservation_terms_flg',
            'sctl_flg',
            'reservation_sctl_flg',
            'charge_breakdown_flg',
            'admin_calendar_type_default',
            'reservation_edit_not_user_flg',
            'reservation_close_reminder_flg',
            'reservation_close_reminder_type',
            'reservation_close_reminder_time',
            'reservation_edit_payment_flg',
            'calendar_registration_deadline_display_flg',
        ])->where(['id' => $this->editId]);

        return $query;
    }

    /**
     * CMSデータ取得ファインダー
     *
     * @param \Cake\ORM\Query $query The query to find with
     * @param array $options The options to use for the find
     * @return \Cake\ORM\Query The query builder
     */
    public function findCms(Query $query, array $options)
    {
        $query->select([
            'id',
            'site_theme',
            'header_logo_pc',
            'header_logo_sp',
            'meta_keyword',
            'meta_description',
            'key_visual_url',
            'top_information',
        ])->where(['id' => $this->editId]);

        return $query;
    }

    /**
     * お知らせ設定取得ファインダー
     *
     * @param \Cake\ORM\Query $query The query to find with
     * @param array $options The options to use for the find
     * @return \Cake\ORM\Query The query builder
     */
    public function findNewsSetting(Query $query, array $options)
    {
        $query->select([
            'id',
            'news_new_period_number',
            'news_new_period_type',
            'top_news_number',
        ])->where(['id' => $this->editId]);

        return $query;
    }

    /**
     * Model.afterSaveイベント
     *
     * @param \Cake\Event\EventInterface $event イベント
     * @param \Cake\Datasource\EntityInterface $entity エンティティ
     * @param \ArrayObject $options オプション
     * @return void
     */
    public function afterSave(EventInterface $event, EntityInterface $entity, ArrayObject $options)
    {
        //CMSの場合だけ
        $cell = Hash::get($options, 'cell');
        if (Hash::get($options, 'save') === 'cms' && $cell instanceof \Cake\View\Cell) {
            $this->updateCustomCss($entity, $cell);
        }
    }

    /**
     * Model.afterSaveCommitイベント
     *
     * @param \Cake\Event\EventInterface $event イベント
     * @param \Cake\Datasource\EntityInterface $entity エンティティ
     * @param \ArrayObject $options オプション
     * @return void
     */
    public function afterSaveCommit(EventInterface $event, EntityInterface $entity, ArrayObject $options)
    {
        $this->deleteCacheData();
    }

    /**
     * カスタムCSSのアップデート
     *
     * @param \Cake\Datasource\EntityInterface $entity entity
     * @param mixed $cell cell
     * @return void
     */
    public function updateCustomCss($entity, $cell)
    {
        //CMSの場合だけ
        $cell->set(['site_theme' => $entity->get('site_theme')]);
        $css = $cell->render();

        $file = new SplFileInfo(CUSTOM_CSS);
        if (!$file->isWritable()) {
            throw new PersistenceFailedException($entity, Message::ERROR_SYSTEM_ERROR);
        }
        if (file_put_contents($file->getPathname(), $css) === false) {
            throw new PersistenceFailedException($entity, Message::ERROR_SYSTEM_ERROR);
        }
    }

    /**
     * カスタムCSSの更新日時を返却
     *
     * @return int|false ファイル更新日時
     */
    public function getCustomCssTimestamp()
    {
        $file = new SplFileInfo(CUSTOM_CSS);

        return $file->getMTime();
    }

    /**
     * データを取得
     *
     * @return \App\Model\Entity\SiteSetting データ
     */
    public function getData()
    {
        if (!isset($this->cacheData)) {
            $this->cacheData = Cache::remember('data', function () {
                $query = $this->find('default');

                return $query->first();
            }, 'siteSettings');
        }

        return $this->cacheData;
    }

    /**
     * リマインダー時間取得（0.5～24）
     *
     * @return mixed
     */
    public function get30minSeparatedTime()
    {
        $timeList = [];
        for ($i = static::REMINDER_TIME_MIN; $i <= static::REMINDER_TIME_MAX; $i += 0.5) {
            $timeList[$i * 60] = $i;
        }

        return $timeList;
    }

    /**
     * リマインダー時間取得（1～30）
     *
     * @return mixed
     */
    public function get1daySeparatedDay()
    {
        $dayList = [];
        for ($i = static::REMINDER_DAY_MIN; $i <= static::REMINDER_DAY_MAX; $i++) {
            $dayList[$i] = $i;
        }

        return $dayList;
    }

    /**
     * キャッシュファイル削除
     *
     * @return void
     */
    public function deleteCacheData()
    {
        Cache::delete('data', 'siteSettings');
        $this->cacheData = null;
    }
}
