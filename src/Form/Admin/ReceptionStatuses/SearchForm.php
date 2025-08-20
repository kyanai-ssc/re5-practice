<?php
declare(strict_types=1);

namespace App\Form\Admin\ReceptionStatuses;

use App\Form\AppForm;
use Cake\Form\Schema;
use Cake\Utility\Hash;
use Cake\Validation\Validator;

/**
 * 予約検索フォーム
 */
class SearchForm extends AppForm
{
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
        $schema = $this->buildReservationSearchSchema($schema);
        $this->addPaginateSchema($schema);

        return $schema;
    }

    /**
     * @inheritDoc
     */
    public function validationDefault(Validator $validator): Validator
    {
        $validator = $this->buildReceptionStatusesSearchValidator($validator);

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
        return [
            'sort' => Hash::combine([
                'Reservations.id',
            ], '{*}'),
        ] + $this->buildPaginateFieldValueOptions();
    }

    /**
     * @inheritDoc
     */
    protected function buildDefaultFieldValues()
    {
        $reservationDefaultFieldValues = $this->buildReservationSearchDefaultFieldValues();

        $defaultFieldValues = [
            'sort' => $reservationDefaultFieldValues['sort'],
        ];
        $defaultFieldValues += $this->buildPaginateDefaultFieldValues() + $reservationDefaultFieldValues;

        return $defaultFieldValues;
    }
}
