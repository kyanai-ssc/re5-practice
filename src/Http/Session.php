<?php
declare(strict_types=1);

namespace App\Http;

use Cake\Http\Session as BaseSession;

/**
 * Session class.
 */
class Session extends BaseSession
{
    /**
     * @var bool
     */
    protected $checkedInvalidSession = false;

    /**
     * @inheritDoc
     */
    public function start(): bool
    {
        $result = parent::start();
        if (!$result) {
            return $result;
        }

        if (!$this->checkedInvalidSession) {
            $this->checkedInvalidSession = true;
            $engine = $this->engine();
            if (isset($engine) && $engine->read($this->id()) === '') {
                $this->renew();
            }
        }

        return $result;
    }
}
