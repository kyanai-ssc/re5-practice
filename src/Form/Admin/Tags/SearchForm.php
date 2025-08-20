<?php
declare(strict_types=1);

namespace App\Form\Admin\Tags;

use App\Form\AppForm;
use App\Locale\Message;
use Cake\Core\Configure;
use Cake\Form\Schema;
use Cake\Utility\Hash;
use Cake\Validation\Validator;

/**
 * 絞り込みキーワード検索フォーム
 */
class SearchForm extends AppForm
{
    public const TAG_NAME_MAX = 100;

    /**
     * @inheritDoc
     */
    public function initialize()
    {
        $this->setFieldValueOptions([
            'publicFlg' => Configure::readOrFail('Master.tag.publicFlg'),
            'sort' => Hash::combine(['id', 'name', 'sort_no', 'public_flg'], '{*}'),
            'direction' => Configure::read('Setting.pagination.direction'),
            'limit' => $this->generatePaginateLimit(Configure::read('Setting.pagination.limit.config')),
        ]);

        $this->setDefaultFieldValues([
            'sort' => 'public_flg',
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
            ->addField('name', 'string')
            ->addField('public_flg', 'integer')
            ->addField('sort', 'string')
            ->addField('direction', 'string')
            ->addField('limit', 'integer')
            ->addField('page', 'integer');

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
                    'rule' => ['maxLength', self::TAG_NAME_MAX],
                    'last' => true,
                    'message' => __(Message::ERROR_MAX_LENGTH, static::TAG_NAME_MAX),
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

        $this->addPaginateValidation($validator, [
            'fieldValueOptions' => $this->getFieldValueOptions(),
        ]);

        return $validator;
    }
}
