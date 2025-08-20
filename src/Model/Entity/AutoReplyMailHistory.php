<?php
declare(strict_types=1);

namespace App\Model\Entity;

use App\Model\AppEntity;
use Cake\Datasource\EntityInterface;

/**
 * AutoReplyMailHistory Entity
 *
 * @property int $id
 * @property int $auto_reply_mail_id
 * @property int|null $user_id
 * @property int|null $reservation_id
 * @property int|null $waiting_cancellation_id
 * @property int|null $admin_id
 * @property string|null $user_mail
 * @property int $send_flg
 * @property \Cake\I18n\FrozenTime|null $send_timestamp
 * @property \Cake\I18n\FrozenTime|null $reminder_setting_timestamp
 * @property string|null $bounce_mail_token
 * @property string|null $data
 * @property \Cake\I18n\FrozenTime $created
 * @property \Cake\I18n\FrozenTime $modified
 *
 * @property \App\Model\Entity\AutoReplyMail $auto_reply_mail
 * @property \App\Model\Entity\User $user
 * @property \App\Model\Entity\Reservation $reservation
 * @property \App\Model\Entity\WaitingCancellation $waiting_cancellation
 * @property \App\Model\Entity\Admin $admin
 */
class AutoReplyMailHistory extends AppEntity
{
    public const SEND_FLG_OFF = 0;
    public const SEND_FLG_ON = 1;

    /**
     * @inheritDoc
     */
    protected $_accessible = [
        'auto_reply_mail_id' => true,
        'user_id' => true,
        'reservation_id' => true,
        'waiting_cancellation_id' => true,
        'admin_id' => true,
        'user_mail' => true,
        'send_flg' => false,
        'send_timestamp' => false,
        'reminder_setting_timestamp' => true,
        'bounce_mail_token' => true,
        'data' => true,
        'created' => false,
        'modified' => false,
        'auto_reply_mail' => false,
        'user' => false,
        'reservation' => false,
        'waiting_cancellation' => false,
        'admin' => false,
    ];

    /**
     * @inheritDoc
     */
    protected $_virtual = [
        'user_password',
    ];

    /**
     * @inheritDoc
     */
    protected $_hidden = [
        'user_password',
    ];

    /**
     * @var array|null
     */
    protected $dataEntity = null;

    /**
     * 送信済みの判定
     *
     * @return bool 判定結果
     */
    public function isSent()
    {
        if ((string)$this->get('send_flg') !== ((string)static::SEND_FLG_ON)) {
            return false;
        }

        return true;
    }

    /**
     * データのエンティティを設定
     *
     * @param array $dataEntity データ
     * @return void
     */
    public function setDataEntity(array $dataEntity)
    {
        $this->dataEntity = $dataEntity;
        $this->setDirty('data', true);
    }

    /**
     * データのゲッター
     *
     * @param array|null $data データ
     * @return array|null
     */
    protected function _getData($data)
    {
        if (!isset($this->dataEntity)) {
            return $data;
        }

        $result = [];
        foreach ($this->dataEntity as $key => $value) {
            if ($value instanceof EntityInterface) {
                $result[$key] = $value->toArray();
            }
        }
        unset($result['user']['password']);
        unset($result['oldUser']['password']);
        unset($result['user']['user_additions']);
        unset($result['oldUser']['user_additions']);

        $this->dataEntity = null;
        $this->_fields['data'] = $result;

        return $result;
    }
}
