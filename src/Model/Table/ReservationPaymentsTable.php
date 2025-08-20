<?php
declare(strict_types=1);

namespace App\Model\Table;

use App\Model\AppTable;
use App\Model\Entity\Reservation;
use App\Model\Entity\ReservationPayment;
use Cake\I18n\FrozenTime;
use Cake\ORM\Query;

/**
 * ReservationPayments Model
 *
 * @method \App\Model\Entity\ReservationPayment newEmptyEntity()
 * @method \App\Model\Entity\ReservationPayment newEntity(array $data, array $options = [])
 * @method \App\Model\Entity\ReservationPayment[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\ReservationPayment get($primaryKey, $options = [])
 * @method \App\Model\Entity\ReservationPayment findOrCreate($search, ?callable $callback = null, $options = [])
 * @method \App\Model\Entity\ReservationPayment patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\ReservationPayment[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\ReservationPayment|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\ReservationPayment saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\ReservationPayment[]|\Cake\Datasource\ResultSetInterface|false saveMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\ReservationPayment[]|\Cake\Datasource\ResultSetInterface saveManyOrFail(iterable $entities, $options = [])
 * @method \App\Model\Entity\ReservationPayment[]|\Cake\Datasource\ResultSetInterface|false deleteMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\ReservationPayment[]|\Cake\Datasource\ResultSetInterface deleteManyOrFail(iterable $entities, $options = [])
 */
class ReservationPaymentsTable extends AppTable
{
    /**
     * @inheritDoc
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->belongsTo('Reservations', [
            'foreignKey' => 'reservation_id',
            'joinType' => 'INNER',
        ]);
    }

    /**
     * 編集時のファインダー
     *
     * @param \Cake\ORM\Query $query The query to find with
     * @param array $options The options to use for the find
     * @return \Cake\ORM\Query The query builder
     */
    public function findEdit(Query $query, array $options)
    {
        $query->where([
            'ReservationPayments.reservation_id' => $options['reservationId'],
        ]);
        $query->order([
            'ReservationPayments.id' => 'DESC',
        ]);
        $query->limit(1);

        return $query;
    }

    /**
     * 決済情報を生成
     *
     * @param \App\Model\Entity\Reservation $reservation 予約
     * @return \Cake\Datasource\EntityInterface
     */
    public function generateData(Reservation $reservation)
    {
        /** @var \App\Model\Table\PaymentSettingsTable $paymentSettingsTable */
        $paymentSettingsTable = $this->getTableLocator()->get('PaymentSettings');
        /** @var \App\Model\Table\PaymentMethodsTable $paymentMethodsTable */
        $paymentMethodsTable = $this->getTableLocator()->get('PaymentMethods');

        $paymentSetting = $paymentSettingsTable->getDataOrFail();

        $entity = $this->newEmptyEntity();
        $entity->set([
            'charge' => $reservation->get('charge'),
            'payment_service' => $paymentSetting->get('payment_service'),
            'payment_method_id' => $reservation->get('payment_method_id'),
            'status' => ReservationPayment::STATUS_UNSETTLED,
        ], ['guard' => false]);

        if ($paymentMethodsTable->isLinkPayment((int)$reservation->get('payment_method_id'))) {
            $entity->set([
                'payment_order_id' => $entity->generatePaymentOrderId(),
                'payment_limit' => $paymentMethodsTable->getPaymentLimit(
                    (int)$reservation->get('payment_method_id')
                )->format('Y-m-d H:i:s'),
            ], ['guard' => false]);
        }

        return $entity;
    }

    /**
     * 決済連携状況を取得
     *
     * @param int $status ステータス
     * @param \Cake\I18n\FrozenTime|null $paymentLimit 有効期限
     * @return int 決済連携状況
     */
    public function getReservationPaymentStatus(int $status, ?FrozenTime $paymentLimit)
    {
        switch ($status) {
            case ReservationPayment::STATUS_UNSETTLED:
                $nowDateTime = $this->commonData()->getNowDateTime();
                if ($paymentLimit === null || $paymentLimit >= $nowDateTime) {
                    $value = $status;
                } else {
                    $value = ReservationPayment::DISPLAY_STATUS_EXPIRED;
                }
                break;
            default:
                $value = $status;
                break;
        }

        return $value;
    }

    /**
     * 3Dセキュアのロックを取得
     *
     * @param int $reservationId 予約ID
     * @return void
     */
    public function getLockForThreeDSecure($reservationId)
    {
        $this->getLock(static::LOCK_TYPE_THREE_D_SECURE, $this->generateLockCode((string)$reservationId));
    }

    /**
     * 3Dセキュアのロックを解放
     *
     * @param int $reservationId 予約ID
     * @return void
     */
    public function releaseLockForThreeDSecure($reservationId)
    {
        $this->releaseLock(static::LOCK_TYPE_THREE_D_SECURE, $this->generateLockCode((string)$reservationId));
    }
}
