<?php
declare(strict_types=1);

namespace App\Form\Admin\Users;

use App\Form\Admin\Reservations\SearchFormTrait as ReservationSearchFormTrait;
use App\Form\AppForm;
use App\Model\Entity\AdminSearchItem;
use Cake\Core\Configure;
use Cake\Core\Exception\CakeException;
use Cake\Form\Schema;
use Cake\Validation\Validator;

/**
 * 会員検索フォーム
 */
class SearchForm extends AppForm
{
    use ReservationSearchFormTrait;
    use SearchFormTrait;

    /**
     * @var array|null
     */
    protected $searchItems = null;

    /**
     * @var array|null
     */
    protected $listItems = null;

    /**
     * @inheritDoc
     */
    protected function _buildSchema(Schema $schema): Schema
    {
        $schema = $this->buildUserSearchSchema($schema, $this->getSearchItems());
        $schema = $this->buildReservationSearchSchema($schema, $this->getSearchItems());

        $this->addPaginateSchema($schema);

        return $schema;
    }

    /**
     * @inheritDoc
     */
    public function validationDefault(Validator $validator): Validator
    {
        $validator = $this->buildUserSearchValidator($validator);
        $validator = $this->buildReservationSearchValidator($validator);

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

        $fieldValueOptions = [
            'sort' => $userFieldValueOptions['sort'],
            'listCheck' => Configure::readOrFail('Master.common.listCheck'),
            'listCheckId' => Configure::readOrFail('Master.common.listCheckId'),
        ];
        $fieldValueOptions += $this->buildPaginateFieldValueOptions() + $userFieldValueOptions
            + $this->buildReservationSearchFieldValueOptions();

        return $fieldValueOptions;
    }

    /**
     * @inheritDoc
     */
    protected function buildDefaultFieldValues()
    {
        $userDefaultFieldValues = $this->buildUserSearchDefaultFieldValues();
        $reservationDefaultFieldValues = $this->buildReservationSearchDefaultFieldValues('users');

        $defaultFieldValues = [
            'sort' => $userDefaultFieldValues['sort'],
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
                'inputs' => ['type' => AdminSearchItem::TYPE_USER_LIST],
            ])->first();
            if (!is_array($searchItems)) {
                throw new CakeException();
            }
            $this->searchItems = $searchItems;
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
                'inputs' => ['type' => AdminSearchItem::TYPE_USER_LIST],
            ])->first();
            if (!is_array($listItems)) {
                throw new CakeException();
            }
            $this->listItems = $listItems;
        }

        return $this->listItems;
    }
}
