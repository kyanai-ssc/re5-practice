<?php
declare(strict_types=1);

namespace App\Model\Behavior;

use App\ORM\Behavior;
use App\Utility\CommonData\CommonDataTrait;
use ArrayObject;
use Cake\Datasource\EntityInterface;

/**
 * Class AccountLock
 */
class AccountLockBehavior extends Behavior
{
    use CommonDataTrait;

    protected $_defaultConfig = [
        'implementedFinders' => [],
        'implementedMethods' => [],
        'admin' => false,
    ];

    /**
     * beforeSave hook
     *
     * @param \Cake\Event\EventInterface $event イベント
     * @param \Cake\Datasource\EntityInterface $entity エンティティ
     * @param \ArrayObject $options オプション
     * @return void
     */
    public function beforeSave($event, EntityInterface $entity, ArrayObject $options)
    {
        if ($entity->isDirty('password')) {
            if ($this->getConfig('admin')) {
                /** @var \App\Model\Table\AdminLoginHistoriesTable $adminLoginHistory */
                $adminLoginHistory = $this->getTableLocator()->get('AdminLoginHistories');
                $adminLoginHistory->releaseAccountLock($entity->get('id'));
            }
        }
    }
}
