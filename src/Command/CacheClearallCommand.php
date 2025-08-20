<?php
declare(strict_types=1);

namespace App\Command;

use App\Command\Traits\ClientTrait;
use Cake\Command\CacheClearallCommand as CakeCacheClearallCommand;
use Cake\Console\ConsoleOptionParser;

/**
 * CacheClearall command.
 */
class CacheClearallCommand extends CakeCacheClearallCommand
{
    use ClientTrait;

    /**
     * @inheritDoc
     */
    public function buildOptionParser(ConsoleOptionParser $parser): ConsoleOptionParser
    {
        $parser = parent::buildOptionParser($parser);

        $this->addClientOption($parser);

        return $parser;
    }
}
