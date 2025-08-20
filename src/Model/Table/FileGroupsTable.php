<?php
declare(strict_types=1);

namespace App\Model\Table;

use App\Locale\Message;
use App\Model\AppTable;
use App\Utility\ArrayUtility;
use App\Utility\FileUtility;
use ArrayObject;
use Cake\Core\Configure;
use Cake\Datasource\EntityInterface;
use Cake\Event\EventInterface;
use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\Validation\Validation;
use Cake\Validation\Validator;

/**
 * FileGroups Model
 *
 * @method \App\Model\Entity\FileGroup newEmptyEntity()
 * @method \App\Model\Entity\FileGroup newEntity(array $data, array $options = [])
 * @method \App\Model\Entity\FileGroup[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\FileGroup get($primaryKey, $options = [])
 * @method \App\Model\Entity\FileGroup findOrCreate($search, ?callable $callback = null, $options = [])
 * @method \App\Model\Entity\FileGroup patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\FileGroup[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\FileGroup|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\FileGroup saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\FileGroup[]|\Cake\Datasource\ResultSetInterface|false saveMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\FileGroup[]|\Cake\Datasource\ResultSetInterface saveManyOrFail(iterable $entities, $options = [])
 * @method \App\Model\Entity\FileGroup[]|\Cake\Datasource\ResultSetInterface|false deleteMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\FileGroup[]|\Cake\Datasource\ResultSetInterface deleteManyOrFail(iterable $entities, $options = [])
 */
class FileGroupsTable extends AppTable
{
    public const NAME_MAX = 100;
    public const DIR_NAME_MAX = 20;

    /**
     * @inheritDoc
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->hasMany('Files', [
            'foreignKey' => 'file_group_id',
            'saveStrategy' => 'replace',
            'dependent' => true,
        ]);

        $this->getBehavior('AdminOperationLog')->setConfig([
            'saveOperation' => true,
        ]);
    }

    /**
     * @inheritDoc
     */
    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->requirePresence('name', true, __(Message::ERROR_NOT_EMPTY))
            ->allowEmptyString('name', __(Message::ERROR_NOT_EMPTY), false)
            ->add('name', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'maxLength' => [
                    'rule' => ['maxLength', static::NAME_MAX],
                    'last' => true,
                    'message' => __(Message::ERROR_MAX_LENGTH, static::NAME_MAX),
                ],
            ]);

        $validator
            ->requirePresence('directory', true, __(Message::ERROR_NOT_EMPTY))
            ->allowEmptyString('directory', __(Message::ERROR_NOT_EMPTY), false)
            ->add('directory', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'isDirectory' => [
                    'rule' => ['isDirectory'],
                    'last' => true,
                    'message' => __(Message::ERROR_IS_DIRECTORY),
                ],
                'maxLength' => [
                    'rule' => ['maxLength', static::DIR_NAME_MAX],
                    'last' => true,
                    'message' => __(Message::ERROR_MAX_LENGTH, static::DIR_NAME_MAX),
                ],
                'exists' => [
                    'rule' => ['dirExists', UPLOAD_FILES],
                    'last' => true,
                    'message' => __(Message::ERROR_EXISTS),
                    'on' => 'create',
                ],
            ]);

        $validator
            ->requirePresence('files', true, __(Message::ERROR_NOT_EMPTY))
            ->array('files', __(Message::ERROR_INVALID_VALUE))
            ->allowEmptyArray('files', __(Message::ERROR_NOT_EMPTY), false);

        return $validator;
    }

    /**
     * @inheritDoc
     */
    public function buildRules(RulesChecker $rules): RulesChecker
    {
        $rules->add(function ($entity, $options) {
            return $this->checkDuplicateFileName($entity, $options['upload']);
        }, 'checkDuplicateFileName');

        return $rules;
    }

    /**
     * Model.beforeSaveイベント
     *
     * @param \Cake\Event\EventInterface $event イベント
     * @param \Cake\Datasource\EntityInterface $entity エンティティ
     * @param \ArrayObject $options オプション
     * @return void|bool
     */
    public function beforeSave(EventInterface $event, EntityInterface $entity, ArrayObject $options)
    {
        $files = $entity->get('files');
        $uploadFiles = $options['upload'];
        $addSize = 0;
        $ids = [];
        foreach ($files as $file) {
            $fileIndex = $file['index'];

            if (!$file->isNew()) {
                $ids[] = $file->get('id');
            }

            if (isset($uploadFiles[$fileIndex])) {
                if (!$file->isNew() && !$uploadFiles[$fileIndex]['new']) {
                    $file->set('file_name', $file->getOriginal('file_name'));
                    $file->set('size', $file->getOriginal('size'));
                    $file->set('file_type', $file->getOriginal('file_type'));
                } else {
                    $file->set('size', $uploadFiles[$fileIndex]['size']);
                    $file->set('file_type', $uploadFiles[$fileIndex]['ext']);
                }
            }

            $addSize += (int)$file->get('size');
        }

        /** @var \App\Model\Table\FilesTable $filesTable */
        $filesTable = $this->getTableLocator()->get('Files');
        if (!$filesTable->chkClientUploadLimit($addSize, $ids)) {
            /** @var \App\Model\Table\SystemSettingsTable $system */
            $system = $this->getTableLocator()->get('SystemSettings');
            $contractPlan = $system->getData()->get('contract_plan');
            $limit = Configure::read('Master.systemSetting.restriction.file.' . $contractPlan);

            $entity->setError('files', __(Message::ERROR_ALL_FILE_SIZE, $limit / 1024 / 1024 / 1024));

            return false;
        }
    }

    /**
     * Model.afterSaveイベント
     *
     * @param \Cake\Event\EventInterface $event イベント
     * @param \Cake\Datasource\EntityInterface $entity エンティティ
     * @param \ArrayObject $options オプション
     * @return void
     */
    public function afterSave(EventInterface $event, EntityInterface $entity, ArrayObject $options)
    {
        if (!$entity->isNew()) {
            //ファイルの削除
            foreach ($this->getDeleteFilesName($entity) as $deleteFile) {
                FileUtility::deleteFile(UPLOAD_FILES . $entity->get('directory') . DS . $deleteFile);
            }
        }

        $files = $entity->get('files');
        $uploadFiles = $options['upload'];
        foreach ($files as $file) {
            if ($uploadFiles[$file->get('index')]['new']) {
                $path = UPLOAD_FILES . $entity->get('directory') . DS . $file->get('full_file_name');

                FileUtility::createDirectory(UPLOAD_FILES, [$entity->get('directory')], 0777);
                FileUtility::copyFile($uploadFiles[$file->get('index')]['file'], $path);
                FileUtility::changePermission($path, 0666);

                FileUtility::deleteFile($uploadFiles[$file->get('index')]['file']);
            }
        }
    }

    /**
     * Model.afterDeleteCommitイベント
     *
     * @param \Cake\Event\EventInterface $event イベント
     * @param \Cake\Datasource\EntityInterface $entity エンティティ
     * @param \ArrayObject $options オプション
     * @return void
     */
    public function afterDeleteCommit(EventInterface $event, EntityInterface $entity, ArrayObject $options)
    {
        FileUtility::deleteDirectory(UPLOAD_FILES . $entity->get('directory'));
    }

    /**
     * 編集のファインダー
     *
     * @param \Cake\ORM\Query $query The query to find with
     * @param array $options The options to use for the find
     * @return \Cake\ORM\Query The query builder
     */
    public function findEdit(Query $query, array $options)
    {
        $query->select(
            ['id', 'name', 'directory']
        )->contain([
            'Files' => [
                'fields' => [
                    'id',
                    'file_group_id',
                    'file_name',
                    'description',
                    'file_type',
                    'size',
                ],
            ],
        ]);

        return $query;
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
        return $query->find('edit');
    }

    /**
     * ファイル名の重複チェック
     *
     * @param \Cake\Datasource\EntityInterface $entity エンティティ
     * @param array $uploadFiles アップロードファイル
     * @return bool
     */
    public function checkDuplicateFileName(EntityInterface $entity, array $uploadFiles)
    {
        $files = $entity->get('files');
        $duplicate = [];

        $success = true;
        foreach ($files as $file) {
            if (isset($uploadFiles[$file->get('index')]) && Validation::notBlank($file->get('file_name'))) {
                if ($uploadFiles[$file->get('index')]['ext'] !== '') {
                    $fullFileName = $file->get('file_name') . '.' . $uploadFiles[$file->get('index')]['ext'];
                } else {
                    $fullFileName = $file->get('file_name') . '.' . $file->get('file_type');
                }
                if ($uploadFiles[$file->get('index')]['new']) {
                    if (ArrayUtility::arraySearch($fullFileName, $duplicate) !== false) {
                        $file->setError('file_name', ['_duplicate' => (string)__(Message::ERROR_DUPLICATION)]);
                        $success = false;
                    }
                }
                $duplicate[] = $fullFileName;
            }
        }

        return $success;
    }

    /**
     * 削除されるファイル名を取得
     *
     * @param \Cake\Datasource\EntityInterface $entity エンティティ
     * @return array ファイル名
     */
    public function getDeleteFilesName(EntityInterface $entity)
    {
        $original = $entity->extractOriginal($entity->getVisible());

        // 登録済みのIDを取得
        $saveFiles = array_column($entity->get('files'), 'full_file_name');
        $deleteFiles = [];

        foreach ($original['files'] as $file) {
            /** @var \App\Model\Entity\File $file */
            $fileOriginal = $file->getOriginalValues();
            $fullFileName = $fileOriginal['file_name'] . '.' . $fileOriginal['file_type'];

            if (ArrayUtility::arraySearch($fullFileName, $saveFiles) === false) {
                $deleteFiles[] = $fullFileName;
            }
        }

        return $deleteFiles;
    }
}
