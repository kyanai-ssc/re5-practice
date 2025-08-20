<?php
declare(strict_types=1);

namespace App\Model\Behavior;

use App\ORM\Behavior;
use App\Utility\CommonData\CommonDataTrait;
use ArrayObject;
use Cake\Core\Configure;
use Cake\Datasource\EntityInterface;
use Cake\Utility\Hash;

/**
 * Class AdminOperationLog
 */
class AdminOperationLogBehavior extends Behavior
{
    use CommonDataTrait;

    protected $_defaultConfig = [
        'implementedFinders' => [],
        'implementedMethods' => [
            'saveOperationalLogs' => 'saveOperationalLogs',
            'isSaveOperation' => 'isSaveOperation',
        ],
        'events' => [],
        'saveOperation' => false,
        'afterSave' => true,
    ];

    /**
     * @inheritDoc
     */
    public function initialize(array $config): void
    {
        if (isset($config['events'])) {
            $this->setConfig('events', $config['events'], false);
        }
    }

    /**
     * afterSave hook
     *
     * @param \Cake\Event\EventInterface $event イベント
     * @param \Cake\Datasource\EntityInterface $entity エンティティ
     * @param \ArrayObject $options オプション
     * @return bool
     */
    public function afterSave($event, EntityInterface $entity, ArrayObject $options)
    {
        if ($this->isSaveOperation($options) && $this->_config['afterSave']) {
            $saveKey = Hash::get($options, 'saveKey');
            if (is_null($saveKey)) {
                $saveKey = $this->table()->getPrimaryKey();
            }
            $this->saveOperationalLogs($entity->get($saveKey), $options['saveOperation']);
        }

        return true;
    }

    /**
     * afterSaveMany hook
     *
     * @param \Cake\Event\EventInterface $event イベント
     * @param array $entities entities
     * @param \ArrayObject $options オプション
     * @return bool
     */
    public function afterSaveMany($event, array $entities, ArrayObject $options)
    {
        if ($this->isSaveOperation($options)) {
            $ids = [];

            $saveKey = Hash::get($options, 'saveKey');
            if (is_null($saveKey)) {
                $saveKey = $this->table()->getPrimaryKey();
            }

            $saveIds = Hash::get($options, 'operationLogsId');
            $exceptionIds = Hash::get($options, 'exceptionIds');

            //特定の情報を保存する場合
            if (empty($exceptionIds)) {
                //特定のIDのみ保存する
                if (is_array($saveIds)) {
                    foreach ($entities as $entity) {
                        if (isset($saveIds[$entity->get('id')])) {
                            $ids[$entity->get($saveKey)] = $entity->get($saveKey);
                        }
                    }
                } else {
                    foreach ($entities as $entity) {
                        $ids[$entity->get($saveKey)] = $entity->get($saveKey);
                    }
                }
            } else {
                $ids = $exceptionIds;
            }

            if (!empty($ids)) {
                $this->saveOperationalLogs($ids, $options['saveOperation']);
            }
        }

        return true;
    }

    /**
     * afterDelete hook
     *
     * @param \Cake\Event\EventInterface $event イベント
     * @param \Cake\Datasource\EntityInterface $entity エンティティ
     * @param \ArrayObject $options オプション
     * @return bool
     */
    public function afterDelete($event, EntityInterface $entity, ArrayObject $options)
    {
        if ($this->isSaveOperation($options)) {
            $this->saveOperationalLogs($entity->get('id'), $options['saveOperation']);
        }

        return true;
    }

    /**
     * beforeDeleteData hook
     *
     * @param \Cake\Event\EventInterface $event イベント
     * @param \Cake\Database\Query $query Query
     * @param \ArrayObject $options オプション
     * @return void
     */
    public function beforeDeleteData($event, $query, ArrayObject $options)
    {
        if ($this->isSaveOperation($options)) {
            $statement = $query->execute();
            $ids = array_unique(Hash::flatten((array)$statement->fetchAll('assoc')));
            $options = $options['saveOperation'] + [
                'data' => preg_replace(
                    '/%count%/',
                    (string)count($ids),
                    Configure::readOrFail('Setting.operationalLog.deleteMany')
                ),
            ];
            $this->saveOperationalLogs($ids, $options);
        }
    }

    /**
     * afterUpdateMany hook
     *
     * @param \Cake\Event\EventInterface $event イベント
     * @param \Cake\Database\Query $query Query
     * @param \ArrayObject $options オプション
     * @return void
     */
    public function afterUpdateMany($event, $query, ArrayObject $options)
    {
        if ($this->isSaveOperation($options)) {
            $statement = $query->execute();
            $this->saveOperationalLogs(
                array_unique(Hash::flatten((array)$statement->fetchAll('assoc'))),
                $options['saveOperation']
            );
        }
    }

    /**
     * 操作ログ保存実施判定
     *
     * @param array|\ArrayObject $options オプション
     * @return bool
     */
    public function isSaveOperation($options)
    {
        if ($this->_config['saveOperation'] && !empty($options['saveOperation'])) {
            return true;
        }

        return false;
    }

    /**
     * 操作ログ保存
     *
     * @param int|array $ids ID
     * @param array $option オプション
     * @return void
     */
    public function saveOperationalLogs($ids, array $option)
    {
        if (is_array($ids)) {
            $data['edited_id'] = implode(',', $ids);
        } else {
            $data['edited_id'] = $ids;
        }

        /** @var \App\Model\Entity\Admin $loginData */
        $loginData = $this->commonData()->getAdminLoginData();

        $data['admin_id'] = $loginData->get('id');
        $data['login_id'] = $loginData->get('login_id');
        $data['operated_function'] = Configure::readOrFail('Master.operation.functionCode.' . $option['controller']);

        //完了画面があるものはConfを削除する
        $action = str_replace('Conf', '', $option['action']);
        $data['operated_type'] = Configure::readOrFail('Master.operation.typeCode.' . $action);

        if (isset($option['data'])) {
            $data['operated_data'] = $option['data'];
        } else {
            $data['operated_data'] = preg_replace(
                '/%id%/',
                (string)$data['edited_id'],
                Configure::readOrFail('Setting.operationalLog.default')
            );
        }

        $adminOperationalLogsTable = $this->getTableLocator()->get('AdminOperationalLogs');
        $adminOperationalLogs = $adminOperationalLogsTable->newEntity($data);

        $adminOperationalLogsTable->saveOrFail($adminOperationalLogs);
    }
}
