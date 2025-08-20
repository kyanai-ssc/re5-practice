<?php
declare(strict_types=1);

namespace App\Command\Setting;

use App\Command\Command;
use App\Model\Entity\SmartLock;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;
use Cake\Core\Exception\CakeException;

/**
 * Class BufferSmartLockSettingCommand
 */
class BufferSmartLockSettingCommand extends Command
{
    /**
     * @inheritDoc
     */
    protected function buildOptionParser(ConsoleOptionParser $parser): ConsoleOptionParser
    {
        $parser = parent::buildOptionParser($parser);

        $this->addClientOption($parser);

        $parser->addOption('buffer', [
            'name' => 'buffer',
        ]);

        return $parser;
    }

    /**
     * @inheritDoc
     */
    public function execute(Arguments $args, ConsoleIo $io): void
    {
        $this->outputStartMessage();

        $this->validateOption($args);

        /** @var \App\Model\Table\SmartLocksTable $smartLocksTable */
        $smartLocksTable = $this->getTableLocator()->get('SmartLocks');
        $smartLock = $smartLocksTable->getData();
        $smartLock->set('buffer', $args->getOption('buffer'));

        // 予期しない原因で保存に失敗した場合、例外をスロー
        if (!$smartLocksTable->save($smartLock)) {
            throw new CakeException('smart_locks save error.');
        }

        $this->outputEndMessage();
    }

    /**
     * オプションのバリデーションを行う
     *
     * @param \Cake\Console\Arguments $args execute() で引数となている $args
     * @return void
     */
    protected function validateOption(Arguments $args): void
    {
        if (!$this->checkClientOption()) {
            $this->errorExit('BufferSmartLockSetting Error: invalid client.');
        }

        if (
            !is_string($args->getOption('buffer'))
            || !preg_match('/^\d+$/', $args->getOption('buffer'))
            || intval($args->getOption('buffer')) > SmartLock::BUFFER_MAX
        ) {
            $this->errorExit('BufferSmartLockSetting Error: invalid buffer.');
        }
    }
}
