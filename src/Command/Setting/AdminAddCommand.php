<?php
declare(strict_types=1);

namespace App\Command\Setting;

use App\Command\Command;
use App\Model\Entity\Admin;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;
use Cake\Core\Configure;
use Cake\Utility\Hash;

/**
 * Class InitialAdminCommand
 */
class AdminAddCommand extends Command
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

        $parser->addOption('initial', [
            'name' => 'initial',
            'default' => false,
            'boolean' => true,
        ]);
        $parser->addOption('password-reset', [
            'name' => 'password-reset',
            'default' => false,
            'boolean' => true,
        ]);

        $parser->addOption('authority', [
            'name' => 'authority',
            'default' => Admin::AUTHORITY_MASTER,
            'choices' => array_map('strval', array_keys(Configure::read('Master.admin.authority'))),
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
            $this->errorExit('InitialAdmin Error: invalid client.');
        }

        /** @var \App\Model\Table\AdminsTable $adminsTable */
        $adminsTable = $this->getTableLocator()->get('Admins');

        $loginId = (string)$args->getOption('login-id');
        if (preg_match('/^[\\x21-\\x7e]{1,100}$/', $loginId) !== 1) {
            $this->errorExit('InitialAdmin Error: invlid login id.');
        }
        $hashedPassword = (string)$args->getOption('hashed-password');
        if ($hashedPassword !== '' && preg_match('/^[\\x21-\\x7e]{1,1000}$/', $hashedPassword) !== 1) {
            $this->errorExit('InitialAdmin Error: invlid hashed password.');
        }

        $authority = (int)$args->getOption('authority');
        $authorityList = Configure::read('Master.admin.authority');
        if (!isset($authorityList[$authority])) {
            $this->errorExit('AdminAdd Error: invlid authority.');
        }

        $initial = (bool)$args->getOption('initial');
        $passwordReset = (bool)$args->getOption('password-reset');
        $admin = $adminsTable->createAdmin($loginId, $hashedPassword, $authority, $initial, $passwordReset);
        if (!$adminsTable->save($admin)) {
            foreach (Hash::flatten($admin->getErrors()) as $error) {
                $this->outputErrorMessage('AdminAdd Error: ' . $error);
                $this->errorExit();
            }
        }
        if ($admin->has('raw_password')) {
            $io->quiet('--------------------------------');
            $io->quiet(
                Configure::read('Setting.auth.admin.authorityName.' . $authority) .
                'AdminAdd Password: ' . $admin->get('raw_password')
            );
            $io->quiet('--------------------------------');
        }

        $this->outputEndMessage();
    }
}
