<?php
declare(strict_types=1);

namespace App\Command\Migrations;

use App\Command\Migrations\Traits\ClientTrait;
use Migrations\Command\Phinx\MarkMigrated as BaseMarkMigrated;

/**
 * MarkMigrated class.
 */
class MarkMigrated extends BaseMarkMigrated
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
