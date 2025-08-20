<?php
declare(strict_types=1);

namespace App\Validation;

use App\Locale\Message;

/**
 * MailValidation class.
 */
class MailValidation
{
    public const MAIL_MAX = 254;

    /**
     * メールアドレスの検証ルールを返却
     *
     * @return array
     */
    public static function getMailValidator()
    {
        $rules = [
            'isScalar' => [
                'rule' => ['isScalar'],
                'last' => true,
                'message' => __(Message::ERROR_INVALID_VALUE),
            ],
            'maxLength' => [
                'rule' => ['maxLength', static::MAIL_MAX],
                'last' => true,
                'message' => __(Message::ERROR_MAX_LENGTH, static::MAIL_MAX),
            ],
            'email' => [
                'rule' => ['email'],
                'last' => true,
                'message' => __(Message::ERROR_MAIL_ADDRESS),
            ],
        ];

        return $rules;
    }
}
