<?php
declare(strict_types=1);

namespace App\Form\Common\Reservations;

use App\Form\AppForm;
use App\Form\Common\CommonFormTrait;
use App\Form\ConfirmTransitionTrait;
use Cake\Form\Schema;

/**
 * 予約フォーム
 */
abstract class ReservationForm extends AppForm
{
    use CommonFormTrait;
    use ConfirmTransitionTrait;
    use ReservationFormTrait;

    /**
     * @inheritDoc
     */
    protected function _buildSchema(Schema $schema): Schema
    {
        $schema = $this->buildReservationSchema($schema);

        return $schema;
    }

    /**
     * @inheritDoc
     */
    public function validate(array $data, ?string $validator = null): bool
    {
        if (!isset($data['reservations']) || !is_array($data['reservations'])) {
            $data['reservations'] = [];
        }
        $data['reservations'] += $this->getReservationParameter();

        $result = parent::validate($data);
        if (!$this->validateReservation($this->getData())) {
            $result = false;
        }

        return $result;
    }

    /**
     * @inheritDoc
     */
    protected function buildFieldValueOptions()
    {
        $fieldValueOptions = $this->buildReservationFieldValueOptions();

        return $fieldValueOptions;
    }
}
