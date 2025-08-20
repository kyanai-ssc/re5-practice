<?php

declare(strict_types=1);

namespace Kuchen\Validation\Validation;

use Cake\Utility\Hash;
use Cake\Validation\RulesProvider;
use Cake\Validation\ValidationSet;
use Cake\Validation\Validator as CakeValidator;

/**
 * Class Validator
 */
class Validator extends CakeValidator
{
    /**
     * エラーのあるフィールド
     *
     * @var mixed[]
     */
    public $errors = [];

    /**
     * バリデーションが成功したデータ
     *
     * @var mixed[]
     */
    public $data = [];

    /**
     * {@inheritdoc}
     *
     * @param mixed[] $data
     * @return mixed[]
     */
    protected function _processRules(string $field, ValidationSet $rules, array $data, $newRecord): array // phpcs:ignore
    {
        // 他のフィールドのバリデーション結果を保持する
        $errors = parent::_processRules($field, $rules, $data, $newRecord);

        if (empty($errors)) {
            $this->data[$field] = $data[$field];
        } else {
            // notBlank, notEmpty, required エラーは全フィールド検証終了後 self::errors() で保持する
            $this->errors[$field] = $errors;
        }

        return $errors;
    }

    /**
     * {@inheritDoc}
     *
     * @param mixed[] $data
     * @return mixed[]
     */
    public function validate(array $data, $newRecord = true): array
    {
        $this->data = [];
        $this->errors = [];

        $errors = parent::validate($data, $newRecord);
        $this->errors = $errors;

        return $errors;
    }

    /**
     * エラーがあるか
     *
     * @param string $field フィールド名
     * @return bool
     */
    public function hasError(string $field): bool
    {
        return Hash::check($this->errors, $field);
    }

    /**
     * データが検証済みか
     *
     * @param string $field フィールド名
     * @return bool
     */
    public function isValid(string $field): bool
    {
        return !$this->hasError($field) && Hash::check($this->data, $field);
    }

    /**
     * @inheritDoc
     */
    public function getProvider($name)
    {
        if (isset($this->_providers[$name])) {
            return $this->_providers[$name];
        }
        if ($name !== 'default') {
            return null;
        }

        $this->_providers[$name] = new RulesProvider(Validation::class);

        return $this->_providers[$name];
    }
}
