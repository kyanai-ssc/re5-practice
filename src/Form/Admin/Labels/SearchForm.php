<?php
declare(strict_types=1);

namespace App\Form\Admin\Labels;

use App\Form\AppForm;
use App\Locale\Message;
use App\Model\Entity\Label;
use Cake\Core\Configure;
use Cake\Form\Schema;
use Cake\Utility\Hash;
use Cake\Validation\Validator;

/**
 * カテゴリー検索フォーム
 */
class SearchForm extends AppForm
{
    public const LABEL_NAME_MAX = 100;

    /**
     * @inheritDoc
     */
    public function initialize()
    {
        $this->setFieldValueOptions([
            'displayLevel' => Configure::read('Master.label.displayLevel'),
            'publicFlg' => Configure::readOrFail('Master.label.publicFlg'),
            'sort' => Hash::combine(['id', 'name', 'parent_id', 'sort_no', 'public_flg'], '{*}'),
            'direction' => Configure::read('Setting.pagination.direction'),
            'limit' => $this->generatePaginateLimit(Configure::read('Setting.pagination.limit.config')),
        ]);

        $this->setDefaultFieldValues([
            'label_id' => $this->commonData()->getAdminLoginLabel(),
            'display_level' => Label::DISPLAY_LEVEL_ALL,
            'sort' => 'sort_no',
            'direction' => 'asc',
            'limit' => Configure::read('Setting.pagination.limit.default'),
            'page' => '1',
        ]);
    }

    /**
     * @inheritDoc
     */
    protected function _buildSchema(Schema $schema): Schema
    {
        $schema
            ->addField('display_level', 'integer')
            ->addField('parent_id', 'integer')
            ->addField('label_id', 'integer')
            ->addField('name', 'string')
            ->addField('public_flg', 'integer')
            ->addField('sort', 'string')
            ->addField('direction', 'string')
            ->addField('limit', 'integer')
            ->addField('page', 'integer')
            ->addField('user_authority_id', 'integer');

        return $schema;
    }

    /**
     * @inheritDoc
     */
    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->requirePresence('display_level', false)
            ->allowEmptyString('display_level')
            ->add('display_level', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'inList' => [
                    'rule' => [
                        'inList',
                        array_keys($this->getFieldValueOptions('displayLevel')),
                    ],
                    'last' => true,
                    'message' => __(Message::ERROR_IN_LIST),
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
                    'rule' => ['maxLength', self::LABEL_NAME_MAX],
                    'last' => true,
                    'message' => __(Message::ERROR_MAX_LENGTH, static::LABEL_NAME_MAX),
                ],
            ]);

        $validator
            ->requirePresence('label_id', false, __(Message::ERROR_NOT_EMPTY_SELECT))
            ->allowEmptyString('label_id');

        /** @var \App\Model\Table\LabelsTable $labelTable */
        $labelTable = $this->getTableLocator()->get('Labels');
        $validator = $labelTable->addValidateLabelId($validator);

        $validator
            ->requirePresence('public_flg', false)
            ->allowEmptyString('public_flg')
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
            ->requirePresence('user_authority_id', false)
            ->allowEmptyArray('user_authority_id')
            ->add('user_authority_id', [
                'isArray' => [
                    'rule' => ['isArray'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'multiple' => [
                    'rule' => ['multiple', [
                        'in' => array_keys($this->getFieldValueOptions('userAuthorityId')),
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
     * フィールドの値リストへ追加
     *
     * @param array $add 追加する値
     * @return void
     */
    public function addFieldValueOptions(array $add)
    {
        $fieldValueOptions = $this->getFieldValueOptions();
        $fieldValueOptions += $add;

        $this->setFieldValueOptions($fieldValueOptions);
    }
}
