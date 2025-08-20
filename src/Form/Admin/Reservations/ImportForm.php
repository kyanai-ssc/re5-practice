<?php
declare(strict_types=1);

namespace App\Form\Admin\Reservations;

use App\Form\Admin\ImportFormInterface;
use App\Form\Admin\ImportFormTrait;
use App\Form\AppForm;
use App\Model\Entity\Event;
use App\Model\Entity\Reservation;
use App\Model\Table\FormItemsTable;
use App\Model\Table\ReservationsTable;
use Cake\Core\Configure;
use Cake\Datasource\EntityInterface;
use Cake\Utility\Hash;

/**
 * インポートフォーム
 */
class ImportForm extends AppForm implements ImportFormInterface
{
    use ImportFormTrait;

    /**
     * @var array
     */
    protected $headerColumns = null;

    /**
     * @var array
     */
    protected $csvItems = null;

    /**
     * @inheritDoc
     */
    protected function initialize()
    {
        /** @var \App\Model\Table\SystemSettingsTable $systemSettingsTable */
        $systemSettingsTable = $this->getTableLocator()->get('SystemSettings');
        /** @var \App\Model\Table\FormItemsTable $formItemsTable */
        $formItemsTable = $this->getTableLocator()->get('FormItems');

        $columns = Configure::readOrFail('Setting.csv.import.reservation.header');
        if (!$systemSettingsTable->getData()->usePayment()) {
            unset($columns[FormItemsTable::CSV_COLUMN_PAYMENT_METHOD]);
            unset($columns[FormItemsTable::CSV_COLUMN_PAYMENT_STATUS]);
        }

        $this->headerColumns = $columns;
        $this->csvItems = $formItemsTable->generateCsvItems('input', array_keys($this->headerColumns));
    }

    /**
     * @inheritDoc
     */
    protected function createCsvHeader(): array
    {
        /** @var \App\Model\Table\FormItemsTable $formItemsTable */
        $formItemsTable = $this->getTableLocator()->get('FormItems');

        $csvHeader = $formItemsTable->generateCsvHeader($this->csvItems, $this->headerColumns);

        return $csvHeader;
    }

    /**
     * @inheritDoc
     */
    protected function formatCsvData(array $data): array
    {
        $result = [];
        $columns = Configure::readOrFail('Setting.csv.import.reservation.column');
        foreach ($this->csvItems as $key => $csvItem) {
            if (is_array($csvItem)) {
                foreach ($csvItem as $item) {
                    $result = Hash::merge($result, $this->formatItemData($data, $key, $item, $columns));
                }
            } else {
                $result = Hash::merge($result, $this->formatItemData($data, $key, $csvItem, $columns));
            }
        }
        if (!isset($result['reservations']['charge'])) {
            $result['reservations']['calculate_charge'] = Reservation::CALCULATE_CHARGE_ON;
        }

        return $result;
    }

    /**
     * @inheritDoc
     */
    protected function createEntity(array $data): ?EntityInterface
    {
        $date = Hash::get($data, 'reservations.usage_timestamp_from_date');
        $time = Hash::get($data, 'reservations.usage_timestamp_from_time');

        $reservationForm = new ReservationForm();
        $reservationForm->setReservationParameter([
            'reservation_type' => ReservationsTable::RESERVATION_TYPE_EXISTING_USER,
            'user_id' => Hash::get($data, 'reservations.user_id'),
            'event_id' => Hash::get($data, 'reservations.event_id'),
            'usage_timestamp_from' => $date . ' ' . $time,
        ]);
        if (!$reservationForm->validateReservationParameter()) {
            $errors = $reservationForm->getErrors();
            if (isset($errors['reservations'])) {
                $this->setErrors($errors['reservations']);
            }

            return null;
        }

        $event = $reservationForm->getEventEntity();
        if (
            isset($event)
            && (string)$event->get('time_plan') === (string)Event::PLAN_MULTIPLE
            && (string)$event->get('multiple_time_plan_type') === (string)Event::MULTIPLE_TIME_PLAN_TYPE_SINGLE
        ) {
            $planValues = Hash::get($data, 'reservations.plan_values');
            if (is_array($planValues) && !empty($planValues)) {
                $singlePlan = array_shift($planValues);
                $planValues = array_merge(['single' => $singlePlan], $planValues);
                $data['reservations']['plan_values'] = $planValues;
                $this->csvData = $data;
            }
        }

        $reservationForm->execute($data);

        $this->isInvalidData = $this->isInvalidDataForFormItem($reservationForm->getReservationFormGroups());

        return $reservationForm->getReservationEntity();
    }

    /**
     * @inheritDoc
     */
    protected function formatErrors(array $errors): array
    {
        $result = [];

        if (isset($errors['usage_timestamp_from'])) {
            $messages = $errors['usage_timestamp_from'];
            if (!empty($messages)) {
                $name = implode('、', [
                    Hash::get((array)$this->csvHeader, (string)FormItemsTable::CSV_COLUMN_USAGE_DATE),
                    Hash::get((array)$this->csvHeader, (string)FormItemsTable::CSV_COLUMN_USAGE_TIME),
                ]);
                $separator = Configure::readOrFail('Setting.csv.import.error.separator');
                $delimiter = Configure::readOrFail('Setting.csv.import.error.delimiter');
                $result[] = $name . $separator . implode($delimiter, Hash::flatten($messages));
            }
        }

        $columns = Configure::readOrFail('Setting.csv.import.reservation.column');
        foreach ($this->csvItems as $key => $csvItem) {
            if (is_array($csvItem)) {
                foreach ($csvItem as $item) {
                    $result = Hash::merge($result, $this->formatItemErrors($errors, $key, $item, $columns));
                }
            } else {
                $result = Hash::merge($result, $this->formatItemErrors($errors, $key, $csvItem, $columns));
            }
        }

        if (isset($errors['event_error'])) {
            $result = Hash::merge($result, $errors['event_error']);
        }

        return $result;
    }

    /**
     * @inheritDoc
     */
    public function getInfoMessages()
    {
        /** @var \App\Model\Table\ReservationVideoMeetingsTable $reservationVideoMeetingsTable */
        $reservationVideoMeetingsTable = $this->getTableLocator()->get('ReservationVideoMeetings');

        $messages = [];

        // ビデオ会議連携のエラーメッセージ
        foreach ($reservationVideoMeetingsTable->flushErrorMessages() as $message) {
            $messages[] = $message;
        }

        return $messages;
    }
}
