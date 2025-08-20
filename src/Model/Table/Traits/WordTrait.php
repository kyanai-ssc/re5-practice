<?php
declare(strict_types=1);

namespace App\Model\Table\Traits;

use App\Locale\Message;
use Cake\Validation\Validator;

/**
 * Inputs trait.
 */
trait WordTrait
{
    /**
     * バリデーター
     *
     * @param \Cake\Validation\Validator $validator validator
     * @param string $key バリデートカラム
     * @param int $max maxLength
     * @return \Cake\Validation\Validator
     */
    public function getWordValidator(Validator $validator, string $key = 'word', $max = 100)
    {
        $validator
            ->requirePresence($key, true, __(Message::ERROR_NOT_EMPTY))
            ->allowEmptyString($key, __(Message::ERROR_NOT_EMPTY), false)
            ->add($key, [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'maxLength' => [
                    'rule' => ['maxLength', static::WORD_MAX],
                    'last' => true,
                    'message' => __(Message::ERROR_MAX_LENGTH, static::WORD_MAX),
                ],
            ]);

        return $validator;
    }
}
