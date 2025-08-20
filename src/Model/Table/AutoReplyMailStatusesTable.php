<?php
declare(strict_types=1);

namespace App\Model\Table;

use App\Locale\Message;
use App\Model\AppTable;
use App\Model\Entity\AutoReplyMail;
use App\Model\Entity\ReservationStatus;
use Cake\Datasource\EntityInterface;
use Cake\Utility\Hash;
use Cake\Validation\Validator;

/**
 * AutoReplyMailStatuses Model
 *
 * @method \App\Model\Entity\AutoReplyMailStatus newEmptyEntity()
 * @method \App\Model\Entity\AutoReplyMailStatus newEntity(array $data, array $options = [])
 * @method \App\Model\Entity\AutoReplyMailStatus[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\AutoReplyMailStatus get($primaryKey, $options = [])
 * @method \App\Model\Entity\AutoReplyMailStatus findOrCreate($search, ?callable $callback = null, $options = [])
 * @method \App\Model\Entity\AutoReplyMailStatus patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\AutoReplyMailStatus[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\AutoReplyMailStatus|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\AutoReplyMailStatus saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\AutoReplyMailStatus[]|\Cake\Datasource\ResultSetInterface|false saveMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\AutoReplyMailStatus[]|\Cake\Datasource\ResultSetInterface saveManyOrFail(iterable $entities, $options = [])
 * @method \App\Model\Entity\AutoReplyMailStatus[]|\Cake\Datasource\ResultSetInterface|false deleteMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\AutoReplyMailStatus[]|\Cake\Datasource\ResultSetInterface deleteManyOrFail(iterable $entities, $options = [])
 */
class AutoReplyMailStatusesTable extends AppTable
{
    /**
     * @var \App\Model\Table\AutoReplyMailsTable
     */
    protected $autoReplyMails;

    /**
     * @var \App\Model\Table\ReservationStatusesTable
     */
    protected $reservationStatuses;

    /**
     * @inheritDoc
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->belongsTo('AutoReplyMails', [
            'foreignKey' => 'auto_reply_mail_id',
            'joinType' => 'INNER',
        ]);
    }

    /**
     * @inheritDoc
     */
    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->requirePresence('reservation_status_from_id', true, __(Message::ERROR_NOT_EMPTY_SELECT))
            ->allowEmptyString('reservation_status_from_id')
            ->add('reservation_status_from_id', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
            ]);

        $validator
            ->requirePresence('reservation_status_to_id', true, __(Message::ERROR_NOT_EMPTY_SELECT))
            ->allowEmptyArray('reservation_status_to_id')
            ->add('reservation_status_to_id', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
            ]);

        return $validator;
    }

    /**
     * ステータス重複チェック
     *
     * @param \Cake\Datasource\EntityInterface $entity entity
     * @param string $check チェックする値
     * @param int $fromId 変更前ステータスID
     * @return bool
     */
    public function checkDuplicationStatus(EntityInterface $entity, $check, $fromId = null)
    {
        /** @var \App\Model\Table\ReservationStatusesTable $reservationStatusesTable */
        $reservationStatusesTable = $this->getTableLocator()->get('ReservationStatuses');
        $statusList = $reservationStatusesTable->getGroupingStatusType();

        /** @var array $list */
        $list = Hash::combine($statusList, '{n}.{n}.status_type', '{n}.{n}.id');
        $cancelStatus = Hash::get($list, [(string)ReservationStatus::STATUS_TYPE_CANCEL]);

        /** @var \App\Model\Table\AutoReplyMailsTable $autoReplyMailsTable */
        $autoReplyMailsTable = $this->getTableLocator()->get('AutoReplyMails');
        $query = $autoReplyMailsTable->find('duplicateCheck', [
            'inputs' => $entity->toArray(),
            'type' => $entity->get('type'),
        ]);

        $query->join([
            'table' => 'auto_reply_mail_statuses',
            'alias' => 'AutoReplyMailStatuses',
            'type' => 'LEFT',
            'conditions' => [
                'AutoReplyMailStatuses.auto_reply_mail_id = AutoReplyMails.id',
            ],
        ]);

        // そのラベルとそのステータスが自分以外で重なっているかを確認
        if ($entity->get('type') === AutoReplyMail::TYPE_RESERVE_CANCEL) {
            $query->where([
                'AutoReplyMailStatuses.reservation_status_from_id' => $check,
                'AutoReplyMailStatuses.reservation_status_to_id' => $cancelStatus,
            ]);
        } elseif (
            $entity->get('type') === AutoReplyMail::TYPE_RESERVE_REMINDER
            || $entity->get('type') === AutoReplyMail::TYPE_RESERVE_REMINDER_CLOSE
        ) {
            $query->where([
                'AutoReplyMailStatuses.reservation_status_from_id' => $check,
                'AutoReplyMailStatuses.reservation_status_to_id' => $check,
            ]);
        } elseif ($entity->get('type') === AutoReplyMail::TYPE_RESERVE_ADD) {
            $query->where([
                'AutoReplyMailStatuses.reservation_status_from_id IS ' => null,
                'AutoReplyMailStatuses.reservation_status_to_id' => $check,
            ]);
        } elseif ($entity->get('type') === AutoReplyMail::TYPE_STATUS_UPDATE) {
            $query->where([
                'AutoReplyMailStatuses.reservation_status_from_id' => $fromId,
                'AutoReplyMailStatuses.reservation_status_to_id' => $check,
            ]);
        }

        if ($query->count() < 1) {
            return true;
        }

        return false;
    }

    /**
     * 入力値をフィルタリング
     *
     * @param array $inputs 入力値
     * @return array フィルタリング済みの入力値
     */
    public function filterDefault(array $inputs)
    {
        $inputs += [
            'reservation_status_from_id' => null,
            'reservation_status_to_id' => null,
        ];

        return $inputs;
    }
}
