<?php
declare(strict_types=1);

namespace App\Model\Entity;

use App\Model\AppEntity;

/**
 * FormPattern Entity
 *
 * @property int $id
 * @property int $form_type
 * @property string $name
 * @property string|null $remark
 * @property int $default_flg
 * @property \Cake\I18n\FrozenTime $created
 * @property \Cake\I18n\FrozenTime $modified
 *
 * @property \App\Model\Entity\Event[] $events
 * @property \App\Model\Entity\FormPatternDisplayType[] $form_pattern_display_types
 * @property \App\Model\Entity\FormPatternOption[] $form_pattern_options
 * @property \App\Model\Entity\UserAuthority[] $user_authorities
 */
class FormPattern extends AppEntity
{
    /**
     * デフォルトフラグON
     */
    public const DEFAULT_FLG_ON = 1;

    /**
     * デフォルトフラグOFF
     */
    public const DEFAULT_FLG_OFF = 0;

    /**
     * @inheritDoc
     */
    protected $_accessible = [
        'form_type' => true,
        'name' => true,
        'remark' => true,
        'default_flg' => false,
        'created' => false,
        'modified' => false,
        'events' => false,
        'form_pattern_display_types' => true,
        'form_pattern_options' => true,
        'user_authorities' => false,
    ];

    /**
     * 削除可能チェック
     *
     * @return bool 判定結果
     */
    public function canDelete()
    {
        $query = null;
        if ($this->get('form_type') === FormGroup::FORM_TYPE_RESERVATION) {
            /** @var \App\Model\Table\EventsTable $eventTable */
            $eventTable = $this->getTableLocator()->get('Events');
            $query = $eventTable->find('all');
        } else {
            /** @var \App\Model\Table\UserAuthoritiesTable $userAuthoritiesTable */
            $userAuthoritiesTable = $this->getTableLocator()->get('UserAuthorities');
            $query = $userAuthoritiesTable->find('all');
        }

        $query->select(['id'])->where([
            'form_pattern_id' => $this->get('id'),
        ]);

        if ($query->count() >= 1 || $this->get('default_flg') === static::DEFAULT_FLG_ON) {
            return false;
        }

        return true;
    }
}
