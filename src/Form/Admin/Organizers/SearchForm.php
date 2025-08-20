<?php
declare(strict_types=1);

namespace App\Form\Admin\Organizers;

use App\Form\AppForm;
use App\Locale\Message;
use App\Validation\CustomValidation;
use Cake\Core\Configure;
use Cake\Form\Schema;
use Cake\Utility\Hash;
use Cake\Validation\Validator;

/**
 * 主催者検索フォーム
 */
class SearchForm extends AppForm
{
    public const NAME_MAXLENGTH = 100;

    /**
     * @inheritDoc
     */
    protected function _buildSchema(Schema $schema): Schema
    {
        $schema
            ->addField('name', 'string')
            ->addField('id', 'string')
            ->addField('video_meeting_type', 'array');

        $this->addPaginateSchema($schema);

        return $schema;
    }

    /**
     * @inheritDoc
     */
    public function validationDefault(Validator $validator): Validator
    {
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
                    'rule' => ['maxLength', static::NAME_MAXLENGTH],
                    'last' => true,
                    'message' => __(Message::ERROR_MAX_LENGTH, static::NAME_MAXLENGTH),
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
            ->requirePresence('video_meeting_type', false)
            ->allowEmptyArray('video_meeting_type')
            ->add('video_meeting_type', [
                'isArray' => [
                    'rule' => ['isArray'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'multiple' => [
                    'rule' => ['multiple', [
                        'in' => array_keys($this->getFieldValueOptions('videoMeetingType')),
                    ]],
                    'last' => true,
                    'message' => __(Message::ERROR_IN_LIST),
                ],
            ]);

        $this->addPaginateValidation($validator, [
            'fieldValueOptions' => $this->getFieldValueOptions(),
        ]);

        return $validator;
    }

    /**
     * @inheritDoc
     */
    protected function buildFieldValueOptions()
    {
        $sort = [
            'id',
            'name',
            'video_meeting_type',
            'zoom_host_email',
        ];

        $fieldValueOptions = [
            'videoMeetingType' => Configure::read('Master.organizer.videoMeetingType'),
            'sort' => Hash::combine($sort, '{*}'),
        ] + $this->buildPaginateFieldValueOptions();

        return $fieldValueOptions;
    }

    /**
     * @inheritDoc
     */
    protected function buildDefaultFieldValues()
    {
        $defaultFieldValues = [
            'sort' => 'name',
            'direction' => 'asc',
            'limit' => Configure::readOrFail('Setting.pagination.limit.default'),
            'page' => '1',
        ] + $this->buildPaginateDefaultFieldValues();

        return $defaultFieldValues;
    }
}
