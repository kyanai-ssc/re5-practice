<?php
declare(strict_types=1);

namespace App\Command\Migrations;

use App\Command\Migrations\Traits\ClientTrait;
use Migrations\Command\Phinx\Rollback as BaseRollback;

/**
 * Rollback class.
 */
class Rollback extends BaseRollback
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
