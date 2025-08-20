<?php
declare(strict_types=1);

namespace App\Command\Migrations;

use App\Command\Migrations\Traits\ClientTrait;
use Migrations\Command\Phinx\Dump as BaseDump;

/**
 * Dump class.
 */
class Dump extends BaseDump
{
    use ClientTrait;

    /**
     * @inheritDoc
     */
    protected function configure(): void
    {
        parent::configure();

        $this->addClientOption();
    }
}
