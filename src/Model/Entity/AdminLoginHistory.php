<?php
declare(strict_types=1);

namespace App\Model\Entity;

use App\Model\AppEntity;
use App\Model\Entity\Traits\LoginHistoryTrait;
use Cake\Core\Configure;

/**
 * AdminLoginHistory Entity
 *
 * @property int $id
 * @property int $admin_id
 * @property \Cake\I18n\FrozenTime|null $last_login_timestamp
 * @property int|null $error_count
 * @property \Cake\I18n\FrozenTime|null $last_error_timestamp
 * @property \Cake\I18n\FrozenTime|null $lock_timestamp
 * @property \Cake\I18n\FrozenTime $created
 * @property \Cake\I18n\FrozenTime $modified
 *
 * @property \App\Model\Entity\Admin $admin
 */
class AdminLoginHistory extends AppEntity
{
    use LoginHistoryTrait;

    /**
     * @inheritDoc
     */
    protected $_accessible = [
        'admin_id' => false,
        'last_login_timestamp' => false,
        'error_count' => false,
        'last_error_timestamp' => false,
        'lock_timestamp' => false,
        'created' => false,
        'modified' => false,
        'admin' => false,
    ];

    /**
     * @inheritDoc
     */
    protected function getLockConfig(): array
    {
        return Configure::readOrFail('Setting.auth.admin.lock');
    }
}
