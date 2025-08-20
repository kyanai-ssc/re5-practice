<?php
declare(strict_types=1);

namespace App\Model\Table;

use App\Model\AppTable;
use Cake\ORM\Query;
use Cake\Utility\Hash;

/**
 * UserAdditions Model
 *
 * @method \App\Model\Entity\UserAddition newEmptyEntity()
 * @method \App\Model\Entity\UserAddition newEntity(array $data, array $options = [])
 * @method \App\Model\Entity\UserAddition[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\UserAddition get($primaryKey, $options = [])
 * @method \App\Model\Entity\UserAddition findOrCreate($search, ?callable $callback = null, $options = [])
 * @method \App\Model\Entity\UserAddition patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\UserAddition[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\UserAddition|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\UserAddition saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\UserAddition[]|\Cake\Datasource\ResultSetInterface|false saveMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\UserAddition[]|\Cake\Datasource\ResultSetInterface saveManyOrFail(iterable $entities, $options = [])
 * @method \App\Model\Entity\UserAddition[]|\Cake\Datasource\ResultSetInterface|false deleteMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\UserAddition[]|\Cake\Datasource\ResultSetInterface deleteManyOrFail(iterable $entities, $options = [])
 */
class UserAdditionsTable extends AppTable
{
    /**
     * @inheritDoc
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->belongsTo('Users', [
            'foreignKey' => 'user_id',
            'joinType' => 'INNER',
        ]);
        $this->belongsTo('FormItems', [
            'foreignKey' => 'form_item_id',
            'joinType' => 'INNER',
        ]);
    }

    /**
     * ログイン情報表示時のファインダー
     *
     * @param \Cake\ORM\Query $query The query to find with
     * @param array $options The options to use for the find
     * @return \Cake\ORM\Query The query builder
     */
    public function findLoginName(Query $query, array $options)
    {
        $query->select([
            'id',
            'user_id',
            'form_item_id',
            'value',
        ]);

        $userId = Hash::get($options, 'inputs.user_id');
        $formItemId = Hash::get($options, 'inputs.form_item_id');
        $query->where([
            'UserAdditions.user_id' => $userId,
            'UserAdditions.form_item_id' => $formItemId,
        ]);

        return $query;
    }

    /**
     * フォーム種別を指定して削除を行う
     *
     * @param int $formType フォーム種別
     * @param array $excludeFormItemId 除外するフォーム項目ID
     * @return void
     */
    public function deleteByFormType(int $formType, ?array $excludeFormItemId = null)
    {
        $formItemsQuery = $this->getAssociation('FormItems')->find();
        $formItemsQuery->select(['FormItems.id']);
        $formItemsQuery->matching('FormGroups', function ($formGroupsQuery) use ($formType) {
            $formGroupsQuery->where(['FormGroups.form_type' => $formType]);

            return $formGroupsQuery;
        });

        $where = [
            'UserAdditions.form_item_id IN' => $formItemsQuery,
        ];
        if (!empty($excludeFormItemId)) {
            $where['UserAdditions.form_item_id NOT IN'] = (array)$excludeFormItemId;
        }
        $this->deleteAll($where);
    }
}
