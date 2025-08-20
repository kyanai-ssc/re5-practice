<?php
declare(strict_types=1);

namespace App\Model\Entity;

use App\Model\AppEntity;

/**
 * NewsAuthority Entity
 *
 * @property int $id
 * @property int $news_id
 * @property int $user_authority_id
 * @property \Cake\I18n\FrozenTime $created
 * @property \Cake\I18n\FrozenTime $modified
 *
 * @property \App\Model\Entity\News $news
 * @property \App\Model\Entity\UserAuthority $user_authority
 */
class NewsAuthority extends AppEntity
{
    /**
     * @inheritDoc
     */
    protected $_accessible = [
        'news_id' => true,
        'user_authority_id' => true,
        'created' => false,
        'modified' => false,
        'news' => false,
        'user_authority' => false,
    ];
}
