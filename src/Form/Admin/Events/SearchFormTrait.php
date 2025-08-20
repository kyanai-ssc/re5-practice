<?php
declare(strict_types=1);

namespace App\Form\Admin\Events;

use App\Locale\Message;
use App\Model\Table\EventSmartLocksTable;
use App\Utility\ArrayUtility;
use App\Utility\CommonData\CommonDataTrait;
use App\Validation\CustomValidation;
use Cake\Core\Configure;
use Cake\Form\Schema;
use Cake\Utility\Hash;
use Cake\Validation\Validator;
use Kuchen\Validation\Validation\Validator as KuchenValidator;

/**
 * 予約枠検索
 */
trait SearchFormTrait
{
    use CommonDataTrait;

    /**
     * 予約枠検索用のスキーマを生成
     *
     * @param \Cake\Form\Schema $schema スキーマ
     * @return \Cake\Form\Schema スキーマ
     */
    protected function buildEventSearchSchema(Schema $schema)
    {
        $schema
            ->addField('id', 'integer')
            ->addField('type', 'integer')
            ->addField('name', 'string')
            ->addField('public_from', 'date')
            ->addField('public_to', 'date')
            ->addField('schedule_date_from', 'date')
            ->addField('schedule_date_to', 'date')
            ->addField('event_tags', 'array')
            ->addField('label_id', 'integer')
            ->addField('public_flg', 'integer')
            ->addField('sort', 'string')
            ->addField('direction', 'string')
            ->addField('limit', 'integer')
            ->addField('page', 'integer');

        /** @var \App\Model\Table\SystemSettingsTable $systemSettingsTable */
        $systemSettingsTable = $this->getTableLocator()->get('SystemSettings');

        if ($systemSettingsTable->getData()->canCoordinateVideoMeeting()) {
            $schema->addField('organizer_id', 'integer');
        }
        if ($systemSettingsTable->getData()->useSmartLock()) {
            $schema->addField('event_smart_lock', 'array');
        }

        return $schema;
    }

    /**
     * 予約枠検索用のバリデータを生成
     *
     * @param \Cake\Validation\Validator $validator バリデータ
     * @param array $options オプション
     * @return \Cake\Validation\Validator バリデータ
     */
    protected function buildEventSearchValidator(Validator $validator, array $options = [])
    {
        $textMax = $options['textMax'];

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
                    'rule' => ['maxLength', $textMax],
                    'last' => true,
                    'message' => __(Message::ERROR_MAX_LENGTH, $textMax),
                ],
            ]);

        $validator
            ->requirePresence('id', false)
            ->allowEmptyString('id')
            ->add('id', [
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
                    'rule' => ['comparison', CustomValidation::COMPARE_LESS_OR_EQUAL, CustomValidation::BIGINT_MAX],
                    'last' => true,
                    'message' => __(Message::ERROR_OVER_DIGIT, CustomValidation::BIGINT_MAX),
                ],
            ]);

        $validator
            ->requirePresence('type', false)
            ->allowEmptyArray('type')
            ->add('type', [
                'isArray' => [
                    'rule' => ['isArray'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'multiple' => [
                    'rule' => [
                        'multiple',
                        [
                            'in' => array_keys($this->getFieldValueOptions('type')),
                        ],
                    ],
                    'last' => true,
                    'message' => __(Message::ERROR_IN_LIST),
                ],
            ]);

        $validator
            ->requirePresence('public_flg', false)
            ->allowEmptyArray('public_flg')
            ->add('public_flg', [
                'isArray' => [
                    'rule' => ['isArray'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'multiple' => [
                    'rule' => [
                        'multiple',
                        [
                            'in' => array_keys($this->getFieldValueOptions('publicFlg')),
                        ],
                    ],
                    'last' => true,
                    'message' => __(Message::ERROR_IN_LIST),
                ],
            ]);

        $validator
            ->requirePresence('label_id', false)
            // 担当カテゴリがある管理者の場合は入力必須にする(マスター管理者を除く)
            ->allowEmptyString('label_id', __(Message::ERROR_INVALID_VALUE), function () {
                /** @var \App\Model\Entity\Admin $loginData */
                $loginData = $this->commonData()->getAdminLoginData();
                if ($loginData->isMasterAdmin()) {
                    return true;
                }

                return $this->commonData()->getAdminLoginLabel() === null;
            });

        /** @var \App\Model\Table\LabelsTable $labelTable */
        $labelTable = $this->getTableLocator()->get('Labels');
        $validator = $labelTable->addValidateLabelId($validator);
        $validator = $labelTable->addValidateLabelIdAdminUsable($validator);

        $validator
            ->requirePresence('schedule_date_from', false)
            ->allowEmptyDate('schedule_date_from')
            ->add('schedule_date_from', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'date' => [
                    'rule' => [
                        'date',
                        'ymd',
                    ],
                    'last' => true,
                    'message' => __(Message::ERROR_DATE),
                ],
            ]);

        $validator
            ->requirePresence('schedule_date_to', false)
            ->allowEmptyDate('schedule_date_to')
            ->add('schedule_date_to', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'date' => [
                    'rule' => [
                        'date',
                        'ymd',
                    ],
                    'last' => true,
                    'message' => __(Message::ERROR_DATE),
                ],
            ]);

        $validator
            ->requirePresence('public_from', false)
            ->allowEmptyDate('public_from')
            ->add('public_from', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'date' => [
                    'rule' => [
                        'date',
                        'ymd',
                    ],
                    'last' => true,
                    'message' => __(Message::ERROR_DATE),
                ],
            ]);

        $validator
            ->requirePresence('public_to', false)
            ->allowEmptyDate('public_to')
            ->add('public_to', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'date' => [
                    'rule' => [
                        'date',
                        'ymd',
                    ],
                    'last' => true,
                    'message' => __(Message::ERROR_DATE),
                ],
            ]);

        $tagValidator = new KuchenValidator();
        $tagValidator
            ->requirePresence('tag_id', false)
            ->allowEmptyArray('tag_id')
            ->add('tag_id', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'integer' => [
                    'rule' => ['integer', CustomValidation::BIGINT_MAX],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'exists' => [
                    'rule' => function ($check) {
                        $tagIds = $this->getFieldValueOptions('tagIds');
                        if (ArrayUtility::arraySearch($check, $tagIds) !== false || empty($check)) {
                            return true;
                        }

                        return false;
                    },
                    'last' => true,
                    'message' => __(Message::ERROR_IN_LIST),
                ],
            ]);

        $validator
            ->requirePresence('event_tags', false)
            ->array('event_tags', __(Message::ERROR_NOT_EMPTY_SELECT))
            ->allowEmptyArray('event_tags')
            ->addNestedMany('event_tags', $tagValidator);

        if (Hash::get($options, 'pageValidator', true)) {
            $this->addPaginateValidation($validator, [
                'fieldValueOptions' => $this->getFieldValueOptions(),
            ]);
        }

        $validator
            ->requirePresence('organizer_id', false)
            ->allowEmptyString('organizer_id')
            ->add('organizer_id', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'inList' => [
                    'rule' => [
                        'inList',
                        array_keys($this->getFieldValueOptions('organizerList')),
                    ],
                    'last' => true,
                    'message' => __(Message::ERROR_IN_LIST),
                ],
            ]);

        $eventSmartLockValidator = new KuchenValidator();
        $validator
            ->requirePresence('event_smart_lock', false)
            ->allowEmptyString('event_smart_lock')
            ->addNested('event_smart_lock', $eventSmartLockValidator);
        $eventSmartLockValidator
            ->requirePresence('smart_lock_device_key', false)
            ->allowEmptyString('smart_lock_device_key')
            ->add('smart_lock_device_key', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'maxLength' => [
                    'rule' => ['maxLength', EventSmartLocksTable::SMART_LOCK_DEVICE_KEY_MAX],
                    'last' => true,
                    'message' => __(Message::ERROR_MAX_LENGTH, EventSmartLocksTable::SMART_LOCK_DEVICE_KEY_MAX),
                ],
            ]);

        return $validator;
    }

    /**
     * 予約枠検索用の値リストを生成
     *
     * @return array 値リスト
     */
    protected function buildEventSearchFieldValueOptions()
    {
        /** @var \App\Model\Table\TagGroupsTable $tagGroupsTable */
        $tagGroupsTable = $this->getTableLocator()->get('TagGroups');
        /** @var \App\Model\Table\OrganizersTable $organizersTable */
        $organizersTable = $this->getTableLocator()->get('Organizers');

        $tagList = $tagGroupsTable->getTagsList();
        $tagIds = [];
        foreach ($tagList as $tags) {
            $tagIds = array_merge($tagIds, array_keys($tags['tag']));
        }

        $fieldValueOptions = [
            'type' => Configure::readOrFail('Master.event.type'),
            'publicFlg' => Configure::readOrFail('Master.event.publicFlg'),
            'tagList' => $tagList,
            'tagIds' => $tagIds,
            'sort' => [],
            'direction' => Configure::read('Setting.pagination.direction'),
            'limit' => $this->generatePaginateLimit(Configure::read('Setting.pagination.limit.config')),
            'organizerList' => $organizersTable->getOrganizerNames(),
        ];

        return $fieldValueOptions;
    }

    /**
     * 予約枠検索用のデフォルト値を生成
     *
     * @return array デフォルト値
     */
    protected function buildEventSearchDefaultFieldValues()
    {
        $defaultFieldValues = [
            'label_id' => $this->commonData()->getAdminLoginLabel(),
            'sort' => 'sort_no',
            'direction' => 'asc',
            'limit' => Configure::read('Setting.pagination.limit.default'),
            'page' => '1',
        ];

        return $defaultFieldValues;
    }
}
