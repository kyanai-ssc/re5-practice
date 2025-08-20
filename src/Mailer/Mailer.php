<?php
declare(strict_types=1);

namespace App\Mailer;

use App\Error\ErrorLoggerTrait;
use App\Utility\CommonData\CommonDataTrait;
use Cake\Core\Configure;
use Cake\Mailer\Mailer as BaseMailer;
use Cake\ORM\Locator\LocatorAwareTrait;

/**
 * Mailer abstract class.
 */
abstract class Mailer extends BaseMailer
{
    use CommonDataTrait;
    use ErrorLoggerTrait;
    use LocatorAwareTrait;

    /**
     * メールベース
     *
     * @param string|null $profile Profile
     * @return void
     */
    protected function getBaseMailSetting($profile = null)
    {
        if (!isset($profile)) {
            $profile = 'default';
        }

        $this->setProfile($profile);
        $this->setDomain(Configure::readOrFail('Client.host'));
        $this->getMessage()->setTransferEncoding('base64');
        $this->addHeaders(['X-SendTag' => Configure::read('Client.host')]);
        $this->viewBuilder()->setTemplate('default');
    }

    /**
     * ユーザ宛てのReturn-pathを取得
     *
     * @param string $localPart ローカルパート
     * @return string
     */
    protected function getUserReturnPath($localPart)
    {
        if (!Configure::check('Client.returnPath.user')) {
            return $localPart . '@' . Configure::read('Client.host');
        }

        return preg_replace('/%LOCAL_PART%/', $localPart, Configure::readOrFail('Client.returnPath.user'));
    }

    /**
     * 管理者宛てのReturn-pathを取得
     *
     * @return string
     */
    protected function getAdminReturnPath()
    {
        if (!Configure::check('Client.returnPath.admin')) {
            return Configure::readOrFail('Setting.mail.admin.returnPath');
        }

        return Configure::readOrFail('Client.returnPath.admin');
    }

    /**
     * メール送信時のデフォルトFromアドレスを取得
     *
     * @return string
     * @throws \RuntimeException if cannot read default from address.
     */
    public static function getDefaultFromAddress(): string
    {
        // 環境設定ファイルを優先
        return (string)(
            self::existsDefaultFromAddressOnEnv()
                ? Configure::read('Env.mail.dkim.defaultFrom')
                : Configure::readOrFail('Setting.mail.admin.from')
        );
    }

    /**
     * メール送信時のデフォルトFromアドレスが環境別設定ファイルに記載されているか判定
     *
     * @return bool
     */
    public static function existsDefaultFromAddressOnEnv(): bool
    {
        return Configure::check('Env.mail.dkim.defaultFrom');
    }

    /**
     * 例外処理を行いメール送信
     *
     * @param \Cake\Mailer\Mailer $email メール
     * @param bool $catchException 例外キャッチ
     * @return bool 成功時true、失敗時false ($catchExceptionがfalseの場合常にtrue)
     */
    protected function sendSafe($email, $catchException = false)
    {
        $send = function () use ($email) {
            $email->send();
        };

        if (!$catchException) {
            call_user_func($send);

            return true;
        }

        return $this->executeSafe($send);
    }
}
