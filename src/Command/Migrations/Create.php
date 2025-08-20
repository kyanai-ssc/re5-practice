<?php
declare(strict_types=1);

namespace App\Command\Migrations;

use App\Command\Migrations\Traits\ClientTrait;
use Migrations\Command\Phinx\Create as BaseCreate;

/**
 * Create class.
 */
class Create extends BaseCreate
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
