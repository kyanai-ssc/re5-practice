<?php
declare(strict_types=1);

namespace App\Model\Entity;

use App\Model\AppEntity;
use Cake\I18n\FrozenTime;

/**
 * MailDelivery Entity
 *
 * @property int $id
 * @property int $send_type
 * @property \Cake\I18n\FrozenDate|null $send_date
 * @property \Cake\I18n\FrozenTime|null $send_time
 * @property string|null $from_mail_name
 * @property string $from_mail
 * @property string|null $reply_to
 * @property int $content_type
 * @property string|null $subject
 * @property string|null $contents
 * @property string $delivery_target
 * @property int $send_status
 * @property \Cake\I18n\FrozenTime|null $send_timestamp
 * @property \Cake\I18n\FrozenTime $created
 * @property \Cake\I18n\FrozenTime $modified
 *
 * @property \App\Model\Entity\MailDeliveryHistory[] $mail_delivery_histories
 */
class MailDelivery extends AppEntity
{
    /**
     * 送信タイプ：即時
     */
    public const SEND_TYPE_IMMEDIATELY = 1;

    /**
     * 送信タイプ：予約
     */
    public const SEND_TYPE_RESERVE = 2;

    /**
     * 配信ステータス：未配信
     */
    public const SEND_STATUS_NOT_SEND = 1;

    /**
     * 配信ステータス：配信中
     */
    public const SEND_STATUS_SENDING = 2;

    /**
     * 配信ステータス：配信済み
     */
    public const SEND_STATUS_SENT = 3;

    /**
     * 配信ステータス：キャンセル
     */
    public const SEND_STATUS_CANCEL = 4;

    /**
     * @inheritDoc
     */
    protected $_accessible = [
        'send_type' => true,
        'send_date' => true,
        'send_time' => true,
        'from_mail_name' => true,
        'from_mail' => true,
        'reply_to' => true,
        'content_type' => true,
        'subject' => true,
        'contents' => true,
        'delivery_target' => true,
        'send_status' => true,
        'send_timestamp' => true,
        'created' => false,
        'modified' => false,
        'mail_delivery_histories' => false,
    ];

    protected $_virtual = [
        'send_datetime',
    ];

    /**
     * send_dateとsend_timeから時間を生成
     *
     * @return \Cake\I18n\FrozenTime
     */
    protected function _getSendDatetime()
    {
        $date = $this->get('send_date');
        $time = $this->get('send_time');

        if (is_string($time)) {
            $datetime = new FrozenTime($this->formatDateToString($date) . ' ' . $this->formatTime24($time));
        } else {
            $datetime = new FrozenTime($this->formatDateToString($date) . ' ' . $this->formatTime24($time));
        }

        return $datetime;
    }

    /**
     * 配信キャンセル可能かどうか
     *
     * @return bool
     */
    public function canCancel()
    {
        return $this->get('send_status') === static::SEND_STATUS_NOT_SEND;
    }
}
