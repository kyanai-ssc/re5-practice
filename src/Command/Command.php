<?php
declare(strict_types=1);

namespace App\Command;

use App\Command\Traits\ClientTrait;
use App\Utility\CommonData\CommonDataTrait;
use Cake\Command\Command as CakeCommand;
use Cake\Console\ConsoleIo;
use Cake\I18n\I18n;
use Cake\Log\Log;

class Command extends CakeCommand
{
    use ClientTrait;
    use CommonDataTrait;

    protected const DEFAULT_LOG_SCOPE = 'command';

    /**
     * @var string|null
     */
    protected $arguments = null;

    /**
     * @var \Cake\Console\ConsoleIo|null
     */
    protected $consoleIo = null;

    /**
     * @var string|null
     */
    protected $commandName = null;

    /**
     * @var string|null
     */
    protected $logScope = null;

    /**
     * @inheritDoc
     */
    public function initialize(): void
    {
        parent::initialize();

        set_time_limit(0);
        $this->setLogConfig(static::DEFAULT_LOG_SCOPE);
    }

    /**
     * @inheritDoc
     */
    public function run(array $argv, ConsoleIo $io): ?int
    {
        $this->consoleIo = $io;

        return parent::run($argv, $io);
    }

    /**
     * 翻訳ファイルをセットする
     *
     * @return void
     */
    protected function setTranslate()
    {
        /** @var \App\Model\Table\WordsTable $wordsTable */
        $wordsTable = $this->getTableLocator()->get('Words');

        /** @var \Cake\I18n\Translator $transrator */
        $transrator = I18n::getTranslator();

        $transrator->getPackage()->setMessages($wordsTable->getData(true));
    }

    /**
     * コマンド名を取得
     *
     * @return string|null
     */
    protected function getCommandName()
    {
        if (!isset($this->commandName)) {
            $this->commandName = preg_replace('/^.*\\\\([^\\\\]+)$/', '$1', static::class);
        }

        return $this->commandName;
    }

    /**
     * メッセージを出力
     *
     * @param string|array $message メッセージ
     * @return void
     */
    protected function outputMessage($message)
    {
        if (is_array($message)) {
            $message = implode("\n", $message);
        }
        Log::write('info', '[PID:' . getmypid() . '] ' . $message, [$this->logScope]);
    }

    /**
     * 開始メッセージを出力
     *
     * @return void
     */
    protected function outputStartMessage()
    {
        $this->outputMessage($this->getCommandName() . ' Start');
    }

    /**
     * 終了メッセージを出力
     *
     * @return void
     */
    protected function outputEndMessage()
    {
        $this->outputMessage($this->getCommandName() . ' End');
    }

    /**
     * エラーメッセージを出力
     *
     * @param string|array $message メッセージ
     * @return void
     */
    protected function outputErrorMessage($message)
    {
        if (is_array($message)) {
            $message = implode("\n", $message);
        }
        Log::write('error', '[PID:' . getmypid() . '] ' . $message, [$this->logScope]);
    }

    /**
     * エラー終了
     *
     * @param string|array|null $message メッセージ
     * @return void
     */
    protected function errorExit($message = null)
    {
        if (isset($message)) {
            $this->outputErrorMessage($message);
        }
        $this->outputEndMessage();
        $this->abort();
    }

    /**
     * ログの設定
     *
     * @param string $scope スコープ
     * @return void
     */
    protected function setLogConfig($scope)
    {
        $config = Log::getConfig($scope);
        $config['file'] = $this->getCommandName();

        Log::drop($scope);
        Log::SetConfig($scope, $config);

        $this->logScope = $scope;
    }
}
