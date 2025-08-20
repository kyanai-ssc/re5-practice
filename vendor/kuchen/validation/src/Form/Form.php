<?php

declare(strict_types=1);

namespace Kuchen\Validation\Form;

use Cake\Event\EventManager;
use Cake\Form\Form as CakeForm;
use Cake\Utility\Hash;
use Kuchen\Validation\Validation\Validator;

/**
 * Cake Form 拡張
 */
class Form extends CakeForm
{
    /**
     * @inheritDoc
     */
    public function __construct(?EventManager $eventManager = null)
    {
        parent::__construct($eventManager);

        $this->_validatorClass = Validator::class;
    }

    /**
     * バリデーション前にスキーマで設定されたフィールドタイプのみデータとして使用します
     *
     * {@inheritDoc}
     *
     * @param mixed[] $data
     */
    public function validate(array $data, ?string $validator = null): bool
    {
        $result = [];
        foreach ($this->getSchema()->fields() as $field) {
            $value = Hash::get($data, $field);
            if ($value === null) { // データに存在するキーのみ判定
                continue;
            }

            $result[$field] = $value;
        }

        $this->_data = Hash::expand($result);

        return parent::validate($this->_data, $validator);
    }

    /**
     * 検証済みデータを取得
     *
     * @param string|null $field 指定時は指定したパスを参照
     * @return mixed
     */
    public function getData(?string $field = null)
    {
        return $field !== null ? Hash::get($this->_data, $field) : $this->_data;
    }
}
