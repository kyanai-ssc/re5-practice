<?php
declare(strict_types=1);

namespace App\Utility\VideoMeeting;

use Cake\Core\InstanceConfigTrait;
use Cake\Event\EventDispatcherInterface;
use Cake\Event\EventDispatcherTrait;
use Cake\Log\Log;
use Psr\Log\LogLevel;

abstract class AbstractVideoMeeting implements EventDispatcherInterface
{
    use EventDispatcherTrait;
    use InstanceConfigTrait;

    /**
     * @var array|null
     */
    protected $errors = null;

    /**
     * Constructor.
     *
     * @param array $options オプション
     */
    public function __construct(array $options = [])
    {
        $this->setConfig($options);
        $this->initialize();
    }

    /**
     * 初期化処理
     *
     * @return void
     */
    protected function initialize()
    {
    }

    /**
     * エラーを判定
     *
     * @return bool
     */
    public function hasErrors()
    {
        if (!empty($this->errors)) {
            return true;
        }

        return false;
    }

    /**
     * エラーメッセージを取得
     *
     * @return array|null
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * ログへメッセージを記録
     *
     * @param string $message メッセージ
     * @param int|string|null $level レベル
     * @return void
     */
    protected function writeLog($message, $level = null)
    {
        $scope = $this->getConfig('errorLog');
        if (((string)$scope) === '') {
            return;
        }

        if (!isset($level)) {
            $level = LogLevel::ERROR;
        }
        Log::write($level, $message . "\n\n", ['scope' => $scope]);
    }

    /**
     * ビデオ会議を作成
     *
     * @param \DateTimeInterface|string $dateTimeFrom 開始日時
     * @param \DateTimeInterface|string $dateTimeTo 終了日時
     * @param array|null $options オプション
     * @return array|null
     */
    abstract public function createMeeting($dateTimeFrom, $dateTimeTo, ?array $options = null);

    /**
     * ビデオ会議を更新
     *
     * @param mixed $id ID
     * @param \DateTimeInterface|string $dateTimeFrom 開始日時
     * @param \DateTimeInterface|string $dateTimeTo 終了日時
     * @param array|null $options オプション
     * @return array|null
     */
    abstract public function updateMeeting($id, $dateTimeFrom, $dateTimeTo, ?array $options = null);

    /**
     * ビデオ会議を削除
     *
     * @param mixed $id ID
     * @param array|null $options オプション
     * @return array|null
     */
    abstract public function deleteMeeting($id, ?array $options = null);

    /**
     * API連携を取消
     *
     * @return array|null
     */
    abstract public function rollbackApi();
}
