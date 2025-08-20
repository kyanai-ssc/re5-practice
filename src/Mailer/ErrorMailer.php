<?php
declare(strict_types=1);

namespace App\Mailer;

use App\Utility\ArrayUtility;
use Cake\Core\Configure;
use Cake\I18n\FrozenTime;
use SplFileInfo;

/**
 * ErrorMailer class.
 */
class ErrorMailer extends Mailer
{
    /**
     * エラーメール送信の判定
     *
     * @param \Throwable|\Cake\Error\PhpError|null $exception Exception
     * @return bool
     */
    public function shouldSendErrorMail($exception = null)
    {
        if (!Configure::read('Error.sendMail', false) || empty(Configure::read('Error.sendMailTo', []))) {
            return false;
        }

        if (isset($exception)) {
            if (Configure::check('Error.skipLog')) {
                foreach ((array)Configure::readOrFail('Error.skipLog') as $class) {
                    if ($exception instanceof $class) {
                        return false;
                    }
                }
            }
            if ($this->checkSameError($exception)) {
                return false;
            }
        }

        return true;
    }

    /**
     * エラー発生時のメール送信
     *
     * @param string $message エラー内容
     * @return void
     */
    public function notifyError(string $message)
    {
        $this->getBaseErrorMailSetting();
        $this->setViewVars(['error' => $message]);
        $this->viewBuilder()->setTemplate('error');
    }

    /**
     * リマインダのエラーメール送信
     *
     * @param int $errorCount エラー件数
     * @param int $allCount 全体件数
     * @return void
     */
    public function sendReminderErrorMail(int $errorCount, int $allCount)
    {
        if (!$this->shouldSendErrorMail()) {
            return;
        }

        $this->getBaseErrorMailSetting();
        $this
            ->setViewVars([
                'allCount' => $allCount,
                'errorCount' => $errorCount,
            ]);

        $this->viewBuilder()->setTemplate('error_reminder');

        $errorMail = clone $this;
        $errorMail->send();
    }

    /**
     * 利用終了リマインダのエラーメール送信
     *
     * @param int $errorCount エラー件数
     * @param int $allCount 全体件数
     * @return void
     */
    public function sendCloseReminderErrorMail(int $errorCount, int $allCount)
    {
        if (!$this->shouldSendErrorMail()) {
            return;
        }

        $this->getBaseErrorMailSetting();
        $this
            ->setViewVars([
                'allCount' => $allCount,
                'errorCount' => $errorCount,
            ]);

        $this->viewBuilder()->setTemplate('error_close_reminder');

        $errorMail = clone $this;
        $errorMail->send();
    }

    /**
     * キャンセル待ち通知のエラーメール送信
     *
     * @param int $errorCount エラー件数
     * @param int $allCount 全体件数
     * @return void
     */
    public function sendNotifyCancellationErrorMail(int $errorCount, int $allCount)
    {
        if (!$this->shouldSendErrorMail()) {
            return;
        }

        $this->getBaseErrorMailSetting();
        $this
            ->setViewVars([
                'allCount' => $allCount,
                'errorCount' => $errorCount,
            ]);

        $this->viewBuilder()->setTemplate('error_notify_cancellation');

        $errorMail = clone $this;
        $errorMail->send();
    }

    /**
     * メール配信のエラーメール送信
     *
     * @param int $mailDeliveryId メール配信ID
     * @param int|null $errorCount エラー件数
     * @param int|null $allCount 全体件数
     * @return void
     */
    public function sendMailDeliveryErrorMail(int $mailDeliveryId, ?int $errorCount = null, ?int $allCount = null)
    {
        if (!$this->shouldSendErrorMail()) {
            return;
        }

        $this->getBaseErrorMailSetting();
        $this
            ->setViewVars([
                'mailDeliveryId' => $mailDeliveryId,
                'allCount' => $allCount,
                'errorCount' => $errorCount,
            ]);

        $this->viewBuilder()->setTemplate('error_mail_delivery');

        $errorMail = clone $this;
        $errorMail->send();
    }

    /**
     * エラーメールベース
     *
     * @return void
     */
    protected function getBaseErrorMailSetting()
    {
        $this->getBaseMailSetting('adminSend');
        $this
            ->setFrom(Configure::read('Error.sendMailFrom'))
            ->setTo(Configure::read('Error.sendMailTo', []))
            ->setSubject(
                str_replace('%client%', Configure::read('Client.host'), Configure::read('Error.sendMailSubject'))
            );
    }

    /**
     * 一定期間内の同一エラー判定
     *
     * @param \Throwable|\Cake\Error\PhpError $exception 例外
     * @return bool
     */
    protected function checkSameError($exception)
    {
        if (ArrayUtility::inArray($exception->getMessage(), Configure::readOrFail('Error.skipMail.messages'))) {
            $errorFile = new SplFileInfo(TMP . 'error' . DS . hash('md5', $exception->getMessage()));

            $errorModified = null;
            if (file_exists($errorFile->getPathname())) {
                $errorModified = new FrozenTime('@' . $errorFile->getMTime());

                $errorLimit = new FrozenTime($this->commonData()->getNowDateTime()->format('Y-m-d H:i:s'));
                $errorLimit = $errorLimit->subSeconds(Configure::readOrFail('Error.skipMail.seconds'));
                if ($errorModified > $errorLimit) {
                    return true;
                }
            }

            if (touch($errorFile->getPathname())) {
                if (!isset($errorModified)) {
                    $posixUserId = null;
                    if (function_exists('posix_geteuid')) {
                        $posixUserId = posix_geteuid();
                    }
                    if ($errorFile->getOwner() === $posixUserId) {
                        chmod($errorFile->getPathname(), 0666);
                    }
                }
            }
        }

        return false;
    }
}
