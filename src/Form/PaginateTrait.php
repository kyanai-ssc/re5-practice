<?php
declare(strict_types=1);

namespace App\Form;

use App\Locale\Message;
use App\Validation\CustomValidation;
use Cake\Core\Configure;
use Cake\Utility\Hash;

/**
 * Paginaite trait.
 */
trait PaginateTrait
{
    /**
     * ページネーションのスキーマを追加
     *
     * @param \Cake\Form\Schema $schema スキーマ
     * @return void
     */
    protected function addPaginateSchema($schema)
    {
        $schema
            ->addField('sort', 'string')
            ->addField('direction', 'string')
            ->addField('limit', 'integer')
            ->addField('page', 'integer');
    }

    /**
     * ページネーションのバリデータを追加
     *
     * @param \Cake\Validation\Validator $validator バリデータ
     * @param array $options オプション
     * @return void
     */
    protected function addPaginateValidation($validator, $options = [])
    {
        $fieldValueOptions = Hash::get($options, 'fieldValueOptions');

        $validator
            ->requirePresence('sort', false)
            ->allowEmptyString('sort')
            ->add('sort', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'inList' => [
                    'rule' => ['inList', array_keys($fieldValueOptions['sort'])],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
            ]);

        $validator
            ->requirePresence('direction', false)
            ->allowEmptyString('direction')
            ->add('direction', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'inList' => [
                    'rule' => ['inList', array_keys($fieldValueOptions['direction'])],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
            ]);

        $validator
            ->requirePresence('limit', false)
            ->allowEmptyString('limit')
            ->add('limit', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'inList' => [
                    'rule' => ['inList', array_keys($fieldValueOptions['limit'])],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
            ]);

        $validator
            ->requirePresence('page', false)
            ->allowEmptyString('page')
            ->add('page', [
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
            ]);
    }

    /**
     * ページネーションの値リストを生成
     *
     * @return array 値リスト
     */
    protected function buildPaginateFieldValueOptions()
    {
        $fieldValueOptions = [
            'sort' => Hash::combine(['id'], '{*}'),
            'direction' => Configure::readOrFail('Setting.pagination.direction'),
            'limit' => $this->generatePaginateLimit(Configure::readOrFail('Setting.pagination.limit.config')),
        ];

        return $fieldValueOptions;
    }

    /**
     * ページネーションのデフォルト値を生成
     *
     * @return array デフォルト値
     */
    protected function buildPaginateDefaultFieldValues()
    {
        $defaultFieldValues = [
            'sort' => 'id',
            'direction' => 'desc',
            'limit' => Configure::readOrFail('Setting.pagination.limit.default'),
            'page' => '1',
        ];

        return $defaultFieldValues;
    }

    /**
     * ページネーションの件数リストを生成
     *
     * @param array $config 設定
     * @return array 件数リスト
     */
    protected function generatePaginateLimit($config)
    {
        $keys = $config['list'];
        $values = Hash::map($keys, '{*}', function ($key) use ($config) {
            return $key . Hash::get($config, 'unit');
        });

        return array_combine($keys, $values);
    }
}
