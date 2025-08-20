<?php
declare(strict_types=1);

namespace App\Command\Setting;

use App\Command\Command;
use App\Model\Entity\AppSetting;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;
use Cake\Core\Exception\CakeException;
use Cake\Utility\Hash;

/**
 * Class CreateAppSettingCommand
 */
class CreateAppSettingCommand extends Command
{
    /**
     * @inheritDoc
     */
    protected function buildOptionParser(ConsoleOptionParser $parser): ConsoleOptionParser
    {
        $parser = parent::buildOptionParser($parser);

        $this->addClientOption($parser);
        $parser->addOption('api-secret', [
            'name' => 'api-sercret',
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
        $createApiSecret = (bool)$args->getOption('create-api-secret');

        $this->outputStartMessage();

        if (!$this->checkClientOption()) {
            $this->errorExit('CreateAppSetting Error: invalid client.');
        }

        /** @var \App\Model\Table\AppSettingsTable $appSettingsTable */
        $appSettingsTable = $this->getTableLocator()->get('AppSettings');

        // API Secretを生成
        $appSettingApiSecret = '';
        $optionAppSettingApiSecret = (string)$args->getOption('api-secret');
        if ($createApiSecret) {
            if ($optionAppSettingApiSecret === '') {
                $appSettingApiSecret = $appSettingsTable->createApiSecret();
            } else {
                $appSettingApiSecret = $optionAppSettingApiSecret;
            }
        }

        $appSetting = $appSettingsTable->getAppSetting();
        if (empty($appSetting)) {
            $appSetting = $appSettingsTable->newEmptyEntity();
            if (!($appSetting instanceof AppSetting)) {
                throw new CakeException();
            }
        }
        if ($appSettingApiSecret !== '') {
            // 暗号化
            $appSetting->setEncryptApiSecret($appSettingApiSecret);
        } else {
            // 削除
            $appSetting->setEmptyApiSecret();
        }
        // API Secretを保存
        if (!$appSettingsTable->save($appSetting)) {
            foreach (Hash::flatten($appSetting->getErrors()) as $error) {
                $this->outputErrorMessage('CreateAppSetting Error: ' . $error);
                $this->errorExit();
            }
        }

        // 生成したAPI Secretをコンソールに出力
        if ($appSettingApiSecret !== '') {
            $io->quiet('--------------------------------');
            $io->quiet('CreateAppSetting API Secret: ' . $appSettingApiSecret);
            $io->quiet('--------------------------------');
        }

        $this->outputEndMessage();
    }
}
