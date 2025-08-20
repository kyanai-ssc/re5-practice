<?php
declare(strict_types=1);

namespace App\Model\Entity;

use App\Model\AppEntity;

/**
 * BounceMailHistory Entity
 *
 * @property int $id
 * @property int $bounce_mail_id
 * @property int|null $user_id
 * @property string|null $contents
 * @property \Cake\I18n\FrozenTime $created
 * @property \Cake\I18n\FrozenTime $modified
 *
 * @property \App\Model\Entity\BounceMail $bounce_mail
 * @property \App\Model\Entity\User $user
 */
class BounceMailHistory extends AppEntity
{
    /**
     * @inheritDoc
     */
    protected $_accessible = [
        'bounce_mail_id' => true,
        'user_id' => true,
        'contents' => true,
        'created' => true,
        'modified' => true,
        'bounce_mail' => true,
        'user' => true,
    ];
}
