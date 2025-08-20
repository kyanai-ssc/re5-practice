<?php
declare(strict_types=1);

namespace App\Validation\InputCheck;

use App\Locale\Message;
use App\Validation\AbstractInputCheck;
use App\Validation\CustomValidation;

/**
 * PhoneNumberWithHyphen class.
 */
class PhoneNumberWithHyphen extends AbstractInputCheck
{
    /**
     * @var string|null
     */
    protected $error = null;

    /**
     * @inheritDoc
     */
    public function validate($value)
    {
        if (preg_match('/-/', $value) !== 1) {
            $this->error = __(Message::ERROR_PHONE_NUMBER_HYPHEN);

            return false;
        }
        if (!CustomValidation::phoneNumberWithHyphen($value)) {
            $this->error = __(Message::ERROR_INVALID_PHONE_NUMBER);

            return false;
        }

        return true;
    }

    /**
     * @inheritDoc
     */
    public function getError()
    {
        if (isset($this->error)) {
            return $this->error;
        }

        return __(Message::ERROR_INVALID_PHONE_NUMBER);
    }
}
