<?php
declare(strict_types=1);

namespace App\Command\Setting;

use App\Command\Command;
use App\Utility\ArrayUtility;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;
use Cake\Core\Configure;

/**
 * Class ContractPlanCommand
 */
class ContractPlanCommand extends Command
{
    /**
     * @inheritDoc
     */
    protected function buildOptionParser(ConsoleOptionParser $parser): ConsoleOptionParser
    {
        $parser = parent::buildOptionParser($parser);
        $plans = Configure::readOrFail('Master.systemSetting.contractPlan');

        $this->addClientOption($parser);
        $parser->addOption('contract-plan', [
            'name' => 'contract-plan',
            'choices' => ArrayUtility::arrayCast(array_keys($plans), 'str'),
        ]);

        return $parser;
    }

    /**
     * @inheritDoc
     */
    public function execute(Arguments $args, ConsoleIo $io)
    {
        $this->outputStartMessage();

        if (!$this->checkClientOption()) {
            $this->errorExit('ContractPlan Error: invalid client.');
        }

        /** @var \App\Model\Table\SystemSettingsTable $systemSettingsTable */
        $systemSettingsTable = $this->getTableLocator()->get('SystemSettings');

        $contractPlan = (int)$args->getOption('contract-plan');

        $systemSettingsTable->updateContractPlan((int)$contractPlan);

        $this->outputEndMessage();
    }
}
