<?php
declare(strict_types=1);

namespace App\Model\Behavior;

use App\ORM\Behavior;
use ArrayObject;
use Cake\Datasource\EntityInterface;
use Cake\Event\EventInterface;
use Cake\ORM\RulesChecker;
use Cake\Utility\Hash;

/**
 * Class RuleCheckingBehavior
 */
class RuleCheckingBehavior extends Behavior
{
    protected $_defaultConfig = [
        'implementedFinders' => [],
        'implementedMethods' => [],
    ];

    /**
     * Model.afterMarshalイベント
     *
     * @param \Cake\Event\EventInterface $event イベント
     * @param \Cake\Datasource\EntityInterface $entity エンティティ
     * @param \ArrayObject $data データ
     * @param \ArrayObject $options オプション
     * @return void
     */
    public function afterMarshal(
        EventInterface $event,
        EntityInterface $entity,
        ArrayObject $data,
        ArrayObject $options
    ) {
        if (Hash::get($options, 'checkRules', false)) {
            $mode = RulesChecker::CREATE;
            if (!$entity->isNew()) {
                $mode = RulesChecker::UPDATE;
            }
            $this->table()->checkRules($entity, $mode, $options);
        }
    }

    /**
     * Model.beforeSaveManyイベント
     *
     * @param \Cake\Event\EventInterface $event イベント
     * @param array $entities エンティティ
     * @param \ArrayObject $options オプション
     * @return bool
     */
    public function beforeSaveMany(EventInterface $event, array $entities, $options)
    {
        $result = true;
        if (Hash::get($options, 'checkRules', true)) {
            foreach ($entities as $entity) {
                $mode = RulesChecker::CREATE;
                if (!$entity->isNew()) {
                    $mode = RulesChecker::UPDATE;
                }
                if (!$this->table()->checkRules($entity, $mode, $options)) {
                    $result = false;
                }
            }
            $options->offsetSet('checkRules', false);
        }

        return $result;
    }
}
