<?php
declare(strict_types=1);

namespace App\Form\Admin\Reservations;

use App\Form\Admin\Users\SearchFormTrait as UserSearchFormTrait;
use App\Form\AppForm;
use App\Model\Entity\AdminListItem;
use App\Model\Entity\AdminSearchItem;
use App\Model\Entity\FormGroup;
use Cake\Core\Configure;
use Cake\Core\Exception\CakeException;
use Cake\Form\Schema;
use Cake\Validation\Validator;

/**
 * 予約検索フォーム
 */
class SearchForm extends AppForm
{
    use SearchFormTrait;
    use UserSearchFormTrait;

    /**
     * @var array|null
     */
    protected $searchItems = null;

    /**
     * @var array|null
     */
    protected $listItems = null;

    /**
     * @var bool|null
     */
    public $searchPaymentExpiredFlg = null;

    /**
     * @var bool|null
     */
    public $searchSmartLockUnlinkedFlg = null;

    /**
     * @inheritDoc
     */
    protected function _buildSchema(Schema $schema): Schema
    {
        $schema = $this->buildUserSearchSchema($schema, $this->getSearchItems());
        $schema = $this->buildReservationSearchSchema($schema, $this->getSearchItems());

        $reservationPaymentStatus = Configure::readOrFail(
            'Master.adminSearchItems.itemsSearchInputKey.' . AdminSearchItem::ITEM_RESERVATION_PAYMENT_STATUS
        );
        if ($this->searchPaymentExpiredFlg && !$schema->field($reservationPaymentStatus)) {
            $schema->addField($reservationPaymentStatus, 'integer');
        }

        $this->addPaginateSchema($schema);

        return $schema;
    }

    /**
     * @inheritDoc
     */
    public function validationDefault(Validator $validator): Validator
    {
        $validator = $this->buildUserSearchValidator($validator);
        $validator = $this->buildReservationSearchValidator($validator, ['reserveFormFlg' => true]);

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
        $userFieldValueOptions = $this->buildUserSearchFieldValueOptions();
        $reservationFieldValueOptions = $this->buildReservationSearchFieldValueOptions();

        $fieldValueOptions = [
            'sort' => $reservationFieldValueOptions['sort'] + $userFieldValueOptions['sort'],
            'listCheck' => Configure::readOrFail('Master.common.listCheck'),
            'listCheckId' => Configure::readOrFail('Master.common.listCheckId'),
        ];
        $fieldValueOptions += $this->buildPaginateFieldValueOptions() + $userFieldValueOptions
            + $reservationFieldValueOptions;

        return $fieldValueOptions;
    }

    /**
     * @inheritDoc
     */
    protected function buildDefaultFieldValues()
    {
        $userDefaultFieldValues = $this->buildUserSearchDefaultFieldValues();
        $reservationDefaultFieldValues = $this->buildReservationSearchDefaultFieldValues();

        $defaultFieldValues = [
            'sort' => $reservationDefaultFieldValues['sort'],
        ];
        $defaultFieldValues += $this->buildPaginateDefaultFieldValues() + $userDefaultFieldValues
            + $reservationDefaultFieldValues;

        return $defaultFieldValues;
    }

    /**
     * 検索項目の表示設定を取得
     *
     * @return array 表示設定
     */
    public function getSearchItems()
    {
        if (!isset($this->searchItems)) {
            /** @var \App\Model\Table\AdminSearchItemsTable $adminSearchItemsTable */
            $adminSearchItemsTable = $this->getTableLocator()->get('AdminSearchItems');

            $searchItems = $adminSearchItemsTable->find('formItem', [
                'inputs' => ['type' => AdminSearchItem::TYPE_RESERVATION_LIST],
            ])->first();
            if (!is_array($searchItems)) {
                throw new CakeException();
            }
            $this->searchItems = $searchItems;
        }

        if (is_scalar($this->searchPaymentExpiredFlg)) {
            // 検索項目設定で非表示の場合でも決済連携状況の検索項目を表示
            $searchItems = $this->searchItems;
            $formGroup = FormGroup::FORM_TYPE_RESERVATION;

            if (empty($searchItems) || empty($searchItems[$formGroup])) {
                $searchItems[$formGroup][] = AdminSearchItem::ITEM_RESERVATION_PAYMENT_STATUS;
                $this->searchItems = $searchItems;
            } elseif (!in_array((string)AdminSearchItem::ITEM_RESERVATION_PAYMENT_STATUS, $searchItems[$formGroup])) {
                // 表示位置調整
                $formItemList = Configure::read('Master.adminSearchItems.formTypeItems.' . $formGroup);
                $length = array_search(AdminSearchItem::ITEM_RESERVATION_PAYMENT_STATUS, $formItemList);
                $insertPosition = 0;
                $targetFormItems = array_slice($formItemList, 0, $length);
                foreach ($searchItems[$formGroup] as $value) {
                    if (is_int($value) && in_array($value, $targetFormItems)) {
                        $insertPosition = array_search($value, $searchItems[$formGroup]) + 1;
                    }
                }
                array_splice(
                    $searchItems[$formGroup],
                    $insertPosition,
                    0,
                    AdminSearchItem::ITEM_RESERVATION_PAYMENT_STATUS
                );

                $this->searchItems = $searchItems;
            }
        }

        if (is_scalar($this->searchSmartLockUnlinkedFlg)) {
            // 検索項目設定で非表示の場合でもスマートロック連携状況・ステータス・利用日時の検索項目を表示
            $searchItems = $this->searchItems;
            $formGroup = FormGroup::FORM_TYPE_RESERVATION;

            if (empty($searchItems) || empty($searchItems[$formGroup])) {
                $searchItems[$formGroup] = [
                    AdminSearchItem::ITEM_RESERVATION_STATUS_ID,
                    AdminSearchItem::ITEM_RESERVATION_SMARTLOCK_STATUS,
                    AdminSearchItem::ITEM_USAGE_TIMESTAMP,
                ];
                $this->searchItems = $searchItems;
            } else {
                $fixedItems = [];
                if (!in_array((string)AdminSearchItem::ITEM_RESERVATION_STATUS_ID, $searchItems[$formGroup])) {
                    $fixedItems[] = AdminSearchItem::ITEM_RESERVATION_STATUS_ID;
                }
                if (!in_array((string)AdminSearchItem::ITEM_RESERVATION_SMARTLOCK_STATUS, $searchItems[$formGroup])) {
                    $fixedItems[] = AdminSearchItem::ITEM_RESERVATION_SMARTLOCK_STATUS;
                }
                if (!in_array((string)AdminSearchItem::ITEM_USAGE_TIMESTAMP, $searchItems[$formGroup])) {
                    $fixedItems[] = AdminSearchItem::ITEM_USAGE_TIMESTAMP;
                }
                foreach ($fixedItems as $target) {
                    // スマートロック連携状況・ステータス・利用日時の表示位置調整
                    $formItemList = Configure::read('Master.adminSearchItems.formTypeItems.' . $formGroup);
                    $length = array_search($target, $formItemList);
                    $insertPosition = 0;
                    $targetFormItems = array_slice($formItemList, 0, $length);
                    foreach ($searchItems[$formGroup] as $value) {
                        if (is_int($value) && in_array($value, $targetFormItems)) {
                            $insertPosition = array_search($value, $searchItems[$formGroup]) + 1;
                        }
                    }

                    array_splice(
                        $searchItems[$formGroup],
                        $insertPosition,
                        0,
                        $target
                    );
                }

                $this->searchItems = $searchItems;
            }
        }

        return $this->searchItems;
    }

    /**
     * 検索項目の表示設定を取得
     *
     * @return array 表示設定
     */
    public function getListItems()
    {
        if (!isset($this->listItems)) {
            /** @var \App\Model\Table\AdminListItemsTable $adminListItemsTable */
            $adminListItemsTable = $this->getTableLocator()->get('AdminListItems');

            $listItems = $adminListItemsTable->find('formItem', [
                'inputs' => ['type' => AdminSearchItem::TYPE_RESERVATION_LIST],
            ])->first();
            if (!is_array($listItems)) {
                throw new CakeException();
            }
            $this->listItems = $listItems;
        }

        if (is_scalar($this->searchPaymentExpiredFlg)) {
            // 表示項目設定で非表示の場合でも決済連携状況の一覧項目を表示
            $listItems = $this->listItems;
            if (!in_array((string)AdminListItem::ITEM_RESERVATION_PAYMENT_STATUS, $listItems)) {
                $listItems[] = AdminListItem::ITEM_RESERVATION_PAYMENT_STATUS;
                $this->listItems = $listItems;
            }
        }

        return $this->listItems;
    }
}
