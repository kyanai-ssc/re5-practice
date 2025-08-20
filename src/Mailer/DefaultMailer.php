<?php
declare(strict_types=1);

namespace App\Mailer;

use App\Model\Entity\AutoReplyMail;
use App\Model\Entity\Event;
use App\Model\Entity\FormGroup;
use App\Model\Entity\FormItem;
use App\Model\InputType\Item\Type\MailOutputInterface;
use App\Utility\ArrayUtility;
use App\Utility\DateTimeUtility;
use App\Utility\SmartLock\SmartLockLinkage;
use App\Utility\StringUtility;
use App\View\Helper\TemplateHelper;
use Cake\Core\Configure;
use Cake\Core\Exception\CakeException;
use Cake\Datasource\EntityInterface;
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\Routing\Router;
use Cake\Utility\Hash;
use Cake\View\View;

/**
 * DefaultMailer class.
 */
class DefaultMailer extends Mailer
{
    /**
     * メール本文：テキスト形式
     */
    public const MAIL_FORMAT_CONTENTS_TEXT = 1;

    /**
     * メール本文：HTML形式
     */
    public const MAIL_FORMAT_CONTENTS_HTML = 2;

    /**
     * QRコードの横幅
     */
    public const QR_CODE_SIZE_WIDTH = 150;

    /**
     * QRコードの縦幅
     */
    public const QR_CODE_SIZE_HEIGHT = 150;

    /**
     * 置き換え文字対象：会員ID
     */
    public const REPLACE_USER_ID = 1;

    /**
     * 置き換え文字対象：会員登録日時
     */
    public const REPLACE_USER_CREATED = 2;

    /**
     * 置き換え文字対象：予約ID
     */
    public const REPLACE_RESERVE_ID = 3;

    /**
     * 置き換え文字対象：開始日
     */
    public const REPLACE_RESERVE_START_DATE = 4;

    /**
     * 置き換え文字対象：開始時間
     */
    public const REPLACE_RESERVE_START_TIME = 5;

    /**
     * 置き換え文字対象：終了日
     */
    public const REPLACE_RESERVE_END_DATE = 6;

    /**
     * 置き換え文字対象：終了時間
     */
    public const REPLACE_RESERVE_END_TIME = 7;

    /**
     * 置き換え文字対象：複数プラン
     */
    public const REPLACE_RESERVE_PLAN = 8;

    /**
     * 置き換え文字対象：予約ステータス
     */
    public const REPLACE_RESERVE_STATUS = 9;

    /**
     * 置き換え文字対象：料金
     */
    public const REPLACE_RESERVE_CHARGE = 10;

    /**
     * 置き換え文字対象：決済方法
     */
    public const REPLACE_RESERVE_PAYMENT_METHOD = 11;

    /**
     * 置き換え文字対象：決済状況
     */
    public const REPLACE_RESERVE_PAYMENT_STATUS = 12;

    /**
     * 置き換え文字対象：予約登録日時
     */
    public const REPLACE_RESERVE_CRATED = 13;

    /**
     * 置き換え文字対象：パスワード変更URL
     */
    public const REPLACE_ADDITIONAL_CHANGE_URL = 14;

    /*
     * 置き換え文字対象：会員登録しないで予約用キャンセルURL
     */
    public const REPLACE_ADDITIONAL_GUEST_LOGIN_URL = 15;

    /**
     * 置き換え文字対象：有効期限
     */
    public const REPLACE_ADDITIONAL_LIMIT = 16;

    /**
     * 置き換え文字対象：会員登録URL
     */
    public const REPLACE_ADDITIONAL_REGISTER_URL_USER = 17;

    /**
     * 置き換え文字対象：予約登録URL
     */
    public const REPLACE_ADDITIONAL_REGISTER_URL_RESERVE = 18;

    /**
     * 置き換え文字対象：会員登録しないで予約認証用コード
     */
    public const REPLACE_ADDITIONAL_GUEST_LOGIN_CODE = 19;

    /**
     * 置き換え文字対象：キャンセル待ち通知解除URL
     */
    public const REPLACE_ADDITIONAL_CANCELWAIT_RELEASE_URL = 20;

    /**
     * 置き換え文字対象：キャンセル待ち用予約枠名
     */
    public const REPLACE_ADDITIONAL_EVENT_NAME = 21;

    /**
     * 置き換え文字対象：キャンセル待ち用開始日
     */
    public const REPLACE_ADDITIONAL_START_DATE = 22;

    /**
     * 置き換え文字対象：キャンセル待ち用開始時間
     */
    public const REPLACE_ADDITIONAL_START_TIME = 23;

    /**
     * 置き換え文字対象：会員フォーム項目
     */
    public const REPLACE_FORM_ITEM_USER = 24;

    /**
     * 置き換え文字対象：予約フォーム項目
     */
    public const REPLACE_FORM_ITEM_RESERVATION = 25;

    /**
     * 置き換え文字対象：ビデオ会議情報
     */
    public const REPLACE_VIDEO_MEETING_INFO = 26;

    /**
     * 置き換え文字対象：予約詳細画面URL
     */
    public const REPLACE_ADDITIONAL_DETAIL_URL = 27;

    /**
     * 置き換え文字対象：QRコード
     */
    public const REPLACE_ADDITIONAL_QR_CODE = 28;

    /**
     * 置き換え文字対象：PIN番号
     */
    public const REPLACE_RESERVE_SMART_LOCK_PIN = 29;

    /**
     * 置き換え文字対象：ロック解除URL
     */
    public const REPLACE_RESERVE_SMART_LOCK_KEY_URL = 30;

    /**
     * 置き換え文字対象：メールアドレス変更認証用URL
     */
    public const REPLACE_ADDITIONAL_MAIL_EDIT_APPROVAL_URL = 31;

    /**
     * 置き換え文字対象：カギ情報URL（ユニバーサルアクセスキー）
     */
    public const REPLACE_RESERVE_SMART_LOCK_UNIVERSAL_ACCESS_KEY = 32;

    public const TEST_MAIL_LOCAL_PART = 'test-mail';

    /**
     * @var array|null
     */
    protected $autoMailReplaceTokens = null;

    /**
     * @var array|null
     */
    protected $mailDeliveryReplaceTokens = null;

    /**
     * 自動返信メールの置き換え文言一覧を取得
     *
     * @param int $type 自動返信メールタイプ
     * @return array 置き換え文言一覧
     */
    public function getAutoMailReplaceTokens(int $type)
    {
        if (isset($this->autoMailReplaceTokens[$type])) {
            return $this->autoMailReplaceTokens[$type];
        }

        /** @var \App\Model\Table\SystemSettingsTable $systemSettingsTable */
        $systemSettingsTable = $this->getTableLocator()->get('SystemSettings');
        /** @var \App\Model\Table\FormItemsTable $formItemsTable */
        $formItemsTable = $this->getTableLocator()->get('FormItems');

        $replaceCodes = [];
        if (ArrayUtility::inArray($type, Configure::readOrFail('Master.autoReplyMail.userType'))) {
            $replaceCodes = array_merge($replaceCodes, Configure::readOrFail('Master.mailReplace.user'));
        }
        if (ArrayUtility::inArray($type, Configure::readOrFail('Master.autoReplyMail.reservationType'))) {
            $replaceCodes = array_merge($replaceCodes, Configure::readOrFail('Master.mailReplace.reservation'));
        }
        $replaceCodes = array_merge(
            $replaceCodes,
            Configure::read('Master.mailReplace.additional.' . $type, [])
        );

        $formItems = [
            static::REPLACE_FORM_ITEM_USER => $formItemsTable->getFormItems(FormGroup::FORM_TYPE_USER),
            static::REPLACE_FORM_ITEM_RESERVATION => $formItemsTable->getFormItems(FormGroup::FORM_TYPE_RESERVATION),
        ];

        $replaceTokens = array_fill_keys(['new', 'old'], []);
        foreach ($replaceCodes as $replaceCode) {
            if (isset($formItems[$replaceCode])) {
                // フォーム項目の置き換え文言
                foreach ($formItems[$replaceCode] as $formItem) {
                    $inputTypeItem = $formItem->getInputTypeItem();
                    if ($inputTypeItem instanceof MailOutputInterface && $inputTypeItem->canOutputMailValue($type)) {
                        $replaceTokens['new'][$replaceCode][$formItem->get('id')] = [
                            'token' => $formItem->getInputTypeItem()->getMailReplaceToken(),
                            'label' => $formItem->get('name'),
                            'formItem' => $formItem,
                        ];
                    }
                }
            } else {
                // 固定項目の置き換え文言
                $replaceTokens['new'][$replaceCode][] = [
                    'token' => Configure::readOrFail('Master.mailReplace.token.' . $replaceCode),
                    'label' => Configure::readOrFail('Master.mailReplace.label.' . $replaceCode),
                ];
            }
        }

        if (!$systemSettingsTable->getData()->usePayment()) {
            unset($replaceTokens['new'][static::REPLACE_RESERVE_PAYMENT_METHOD]);
            unset($replaceTokens['new'][static::REPLACE_RESERVE_PAYMENT_STATUS]);
        }

        if (!$systemSettingsTable->getData()->canCoordinateVideoMeeting()) {
            // ビデオ会議連携の置き換えを削除
            unset($replaceTokens['new'][static::REPLACE_VIDEO_MEETING_INFO]);
        }

        $smatLock = new SmartLockLinkage();
        if ($smatLock->useRemoteLock()) {
            unset($replaceTokens['new'][static::REPLACE_RESERVE_SMART_LOCK_KEY_URL]);
        } elseif ($smatLock->useAkerun()) {
            unset($replaceTokens['new'][static::REPLACE_RESERVE_SMART_LOCK_PIN]);
            unset($replaceTokens['new'][static::REPLACE_RESERVE_SMART_LOCK_UNIVERSAL_ACCESS_KEY]);
        } else {
            unset($replaceTokens['new'][static::REPLACE_RESERVE_SMART_LOCK_PIN]);
            unset($replaceTokens['new'][static::REPLACE_RESERVE_SMART_LOCK_KEY_URL]);
            unset($replaceTokens['new'][static::REPLACE_RESERVE_SMART_LOCK_UNIVERSAL_ACCESS_KEY]);
        }

        // 変更前データの置き換え文言
        if (ArrayUtility::inArray($type, Configure::readOrFail('Master.autoReplyMail.oldUserType'))) {
            foreach ($replaceTokens['new'] as $replaceCode => $newReplaceTokens) {
                if (ArrayUtility::inArray($replaceCode, Configure::readOrFail('Master.mailReplace.oldUser'))) {
                    foreach ($newReplaceTokens as $key => $replaceToken) {
                        $formItem = Hash::get($replaceToken, 'formItem');
                        $inputTypeItem = null;
                        if (isset($formItem)) {
                            $inputTypeItem = $formItem->getInputTypeItem();
                            if (!($inputTypeItem instanceof MailOutputInterface)) {
                                throw new CakeException();
                            }
                        }
                        if (!isset($inputTypeItem) || $inputTypeItem->canOutputMailValue($type, true)) {
                            $replaceTokens['old'][$replaceCode][$key] = $this->createOldReplaceToken($replaceToken);
                        }
                    }
                }
            }
        }
        if (ArrayUtility::inArray($type, Configure::readOrFail('Master.autoReplyMail.oldReservationType'))) {
            foreach ($replaceTokens['new'] as $replaceCode => $newReplaceTokens) {
                if (ArrayUtility::inArray($replaceCode, Configure::readOrFail('Master.mailReplace.oldReservation'))) {
                    foreach ($newReplaceTokens as $key => $replaceToken) {
                        $formItem = Hash::get($replaceToken, 'formItem');
                        $inputTypeItem = null;
                        if (isset($formItem)) {
                            $inputTypeItem = $formItem->getInputTypeItem();
                            if (!($inputTypeItem instanceof MailOutputInterface)) {
                                throw new CakeException();
                            }
                        }
                        if (!isset($inputTypeItem) || $inputTypeItem->canOutputMailValue($type, true)) {
                            $replaceTokens['old'][$replaceCode][$key] = $this->createOldReplaceToken($replaceToken);
                        }
                    }
                }
            }
        }

        // 置き換えタグの整形
        foreach (['new', 'old'] as $value) {
            foreach ($replaceTokens[$value] as $code => $tokens) {
                foreach ($tokens as $key => $token) {
                    $replaceTokens[$value][$code][$key] = $this->encloseReplaceToken($token);
                }
            }
        }

        if (!isset($this->autoMailReplaceTokens)) {
            $this->autoMailReplaceTokens = [];
        }
        $this->autoMailReplaceTokens[$type] = $replaceTokens;

        return $replaceTokens;
    }

    /**
     * 一斉メール配信の置き換え文言一覧を取得
     *
     * @return array 置き換え文言一覧
     */
    public function getMailDeliveryReplaceTokens()
    {
        if (isset($this->mailDeliveryReplaceTokens)) {
            return $this->mailDeliveryReplaceTokens;
        }

        /** @var \App\Model\Table\FormItemsTable $formItemsTable */
        $formItemsTable = $this->getTableLocator()->get('FormItems');

        $replaceTokens = $this->getAutoMailReplaceTokens(AutoReplyMail::TYPE_USER_ADD);
        foreach ($formItemsTable->getFormItemByInputType(FormItem::INPUT_TYPE_PASSWORD) as $formItem) {
            unset($replaceTokens['new'][static::REPLACE_FORM_ITEM_USER][$formItem->get('id')]);
        }

        $this->mailDeliveryReplaceTokens = $replaceTokens;

        return $replaceTokens;
    }

    /**
     * 自動返信メールの送信
     *
     * @param \Cake\Datasource\EntityInterface $autoReplyMailHistory 自動返信メール履歴
     * @param bool $adminSend 管理者送信フラグ
     * @param array|null $additional 追加データ
     * @param string|null $transport トランスポート
     * @param bool $catchException 例外キャッチ
     * @return bool 成功時true、失敗時false ($catchExceptionがfalseの場合常にtrue)
     */
    public function sendAutoReplyMail(
        EntityInterface $autoReplyMailHistory,
        $adminSend = true,
        ?array $additional = null,
        ?string $transport = null,
        bool $catchException = false
    ) {
        $adminMailsTable = $this->getTableLocator()->get('AdminMails');
        $autoReplyMailsTable = $this->getTableLocator()->get('AutoReplyMails');
        $eventsTable = $this->getTableLocator()->get('Events');
        $bounceMailsTable = $this->getTableLocator()->get('BounceMails');

        $userSend = false;
        if ($autoReplyMailHistory->has('user_mail')) {
            $excludeMail = $bounceMailsTable->find('checkExclude', [
                'inputs' => [
                    'mail' => $autoReplyMailHistory->get('user_mail'),
                ],
            ]);
            if ($excludeMail->count() === 0) {
                $userSend = true;
            }
        }
        if (!$userSend && !$adminSend) {
            return true;
        }

        // 自動返信メールのデータ取得
        $autoReplyMail = null;
        try {
            $autoReplyMail = $autoReplyMailsTable->get($autoReplyMailHistory->get('auto_reply_mail_id'), [
                'finder' => 'sendAutoReplyMail',
            ]);
        } catch (RecordNotFoundException $e) {
            throw new CakeException();
        }
        if (!($autoReplyMail instanceof AutoReplyMail)) {
            throw new CakeException();
        }

        // 置き換え用のデータ取得
        $data = $autoReplyMailHistory->get('data');
        $data['additional'] = $additional;
        $data['user']['password'] = $autoReplyMailHistory->get('user_password');
        try {
            if (isset($data['reservation']['event_id'])) {
                $data['reservation']['event'] = $eventsTable->get($data['reservation']['event_id'], [
                    'finder' => 'reservation',
                ])->toArray();
            }
            if (isset($data['oldReservation']['event_id'])) {
                $data['oldReservation']['event'] = $eventsTable->get($data['oldReservation']['event_id'], [
                    'finder' => 'reservation',
                ])->toArray();
            }
        } catch (RecordNotFoundException $e) {
            throw new CakeException();
        }

        // メール設定
        $this->getBaseMailSetting();
        $this
            ->setFrom($autoReplyMail->get('from_mail'), $autoReplyMail->get('from_mail_name'))
            ->setSubject($this->replaceContents(
                $autoReplyMail->get('content_type'),
                $autoReplyMail->get('subject'),
                $this->getAutoMailReplaceTokens($autoReplyMail->get('type')),
                $data
            ))
            ->setEmailFormat(
                Configure::readOrFail('Master.common.mailFormat.' . $autoReplyMail->get('content_type'))
            )
            ->setViewVars([
                'content' => $this->replaceContents(
                    $autoReplyMail->get('content_type'),
                    $autoReplyMail->getMailMessage($autoReplyMailHistory->get('admin_id')),
                    $this->getAutoMailReplaceTokens($autoReplyMail->get('type')),
                    $data
                ),
            ]);
        if (((string)$transport) !== '') {
            $this->setTransport((string)$transport);
        }
        if ((string)$autoReplyMail->get('reply_to') !== '') {
            $this->setReplyTo($autoReplyMail->get('reply_to'));
        }
        $this->setReturnPath($this->getUserReturnPath($autoReplyMailHistory->get('bounce_mail_token')));

        $result = true;

        // 利用者への送信
        if ($userSend) {
            $userEmail = clone $this;
            $userEmail->setTo($autoReplyMailHistory->get('user_mail'));
            $userEmail->setMessageId(true);
            if (!$this->sendSafe($userEmail, $catchException)) {
                $result = false;
            }
        }

        // 管理者への送信
        if ($adminSend) {
            $adminEmailTemplate = clone $this;
            $adminEmailTemplate->setReturnPath($this->getAdminReturnPath());

            $reservationLabelId = [];
            if (isset($data['reservation']['event']['label_id'])) {
                $reservationLabelId[] = $data['reservation']['event']['label_id'];
            }
            if (isset($data['oldReservation']['event']['label_id'])) {
                $reservationLabelId[] = $data['oldReservation']['event']['label_id'];
            }

            $adminMails = $adminMailsTable->find('autoReplyMail', [
                'inputs' => [
                    'label_id' => $reservationLabelId,
                ],
            ]);
            foreach ($adminMails as $adminMail) {
                $adminEmail = clone $adminEmailTemplate;
                $adminEmail->setTo($adminMail->get('mail'));
                $adminEmail->setMessageId(true);
                if (!$this->sendSafe($adminEmail, $catchException)) {
                    $result = false;
                }
            }
        }

        return $result;
    }

    /**
     * 自動返信メールのテスト送信
     *
     * @param \Cake\Datasource\EntityInterface $testMail 自動返信メールテスト
     * @param string $to テスト宛先
     * @return void
     */
    public function sendAutoReplyMailTest(EntityInterface $testMail, string $to)
    {
        $delimiter = Configure::readOrFail('Master.common.mailFormatDelimiter.' . $testMail->get('content_type'));
        $message = implode($delimiter, [
            $testMail->get('header'),
            $testMail->get('contents'),
            $testMail->get('footer'),
        ]);

        $this->getBaseMailSetting();
        $this
            ->setFrom($testMail->get('from_mail'), $testMail->get('from_mail_name'))
            ->setSubject($testMail->get('subject'))
            ->setEmailFormat(
                Configure::readOrFail('Master.common.mailFormat.' . $testMail->get('content_type'))
            )
            ->setViewVars([
                'content' => $message,
            ]);
        $this->setReturnPath($this->getUserReturnPath(static::TEST_MAIL_LOCAL_PART));

        if ((string)$testMail->get('reply_to') !== '') {
            $this->setReplyTo($testMail->get('reply_to'));
        }

        $email = clone $this;
        $email->setTo($to);
        $email->send();
    }

    /**
     * 利用者への一斉メール配信
     *
     * @param \Cake\Datasource\EntityInterface $mailDelivery メール配信情報
     * @param \Cake\Datasource\EntityInterface $user 会員情報
     * @param string $testTo テスト配信先
     * @return bool
     */
    public function mailDelivery(EntityInterface $mailDelivery, EntityInterface $user, $testTo = null)
    {
        $subject = $mailDelivery->get('subject');
        $contents = $mailDelivery->get('contents');
        if ($testTo !== null) {
            $to = $testTo;
            $token = static::TEST_MAIL_LOCAL_PART;
        } else {
            $userData = $user->get('user')->toArray();
            $subject = $this->replaceContents(
                $mailDelivery->get('content_type'),
                $subject,
                $this->getMailDeliveryReplaceTokens(),
                [
                    'user' => $userData,
                ]
            );
            $contents = $this->replaceContents(
                $mailDelivery->get('content_type'),
                $contents,
                $this->getMailDeliveryReplaceTokens(),
                [
                    'user' => $userData,
                ]
            );

            $to = $user->get('mail');
            $token = $user->get('bounce_mail_token');
        }

        $this->getBaseMailSetting('mailDelivery');
        $this
            ->setFrom($mailDelivery->get('from_mail'), $mailDelivery->get('from_mail_name'))
            ->setSubject($subject)
            ->setEmailFormat(
                Configure::readOrFail('Master.common.mailFormat.' . $mailDelivery->get('content_type'))
            )
            ->setViewVars([
                'content' => $contents,
            ]);
        $this->setReturnPath($this->getUserReturnPath($token));

        if ((string)$mailDelivery->get('reply_to') !== '') {
            $this->setReplyTo($mailDelivery->get('reply_to'));
        }

        $result = true;
        $catchException = false;
        if (!is_null($testTo)) {
            $catchException = true;
        }

        $email = clone $this;
        $email->setTo($to);
        if (!$this->sendSafe($email, $catchException)) {
            $result = false;
        }

        return $result;
    }

    /**
     * 置き換え文言の処理
     *
     * @param int $contentType 配信フォーマット
     * @param string $contents 対象文字列
     * @param array $replaceTokens 置き換え文言一覧
     * @param array $data データ
     * @return string 置き換え後の文字列
     */
    protected function replaceContents($contentType, $contents, $replaceTokens, $data)
    {
        $replacement = [];
        $replaceValues = [
            'new' => [
                'user' => Hash::get($data, 'user', []),
                'reservation' => Hash::get($data, 'reservation', []),
                'event' => Hash::get($data, 'reservation.event', []),
                'additional' => Hash::get($data, 'additional', []),
            ],
            'old' => [
                'user' => Hash::get($data, 'oldUser', []),
                'reservation' => Hash::get($data, 'oldReservation', []),
                'event' => Hash::get($data, 'oldReservation.event', []),
                'additional' => Hash::get($data, 'additional', []),
            ],
        ];

        $notEscapeValue = [];
        foreach (array_keys($replaceTokens) as $type) {
            foreach ($replaceTokens[$type] as $code => $tokens) {
                foreach ($tokens as $token) {
                    if (isset($token['formItem'])) {
                        $inputTypeItem = $token['formItem']->getInputTypeItem();
                        if (!($inputTypeItem instanceof MailOutputInterface)) {
                            throw new CakeException();
                        }
                        $replacement[$token['token']] = (string)$inputTypeItem->getMailOutputValue(
                            $replaceValues[$type],
                            ['content_type' => $contentType]
                        );
                        if (!$inputTypeItem->useEscapeValue()) {
                            $notEscapeValue[$token['token']] = true;
                        }
                    } else {
                        $replacement[$token['token']] = (string)$this->getReplaceValue(
                            $code,
                            $replaceValues[$type],
                            $contentType
                        );
                        if (
                            (string)$code === (string)static::REPLACE_ADDITIONAL_QR_CODE
                            && (string)$contentType === (string)static::MAIL_FORMAT_CONTENTS_HTML
                        ) {
                            $notEscapeValue[$token['token']] = true;
                        }
                    }
                }
            }
        }

        if (((string)$contentType) === ((string)static::MAIL_FORMAT_CONTENTS_HTML)) {
            foreach ($replacement as $key => $value) {
                if (Hash::get($notEscapeValue, (string)$key, false)) {
                    $replacement[$key] = (string)$value;
                } else {
                    $replacement[$key] = nl2br(htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8'));
                }
            }
        }

        $contents = str_replace(array_keys($replacement), array_values($replacement), $contents);

        return $contents;
    }

    /**
     * 変更前の置き換え文言を作成
     *
     * @param array $token 置き換え文言
     * @return array 変更前の置き換え文言
     */
    protected function createOldReplaceToken($token)
    {
        $token['token'] = Configure::readOrFail('Setting.mail.replace.oldReplaceTokenPrefix') . $token['token'];
        $token['label'] = preg_replace(
            '/%LABEL%/',
            StringUtility::pregReplaceQuote($token['label']),
            Configure::readOrFail('Setting.mail.replace.oldReplaceLabel')
        );

        return $token;
    }

    /**
     * タグを囲み文字で整形
     *
     * @param array $token 置き換え文言
     * @return array 整形後の置き換え文言
     */
    protected function encloseReplaceToken($token)
    {
        $enclosure = Configure::readOrFail('Setting.mail.replace.replaceEnclosure');
        $token['token'] = $enclosure . strtoupper($token['token']) . $enclosure;

        return $token;
    }

    /**
     * 固定項目の置き換え後の値を取得
     *
     * @param int $code 置換コード
     * @param array $options オプション
     * @param int $contentType 配信フォーマット
     * @return string|null 置き換え後の値
     */
    protected function getReplaceValue($code, $options, $contentType)
    {
        $value = null;
        $column = Configure::read('Master.mailReplace.column.' . $code);
        if (isset($column)) {
            $value = Hash::get($options, $column);
        }

        if (
            ((string)$code) === ((string)static::REPLACE_RESERVE_START_DATE)
            || ((string)$code) === ((string)static::REPLACE_RESERVE_END_DATE)
            || ((string)$code) === ((string)static::REPLACE_ADDITIONAL_START_DATE)
        ) {
            $value = DateTimeUtility::convertToDateObject($value);
            if (isset($value)) {
                $value = $value->format('Y/m/d');
            }
        } elseif (
            ((string)$code) === ((string)static::REPLACE_RESERVE_START_TIME)
            || ((string)$code) === ((string)static::REPLACE_RESERVE_END_TIME)
            || ((string)$code) === ((string)static::REPLACE_ADDITIONAL_START_TIME)
        ) {
            $value = DateTimeUtility::convertToTimeObject($value);
            if (isset($value)) {
                $value = $value->format('H:i');
            }
        } elseif (((string)$code) === ((string)static::REPLACE_RESERVE_PLAN)) {
            $reservation = Hash::get($options, 'reservation');
            $event = Hash::get($options, 'event');
            if (
                isset($value) && isset($reservation) && isset($event)
                && ((string)$event['time_plan']) === ((string)Event::PLAN_MULTIPLE)
            ) {
                $eventPlans = [];
                foreach ($event['event_plans'] as $eventPlan) {
                    $eventPlans[$eventPlan['id']] = $eventPlan;
                }
                $value = [];
                foreach ($reservation['reservation_event_plans'] as $reservationEventPlan) {
                    $value[] = $eventPlans[$reservationEventPlan['event_plan_id']]['name'];
                }
            }
        } elseif (((string)$code) === ((string)static::REPLACE_RESERVE_STATUS)) {
            /** @var \App\Model\Table\ReservationStatusesTable $reservationStatusesTable */
            $reservationStatusesTable = $this->getTableLocator()->get('ReservationStatuses');

            if (isset($value)) {
                $value = $reservationStatusesTable->getReservationStatusName($value);
            }
        } elseif (((string)$code) === ((string)static::REPLACE_RESERVE_PAYMENT_METHOD)) {
            /** @var \App\Model\Table\PaymentMethodsTable $paymentMethodsTable */
            $paymentMethodsTable = $this->getTableLocator()->get('PaymentMethods');

            if (isset($value)) {
                $value = $paymentMethodsTable->getPaymentMethodName((int)$value);
            }
        } elseif (((string)$code) === ((string)static::REPLACE_RESERVE_PAYMENT_STATUS)) {
            /** @var \App\Model\Table\PaymentStatusesTable $paymentStatusesTable */
            $paymentStatusesTable = $this->getTableLocator()->get('PaymentStatuses');

            if (isset($value)) {
                $value = $paymentStatusesTable->getPaymentStatusName((int)$value);
            }
        } elseif (((string)$code) === ((string)static::REPLACE_ADDITIONAL_GUEST_LOGIN_URL)) {
            $value = Router::url([
                'prefix' => 'User',
                'controller' => 'Guest',
                'action' => 'login',
            ], true);
        } elseif (((string)$code) === ((string)static::REPLACE_VIDEO_MEETING_INFO)) {
            // ビデオ会議情報
            if (is_array($value) && !empty($value)) {
                $videoMeeting = reset($value);
                $value = Configure::readOrFail(
                    'Setting.mail.replaceValue.videoMeetingInfo.value.' . $videoMeeting['video_meeting_type']
                );

                $replaceKeys = Configure::readOrFail('Setting.mail.replaceValue.videoMeetingInfo.replaceKey');
                foreach ($replaceKeys as $replaceKey) {
                    $value = preg_replace(
                        '/' . preg_quote('%' . strtoupper($replaceKey) . '%', '/') . '/',
                        StringUtility::pregReplaceQuote(Hash::get($videoMeeting, $replaceKey)),
                        $value
                    );
                }
            }
        } elseif (((string)$code) === ((string)static::REPLACE_ADDITIONAL_DETAIL_URL)) {
            // 詳細画面のURLを生成
            $value = Router::url([
                'prefix' => 'User',
                'controller' => 'reservations',
                'action' => 'view',
                'id' => Hash::get($options, 'reservation.id'),
            ], true);
        } elseif (((string)$code) === ((string)static::REPLACE_ADDITIONAL_QR_CODE)) {
            if ((string)$options['event']['qr_code_flg'] === (string)Event::QR_CODE_FLG_ON) {
                // QRコードの生成
                if ($contentType === static::MAIL_FORMAT_CONTENTS_HTML) {
                    $templateHelper = new TemplateHelper(new View());
                    $value = $templateHelper->qrCodeImage(Hash::get($options, 'reservation.qr_code'), true);
                } else {
                    $value = '';
                }
            }
        } elseif (
            ((string)$code) === ((string)static::REPLACE_USER_CREATED)
            || ((string)$code) === ((string)static::REPLACE_RESERVE_CRATED)
        ) {
            $value = DateTimeUtility::convertToDateTimeObject($value);
            if (isset($value)) {
                $value = $value->format('Y/m/d H:i');
            }
        }

        if (is_array($value)) {
            $value = implode("\n", $value);
        }

        return $value;
    }
}
