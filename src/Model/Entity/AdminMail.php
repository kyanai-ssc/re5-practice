<?php
declare(strict_types=1);

namespace App\Model\Entity;

use App\Model\AppEntity;

/**
 * AdminMail Entity
 *
 * @property int $id
 * @property int $admin_id
 * @property string $mail
 * @property \Cake\I18n\FrozenTime $created
 * @property \Cake\I18n\FrozenTime $modified
 *
 * @property \App\Model\Entity\Admin $admin
 */
class AdminMail extends AppEntity
{
    /**
     * @inheritDoc
     */
    protected $_accessible = [
        'admin_id' => false,
        'mail' => true,
        'created' => false,
        'modified' => false,
        'admin' => false,
    ];
}
