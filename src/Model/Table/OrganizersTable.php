<?php
declare(strict_types=1);

namespace App\Model\Table;

use App\Locale\Message;
use App\Model\AppTable;
use App\Model\Entity\Organizer;
use ArrayObject;
use Cake\Core\Configure;
use Cake\Core\Exception\CakeException;
use Cake\Datasource\EntityInterface;
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\Event\EventInterface;
use Cake\Http\Exception\BadRequestException;
use Cake\ORM\Query;
use Cake\Utility\Hash;
use Cake\Validation\Validator;

/**
 * Organizers Model
 *
 * @method \App\Model\Entity\Organizer newEmptyEntity()
 * @method \App\Model\Entity\Organizer newEntity(array $data, array $options = [])
 * @method \App\Model\Entity\Organizer[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\Organizer get($primaryKey, $options = [])
 * @method \App\Model\Entity\Organizer findOrCreate($search, ?callable $callback = null, $options = [])
 * @method \App\Model\Entity\Organizer patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\Organizer[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\Organizer|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\Organizer saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\Organizer[]|\Cake\Datasource\ResultSetInterface|false saveMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\Organizer[]|\Cake\Datasource\ResultSetInterface saveManyOrFail(iterable $entities, $options = [])
 * @method \App\Model\Entity\Organizer[]|\Cake\Datasource\ResultSetInterface|false deleteMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\Organizer[]|\Cake\Datasource\ResultSetInterface deleteManyOrFail(iterable $entities, $options = [])
 */
class OrganizersTable extends AppTable
{
    public const NAME_MAXLENGTH = 100;
    public const ZOOM_API_KEY_MAXLENGTH = 2000;
    public const ZOOM_API_SECRET_MAXLENGTH = 2000;
    public const ZOOM_HOST_EMAIL_MAXLENGTH = 254;
    public const MEET_API_KEY_MAXLENGTH = 5000;
    public const MEET_CALENDAR_ID_MAXLENGTH = 254;
    public const SORT_KEY_MAXLENGTH = 100;

    /**
     * @var array|null
     */
    protected $organizerList = null;

    /**
     * @var array|null
     */
    protected $organizersForApi = null;

    /**
     * @inheritDoc
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->hasMany('Events', [
            'foreignKey' => 'organizer_id',
        ]);
        $this->hasMany('ReservationVideoMeetings', [
            'foreignKey' => 'organizer_id',
        ]);

        $this->getBehavior('AdminOperationLog')->setConfig('saveOperation', true);
    }

    /**
     * @inheritDoc
     */
    public function validationDefault(Validator $validator): Validator
    {
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
                    'rule' => ['maxLength', static::NAME_MAXLENGTH],
                    'last' => true,
                    'message' => __(Message::ERROR_MAX_LENGTH, static::NAME_MAXLENGTH),
                ],
            ]);

        $validator
            ->requirePresence('video_meeting_type', true, __(Message::ERROR_NOT_EMPTY_SELECT))
            ->allowEmptyString('video_meeting_type', __(Message::ERROR_NOT_EMPTY_SELECT), false)
            ->add('video_meeting_type', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'inList' => [
                    'rule' => ['inList', array_keys($this->getFieldValueOptions('videoMeetingType'))],
                    'last' => true,
                    'message' => __(Message::ERROR_IN_LIST),
                ],
            ]);

        // Zoomかどうかの判定
        $isZoom = function ($context) {
            $videoMeetingType = Hash::get($context['data'], 'video_meeting_type');
            if (
                !is_scalar($videoMeetingType)
                || (string)$videoMeetingType !== (string)Organizer::VIDEO_MEETING_TYPE_ZOOM
            ) {
                return false;
            }

            return true;
        };
        $isNotZoom = function ($context) use ($isZoom) {
            return !call_user_func($isZoom, $context);
        };

        // Zoom+JWTかどうかの判定
        $isZoomJwt = function ($context) use ($isZoom) {
            $zoomConnectType = Hash::get($context['data'], 'zoom_connect_type');

            return call_user_func($isZoom, $context) && is_scalar($zoomConnectType)
                && (string)$zoomConnectType === (string)Organizer::ZOOM_CONNECT_TYPE_JWT;
        };

        // Meetかどうかの判定
        $isMeet = function ($context) {
            $videoMeetingType = Hash::get($context['data'], 'video_meeting_type');
            if (
                !is_scalar($videoMeetingType)
                || (string)$videoMeetingType !== (string)Organizer::VIDEO_MEETING_TYPE_MEET
            ) {
                return false;
            }

            return true;
        };
        $isNotMeet = function ($context) use ($isMeet) {
            return !call_user_func($isMeet, $context);
        };

        $isZoomApiRequired = function ($context) use ($isZoomJwt) {
            if ($context['newRecord']) {
                return call_user_func($isZoomJwt, $context);
            }

            if (
                !call_user_func($isZoomJwt, $context) || !isset($context['data']['id'])
                || !$this->validatePrimaryKey($context['data']['id'])
            ) {
                return false;
            }

            $original = $this->find()->where(['id' => $context['data']['id']])->first();
            if (!($original instanceof Organizer)) {
                return false;
            }

            return (string)$original->get('zoom_connect_type') !== (string)Organizer::ZOOM_CONNECT_TYPE_JWT;
        };
        $isNotZoomApiRequired = function ($context) use ($isZoomApiRequired) {
            return !call_user_func($isZoomApiRequired, $context);
        };

        $isMeetApiRequired = function ($context) use ($isMeet) {
            if (!call_user_func($isMeet, $context) || !$context['newRecord']) {
                return false;
            }

            return true;
        };
        $isNotMeetApiRequired = function ($context) use ($isMeetApiRequired) {
            return !call_user_func($isMeetApiRequired, $context);
        };

        $validator
            ->requirePresence('zoom_connect_type', $isZoom, __(Message::ERROR_NOT_EMPTY))
            ->allowEmptyString('zoom_connect_type', __(Message::ERROR_NOT_EMPTY), $isNotZoom)
            ->add('zoom_connect_type', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'inList' => [
                    'rule' => ['inList', array_keys($this->getFieldValueOptions('zoomConnectType'))],
                    'last' => true,
                    'message' => __(Message::ERROR_IN_LIST),
                ],
            ]);

        $validator
            ->requirePresence('zoom_api_key', $isZoomApiRequired, __(Message::ERROR_NOT_EMPTY))
            ->allowEmptyString('zoom_api_key', __(Message::ERROR_NOT_EMPTY), $isNotZoomApiRequired)
            ->add('zoom_api_key', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'maxLength' => [
                    'rule' => ['maxLength', static::ZOOM_API_KEY_MAXLENGTH],
                    'last' => true,
                    'message' => __(Message::ERROR_MAX_LENGTH, static::ZOOM_API_KEY_MAXLENGTH),
                ],
            ]);

        $validator
            ->requirePresence('zoom_api_secret', $isZoomApiRequired, __(Message::ERROR_NOT_EMPTY))
            ->allowEmptyString('zoom_api_secret', __(Message::ERROR_NOT_EMPTY), $isNotZoomApiRequired)
            ->add('zoom_api_secret', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'maxLength' => [
                    'rule' => ['maxLength', static::ZOOM_API_SECRET_MAXLENGTH],
                    'last' => true,
                    'message' => __(Message::ERROR_MAX_LENGTH, static::ZOOM_API_SECRET_MAXLENGTH),
                ],
            ]);

        $validator
            ->requirePresence('zoom_host_email', $isZoom, __(Message::ERROR_NOT_EMPTY))
            ->allowEmptyString('zoom_host_email', __(Message::ERROR_NOT_EMPTY), $isNotZoom)
            ->add('zoom_host_email', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'maxLength' => [
                    'rule' => ['maxLength', static::ZOOM_HOST_EMAIL_MAXLENGTH],
                    'last' => true,
                    'message' => __(Message::ERROR_MAX_LENGTH, static::ZOOM_HOST_EMAIL_MAXLENGTH),
                ],
                'email' => [
                    'rule' => ['email'],
                    'last' => true,
                    'message' => __(Message::ERROR_MAIL_ADDRESS),
                ],
            ]);

        $validator
            ->requirePresence('meet_api_key', $isMeetApiRequired, __(Message::ERROR_NOT_EMPTY))
            ->allowEmptyString('meet_api_key', __(Message::ERROR_NOT_EMPTY), $isNotMeetApiRequired)
            ->add('meet_api_key', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'maxLength' => [
                    'rule' => ['maxLength', static::MEET_API_KEY_MAXLENGTH],
                    'last' => true,
                    'message' => __(Message::ERROR_MAX_LENGTH, static::MEET_API_KEY_MAXLENGTH),
                ],
                'isJson' => [
                    'rule' => function ($value) {
                        if (!is_array(json_decode($value, true))) {
                            return false;
                        }

                        return true;
                    },
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_JSON),
                ],
            ]);

        $validator
            ->requirePresence('meet_calendar_id', $isMeet, __(Message::ERROR_NOT_EMPTY))
            ->allowEmptyString('meet_calendar_id', __(Message::ERROR_NOT_EMPTY), $isNotMeet)
            ->add('meet_calendar_id', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'maxLength' => [
                    'rule' => ['maxLength', static::MEET_CALENDAR_ID_MAXLENGTH],
                    'last' => true,
                    'message' => __(Message::ERROR_MAX_LENGTH, static::MEET_CALENDAR_ID_MAXLENGTH),
                ],
                'email' => [
                    'rule' => ['email'],
                    'last' => true,
                    'message' => __(Message::ERROR_MAIL_ADDRESS),
                ],
            ]);

        $validator
            ->requirePresence('sort_key', false)
            ->allowEmptyString('sort_key')
            ->add('sort_key', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'maxLength' => [
                    'rule' => ['maxLength', static::SORT_KEY_MAXLENGTH],
                    'last' => true,
                    'message' => __(Message::ERROR_MAX_LENGTH, static::SORT_KEY_MAXLENGTH),
                ],
            ]);

        return $validator;
    }

    /**
     * @inheritDoc
     */
    protected function searchConfiguration()
    {
        $this->searchManager()
            ->value('id')
            ->like('name', [
                'before' => true,
                'after' => true,
            ])
            ->value('video_meeting_type', [
                'multiValue' => true,
            ]);
    }

    /**
     * @inheritDoc
     */
    public function buildFieldValueOptions()
    {
        $fieldValueOptions = [
            'videoMeetingType' => Configure::readOrFail('Master.organizer.videoMeetingType'),
            'zoomConnectType' => Configure::readOrFail('Master.organizer.zoomConnectType'),
        ];

        return $fieldValueOptions;
    }

    /**
     * 一覧のファインダー
     *
     * @param \Cake\ORM\Query $query The query to find with
     * @param array $options The options to use for the find
     * @return \Cake\ORM\Query The query builder
     */
    public function findSearchList(Query $query, array $options = [])
    {
        $query->select([
            'id',
            'name',
            'video_meeting_type',
            'zoom_host_email',
            'meet_calendar_id',
            'sort_key',
            'created',
            'modified',
        ]);

        $sort = Hash::get($options, 'inputs.sort', 'id');
        $direction = strtoupper(Hash::get($options, 'inputs.direction', 'asc'));
        $query->order([
            'Organizers.' . $sort => $direction,
        ] + [
            'Organizers.id' => $direction,
        ], true);

        return $this->callFinder('search', $query, ['search' => Hash::get($options, 'inputs', [])]);
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
            'name',
            'video_meeting_type',
            'zoom_connect_type',
            'zoom_host_email',
            'meet_calendar_id',
            'sort_key',
        ]);

        return $query;
    }

    /**
     * 主催者選択用のファインダー
     *
     * @param \Cake\ORM\Query $query The query to find with
     * @param array $options The options to use for the find
     * @return \Cake\ORM\Query The query builder
     */
    public function findOrganizerList(Query $query, array $options = [])
    {
        $query->select([
            'id',
            'name',
            'video_meeting_type',
        ]);
        $query->order([
            'sort_key' => 'ASC',
            'name' => 'ASC',
            'id' => 'ASC',
        ]);

        $query->enableHydration(false);
        $query->formatResults(function ($organizers) {
            $result = [];
            foreach ($organizers as $organizer) {
                $result[$organizer['id']] = $organizer;
            }

            return $result;
        });

        return $query;
    }

    /**
     * API連携用のファインダー
     *
     * @param \Cake\ORM\Query $query The query to find with
     * @param array $options The options to use for the find
     * @return \Cake\ORM\Query The query builder
     */
    public function findApi(Query $query, array $options)
    {
        $query->select([
            'id',
            'name',
            'video_meeting_type',
            'zoom_api_key',
            'zoom_api_secret',
            'zoom_connect_type',
            'zoom_host_email',
            'meet_api_key',
            'meet_calendar_id',
        ]);

        return $query;
    }

    /**
     * Model.beforeMarshalイベント
     *
     * @param \Cake\Event\EventInterface $event イベント
     * @param \ArrayObject $data データ
     * @param \ArrayObject $options オプション
     * @return void
     */
    public function beforeMarshal(EventInterface $event, ArrayObject $data, ArrayObject $options)
    {
        $type = Hash::get($data, 'video_meeting_type');
        if (!is_scalar($type) || (string)$type !== (string)Organizer::VIDEO_MEETING_TYPE_ZOOM) {
            $data->offsetSet('zoom_api_key', null);
            $data->offsetSet('zoom_api_secret', null);
            $data->offsetSet('zoom_connect_type', null);
            $data->offsetSet('zoom_host_email', null);
        }
        if (!is_scalar($type) || (string)$type !== (string)Organizer::VIDEO_MEETING_TYPE_MEET) {
            $data->offsetSet('meet_api_key', null);
            $data->offsetSet('meet_calendar_id', null);
        }

        $zoomConnectType = Hash::get($data, 'zoom_connect_type');
        if (!is_scalar($zoomConnectType) || (string)$zoomConnectType !== (string)Organizer::ZOOM_CONNECT_TYPE_JWT) {
            $data->offsetSet('zoom_api_key', null);
            $data->offsetSet('zoom_api_secret', null);
        }
    }

    /**
     * Model.beforeSaveイベント
     *
     * @param \Cake\Event\EventInterface $event イベント
     * @param \Cake\Datasource\EntityInterface $entity エンティティ
     * @param \ArrayObject $options オプション
     * @return void
     */
    public function beforeSave(EventInterface $event, EntityInterface $entity, ArrayObject $options)
    {
        if (!($entity instanceof Organizer)) {
            throw new CakeException();
        }

        if (!$entity->isNew()) {
            $notUpdateEmptyColumns = [
                'zoom_api_key',
                'zoom_api_secret',
                'meet_api_key',
            ];
            foreach ($notUpdateEmptyColumns as $column) {
                if ((string)$entity->get($column) === '') {
                    $entity->set($column, $entity->getOriginal($column));
                    $entity->setDirty($column, false);
                }
            }

            if ((string)$entity->get('zoom_connect_type') !== (string)Organizer::ZOOM_CONNECT_TYPE_JWT) {
                $entity->set('zoom_api_key', null);
                $entity->set('zoom_api_secret', null);
            }
        }

        if (!$entity->has('zoom_connect_type')) {
            $entity->set('zoom_connect_type', Organizer::ZOOM_CONNECT_TYPE_JWT);
        }
    }

    /**
     * 主催者一覧を取得
     *
     * @return array
     */
    public function getOrganizerList()
    {
        if (!isset($this->organizerList)) {
            $this->organizerList = $this->find('organizerList')->toArray();
        }

        return $this->organizerList;
    }

    /**
     * 主催者名一覧を取得
     *
     * @return array
     */
    public function getOrganizerNames()
    {
        $names = [];
        foreach ($this->getOrganizerList() as $organizer) {
            $names[$organizer['id']] = $this->createDisplayName(
                $organizer['video_meeting_type'],
                $organizer['name']
            );
        }

        return $names;
    }

    /**
     * 主催者の表示名を生成
     *
     * @param int $videoMeetingType ビデオ会議種別
     * @param string $name 主催者名
     * @return string
     */
    public function createDisplayName(int $videoMeetingType, string $name)
    {
        $result = sprintf(
            '%s（%s）',
            $name,
            Configure::read('Master.organizer.videoMeetingType.' . $videoMeetingType, '')
        );

        return $result;
    }

    /**
     * API用に主催者を取得
     *
     * @param int $organizerId 主催者ID
     * @return \App\Model\Entity\Organizer|null
     */
    public function getOrganizerForApi(int $organizerId)
    {
        if (!isset($this->organizersForApi)) {
            $this->organizersForApi = [];
        }

        if (!isset($this->organizersForApi[$organizerId])) {
            try {
                /** @throws \Cake\Datasource\Exception\RecordNotFoundException */
                $organizer = $this->get($organizerId, [
                    'finder' => 'api',
                ]);
            } catch (RecordNotFoundException $e) {
                $organizer = null;
            }

            if (isset($organizer)) {
                $this->organizersForApi[$organizerId] = $organizer;
            } else {
                $this->organizersForApi[$organizerId] = false;
            }
        }

        if ($this->organizersForApi[$organizerId] === false) {
            return null;
        }

        return $this->organizersForApi[$organizerId];
    }

    /**
     * プランの権限をチェック
     *
     * @return void
     */
    public function checkPlan()
    {
        /** @var \App\Model\Table\SystemSettingsTable $systemSettingsTable */
        $systemSettingsTable = $this->getTableLocator()->get('SystemSettings');

        if (!$systemSettingsTable->getData()->canCoordinateVideoMeeting()) {
            throw new BadRequestException(Message::ERROR_PLAN_AUTHORITY);
        }
    }
}
