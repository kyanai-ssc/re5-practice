<?php
declare(strict_types=1);

namespace App\Command\Setting;

use App\Command\Command;
use App\Exception\SmartLockException;
use App\Model\Entity\SystemSetting;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;

/**
 * Class DeleteSmartLockSettingCommand
 */
class DeleteSmartLockSettingCommand extends Command
{
    /**
     * @inheritDoc
     */
    protected function buildOptionParser(ConsoleOptionParser $parser): ConsoleOptionParser
    {
        $parser = parent::buildOptionParser($parser);

        $this->addClientOption($parser);

        return $parser;
    }

    /**
     * @inheritDoc
     */
    public function execute(Arguments $args, ConsoleIo $io): void
    {
        $this->outputStartMessage();

        if (!$this->checkClientOption()) {
            $this->errorExit('DeleteSmartLockSetting Error: invalid client.');
        }

        /** @var \App\Model\Table\SmartLocksTable $smartLocksTable */
        $smartLocksTable = $this->getTableLocator()->get('SmartLocks');
        /** @var \App\Model\Table\SystemSettingsTable $systemSettingsTable */
        $systemSettingsTable = $this->getTableLocator()->get('SystemSettings');

        $smartLocksTable->getConnection()->transactional(function () use ($smartLocksTable, $systemSettingsTable) {
            $smartLocksTable->deleteAll([]);
            $smartLocksTable->initializeSequence();

            $systemSetting = $systemSettingsTable->getData();
            $systemSetting->set('smart_lock_use_flg', SystemSetting::SMART_LOCK_USE_FLG_OFF);

            if (!$systemSettingsTable->save($systemSetting)) {
                throw new SmartLockException('system_setting save error.');
            }

            return true;
        });
        $smartLocksTable->deleteCacheData();
        $systemSettingsTable->deleteCacheData();

        $this->outputEndMessage();
    }
}
