<?php
declare(strict_types=1);

namespace App\Form\Common\Labels;

use App\Validation\CustomValidation;
use Cake\Form\Schema;
use Cake\Validation\Validator;

/**
 * ラベルフォームtrait
 */
trait LabelFormTrait
{
    /**
     * スキーマ生成
     *
     * @param \Cake\Form\Schema $schema Schema
     * @return \Cake\Form\Schema
     */
    protected function buildLabelSchema(Schema $schema)
    {
        $schema
            ->addField('id', 'integer')
            ->addField('parent_id', 'integer');

        return $schema;
    }

    /**
     * バリデータ生成
     *
     * @param \Cake\Validation\Validator $validator Validator
     * @return \Cake\Validation\Validator
     */
    public function buildLabelValidator(Validator $validator)
    {
        $validator
            ->requirePresence('id', true)
            ->allowEmptyString('id')
            ->add('id', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                ],
                'integer' => [
                    'rule' => ['integer', CustomValidation::BIGINT_MAX],
                    'last' => true,
                ],
            ]);

        $validator
            ->requirePresence('parent_id', false)
            ->allowEmptyString('parent_id')
            ->add('parent_id', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                ],
                'integer' => [
                    'rule' => ['integer', CustomValidation::BIGINT_MAX],
                    'last' => true,
                ],
            ]);

        $validator
            ->requirePresence('exclude_id', false)
            ->allowEmptyString('exclude_id')
            ->add('exclude_id', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                ],
                'integer' => [
                    'rule' => ['integer', CustomValidation::BIGINT_MAX],
                    'last' => true,
                ],
            ]);

        return $validator;
    }
}
