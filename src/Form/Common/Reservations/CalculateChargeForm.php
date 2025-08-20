<?php
declare(strict_types=1);

namespace App\Form\Common\Reservations;

use App\Model\Entity\FormGroup;
use App\Model\Entity\Reservation;
use App\Model\InputType\Item\Type\ChargeTypeInterface;
use Cake\Form\Schema;

/**
 * 料金計算フォーム
 */
abstract class CalculateChargeForm extends ReservationForm
{
    /**
     * @inheritDoc
     */
    protected function _buildSchema(Schema $schema): Schema
    {
        $schema = parent::_buildSchema($schema);
        $schema
            ->addField('reservations.user_id', 'string')
            ->addField('reservations.calculate_charge', 'string')
            ->addField('reservations.reservation_status_id', 'string')
            ->addField('reservations.reception_status_id', 'string')
            ->addField('reservations.payment_method_id', 'string')
            ->addField('reservations.payment_status_id', 'string');

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
        $data['reservations']['calculate_charge'] = Reservation::CALCULATE_CHARGE_ON;

        return parent::validate($data);
    }

    /**
     * @inheritDoc
     */
    public function getReservationFormGroups(?int $formType = null)
    {
        if (!isset($this->reservationFormGroups)) {
            $formGroups = parent::getReservationFormGroups($formType);

            $formGroups[FormGroup::FORM_TYPE_USER] = [];
            foreach ($formGroups[FormGroup::FORM_TYPE_RESERVATION] as $index => $formGroup) {
                $formGroup = clone $formGroup;
                $formGroups[FormGroup::FORM_TYPE_RESERVATION][$index] = $formGroup;

                $formItems = [];
                foreach ((array)$formGroup->get('form_items') as $formItem) {
                    if ($formItem->getInputTypeItem() instanceof ChargeTypeInterface) {
                        $formItems[] = $formItem;
                    }
                }
                $formGroup->set('form_items', $formItems);
                $formGroup->clean();
            }

            $this->reservationFormGroups = $formGroups;
        }

        return parent::getReservationFormGroups($formType);
    }

    /**
     * @inheritDoc
     */
    protected function createReservationEntity($data, $options = null)
    {
        if (isset($options['checkRules'])) {
            $options['checkRules'] = false;
        }

        parent::createReservationEntity($data, $options);
    }
}
