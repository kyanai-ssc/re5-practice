<?php
declare(strict_types=1);

namespace App\Form\Admin\Events;

use App\Form\AppForm;
use App\Locale\Message;
use App\Validation\CustomValidation;
use Cake\Core\Configure;
use Cake\Form\Schema;
use Cake\Utility\Hash;
use Cake\Validation\Validator;

/**
 * プラン検索
 */
class PlanSearchForm extends AppForm
{
    public const TEXT_MAX = 320;

    /**
     * @inheritDoc
     */
    protected function _buildSchema(Schema $schema): Schema
    {
        $schema
            ->addField('label_id', 'integer')
            ->addField('event_name', 'string')
            ->addField('event_id', 'integer');

        return $schema;
    }

    /**
     * @inheritDoc
     */
    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->requirePresence('label_id', false)
            ->allowEmptyString('label_id');

        /** @var \App\Model\Table\LabelsTable $labelTable */
        $labelTable = $this->getTableLocator()->get('Labels');
        $validator = $labelTable->addValidateLabelId($validator);

        $validator
            ->requirePresence('event_name', false)
            ->allowEmptyString('event_name')
            ->add('event_name', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'maxLength' => [
                    'rule' => ['maxLength', static::TEXT_MAX],
                    'last' => true,
                    'message' => __(Message::ERROR_MAX_LENGTH, static::TEXT_MAX),
                ],
            ]);

        $validator
            ->requirePresence('event_id', false)
            ->allowEmptyString('event_id')
            ->add('event_id', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'integer' => [
                    'rule' => ['integer', CustomValidation::BIGINT_MAX],
                    'last' => true,
                    'message' => __(Message::ERROR_NATURAL_NUMBER),
                ],
            ]);

        return $validator;
    }

    /**
     * @inheritDoc
     */
    protected function buildFieldValueOptions()
    {
        $fieldValueOptions = [
            'sort' => Hash::combine(['id', 'name', 'event_id'], '{*}'),
            'direction' => Configure::read('Setting.pagination.direction'),
            'limit' => $this->generatePaginateLimit(Configure::read('Setting.pagination.limit.config')),
        ];

        return $fieldValueOptions;
    }

    /**
     * @inheritDoc
     */
    protected function buildDefaultFieldValues()
    {
        $defaultFieldValues = [
            'label_id' => $this->commonData()->getAdminLoginLabel(),
            'direction' => 'asc',
            'limit' => Configure::read('Setting.pagination.limit.default'),
            'page' => '1',
        ];

        return $defaultFieldValues;
    }
}
