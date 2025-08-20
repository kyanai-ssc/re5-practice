<?php
declare(strict_types=1);

namespace App\Command\Migrations;

use App\Command\Migrations\Traits\ClientTrait;
use Migrations\Command\Phinx\CacheBuild as BaseCacheBuild;

/**
 * CacheBuild class.
 */
class CacheBuild extends BaseCacheBuild
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
