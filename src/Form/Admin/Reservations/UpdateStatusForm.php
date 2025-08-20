<?php
declare(strict_types=1);

namespace App\Form\Admin\Reservations;

use App\Form\AppForm;
use App\Locale\Message;
use Cake\Core\Configure;
use Cake\Datasource\EntityInterface;
use Cake\Form\Schema;
use Cake\Utility\Hash;
use Cake\Validation\Validator;

/**
 * ステータス更新フォーム
 */
class UpdateStatusForm extends AppForm
{
    /**
     * @var \Cake\Datasource\EntityInterface|null
     */
    protected $entity = null;

    /**
     * @inheritDoc
     */
    protected function _buildSchema(Schema $schema): Schema
    {
        $schema
            ->addField('reservation_status_id', 'integer')
            ->addField('mail_check', 'string');

        return $schema;
    }

    /**
     * @inheritDoc
     */
    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->requirePresence('reservation_status_id', true, __(Message::ERROR_NOT_EMPTY_SELECT))
            ->allowEmptyString('reservation_status_id', __(Message::ERROR_NOT_EMPTY_SELECT), false)
            ->add('reservation_status_id', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'inList' => [
                    'rule' => ['inList', array_keys($this->getFieldValueOptions('reservation_status_id'))],
                    'last' => true,
                    'message' => __(Message::ERROR_IN_LIST),
                ],
            ]);

        $validator
            ->requirePresence('mail_check', false)
            ->allowEmptyString('mail_check')
            ->add('mail_check', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'inList' => [
                    'rule' => ['inList', Configure::readOrFail('Master.common.flg')],
                    'last' => true,
                    'message' => __(Message::ERROR_IN_LIST),
                ],
            ]);

        return $validator;
    }

    /**
     * @inheritDoc
     */
    protected function buildFieldValueOptions()
    {
        /** @var \App\Model\Table\ReservationStatusesTable $reservationStatusesTable */
        $reservationStatusesTable = $this->getTableLocator()->get('ReservationStatuses');

        $fieldValueOptions = [
            'reservation_status_id' => $reservationStatusesTable->getValueOptions(),
        ];

        return $fieldValueOptions;
    }

    /**
     * @inheritDoc
     */
    protected function buildDefaultFieldValues()
    {
        $defaultFieldValues = [];

        $entity = $this->getEntity();
        if (isset($entity)) {
            $defaultFieldValues += [
                'reservation_status_id' => $entity->get('reservation_status_id'),
            ];
        }

        return $defaultFieldValues;
    }

    /**
     * エンティティーを取得
     *
     * @return \Cake\Datasource\EntityInterface|null エンティティー
     */
    public function getEntity()
    {
        return $this->entity;
    }

    /**
     * エンティティーを設定
     *
     * @param \Cake\Datasource\EntityInterface $entity エンティティー
     * @return void
     */
    public function setEntity(EntityInterface $entity)
    {
        $this->entity = $entity;
    }

    /**
     * アプリケーションルールのエラーを設定
     *
     * @return void
     */
    public function setRulesError()
    {
        if (isset($this->entity)) {
            $errors = Hash::flatten($this->entity->getErrors());
            if (!empty($errors)) {
                $this->setErrors([
                    'reservation_status_id' => reset($errors),
                ]);
            }
        }
    }

    /**
     * 更新したステータス情報を返却
     *
     * @param bool $finish 更新フラグ
     * @return array status情報
     */
    public function getStatusData(bool $finish)
    {
        if (!$finish) {
            return [];
        }

        /** @var \App\Model\Table\ReservationStatusesTable $reservationStatusesTable */
        $reservationStatusesTable = $this->getTableLocator()->get('ReservationStatuses');
        $statusData = [
            'class' => Configure::read(
                'Master.reservation.statusClass.' .
                $reservationStatusesTable->getReservationStatusType(
                    (int)$this->getData('reservation_status_id')
                )
            ),
            'name' => $reservationStatusesTable->getReservationStatusName((int)$this->getData('reservation_status_id')),
        ];

        return $statusData;
    }
}
