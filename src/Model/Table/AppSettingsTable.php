<?php
declare(strict_types=1);

namespace App\Model\Table;

use App\Model\AppTable;
use App\Model\Entity\AppSetting;
use Cake\Utility\Security;

/**
 * AppSettings Model
 * *
 *
 * @method \App\Model\Entity\AppSetting newEmptyEntity()
 * @method \App\Model\Entity\AppSetting newEntity(array $data, array $options = [])
 * @method \App\Model\Entity\AppSetting[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\AppSetting get($primaryKey, $options = [])
 * @method \App\Model\Entity\AppSetting findOrCreate($search, ?callable $callback = null, $options = [])
 * @method \App\Model\Entity\AppSetting patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\AppSetting[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\AppSetting|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\AppSetting saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\AppSetting[]|\Cake\Datasource\ResultSetInterface|false saveMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\AppSetting[]|\Cake\Datasource\ResultSetInterface saveManyOrFail(iterable $entities, $options = [])
 * @method \App\Model\Entity\AppSetting[]|\Cake\Datasource\ResultSetInterface|false deleteMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\AppSetting[]|\Cake\Datasource\ResultSetInterface deleteManyOrFail(iterable $entities, $options = [])
 */
class AppSettingsTable extends AppTable
{
    public const API_SECRET_LENGTH = 16;

    /**
     * API Secretを生成
     *
     * @return string
     */
    public function createApiSecret()
    {
        return Security::randomString(static::API_SECRET_LENGTH);
    }

    /**
     * アプリ設定
     *
     * @return \App\Model\Entity\AppSetting|null
     */
    public function getAppSetting()
    {
        $query = $this->find()->select([
            'id',
            'api_secret',
        ]);
        $entity = $query->first();
        if (!($entity instanceof AppSetting)) {
            return null;
        }

        return $entity;
    }
}
