<?php
declare(strict_types=1);

namespace App\Model\Entity;

use App\Model\AppEntity;
use Cake\Core\Configure;
use Cake\Utility\Hash;

/**
 * AutoReplyMail Entity
 *
 * @property int $id
 * @property int $type
 * @property int|null $user_authority_id
 * @property int|null $label_id
 * @property int $except_sub_label_flg
 * @property string|null $from_mail_name
 * @property string $from_mail
 * @property string|null $reply_to
 * @property int $content_type
 * @property string|null $subject
 * @property string|null $header
 * @property string|null $contents
 * @property string|null $footer
 * @property int $admin_operation_mail_flg
 * @property string|null $header_admin
 * @property string|null $contents_admin
 * @property string|null $footer_admin
 * @property \Cake\I18n\FrozenTime $created
 * @property \Cake\I18n\FrozenTime $modified
 *
 * @property \App\Model\Entity\UserAuthority $user_authority
 * @property \App\Model\Entity\Label $label
 * @property \App\Model\Entity\AutoReplyMailHistory[] $auto_reply_mail_histories
 * @property \App\Model\Entity\AutoReplyMailStatus[] $auto_reply_mail_statuses
 */
class AutoReplyMail extends AppEntity
{
    /**
     * タイプ：会員登録
     */
    public const TYPE_USER_ADD = 1;

    /**
     * タイプ：会員変更
     */
    public const TYPE_USER_EDIT = 2;

    /**
     * タイプ：
     */
    public const TYPE_USER_DELETE = 3;

    /**
     * タイプ：
     */
    public const TYPE_ID_REMINDER = 4;

    /**
     * タイプ：
     */
    public const TYPE_PASSWORD_REMINDER = 5;

    /**
     * タイプ：予約登録
     */
    public const TYPE_RESERVE_ADD = 6;

    /**
     * タイプ：予約変更
     */
    public const TYPE_RESERVE_EDIT = 7;

    /**
     * タイプ：予約キャンセル
     */
    public const TYPE_RESERVE_CANCEL = 8;

    /**
     * タイプ：キャンセル待ち
     */
    public const TYPE_RESERVE_CANCELWAIT = 9;

    /**
     * タイプ：予約リマインダー
     */
    public const TYPE_RESERVE_REMINDER = 10;

    /**
     * タイプ：オプトイン(会員登録)
     */
    public const TYPE_OPTIN_USER = 11;

    /**
     * タイプ：オプトイン(予約登録)
     */
    public const TYPE_OPTIN_RESERVE = 12;

    /**
     * タイプ：予約ステータス更新
     */
    public const TYPE_STATUS_UPDATE = 13;

    /**
     * タイプ：会員登録しないで予約認証
     */
    public const TYPE_NOT_MEMBER_LOGIN = 14;

    /**
     * タイプ：キャンセル待ち通知解除
     */
    public const TYPE_RESERVE_CANCELWAIT_RELEASE = 15;

    /**
     * タイプ：利用終了リマインダー
     */
    public const TYPE_RESERVE_REMINDER_CLOSE = 16;

    /**
     * タイプ：メールアドレス変更認証
     */
    public const TYPE_OPTIN_MAIL_EDIT = 17;

    /**
     * タイプ：繰り返し予約：予約登録
     */
    public const TYPE_REPEAT_RESERVATION = 18;

    /**
     * 下位ラベル除外フラグ OFF
     */
    public const EXCEPT_SUB_LABEL_FLG_OFF = 0;

    /**
     * 下位ラベル除外フラグ ON
     */
    public const EXCEPT_SUB_LABEL_FLG_ON = 1;

    /**
     * 管理者用メールフラグ OFF
     */
    public const ADMIN_OPERATION_MAIL_FLG_OFF = 0;

    /**
     * 管理者用メールフラグ ON
     */
    public const ADMIN_OPERATION_MAIL_FLG_ON = 1;

    /**
     * @inheritDoc
     */
    protected $_accessible = [
        'type' => true,
        'user_authority_id' => true,
        'label_id' => true,
        'except_sub_label_flg' => true,
        'from_mail_name' => true,
        'from_mail' => true,
        'reply_to' => true,
        'content_type' => true,
        'subject' => true,
        'header' => true,
        'contents' => true,
        'footer' => true,
        'admin_operation_mail_flg' => true,
        'header_admin' => true,
        'contents_admin' => true,
        'footer_admin' => true,
        'created' => false,
        'modified' => false,
        'user_authority' => true,
        'label' => true,
        'auto_reply_mail_histories' => false,
        'auto_reply_mail_statuses' => true,
    ];

    /**
     * 管理者用メールの判定
     *
     * @return bool 判定結果
     */
    public function hasAdminOperationMail()
    {
        if ((string)$this->get('admin_operation_mail_flg') !== ((string)static::ADMIN_OPERATION_MAIL_FLG_ON)) {
            return false;
        }

        return true;
    }

    /**
     * メール本文を取得
     *
     * @param int|null $adminId 操作管理者ID
     * @return string 本文
     */
    public function getMailMessage($adminId = null)
    {
        $delimiter = Configure::readOrFail('Master.common.mailFormatDelimiter.' . $this->get('content_type'));
        $message = null;
        if (((string)$adminId) !== '' && $this->hasAdminOperationMail()) {
            $message = implode($delimiter, [
                $this->get('header_admin'),
                $this->get('contents_admin'),
                $this->get('footer_admin'),
            ]);
        } else {
            $message = implode($delimiter, [
                $this->get('header'),
                $this->get('contents'),
                $this->get('footer'),
            ]);
        }

        return $message;
    }

    /**
     * 削除可否
     *
     * @return bool
     */
    public function canDelete(): bool
    {
        $canDelete = Configure::readOrFail('Master.autoReplyMail.canDelete');

        return Hash::get($canDelete, $this->get('type'), true);
    }

    /**
     * タイプをテキスト表示とするかどうか
     *
     * @return bool
     */
    public function isTypeDisplayText(): bool
    {
        if ($this->isNew() || $this->canDelete() || $this->isDirty('type')) {
            return false;
        }

        return true;
    }
}
