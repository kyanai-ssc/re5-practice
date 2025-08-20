<?php
declare(strict_types=1);

namespace App\Command\Setting;

use App\Command\Command;
use App\Model\Entity\Admin;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;

/**
 * Class AdminPasswordResetCommand
 */
class AdminPasswordResetCommand extends Command
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
            $this->errorExit('AdminPasswordReset Error: invalid client.');
        }

        $loginId = (string)$args->getOption('login-id');
        if (preg_match('/^[\\x21-\\x7e]{1,100}$/', $loginId) !== 1) {
            $this->errorExit('AdminPasswordReset Error: invlid login id.');
        }

        if (!$this->resetPassword($loginId)) {
            $this->errorExit('AdminPasswordReset Error: failed to reset password.');
        }

        $this->outputEndMessage();
    }

    /**
     * パスワードを初期化
     *
     * @param string $loginId ログインID
     * @return bool
     */
    protected function resetPassword($loginId)
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
        if ((string)$admin->get('initial_password') === '') {
            return false;
        }

        if (!$adminsTable->resetPassword($admin->get('id'), $admin->get('initial_password'))) {
            return false;
        }

        return true;
    }
}
