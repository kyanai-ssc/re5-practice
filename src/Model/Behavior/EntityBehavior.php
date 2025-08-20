<?php
declare(strict_types=1);

namespace App\Model\Behavior;

use App\ORM\Behavior;
use Cake\Datasource\EntityInterface;
use Cake\Utility\Hash;

/**
 * Class EntityBehavior
 */
class EntityBehavior extends Behavior
{
    protected $_defaultConfig = [
        'implementedFinders' => [],
        'implementedMethods' => [
            'extractEntity' => 'extractEntity',
            'excludeQueryByEntities' => 'excludeQueryByEntities',
            'setEntityErrors' => 'setEntityErrors',
            'checkEntityErrors' => 'checkEntityErrors',
            'sortEntities' => 'sortEntities',
        ],
    ];

    /**
     * 全エンティティを1次元配列で取得
     *
     * @param \Cake\Datasource\EntityInterface $entity エンティティー
     * @return array
     */
    public function extractEntity(EntityInterface $entity)
    {
        $extract = function ($entity, $table) use (&$extract) {
            $result = [$entity];
            foreach ($table->associations() as $association) {
                $associationEntities = $entity->get($association->getProperty());
                if (!is_array($associationEntities)) {
                    $associationEntities = [$associationEntities];
                }
                foreach ($associationEntities as $associationEntity) {
                    if ($associationEntity instanceof EntityInterface) {
                        $result = array_merge($result, call_user_func(
                            $extract,
                            $associationEntity,
                            $association->getTarget()
                        ));
                    }
                }
            }

            return $result;
        };

        return call_user_func($extract, $entity, $this->table());
    }

    /**
     * 指定のエンティティーを除外するクエリーを生成
     *
     * @param array|\Cake\Datasource\EntityInterface|null $entities エンティティー
     * @return array クエリー
     */
    public function excludeQueryByEntities($entities = null)
    {
        if (!isset($entities)) {
            return [];
        }
        if (!is_array($entities)) {
            $entities = [$entities];
        }

        $condition = [];
        foreach ($entities as $entity) {
            $table = $this->getTableLocator()->get($entity->getSource());
            $primaryKeys = (array)$table->getPrimaryKey();
            if ($entity->has($primaryKeys)) {
                $excludeCondition = [];
                foreach ($primaryKeys as $primaryKey) {
                    $excludeCondition[$table->aliasField($primaryKey) . ' <>'] = $entity->get($primaryKey);
                }
                $condition[] = ['OR' => $excludeCondition];
            }
        }

        return $condition;
    }

    /**
     * エンティティーへ再帰的にエラーを設定
     *
     * @param \Cake\Datasource\EntityInterface $entity エンティティー
     * @param array $errors エラー
     * @param bool $overwrite 上書き設定
     * @return void
     * @deprecated
     */
    public function setEntityErrors(EntityInterface $entity, array $errors, bool $overwrite = false)
    {
        $setErrors = function ($table, $entity, $errors, $overwrite) use (&$setErrors) {
            $associations = [];
            foreach ($table->associations() as $association) {
                $associations[$association->getProperty()] = $association->getTarget();
            }

            foreach ($errors as $key => $value) {
                if (isset($associations[$key]) && is_array($value)) {
                    $associationEntities = $entity->get($key);
                    if (!is_array($associationEntities)) {
                        $associationEntities = [$associationEntities];
                    }

                    foreach ($associationEntities as $associationIndex => $associationEntity) {
                        $associationErrors = Hash::get($value, $associationIndex);
                        if ($associationEntity instanceof EntityInterface && is_array($associationErrors)) {
                            call_user_func(
                                $setErrors,
                                $associations[$key],
                                $associationEntity,
                                $associationErrors,
                                $overwrite
                            );
                        }
                        unset($errors[$key][$associationIndex]);
                    }
                    if (count($errors[$key]) <= 0) {
                        unset($errors[$key]);
                    }
                }
            }

            $entity->setErrors($errors, $overwrite);
        };

        call_user_func($setErrors, $this->table(), $entity, $errors, $overwrite);
    }

    /**
     * newEntities,pathEntities内にエラーが存在するかどうか
     *
     * @param array $entities エンティティ
     * @return bool
     */
    public function checkEntityErrors(array $entities)
    {
        $errors = false;
        foreach ($entities as $entity) {
            if ($entity->getErrors()) {
                $errors = true;
                break;
            }
        }

        return $errors;
    }

    /**
     * 複数のentityを特定の項目でソート
     *
     * @param \Cake\Datasource\EntityInterface[] $entities エンティティ
     * @param string $sortName ソートキー
     * @param bool $rebalance キーの振り直しを実行するかどうか
     * @return \Cake\Datasource\EntityInterface[]
     */
    public function sortEntities($entities, $sortName = 'sort_no', $rebalance = false)
    {
        uasort($entities, function ($a, $b) use ($sortName) {
            return $a[$sortName] - $b[$sortName];
        });

        if ($rebalance) {
            $entities = array_values($entities);
        }

        return $entities;
    }
}
