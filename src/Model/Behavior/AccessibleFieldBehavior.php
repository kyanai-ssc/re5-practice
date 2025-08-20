<?php
declare(strict_types=1);

namespace App\Model\Behavior;

use App\ORM\Behavior;
use ArrayObject;
use Cake\Datasource\EntityInterface;
use Cake\Event\EventInterface;
use Cake\Utility\Hash;

/**
 * Class AccessibleFieldBehavior
 */
class AccessibleFieldBehavior extends Behavior
{
    protected $_defaultConfig = [
        'implementedFinders' => [],
        'implementedMethods' => [],
    ];

    /**
     * Model.beforeSaveイベント
     *
     * @param \Cake\Event\EventInterface $event イベント
     * @param \Cake\Datasource\EntityInterface $entity エンティティ
     * @param \ArrayObject $options オプション
     * @return void
     */
    public function beforeSave(EventInterface $event, EntityInterface $entity, ArrayObject $options)
    {
        if (Hash::get($options, 'accessibleDirty', false)) {
            $this->setAccessibleDirty($entity, [
                'withPrimaryKey' => false,
                'withAssociations' => false,
            ]);
        }
    }

    /**
     * アクセス可能なフィールドを変更済みとしてマーク
     *
     * @param \Cake\Datasource\EntityInterface $entity エンティティー
     * @param array $options オプション
     * @return void
     */
    protected function setAccessibleDirty(EntityInterface $entity, array $options = [])
    {
        $setDirty = function ($entity, $accessibleFields) use (&$setDirty) {
            foreach ($accessibleFields as $field => $nested) {
                if (!$entity->isDirty($field)) {
                    $entity->setDirty($field, true);
                }

                // 関連データをマーク
                if (is_array($nested)) {
                    $associationEntities = $entity->get($field);
                    if (!is_array($associationEntities)) {
                        $associationEntities = [$associationEntities];
                    }
                    foreach ($associationEntities as $associationIndex => $associationEntity) {
                        if (isset($accessibleFields[$field][$associationIndex])) {
                            call_user_func($setDirty, $associationEntity, $accessibleFields[$field][$associationIndex]);
                        }
                    }
                }
            }
        };

        $options += [
            'withPrimaryKey' => false,
            'withAssociations' => true,
        ];

        call_user_func($setDirty, $entity, $this->getAccessibleFields($entity, $options));
    }

    /**
     * アクセス可能なフィールドを取得
     *
     * @param \Cake\Datasource\EntityInterface $entity エンティティー
     * @param array $options オプション
     * @return array フィールド
     */
    protected function getAccessibleFields($entity, $options = [])
    {
        $getFields = function ($table, $entity, $options) use (&$getFields) {
            $fields = [];

            $withPrimaryKey = Hash::get($options, 'withPrimaryKey', false);
            if ($withPrimaryKey) {
                foreach ((array)$table->getPrimaryKey() as $primaryKey) {
                    $fields[$primaryKey] = $primaryKey;
                }
            }

            foreach ($table->getSchema()->columns() as $column) {
                if ($entity->isAccessible($column)) {
                    $fields[$column] = $column;
                }
            }

            $withAssociations = Hash::get($options, 'withAssociations', false);
            if (!$withAssociations) {
                return $fields;
            }

            // 関連データのフィールドを取得
            foreach ($table->associations() as $association) {
                $associationTable = $association->getTarget();
                $associationKey = $association->getProperty();

                if ($entity->isAccessible($associationKey)) {
                    $associationEntities = $entity->get($associationKey);
                    if (!is_array($associationEntities)) {
                        $associationEntities = [$associationEntities];
                    }

                    $fields[$associationKey] = [];
                    foreach ($associationEntities as $associationIndex => $associationEntity) {
                        if ($associationEntity instanceof EntityInterface) {
                            $fields[$associationKey][$associationIndex] = call_user_func(
                                $getFields,
                                $associationTable,
                                $associationEntity,
                                $options
                            );
                        }
                    }
                }
            }

            return $fields;
        };

        return call_user_func($getFields, $this->table(), $entity, $options);
    }
}
