<?php
declare(strict_types=1);

namespace App\Form\Common\Reservations;

use App\Form\AppForm;
use App\Form\Common\CommonFormTrait;
use App\Locale\Message;
use App\Model\EventCalendar\PaginateTypeInterface;
use App\Utility\ArrayUtility;
use App\Validation\CustomValidation;
use ArrayObject;
use Cake\Core\Configure;
use Cake\Core\Exception\CakeException;
use Cake\Form\Schema;
use Cake\Utility\Hash;
use Cake\Validation\Validator;
use Kuchen\Validation\Validation\Validator as KuchenValidator;

/**
 * カレンダーフォーム
 */
abstract class CalendarForm extends AppForm
{
    use CalendarFormTrait;
    use CommonFormTrait;

    public const EVENT_NAME_MAX = 1000;

    /**
     * @var \App\Model\EventCalendar\AbstractCalendarType|null
     */
    protected $eventCalendar = null;

    /**
     * @var int|null
     */
    protected $labelId = null;

    /**
     * @inheritDoc
     */
    protected function _buildSchema(Schema $schema): Schema
    {
        $schema
            ->addField('calendar_type', 'string')
            ->addField('date', 'string')
            ->addField('id', 'string')
            ->addField('label_id', 'string')
            ->addField('tag_id', 'string')
            ->addField('next_date', 'string');

        if ($this->eventCalendar instanceof PaginateTypeInterface) {
            $this->addPaginateSchema($schema);
        }

        $this->addCalendarSchema($schema);

        return $schema;
    }

    /**
     * @inheritDoc
     */
    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->requirePresence('calendar_type', true, __(Message::ERROR_NOT_EMPTY_SELECT))
            ->allowEmptyString('calendar_type', __(Message::ERROR_NOT_EMPTY_SELECT), false)
            ->add('calendar_type', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'inList' => [
                    'rule' => ['inList', array_keys($this->getFieldValueOptions('calendarType'))],
                    'last' => true,
                    'message' => __(Message::ERROR_IN_LIST),
                ],
            ]);

        $validator
            ->requirePresence('date', false)
            ->allowEmptyString('date')
            ->add('date', [
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

        $validator->requirePresence('label_id', false);
        if ($this->commonData()->existsAdminLoginData()) {
            // 管理側
            // 担当カテゴリがある管理者の場合は入力必須にする(マスター管理者を除く)
            $validator->allowEmptyString('label_id', __(Message::ERROR_INVALID_VALUE), function () {
                /** @var \App\Model\Entity\Admin $loginData */
                $loginData = $this->commonData()->getAdminLoginData();
                if ($loginData->isMasterAdmin()) {
                    return true;
                }

                return $this->commonData()->getAdminLoginLabel() === null;
            });
        } else {
            $validator->allowEmptyString('label_id');
        }

        /** @var \App\Model\Table\LabelsTable $labelTable */
        $labelTable = $this->getTableLocator()->get('Labels');
        $validator = $labelTable->addValidateLabelId($validator, 'label_id', $this->labelId);
        if ($this->commonData()->existsAdminLoginData()) {
            $validator = $labelTable->addValidateLabelIdAdminUsable($validator);
        }

        $tagGroupsValidator = new KuchenValidator();
        $validator
            ->requirePresence('tag_id', false)
            ->allowEmptyString('tag_id')
            ->add('tag_id', [
                'isArray' => [
                    'rule' => ['isArray'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'tagGroups' => [
                    'rule' => function ($value) {
                        $tagGroups = (array)$this->getFieldValueOptions('tagGroup');
                        foreach ((array)$value as $tagGroupId => $tagIds) {
                            $values = Hash::get($tagGroups, $tagGroupId);
                            if (is_array($tagIds) && is_array($values) && !empty($values['tag'])) {
                                foreach ($tagIds as $tagId) {
                                    if (
                                        !is_scalar($tagId)
                                        || !ArrayUtility::inArray($tagId, array_keys($values['tag']))
                                    ) {
                                        return false;
                                    }
                                }
                            }
                        }

                        return true;
                    },
                    'last' => true,
                    'message' => __(Message::ERROR_IN_LIST),
                ],
            ]);

        $validator
            ->requirePresence('next_date', false)
            ->allowEmptyString('next_date')
            ->add('next_date', [
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

        if ($this->eventCalendar instanceof PaginateTypeInterface) {
            $this->addPaginateValidation($validator, [
                'fieldValueOptions' => $this->getFieldValueOptions(),
            ]);
        }

        $this->addCalendarValidator($validator);

        return $validator;
    }

    /**
     * @inheritDoc
     */
    public function filterInputs(ArrayObject $inputs)
    {
        parent::filterInputs($inputs);

        $data = $this->filterTagId($inputs->getArrayCopy());
        $inputs->exchangeArray($data);
    }

    /**
     * @inheritDoc
     */
    protected function buildFieldValueOptions()
    {
        /** @var \App\Model\Table\TagGroupsTable $tagGroupsTable */
        $tagGroupsTable = $this->getTableLocator()->get('TagGroups');

        $fieldValueOptions = [
            'calendarType' => Configure::readOrFail('Master.event.calendarType'),
            'tagGroup' => $tagGroupsTable->getTagsList(!$this->isAdmin()),
        ];
        if ($this->eventCalendar instanceof PaginateTypeInterface) {
            $fieldValueOptions += $this->eventCalendar->paginateValueOptions();
        }

        return $fieldValueOptions;
    }

    /**
     * @inheritDoc
     */
    protected function buildDefaultFieldValues()
    {
        $defaultFieldValues = [
            'label_id' => $this->labelId,
        ];
        if ($this->eventCalendar instanceof PaginateTypeInterface) {
            $defaultFieldValues += $this->eventCalendar->paginateDefaultValues();
        }

        return $defaultFieldValues;
    }

    /**
     * 予約カレンダーを取得
     *
     * @return \App\Model\EventCalendar\AbstractCalendarType
     */
    public function getEventCalendar()
    {
        if (!isset($this->eventCalendar)) {
            throw new CakeException();
        }

        return $this->eventCalendar;
    }

    /**
     * 予約カレンダーのインスタンスを作成
     *
     * @param array|string|null $inputs 入力値
     * @return \App\Model\EventCalendar\AbstractCalendarType|null
     */
    public function createEventCalendarInstance($inputs = null)
    {
        /** @var \App\Model\Table\ReservationsTable $reservationsTable */
        $reservationsTable = $this->getTableLocator()->get('Reservations');

        $this->eventCalendar = $reservationsTable->getEventCalendar(
            Hash::get((array)$inputs, 'calendar_type'),
            $this->isAdmin()
        );

        $this->fieldValueOptions = null;
        $this->defaultFieldValues = null;

        return $this->eventCalendar;
    }

    /**
     * セッションへ保持するパラメータを取得
     *
     * @return array
     */
    public function getParameters()
    {
        $keys = [
            'edit_reservation_id',
            'select_usage_timestamp_from',
        ];
        $data = array_intersect_key($this->getData(), array_fill_keys($keys, true));

        return $data;
    }

    /**
     * ラベルIDを設定
     *
     * @param int|null $labelId ラベルID
     * @return void
     */
    public function setLabelId($labelId)
    {
        $this->labelId = $labelId;
    }

    /**
     * タグの入力値をフィルタリング
     *
     * @param array $inputs 入力値
     * @return array
     */
    protected function filterTagId($inputs)
    {
        if (!isset($inputs['tag_id']) || !is_array($inputs['tag_id'])) {
            return $inputs;
        }

        $validKeys = [];
        foreach ($this->getFieldValueOptions('tagGroup') as $tagGroupId => $valueOptions) {
            foreach (array_keys($valueOptions['tag']) as $tagId) {
                $validKeys[$tagGroupId][$tagId] = true;
            }
        }

        // 不正なキーの入力値を削除
        foreach ($inputs['tag_id'] as $tagGroupId => $tagIds) {
            if (is_array($tagIds)) {
                foreach (array_keys($tagIds) as $tagId) {
                    if (!isset($validKeys[$tagGroupId][$tagId])) {
                        unset($inputs['tag_id'][$tagGroupId][$tagId]);
                    }
                }
            }
        }

        return $inputs;
    }
}
