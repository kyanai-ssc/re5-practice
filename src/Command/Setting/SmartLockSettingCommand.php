<?php
declare(strict_types=1);

namespace App\Command\Setting;

use App\Command\Command;
use App\Model\Entity\SmartLock;
use App\Utility\SmartLock\Akerun;
use App\Utility\SmartLock\RemoteLock;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;

/**
 * Class SmartLockSettingCommand
 */
class SmartLockSettingCommand extends Command
{
    /**
     * スマートロックの種別 => 対応するロジックのクラス名
     *
     * @var array
     */
    public const SERVICE_TYPE = [
        SmartLock::TYPE_REMOTE_LOCK => RemoteLock::class,
        SmartLock::TYPE_AKERUN => Akerun::class,
    ];

    /**
     * バッファーのデフォルト、単位は分
     *
     * @var int
     */
    public const BUFFER_DEFAULT = 30;

    /**
     * @inheritDoc
     */
    protected function buildOptionParser(ConsoleOptionParser $parser): ConsoleOptionParser
    {
        $parser = parent::buildOptionParser($parser);

        $this->addClientOption($parser);
        $parser->addOption('type', [
            'name' => 'type',
            'choices' => array_map('strval', array_keys(static::SERVICE_TYPE)),
        ]);
        $parser->addOption('api-key', [
            'name' => 'api-key',
        ]);
        $parser->addOption('buffer', [
            'name' => 'buffer',
            'default' => static::BUFFER_DEFAULT,
        ]);
        $parser->addOption('organizations-id', [
            'name' => 'organizations-id',
        ]);
        $parser->addOption('secret-key', [
            'name' => 'secret-key',
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

        $secretKey = (string)$args->getOption('secret-key');
        if ($secretKey === '') {
            $secretKey = $io->ask('secret key:');
        }
        $this->validateSecretKey($secretKey);

        // 対応するスマートロックのロジックに関するオブジェクトをインスタンス化して初期設定を行う
        $class = static::SERVICE_TYPE[$args->getOption('type')];
        /** @var \App\Utility\SmartLock\RemoteLock|\App\Utility\SmartLock\Akerun $instance */
        $instance = new $class();
        $instance->initialSetting($args, $secretKey);

        $this->outputEndMessage();
    }

    /**
     * オプションのバリデーションを行う
     *
     * @param \Cake\Console\Arguments $args execute() で引数となている $args
     * @return void
     */
    public function validateOption(Arguments $args): void
    {
        if (!$this->checkClientOption()) {
            $this->errorExit('SmartLockSetting Error: invalid client.');
        }

        if (!isset(static::SERVICE_TYPE[$args->getOption('type')])) {
            $this->errorExit('SmartLockSetting Error: invalid type.');
        }

        if (!is_string($args->getOption('api-key')) || $args->getOption('api-key') === '') {
            $this->errorExit('SmartLockSetting Error: invalid api-key.');
        }

        if (
            !is_string($args->getOption('buffer'))
            || !preg_match('/^\d+$/', $args->getOption('buffer'))
            || intval($args->getOption('buffer')) > SmartLock::BUFFER_MAX
        ) {
            $this->errorExit('SmartLockSetting Error: invalid buffer.');
        }

        // アケルンの場合、事務所IDを必須とする
        if ($args->getOption('type') === strval(SmartLock::TYPE_AKERUN)) {
            if (!is_string($args->getOption('organizations-id')) || $args->getOption('organizations-id') === '') {
                $this->errorExit('SmartLockSetting Error: invalid organizations-id.');
            }
        }
    }

    /**
     * client secret のバリデーションを行う
     *
     * @param string $secretKey client secret
     * @return void
     */
    public function validateSecretKey(string $secretKey): void
    {
        if ($secretKey === '') {
            $this->errorExit('SmartLockSetting Error: Empty secret key.');
        } elseif (preg_match('/^[\\x21-\\x7e]{1,1000}$/', $secretKey) !== 1) {
            $this->errorExit('SmartLockSetting Error: invalid secret key.');
        }
    }
}
