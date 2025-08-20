<?php
declare(strict_types=1);

namespace App\Command\Setting;

use App\Command\Command;
use App\Command\Traits\CommandTrait;
use App\Model\Entity\Admin;
use App\Model\Entity\SystemSetting;
use App\Utility\FileUtility;
use App\Utility\StringUtility;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;
use Cake\Core\Configure;
use Cake\Core\Exception\CakeException;
use Cake\Database\Connection;
use Cake\Database\Exception\MissingConnectionException;
use Cake\Datasource\ConnectionManager;

/**
 * Class NewClientCommand
 */
class NewClientCommand extends Command
{
    use CommandTrait;

    public const CHANGE_PERMISSION = [
        'css',
        'files',
        'logs',
        'tmp',
    ];

    /**
     * @inheritDoc
     */
    protected function buildOptionParser(ConsoleOptionParser $parser): ConsoleOptionParser
    {
        $parser = parent::buildOptionParser($parser);

        $parser->addOption('name', [
            'name' => 'name',
        ]);
        $parser->addOption('domain', [
            'name' => 'domain',
        ]);
        $parser->addOption('database', [
            'name' => 'database',
        ]);
        $parser->addOption('plan', [
            'name' => 'plan',
        ]);
        $parser->addOption('admin', [
            'name' => 'admin',
        ]);
        $parser->addOption('site', [
            'name' => 'site',
        ]);
        $parser->addOption('system-admin-password', [
            'name' => 'system-admin-password',
        ]);
        $parser->addOption('admin-password', [
            'name' => 'admin-password',
        ]);
        $parser->addOption('operator-password', [
            'name' => 'operator-password',
        ]);
        $parser->addOption('password-reset', [
            'name' => 'password-reset',
            'default' => false,
            'boolean' => true,
        ]);
        $parser->addOption('database-password', [
            'name' => 'database-password',
        ]);
        $parser->addOption('create-api-secret', [
            'name' => 'create-api-secret',
            'default' => false,
            'boolean' => true,
        ]);

        return $parser;
    }

    /**
     * @inheritDoc
     */
    public function execute(Arguments $args, ConsoleIo $io)
    {
        $this->outputStartMessage();

        $name = (string)$args->getOption('name');
        if (preg_match('/^[\\-\\.0-9a-z_]{1,255}$/', $name) !== 1) {
            $this->errorExit('NewClient Error: invlid name.');
        }
        $domain = (string)$args->getOption('domain');
        if (preg_match('/^[\\-\\.0-9a-z]{1,255}$/', $domain) !== 1) {
            $this->errorExit('NewClient Error: invlid domain.');
        }
        if (!$this->checkFile($name, $domain)) {
            $this->errorExit('NewClient Error: already exists.');
        }

        $database = (string)$args->getOption('database');
        if (preg_match('/^[\\x21-\\x7e]{1,100}$/', $database) !== 1) {
            $this->errorExit('NewClient Error: invlid database.');
        }

        $site = (string)$args->getOption('site');
        if (preg_match('/^\\s*$/u', $site) === 1 || mb_strlen($site) > 1000) {
            $this->errorExit('NewClient Error: invlid site.');
        }

        $plan = (string)$args->getOption('plan');
        $admin = (string)$args->getOption('admin');
        $systemAdminPassword = (string)$args->getOption('system-admin-password');
        $adminPassword = (string)$args->getOption('admin-password');
        $operatorPassword = (string)$args->getOption('operator-password');
        $passwordReset = (bool)$args->getOption('password-reset');
        $createApiSecret = (bool)$args->getOption('create-api-secret');

        $databaseUser = $database;
        $databasePassword = (string)$args->getOption('database-password');
        if ($databasePassword === '') {
            $databasePassword = (string)$io->ask('database password:');
        }
        if ($databasePassword !== '' && preg_match('/^[\\x21-\\x7e]{1,100}$/', $databasePassword) !== 1) {
            $this->errorExit('NewClient Error: invlid database password.');
        }
        try {
            $this->connectDatabase($databaseUser, $databasePassword, $database);
        } catch (MissingConnectionException $e) {
            $this->errorExit('CreateClient Error: ' . $e->getMessage());
        }

        $this->outputMessage('NewClient copy client file.');
        $this->copyClientFile($name, $domain, $databaseUser, $databasePassword, $database);

        $this->outputMessage('NewClient create table.');
        $this->createTable($name);

        $this->outputMessage('NewClient insert data.');
        $this->insertData($name);

        $this->outputMessage('NewClient create Api Secret.');
        $createApiSecretResult = $this->createApiSecret($name, $createApiSecret);
        if (!empty($createApiSecretResult)) {
            $io->quiet($createApiSecretResult);
        }

        $this->outputMessage('NewClient update contract plan.');
        $this->updateContractPlan($name, $plan);

        $this->outputMessage('NewClient default mail setting.');
        $this->defaultMailSetting($name);

        $this->outputMessage('NewClient create system admin.');
        $systemAdminResult = $this->createSystemAdmin($name, $systemAdminPassword);
        if (!empty($systemAdminResult)) {
            $io->quiet($systemAdminResult);
        }

        $this->outputMessage('NewClient create initial admin.');
        $initialAdminResult = $this->createInitialAdmin($name, $admin, $adminPassword, $passwordReset);
        if (!empty($initialAdminResult)) {
            $io->quiet($initialAdminResult);
        }

        if (
            $plan === (string)SystemSetting::CONTRACT_PLAN_BASIC
            || $plan === (string)SystemSetting::CONTRACT_PLAN_CUSTOMIZE
            || $plan === (string)SystemSetting::CONTRACT_PLAN_CUSTOMIZE_BASIC
        ) {
            $this->outputMessage('NewClient create operator admin.');
            $operatorAdminResult = $this->createOperatorAdmin(
                $name,
                $admin . Configure::readOrFail('Setting.auth.admin.initialOperatorSuffix'),
                $operatorPassword,
                $passwordReset
            );
            if (!empty($operatorAdminResult)) {
                $io->quiet($operatorAdminResult);
            }
        }

        $this->outputMessage('NewClient update word.');
        $this->updateWord($site);

        $this->outputMessage('NewClient clear cache.');
        $this->clearCache($name);

        $this->outputMessage('NewClient analyze table.');
        $this->analyzeTable($name);

        $this->outputEndMessage();
    }

    /**
     * クライアント用ファイル存在チェック
     *
     * @param string $client クライアント名
     * @param string $domain ドメイン名
     * @return bool
     */
    protected function checkFile($client, $domain)
    {
        if (
            file_exists(ROOT . DS . 'clients' . DS . $client)
            || file_exists(ROOT . DS . 'hosts' . DS . $domain . '.php')
        ) {
            return false;
        }

        return true;
    }

    /**
     * DB接続
     *
     * @param string $databaseUser DBユーザ
     * @param string $databasePassword DBパスワード
     * @param string $databaseName DB名
     * @return void
     */
    protected function connectDatabase($databaseUser, $databasePassword, $databaseName)
    {
        ConnectionManager::setConfig('client', array_merge((array)ConnectionManager::getConfig('default'), [
            'username' => $databaseUser,
            'password' => $databasePassword,
            'database' => $databaseName,
        ]));

        $connection = ConnectionManager::get('client');
        if (!($connection instanceof Connection)) {
            throw new CakeException();
        }
        if (!$connection->getDriver()->isConnected()) {
            $connection->getDriver()->connect();
        }
    }

    /**
     * クライアント用ファイルコピー
     *
     * @param string $client クライアント名
     * @param string $domain ドメイン名
     * @param string $databaseUser DBユーザ
     * @param string $databasePassword DBパスワード
     * @param string $databaseName DB名
     * @return void
     */
    protected function copyClientFile($client, $domain, $databaseUser, $databasePassword, $databaseName)
    {
        FileUtility::copyDirectory(CONFIG . 'files' . DS . 'client', ROOT . DS . 'clients' . DS . $client);
        foreach (static::CHANGE_PERMISSION as $directory) {
            FileUtility::changePermissionRecursive(ROOT . DS . 'clients' . DS . $client . DS . $directory, 0777, 0666);
        }

        $envFile = ROOT . DS . 'clients' . DS . $client . DS . 'config' . DS . 'env.php';
        $envConfig = file_get_contents($envFile);
        if ($envConfig === false) {
            throw new CakeException();
        }
        $envConfig = preg_replace(
            [
                '/%DATABASE_USER%/',
                '/%DATABASE_PASSWORD%/',
                '/%DATABASE_NAME%/',
                '/%HOST%/',
            ],
            [
                StringUtility::pregReplaceQuote($databaseUser),
                StringUtility::pregReplaceQuote($databasePassword),
                StringUtility::pregReplaceQuote($databaseName),
                $domain,
            ],
            $envConfig
        );
        if (!file_put_contents($envFile, $envConfig, LOCK_EX)) {
            throw new CakeException();
        }

        $hostFile = ROOT . DS . 'hosts' . DS . $domain . '.php';
        if (!copy(CONFIG . 'files' . DS . 'host' . DS . 'host.php', $hostFile)) {
            throw new CakeException();
        }
        $hostConfig = file_get_contents($hostFile);
        if ($hostConfig === false) {
            throw new CakeException();
        }
        $hostConfig = preg_replace('/%CLIENT%/', $client, $hostConfig);
        if (!file_put_contents($hostFile, $hostConfig, LOCK_EX)) {
            throw new CakeException();
        }

        FileUtility::deleteByName(ROOT . DS . 'clients' . DS . $client, '/^\\.gitkeep$/');
    }

    /**
     * テーブル作成
     *
     * @param string $client クライアント名
     * @return void
     */
    protected function createTable($client)
    {
        $result = $this->execCommand(Configure::readOrFail('Setting.batch.shell'), [
            'migrations',
            'migrate',
            '--quiet',
            '--no-lock',
            '--client=' . $client,
        ], false);
        if (isset($result['status']) && $result['status'] !== 0) {
            if (!empty($result['output'])) {
                $this->outputErrorMessage($result['output']);
            }
            throw new CakeException();
        }
    }

    /**
     * 初期データ登録
     *
     * @param string $client クライアント名
     * @return void
     */
    protected function insertData($client)
    {
        $result = $this->execCommand(Configure::readOrFail('Setting.batch.shell'), [
            'migrations',
            'seed',
            '--quiet',
            '--client=' . $client,
        ], false);
        if (isset($result['status']) && $result['status'] !== 0) {
            if (!empty($result['output'])) {
                $this->outputErrorMessage($result['output']);
            }
            throw new CakeException();
        }
    }

    /**
     * 契約プラン更新
     *
     * @param string $client クライアント名
     * @param int|string $contractPlan 契約プラン
     * @return void
     */
    protected function updateContractPlan($client, $contractPlan)
    {
        $result = $this->execCommand(Configure::readOrFail('Setting.batch.shell'), [
            'setting',
            'contract-plan',
            '--quiet',
            '--client=' . $client,
            '--contract-plan=' . $contractPlan,
        ], false);
        if (isset($result['status']) && $result['status'] !== 0) {
            if (!empty($result['output'])) {
                $this->outputErrorMessage($result['output']);
            }
            throw new CakeException();
        }
    }

    /**
     * 自動返信メールの初期設定
     *
     * @param string $client クライアント名
     * @return void
     */
    protected function defaultMailSetting($client)
    {
        $result = $this->execCommand(Configure::readOrFail('Setting.batch.shell'), [
            'setting',
            'default-mail-setting',
            '--quiet',
            '--client=' . $client,
        ], false);
        if (isset($result['status']) && $result['status'] !== 0) {
            if (!empty($result['output'])) {
                $this->outputErrorMessage($result['output']);
            }
            throw new CakeException();
        }
    }

    /**
     * システム管理者登録
     *
     * @param string $client クライアント名
     * @param string|null $hashedPassword ハッシュ化パスワード
     * @return array|null
     */
    protected function createSystemAdmin($client, $hashedPassword = null)
    {
        $result = $this->execCommand(Configure::readOrFail('Setting.batch.shell'), [
            'setting',
            'system-admin',
            '--quiet',
            '--client=' . $client,
            '--hashed-password=' . $hashedPassword,
        ], false);
        if (isset($result['status']) && $result['status'] !== 0) {
            if (!empty($result['output'])) {
                $this->outputErrorMessage($result['output']);
            }
            throw new CakeException();
        }

        $output = null;
        if (!empty($result['output'])) {
            $output = $result['output'];
        }

        return $output;
    }

    /**
     * 初期管理者登録
     *
     * @param string $client クライアント名
     * @param string $loginId ログインID
     * @param string|null $hashedPassword ハッシュ化パスワード
     * @param bool $passwordReset パスワード初期化フラグ
     * @return array|null
     */
    protected function createInitialAdmin($client, $loginId, $hashedPassword = null, $passwordReset = false)
    {
        $passwordResetOption = [];
        if ($passwordReset) {
            $passwordResetOption[] = '--password-reset';
        }

        $result = $this->execCommand(Configure::readOrFail('Setting.batch.shell'), array_merge([
            'setting',
            'admin-add',
            '--quiet',
            '--client=' . $client,
            '--login-id=' . $loginId,
            '--hashed-password=' . $hashedPassword,
            '--initial',
        ], $passwordResetOption), false);
        if (isset($result['status']) && $result['status'] !== 0) {
            if (!empty($result['output'])) {
                $this->outputErrorMessage($result['output']);
            }
            throw new CakeException();
        }

        $output = null;
        if (!empty($result['output'])) {
            $output = $result['output'];
        }

        return $output;
    }

    /**
     * オペレーター管理者登録
     *
     * @param string $client クライアント名
     * @param string $loginId ログインID
     * @param string|null $hashedPassword ハッシュ化パスワード
     * @param bool $passwordReset パスワード初期化フラグ
     * @return array|null
     */
    protected function createOperatorAdmin($client, $loginId, $hashedPassword = null, $passwordReset = false)
    {
        $passwordResetOption = [];
        if ($passwordReset) {
            $passwordResetOption[] = '--password-reset';
        }

        $result = $this->execCommand(Configure::readOrFail('Setting.batch.shell'), array_merge([
            'setting',
            'admin-add',
            '--quiet',
            '--client=' . $client,
            '--authority=' . Admin::AUTHORITY_OPERATOR,
            '--login-id=' . $loginId,
            '--hashed-password=' . $hashedPassword,
        ], $passwordResetOption), false);
        if (isset($result['status']) && $result['status'] !== 0) {
            if (!empty($result['output'])) {
                $this->outputErrorMessage($result['output']);
            }
            throw new CakeException();
        }

        $output = null;
        if (!empty($result['output'])) {
            $output = $result['output'];
        }

        return $output;
    }

    /**
     * 文言更新
     *
     * @param string $site サイト名
     * @return void
     */
    protected function updateWord($site)
    {
        /** @var \App\Model\Table\WordsTable $wordsTable */
        $wordsTable = $this->getTableLocator()->get('Words', [
            'connectionName' => 'client',
        ]);

        $wordsTable->updateSiteName($site);
    }

    /**
     * キャッシュクリア
     *
     * @param string $client クライアント名
     * @return void
     */
    protected function clearCache($client)
    {
        $result = $this->execCommand(Configure::readOrFail('Setting.batch.shell'), [
            'cache',
            'clear_all',
            '--quiet',
            '--client=' . $client,
        ], false);
        if (isset($result['status']) && $result['status'] !== 0) {
            if (!empty($result['output'])) {
                $this->outputErrorMessage($result['output']);
            }
            throw new CakeException();
        }
    }

    /**
     * 統計情報更新
     *
     * @param string $client クライアント名
     * @return void
     */
    protected function analyzeTable($client)
    {
        $result = $this->execCommand(Configure::readOrFail('Setting.batch.shell'), [
            'setting',
            'analyze-table',
            '--quiet',
            '--client=' . $client,
        ], false);
        if (isset($result['status']) && $result['status'] !== 0) {
            if (!empty($result['output'])) {
                $this->outputErrorMessage($result['output']);
            }
            throw new CakeException();
        }
    }

    /**
     * AppSettingのAPI Secretを生成
     *
     * @param string $client クライアント名
     * @param bool $createApiSecret API Secret生成フラグ
     * @return array
     */
    protected function createApiSecret($client, $createApiSecret = false)
    {
        $createApiSecretOption = [];
        if ($createApiSecret) {
            $createApiSecretOption[] = '--create-api-secret';
        }

        $result = $this->execCommand(Configure::readOrFail('Setting.batch.shell'), array_merge([
            'setting',
            'create-app-setting',
            '--quiet',
            '--client=' . $client,
        ], $createApiSecretOption), false);
        if (isset($result['status']) && $result['status'] !== 0) {
            if (!empty($result['output'])) {
                $this->outputErrorMessage($result['output']);
            }
            throw new CakeException();
        }

        $output = null;
        if (!empty($result['output'])) {
            $output = $result['output'];
        }

        return $output;
    }
}
