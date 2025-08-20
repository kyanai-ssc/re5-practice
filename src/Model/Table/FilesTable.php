<?php
declare(strict_types=1);

namespace App\Model\Table;

use App\Locale\Message;
use App\Model\AppTable;
use App\Utility\FileUtility;
use Cake\Core\Configure;
use Cake\Datasource\EntityInterface;
use Cake\Event\EventInterface;
use Cake\ORM\Query;
use Cake\Utility\Hash;
use Cake\Validation\Validator;

/**
 * Files Model
 *
 * @method \App\Model\Entity\File newEmptyEntity()
 * @method \App\Model\Entity\File newEntity(array $data, array $options = [])
 * @method \App\Model\Entity\File[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\File get($primaryKey, $options = [])
 * @method \App\Model\Entity\File findOrCreate($search, ?callable $callback = null, $options = [])
 * @method \App\Model\Entity\File patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\File[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\File|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\File saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\File[]|\Cake\Datasource\ResultSetInterface|false saveMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\File[]|\Cake\Datasource\ResultSetInterface saveManyOrFail(iterable $entities, $options = [])
 * @method \App\Model\Entity\File[]|\Cake\Datasource\ResultSetInterface|false deleteMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\File[]|\Cake\Datasource\ResultSetInterface deleteManyOrFail(iterable $entities, $options = [])
 */
class FilesTable extends AppTable
{
    public const FILE_NAME_MAX = 30;
    public const DESCRIPTION_MAX = 100;

    /**
     * @inheritDoc
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->belongsTo('FileGroups', [
            'foreignKey' => 'file_group_id',
            'joinType' => 'INNER',
        ]);
    }

    /**
     * @inheritDoc
     */
    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->requirePresence('file_name', 'create', __(Message::ERROR_NOT_EMPTY))
            ->allowEmptyFile('file_name', __(Message::ERROR_NOT_EMPTY), 'update')
            ->add('file_name', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'file' => [
                    'rule' => ['isFile'],
                    'last' => true,
                    'message' => __(Message::ERROR_IS_FILE),
                    'on' => 'create',
                ],
                'maxLength' => [
                    'rule' => ['maxLength', static::FILE_NAME_MAX],
                    'last' => true,
                    'message' => __(Message::ERROR_MAX_LENGTH, static::FILE_NAME_MAX),
                    'on' => 'create',
                ],
            ]);

        $validator
            ->requirePresence('description', true, __(Message::ERROR_NOT_EMPTY))
            ->allowEmptyString('description')
            ->add('description', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'maxLength' => [
                    'rule' => ['maxLength', static::DESCRIPTION_MAX],
                    'last' => true,
                    'message' => __(Message::ERROR_MAX_LENGTH, static::DESCRIPTION_MAX),
                ],
            ]);

        return $validator;
    }

    /**
     * @inheritDoc
     */
    protected function searchConfiguration()
    {
        $this->searchManager()
            ->value('file_group_id', [
                'multiValue' => false,
            ]);
    }

    /**
     * 一覧のファインダー
     *
     * @param \Cake\ORM\Query $query The query to find with
     * @param array $options The options to use for the find
     * @return \Cake\ORM\Query The query builder
     */
    public function findSearchList(Query $query, array $options)
    {
        $query->contain([
            'FileGroups' => [
                'fields' => [
                    'id',
                    'file_group_name' => 'name',
                    'directory',
                ],
            ],
        ]);

        $sort = Hash::get($options, 'inputs.sort', 'id');
        $direction = strtoupper(Hash::get($options, 'inputs.direction', 'asc'));
        $query
            ->order([
                    $sort => $direction,
                ] + [
                    'Files.id' => $direction,
                ], true);

        return $this->callFinder('search', $query, ['search' => Hash::get($options, 'inputs', [])]);
    }

    /**
     * Model.afterDeleteイベント
     *
     * @param \Cake\Event\EventInterface $event イベント
     * @param \Cake\Datasource\EntityInterface $entity エンティティ
     * @param \ArrayObject $options オプション
     * @return void
     */
    public function afterDelete(EventInterface $event, EntityInterface $entity, \ArrayObject $options)
    {
        if (Hash::get($options, 'fileDelete', false)) {
            $fileGroups = $entity->get('file_group');
            $directory = UPLOAD_FILES . $fileGroups['directory'];
            FileUtility::deleteFile($directory . DS . $entity->get('file_name') . '.' . $entity->get('file_type'));
        }
    }

    /**
     * 削除のファインダー
     *
     * @param \Cake\ORM\Query $query The query to find with
     * @param array $options The options to use for the find
     * @return \Cake\ORM\Query The query builder
     */
    public function findDelete(Query $query, array $options)
    {
        $query->select(['id', 'file_group_id', 'file_name', 'file_type'])
            ->contain([
                'FileGroups' => [
                    'fields' => [
                        'id',
                        'directory',
                    ],
                ],
            ]);

        return $query;
    }

    /**
     * ファイル容量のファインダー
     *
     * @param \Cake\ORM\Query $query The query to find with
     * @param array $options The options to use for the find
     * @return \Cake\ORM\Query The query builder
     */
    public function findSizeCount(Query $query, array $options)
    {
        $query->select(['sum' => $query->func()->sum('size')]);

        $excludeIds = Hash::get($options, 'excludeIds', []);
        if (!empty($excludeIds)) {
            $query->where(['id NOT IN' => $excludeIds]);
        }

        return $query;
    }

    /**
     * ファイルアップロード上限チェック
     *
     * @param int $add 追加されるサイズ
     * @param array $excludeIds 除外するID
     * @return bool
     */
    public function chkClientUploadLimit(int $add = 0, array $excludeIds = [])
    {
        $size = $this->find('sizeCount', ['excludeIds' => $excludeIds])->first();

        /** @var \App\Model\Table\SystemSettingsTable $system */
        $system = $this->getTableLocator()->get('SystemSettings');
        $contractPlan = $system->getData()->get('contract_plan');
        $limit = Configure::read('Master.systemSetting.restriction.file.' . $contractPlan);

        if (!is_null($limit) && isset($size) && $limit < $size['sum'] + $add) {
            return false;
        }

        return true;
    }

    /**
     * ファイル数のファインダー
     *
     * @param \Cake\ORM\Query $query The query to find with
     * @param array $options The options to use for the find
     * @return \Cake\ORM\Query The query builder
     */
    public function findFilesCount(Query $query, array $options)
    {
        $query->select(['id'])->where(['file_group_id' => Hash::get($options, 'fileGroupId')]);

        return $query;
    }
}
