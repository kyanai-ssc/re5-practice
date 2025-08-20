<?php
declare(strict_types=1);

namespace App\Form\Admin\Reservations;

use App\Locale\Message;
use App\Model\Entity\AdminListItem;
use App\Model\Entity\AdminSearchItem;
use App\Model\Entity\FormGroup;
use App\Model\Entity\FormItem;
use App\Model\Entity\ReservationSmartLock;
use App\Model\Entity\ReservationStatus;
use App\Model\InputType\Item\Type\SearchDisplayInterface;
use App\Utility\CommonData\CommonDataTrait;
use App\Utility\DateTimeUtility;
use App\Validation\CustomValidation;
use Cake\Core\Configure;
use Cake\Core\Exception\CakeException;
use Cake\Utility\Hash;
use Cake\Validation\Validation;
use Kuchen\Validation\Validation\Validator as KuchenValidator;

/**
 * 予約検索
 */
trait SearchFormTrait
{
    use CommonDataTrait;

    /**
     * 予約検索用のスキーマを生成
     *
     * @param \Cake\Form\Schema $schema スキーマ
     * @param array $searchItems 検索項目
     * @return \Cake\Form\Schema スキーマ
     */
    protected function buildReservationSearchSchema($schema, $searchItems)
    {
        foreach (Hash::get($searchItems, (string)FormGroup::FORM_TYPE_RESERVATION, []) as $item) {
            if ($item instanceof FormItem) {
                $inputTypeItem = $item->getInputTypeItem();
                if (!($inputTypeItem instanceof SearchDisplayInterface)) {
                    throw new CakeException();
                }
                $schema->addField($inputTypeItem->getSearchInputKey(), 'string');
            } else {
                $schema->addField(
                    Configure::readOrFail('Master.adminSearchItems.itemsSearchInputKey.' . $item),
                    'string'
                );
            }
        }

        return $schema;
    }

    /**
     * 予約検索用のバリデータを生成
     *
     * @param \Cake\Validation\Validator $validator バリデータ
     * @param array $options オプション
     * @return \Cake\Validation\Validator バリデータ
     */
    protected function buildReservationSearchValidator($validator, $options = [])
    {
        /** @var \App\Model\Table\FormItemsTable $formItemsTable */
        $formItemsTable = $this->getTableLocator()->get('FormItems');

        $searchInputKey = Configure::readOrFail('Master.adminSearchItems.itemsSearchInputKey');

        $validator
            ->requirePresence($searchInputKey[AdminSearchItem::ITEM_RESERVATION_ID], false)
            ->allowEmptyString($searchInputKey[AdminSearchItem::ITEM_RESERVATION_ID])
            ->add($searchInputKey[AdminSearchItem::ITEM_RESERVATION_ID], [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'naturalNumber' => [
                    'rule' => ['naturalNumber'],
                    'last' => true,
                    'message' => __(Message::ERROR_NATURAL_NUMBER),
                ],
                'lessThanOrEqual' => [
                    'rule' => ['comparison', CustomValidation::COMPARE_LESS_OR_EQUAL, CustomValidation::BIGINT_MAX],
                    'last' => true,
                    'message' => __(Message::ERROR_OVER_DIGIT, CustomValidation::BIGINT_MAX),
                ],
            ]);

        $validator
            ->requirePresence($searchInputKey[AdminSearchItem::ITEM_EVENT_ID], false)
            ->allowEmptyString($searchInputKey[AdminSearchItem::ITEM_EVENT_ID])
            ->add($searchInputKey[AdminSearchItem::ITEM_EVENT_ID], [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'naturalNumber' => [
                    'rule' => ['naturalNumber'],
                    'last' => true,
                    'message' => __(Message::ERROR_NATURAL_NUMBER),
                ],
                'lessThanOrEqual' => [
                    'rule' => ['comparison', CustomValidation::COMPARE_LESS_OR_EQUAL, CustomValidation::BIGINT_MAX],
                    'last' => true,
                    'message' => __(Message::ERROR_OVER_DIGIT, CustomValidation::BIGINT_MAX),
                ],
            ]);

        $validator
            ->requirePresence($searchInputKey[AdminSearchItem::ITEM_RESERVATION_STATUS_ID], false)
            ->allowEmptyArray($searchInputKey[AdminSearchItem::ITEM_RESERVATION_STATUS_ID])
            ->add($searchInputKey[AdminSearchItem::ITEM_RESERVATION_STATUS_ID], [
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

        $validator
            ->requirePresence($searchInputKey[AdminSearchItem::ITEM_RECEPTION_STATUS_ID], false)
            ->allowEmptyArray($searchInputKey[AdminSearchItem::ITEM_RECEPTION_STATUS_ID])
            ->add($searchInputKey[AdminSearchItem::ITEM_RECEPTION_STATUS_ID], [
                'isArray' => [
                    'rule' => ['isArray'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'multiple' => [
                    'rule' => ['multiple', [
                        'in' => array_keys($this->getFieldValueOptions('receptionStatusId')),
                    ]],
                    'last' => true,
                    'message' => __(Message::ERROR_IN_LIST),
                ],
            ]);

        $usageTimestampValidator = new KuchenValidator();
        $validator
            ->requirePresence($searchInputKey[AdminSearchItem::ITEM_USAGE_TIMESTAMP], false)
            ->allowEmptyString($searchInputKey[AdminSearchItem::ITEM_USAGE_TIMESTAMP])
            ->addNested($searchInputKey[AdminSearchItem::ITEM_USAGE_TIMESTAMP], $usageTimestampValidator);

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
                    'rule' => ['dateTime', 'ymd'],
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
                    'rule' => ['dateTime', 'ymd'],
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
            ->requirePresence($searchInputKey[AdminSearchItem::ITEM_PAYMENT_METHOD], false)
            ->allowEmptyArray($searchInputKey[AdminSearchItem::ITEM_PAYMENT_METHOD])
            ->add($searchInputKey[AdminSearchItem::ITEM_PAYMENT_METHOD], [
                'isArray' => [
                    'rule' => ['isArray'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'multiple' => [
                    'rule' => ['multiple', [
                        'in' => array_keys($this->getFieldValueOptions('paymentMethod')),
                    ]],
                    'last' => true,
                    'message' => __(Message::ERROR_IN_LIST),
                ],
            ]);

        $validator
            ->requirePresence($searchInputKey[AdminSearchItem::ITEM_PAYMENT_STATUS], false)
            ->allowEmptyArray($searchInputKey[AdminSearchItem::ITEM_PAYMENT_STATUS])
            ->add($searchInputKey[AdminSearchItem::ITEM_PAYMENT_STATUS], [
                'isArray' => [
                    'rule' => ['isArray'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'multiple' => [
                    'rule' => ['multiple', [
                        'in' => array_keys($this->getFieldValueOptions('paymentStatus')),
                    ]],
                    'last' => true,
                    'message' => __(Message::ERROR_IN_LIST),
                ],
            ]);

        $validator
            ->requirePresence($searchInputKey[AdminSearchItem::ITEM_RESERVATION_PAYMENT_STATUS], false)
            ->allowEmptyArray($searchInputKey[AdminSearchItem::ITEM_RESERVATION_PAYMENT_STATUS])
            ->add($searchInputKey[AdminSearchItem::ITEM_RESERVATION_PAYMENT_STATUS], [
                'isArray' => [
                    'rule' => ['isArray'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'multiple' => [
                    'rule' => ['multiple', [
                        'in' => array_keys($this->getFieldValueOptions('reservationPaymentStatus')),
                    ]],
                    'last' => true,
                    'message' => __(Message::ERROR_IN_LIST),
                ],
            ]);

        $validator
            ->requirePresence($searchInputKey[AdminSearchItem::ITEM_RESERVATION_SMARTLOCK_STATUS], false)
            ->allowEmptyArray($searchInputKey[AdminSearchItem::ITEM_RESERVATION_SMARTLOCK_STATUS])
            ->add($searchInputKey[AdminSearchItem::ITEM_RESERVATION_SMARTLOCK_STATUS], [
                'isArray' => [
                    'rule' => ['isArray'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'multiple' => [
                    'rule' => ['multiple', [
                        'in' => array_keys($this->getFieldValueOptions('reservationSmartLockStatus')),
                    ]],
                    'last' => true,
                    'message' => __(Message::ERROR_IN_LIST),
                ],
            ]);

        $reservationInsTimestampValidator = new KuchenValidator();
        $validator
            ->requirePresence($searchInputKey[AdminSearchItem::ITEM_RESERVATION_INS_TIMESTAMP], false)
            ->allowEmptyString($searchInputKey[AdminSearchItem::ITEM_RESERVATION_INS_TIMESTAMP])
            ->addNested(
                $searchInputKey[AdminSearchItem::ITEM_RESERVATION_INS_TIMESTAMP],
                $reservationInsTimestampValidator
            );

        $reservationInsTimestampValidator
            ->requirePresence('from', false)
            ->allowEmptyDateTime('from')
            ->add('from', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'date' => [
                    'rule' => ['date', 'ymd'],
                    'last' => true,
                    'message' => __(Message::ERROR_DATE),
                ],
            ]);

        $reservationInsTimestampValidator
            ->requirePresence('to', false)
            ->allowEmptyDateTime('to')
            ->add('to', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'date' => [
                    'rule' => ['date', 'ymd'],
                    'last' => true,
                    'message' => __(Message::ERROR_DATE),
                ],
                'compareFields' => [
                    'rule' => ['compareDateTimeFields', 'from', Validation::COMPARE_GREATER_OR_EQUAL],
                    'last' => true,
                    'message' => __(Message::ERROR_OVER_FROM_DATE),
                    'on' => function () use ($reservationInsTimestampValidator) {
                        return $reservationInsTimestampValidator->isValid('from');
                    },
                ],
            ]);

        $validator
            ->requirePresence($searchInputKey[AdminSearchItem::ITEM_RESERVATION_VIDEO_METTING], false)
            ->allowEmptyArray($searchInputKey[AdminSearchItem::ITEM_RESERVATION_VIDEO_METTING])
            ->add($searchInputKey[AdminSearchItem::ITEM_RESERVATION_VIDEO_METTING], [
                'isArray' => [
                    'rule' => ['isArray'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'multiple' => [
                    'rule' => ['multiple', [
                        'in' => array_keys($this->getFieldValueOptions('reservationVideoMeeting')),
                    ]],
                    'last' => true,
                    'message' => __(Message::ERROR_IN_LIST),
                ],
            ]);

        $reserveFormFlg = Hash::get($options, 'reserveFormFlg', false);
        foreach ($formItemsTable->getSearchableFormItems(FormGroup::FORM_TYPE_RESERVATION) as $formItems) {
            foreach ($formItems as $formItem) {
                $validator = $formItem->getInputTypeItem()->buildSearchValidator($validator);
                // 予約フォームのカテゴリーにバリデータを追加
                if ($formItem->get('input_type') === FormItem::INPUT_TYPE_LABEL && $reserveFormFlg) {
                    // 担当カテゴリがある管理者の場合は入力必須にする(マスター管理者を除く)
                    $validator->allowEmptyString(
                        $formItem->getInputTypeItem()->getSearchInputKey(),
                        __(Message::ERROR_IN_LIST),
                        function () {
                            /** @var \App\Model\Entity\Admin $loginData */
                            $loginData = $this->commonData()->getAdminLoginData();
                            if ($loginData->isMasterAdmin()) {
                                return true;
                            }

                            return $this->commonData()->getAdminLoginLabel() === null;
                        }
                    );

                    /** @var \App\Model\Table\LabelsTable $labelTable */
                    $labelTable = $this->getTableLocator()->get('Labels');
                    $validator = $labelTable->addValidateLabelIdAdminUsable(
                        $validator,
                        $formItem->getInputTypeItem()->getSearchInputKey()
                    );
                }
            }
        }

        return $validator;
    }

    /**
     * 予約検索用の値リストを生成
     *
     * @return array 値リスト
     */
    protected function buildReservationSearchFieldValueOptions()
    {
        /** @var \App\Model\Table\SystemSettingsTable $systemSettingsTable */
        $systemSettingsTable = $this->getTableLocator()->get('SystemSettings');
        /** @var \App\Model\Table\ReservationStatusesTable $reservationStatusesTable */
        $reservationStatusesTable = $this->getTableLocator()->get('ReservationStatuses');
        /** @var \App\Model\Table\ReceptionStatusesTable $receptionStatusesTable */
        $receptionStatusesTable = $this->getTableLocator()->get('ReceptionStatuses');
        /** @var \App\Model\Table\PaymentMethodsTable $paymentMethodsTable */
        $paymentMethodsTable = $this->getTableLocator()->get('PaymentMethods');
        /** @var \App\Model\Table\PaymentStatusesTable $paymentStatusesTable */
        $paymentStatusesTable = $this->getTableLocator()->get('PaymentStatuses');
        /** @var \App\Model\Table\FormItemsTable $formItemsTable */
        $formItemsTable = $this->getTableLocator()->get('FormItems');

        $reservationVideoMeeting = [];
        foreach (Configure::readOrFail('Master.videoMeeting.search') as $key => $value) {
            $reservationVideoMeeting[Configure::readOrFail('Master.common.flg.' . $key)] = $value;
        }

        $reservationPaymentStatus = [];
        foreach (Configure::readOrFail('Master.payment.reservationPaymentStatus') as $key => $value) {
            $reservationPaymentStatus[$key] = $value;
        }

        $reservationSmartLockStatus = [];
        foreach (Configure::readOrFail('Master.smartLock.reservationSmartLockStatus') as $key => $value) {
            $reservationSmartLockStatus[$key] = $value;
        }

        $sortKey = Configure::readOrFail('Master.adminListItems.sortKey');
        $sort = [
            $sortKey[AdminListItem::ITEM_RESERVATION_ID],
            $sortKey[AdminListItem::ITEM_RESERVATION_STATUS_ID],
            $sortKey[AdminListItem::ITEM_RECEPTION_STATUS_ID],
            $sortKey[AdminListItem::ITEM_USAGE_TIMESTAMP],
            $sortKey[AdminListItem::ITEM_CHARGE],
            $sortKey[AdminListItem::ITEM_PAYMENT_METHOD],
            $sortKey[AdminListItem::ITEM_PAYMENT_STATUS],
            $sortKey[AdminListItem::ITEM_RESERVATION_PAYMENT_STATUS],
            $sortKey[AdminListItem::ITEM_RESERVATION_INS_TIMESTAMP],
        ];

        foreach ($formItemsTable->getListDisplayableFormItems(FormGroup::FORM_TYPE_RESERVATION) as $formItems) {
            foreach ($formItems as $formItem) {
                if ($formItem->getInputTypeItem()->canSort()) {
                    $sort[] = $formItem->getInputTypeItem()->getSortKey();
                }
            }
        }

        $fieldValueOptions = [
            'reservationStatusId' => $reservationStatusesTable->getValueOptions(true),
            'receptionStatusId' => $receptionStatusesTable->getValueOptions(true),
            'paymentMethod' => $paymentMethodsTable->getValueOptions(),
            'paymentStatus' => $paymentStatusesTable->getValueOptions(),
            'reservationPaymentStatus' => $reservationPaymentStatus,
            'reservationSmartLockStatus' => $reservationSmartLockStatus,
            'reservationVideoMeeting' => $reservationVideoMeeting,
            'sort' => Hash::combine($sort, '{*}'),
        ];

        if (!$systemSettingsTable->getData()->usePayment()) {
            unset($fieldValueOptions['sort']['payment_method_id']);
            unset($fieldValueOptions['sort']['payment_status_id']);
        }

        return $fieldValueOptions;
    }

    /**
     * 予約検索用のデフォルト値を生成
     *
     * @param string $pageType 仕様ぺージ
     * @return array デフォルト値
     */
    protected function buildReservationSearchDefaultFieldValues($pageType = 'reservations')
    {
        $defaultValues = [
            'sort' => Configure::readOrFail('Master.adminListItems.sortKey.' . AdminListItem::ITEM_RESERVATION_ID),
        ];

        $schema = $this->getSchema();
        if ($pageType === 'reservations') {
            if ($schema->field('events_label_id')) {
                $defaultValues['events_label_id'] = $this->commonData()->getAdminLoginLabel();
            }
        }

        return $defaultValues;
    }

    /**
     * スマートロック連携用のパラメータをセット
     *
     * @param array $searchInputs 入力値
     * @return array スマートロック連携をした入力値
     */
    public function setSmartLockSearchParameter(array $searchInputs)
    {
        // スマートロック未連携
        $target = Configure::readOrFail(
            'Master.adminSearchItems.itemsSearchInputKey.' . AdminSearchItem::ITEM_RESERVATION_SMARTLOCK_STATUS
        );
        $inputReservationUnlinkedStatus = Hash::get($searchInputs, $target);
        $smartlockUnlinkedValue = (string)ReservationSmartLock::DISPLAY_STATUS_UNLINKED;

        if (!is_array($inputReservationUnlinkedStatus)) {
            $searchInputs[$target] = [$smartlockUnlinkedValue];
        } elseif (!in_array($smartlockUnlinkedValue, $inputReservationUnlinkedStatus, true)) {
            $searchInputs[$target][] = $smartlockUnlinkedValue;
        }

        // ステータス
        $target = Configure::readOrFail(
            'Master.adminSearchItems.itemsSearchInputKey.' . AdminSearchItem::ITEM_RESERVATION_STATUS_ID
        );
        $inputReservationStatus = Hash::get($searchInputs, $target);

        /** @var \App\Model\Table\ReservationStatusesTable $reservationStatusesTable */
        $reservationStatusesTable = $this->getTableLocator()->get('ReservationStatuses');
        $reservationStatusIds = array_merge(
            $reservationStatusesTable->getReservationStatusIds(ReservationStatus::STATUS_TYPE_FIXED),
            $reservationStatusesTable->getReservationStatusIds(ReservationStatus::STATUS_TYPE_VISIT),
        );

        if (!is_array($inputReservationStatus)) {
            $searchInputs[$target] = $reservationStatusIds;
        } elseif ($reservationStatusIds !== $inputReservationStatus) {
            $searchInputs[$target] = array_unique(
                array_merge(
                    $inputReservationStatus,
                    $reservationStatusIds,
                )
            );
        }

        // 利用日時 (入力内容にかかわらず固定)
        $target = Configure::readOrFail(
            'Master.adminSearchItems.itemsSearchInputKey.' . AdminSearchItem::ITEM_USAGE_TIMESTAMP
        );

        // 利用日時To用に現在日時を取得(指定した単位で分を切り捨て)
        $nowDateTime = DateTimeUtility::truncateMinute(
            $this->commonData()->getNowDateTime()->Format('Y-m-d H:i'),
            Configure::read('Setting.smartLock.truncateMinute')
        );
        $searchInputs[$target]['from'] = $nowDateTime->format('Y-m-d H:i');

        return $searchInputs;
    }
}
