<?php
declare(strict_types=1);

namespace App\Model\Entity;

use App\Model\AppEntity;
use App\Utility\ArrayUtility;

/**
 * UserAuthority Entity
 *
 * @property int $id
 * @property string $name
 * @property string|null $access
 * @property int $form_pattern_id
 * @property string|null $calendar_type
 * @property int|null $calendar_type_default
 * @property int|null $login_name_form_item_id
 * @property int|null $reservation_limit_all
 * @property int|null $reservation_limit_future
 * @property int|null $reservation_limit_month
 * @property int|null $reservation_limit_day
 * @property int $guest_flg
 * @property int $default_flg
 * @property \Cake\I18n\FrozenTime $created
 * @property \Cake\I18n\FrozenTime $modified
 * @property int|null $charge_multiplier
 *
 * @property \App\Model\Entity\FormPattern $form_pattern
 * @property \App\Model\Entity\FormItem $form_item
 * @property \App\Model\Entity\AutoReplyMail[] $auto_reply_mails
 * @property \App\Model\Entity\NewsAuthority[] $news_authorities
 * @property \App\Model\Entity\User[] $users
 */
class UserAuthority extends AppEntity
{
    /**
     * デフォルトフラグ:オン
     */
    public const DEFAULT_FLG_ON = 1;

    /**
     * デフォルトフラグ:オフ
     */
    public const DEFAULT_FLG_OFF = 0;

    /**
     * ゲストフラグ：オン
     */
    public const GUEST_FLG_ON = 1;

    /**
     * ゲストフラグ：オフ
     */
    public const GUEST_FLG_OFF = 0;

    /**
     * 検索：全ての選択肢のキー
     */
    public const SELECT_ALL = -1;

    /**
     * デフォルト権限：ログイン
     */
    public const DEFAULT_AUTHORITY = 2;

    /**
     * @inheritDoc
     */
    protected $_accessible = [
        'name' => true,
        'access' => true,
        'form_pattern_id' => true,
        'calendar_type' => true,
        'calendar_type_default' => true,
        'login_name_form_item_id' => true,
        'reservation_limit_all' => true,
        'reservation_limit_future' => true,
        'reservation_limit_month' => true,
        'reservation_limit_day' => true,
        'guest_flg' => false,
        'default_flg' => false,
        'created' => false,
        'modified' => false,
        'form_pattern' => false,
        'form_item' => true,
        'auto_reply_mails' => false,
        'news_authorities' => false,
        'users' => false,
        'charge_multiplier' => true,
    ];

    /**
     * calendar_typeのミューテーター
     *
     * @param array|null $data 値
     * @return array|null
     */
    protected function _setCalendarType($data)
    {
        if (!is_array($data)) {
            return $data;
        }

        return ArrayUtility::arrayMapRecursive(function ($value) {
            return (int)$value;
        }, $data);
    }

    /**
     * 削除可否
     *
     * @return bool
     */
    public function cnaDelete()
    {
        if (
            $this->get('default_flg') === static::DEFAULT_FLG_OFF
            && count($this->get('users')) < 1
        ) {
            return true;
        }

        return false;
    }
}
