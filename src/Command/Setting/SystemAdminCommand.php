<?php
declare(strict_types=1);

namespace App\Command\Setting;

use App\Command\Command;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;
use Cake\Utility\Hash;

/**
 * Class SystemAdminCommand
 */
class SystemAdminCommand extends Command
{
    /**
     * @inheritDoc
     */
    protected function buildOptionParser(ConsoleOptionParser $parser): ConsoleOptionParser
    {
        $parser = parent::buildOptionParser($parser);

        $this->addClientOption($parser);
        $parser->addOption('login-id', [
            'name' => 'login-id',
        ]);
        $parser->addOption('hashed-password', [
            'name' => 'hashed-password',
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
            $this->errorExit('SystemAdmin Error: invalid client.');
        }

        /** @var \App\Model\Table\AdminsTable $adminsTable */
        $adminsTable = $this->getTableLocator()->get('Admins');

        $loginId = (string)$args->getOption('login-id');
        if ($loginId !== '' && preg_match('/^[\\x21-\\x7e]{1,100}$/', $loginId) !== 1) {
            $this->errorExit('SystemAdmin Error: invlid login id.');
        }
        $hashedPassword = (string)$args->getOption('hashed-password');
        if ($hashedPassword !== '' && preg_match('/^[\\x21-\\x7e]{1,1000}$/', $hashedPassword) !== 1) {
            $this->errorExit('SystemAdmin Error: invlid hashed password.');
        }

        $admin = $adminsTable->createSystemAdmin($loginId, $hashedPassword);
        if (!$adminsTable->save($admin)) {
            foreach (Hash::flatten($admin->getErrors()) as $error) {
                $this->outputErrorMessage('SystemAdmin Error: ' . $error);
                $this->errorExit();
            }
        }
        if ($admin->has('raw_password')) {
            $io->quiet('--------------------------------');
            $io->quiet('SystemAdmin Password: ' . $admin->get('raw_password'));
            $io->quiet('--------------------------------');
        }

        $this->outputEndMessage();
    }
}
