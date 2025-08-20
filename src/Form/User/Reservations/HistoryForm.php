<?php
declare(strict_types=1);

namespace App\Form\User\Reservations;

use App\Form\AppForm;
use App\Locale\Message;
use Cake\Core\Configure;
use Cake\Form\Schema;
use Cake\Validation\Validation;
use Cake\Validation\Validator;
use Kuchen\Validation\Validation\Validator as KuchenValidator;

/**
 * 予約履歴フォーム
 */
class HistoryForm extends AppForm
{
    /**
     * @inheritDoc
     */
    protected function _buildSchema(Schema $schema): Schema
    {
        $schema
            ->addField('usage_timestamp', 'date')
            ->addField('status', 'array')
            ->addField('page', 'integer');

        return $schema;
    }

    /**
     * @inheritDoc
     */
    public function validationDefault(Validator $validator): Validator
    {
        $usageTimestampValidator = new KuchenValidator();
        $validator
            ->requirePresence('usage_timestamp', false)
            ->allowEmptyString('usage_timestamp')
            ->addNested('usage_timestamp', $usageTimestampValidator);

        $usageTimestampValidator
            ->requirePresence('from', false)
            ->allowEmptyDateTime('from')
            ->add('from', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'dateTime' => [
                    'rule' => [
                        'dateTime',
                        'ymd',
                    ],
                    'last' => true,
                    'message' => __(Message::ERROR_DATE_TIME),
                ],
            ]);

        $usageTimestampValidator
            ->requirePresence('to', false)
            ->allowEmptyDateTime('to')
            ->add('to', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'dateTime' => [
                    'rule' => [
                        'dateTime',
                        'ymd',
                    ],
                    'last' => true,
                    'message' => __(Message::ERROR_DATE_TIME),
                ],
                'compareFields' => [
                    'rule' => ['compareDateTimeFields', 'from', Validation::COMPARE_GREATER],
                    'last' => true,
                    'message' => __(Message::ERROR_OVER_FROM_DATETIME),
                    'on' => function () use ($usageTimestampValidator) {
                        return $usageTimestampValidator->isValid('from');
                    },
                ],
            ]);

        $validator
            ->requirePresence('status', false)
            ->allowEmptyDateTime('status')
            ->add('status', [
                'isArray' => [
                    'rule' => ['isArray'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'multiple' => [
                    'rule' => ['multiple', [
                        'in' => array_keys($this->getFieldValueOptions('reservationStatusId')),
                    ]],
                    'last' => true,
                    'message' => __(Message::ERROR_IN_LIST),
                ],
            ]);

        $this->addPaginateValidation($validator, [
            'fieldValueOptions' => $this->getFieldValueOptions(),
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

        /** @var \App\Model\Table\PaymentStatusesTable $paymentStatusesTable */
        $paymentStatusesTable = $this->getTableLocator()->get('PaymentStatuses');

        $fieldValueOptions = [
                'reservationStatusId' => $reservationStatusesTable->getValueOptions(true),
                'paymentStatus' => $paymentStatusesTable->getValueOptions(),
            ] + $this->buildPaginateFieldValueOptions();

        return $fieldValueOptions;
    }

    /**
     * @param mixed $searchData 検索情報
     * @param int $userId 会員ID
     * @return mixed
     */
    public function setFixedValue($searchData, int $userId)
    {
        $searchData['user_id'] = $userId;
        $searchData['limit'] = Configure::read('Setting.pagination.reservationHistory.limit');

        return $searchData;
    }

    /**
     * @inheritDoc
     */
    protected function buildDefaultFieldValues()
    {
        $defaultFieldValues = [
            'usage_timestamp' => [
                'from' => $this->commonData()->getNowDateTime()->format('Y/m/d 00:00'),
            ],
        ];

        return $defaultFieldValues;
    }
}
