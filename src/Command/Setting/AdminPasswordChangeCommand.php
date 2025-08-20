<?php
declare(strict_types=1);

namespace App\Command\Setting;

use App\Command\Command;
use App\Model\Entity\Admin;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;

/**
 * Class AdminPasswordChangeCommand
 */
class AdminPasswordChangeCommand extends Command
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

        return $parser;
    }

    /**
     * @inheritDoc
     */
    public function execute(Arguments $args, ConsoleIo $io)
    {
        $this->outputStartMessage();

        if (!$this->checkClientOption()) {
            $this->errorExit('AdminPasswordChange Error: invalid client.');
        }

        $loginId = (string)$args->getOption('login-id');
        if (preg_match('/^[\\x21-\\x7e]{1,100}$/', $loginId) !== 1) {
            $this->errorExit('AdminPasswordChange Error: invlid login id.');
        }

        $password = (string)$io->ask('password:');
        if ($password !== '' && preg_match('/^[\\x21-\\x7e]{1,32}$/', $password) !== 1) {
            $this->errorExit('AdminPasswordChange Error: invlid password.');
        }

        /** @var \App\Model\Table\AdminsTable $adminsTable */
        $adminsTable = $this->getTableLocator()->get('Admins');

        $generated = null;
        if ($password === '') {
            $password = $adminsTable->generateInitialPassword();
            $generated = $password;
        }

        if (!$this->changePassword($loginId, $password)) {
            $this->errorExit('AdminPasswordChange Error: failed to change password.');
        }

        if (isset($generated)) {
            $io->quiet('--------------------------------');
            $io->quiet('AdminPasswordChange Password: ' . $generated);
            $io->quiet('--------------------------------');
        }

        $this->outputEndMessage();
    }

    /**
     * パスワードを変更
     *
     * @param string $loginId ログインID
     * @param string $password パスワード
     * @return bool
     */
    protected function changePassword($loginId, $password)
    {
        /** @var \App\Model\Table\AdminsTable $adminsTable */
        $adminsTable = $this->getTableLocator()->get('Admins');

        $admin = $adminsTable->find('passwordChange', [
            'inputs' => [
                'login_id' => $loginId,
            ],
        ])->first();
        if (!($admin instanceof Admin)) {
            return false;
        }

        if (!$adminsTable->resetPassword($admin->get('id'), (string)$admin->passwordHash($password))) {
            return false;
        }

        return true;
    }
}
