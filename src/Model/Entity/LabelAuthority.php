<?php
declare(strict_types=1);

namespace App\Model\Entity;

use App\Model\AppEntity;

/**
 * LabelAuthority Entity
 *
 * @property int $id
 * @property int $label_id
 * @property int $user_authority_id
 * @property \Cake\I18n\FrozenTime $created
 * @property \Cake\I18n\FrozenTime $modified
 * @property \App\Model\Entity\Label $label
 * @property \App\Model\Entity\UserAuthority $user_authority
 */
class LabelAuthority extends AppEntity
{
    /**
     * @inheritDoc
     */
    protected $_accessible = [
        'label_id' => true,
        'user_authority_id' => true,
        'created' => false,
        'modified' => false,
        'label' => false,
        'user_authority' => false,
    ];
}
