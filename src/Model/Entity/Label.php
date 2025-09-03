<?php
declare(strict_types=1);

namespace App\Model\Entity;

use App\Model\AppEntity;

/**
 * Label Entity
 *
 * @property int $id
 * @property int|null $parent_id
 * @property string $name
 * @property int|null $sort_no
 * @property int $public_flg
 * @property \Cake\I18n\FrozenTime $created
 * @property \Cake\I18n\FrozenTime $modified
 *
 * @property \App\Model\Entity\Label $parent_label
 * @property \App\Model\Entity\Admin[] $admins
 * @property \App\Model\Entity\AutoReplyMail[] $auto_reply_mails
 * @property \App\Model\Entity\Event[] $events
 * @property \App\Model\Entity\Label[] $child_labels
 * @property \App\Model\Entity\News[] $news
 * @property \App\Model\Entity\LabelAuthority[] $label_authorities
 */
class Label extends AppEntity
{
    /**
     * 公開フラグ：オン
     */
    public const PUBLIC_FLG_ON = 1;

    /**
     * 公開フラグ：オフ
     */
    public const PUBLIC_FLG_OFF = 0;

    /**
     * 検索：全階層
     */
    public const DISPLAY_LEVEL_ALL = 1;

    /**
     * 検索：1階層
     */
    public const DISPLAY_LEVEL_ONE = 2;

    /**
     * @inheritDoc
     */
    protected $_accessible = [
        'parent_id' => true,
        'name' => true,
        'sort_no' => true,
        'public_flg' => true,
        'created' => false,
        'modified' => false,
        'parent_label' => false,
        'admins' => false,
        'auto_reply_mails' => false,
        'events' => false,
        'child_labels' => false,
        'news' => false,
        'label_authorities' => true,
    ];

    /**
     * 削除可否
     *
     * @return bool
     */
    public function canDelete()
    {
        if (
            !empty($this->get('child_labels'))
            || !empty($this->get('news'))
            || !empty($this->get('auto_reply_mails'))
            || !empty($this->get('events'))
            || !empty($this->get('admins'))
        ) {
            return false;
        }

        return true;
    }
}
