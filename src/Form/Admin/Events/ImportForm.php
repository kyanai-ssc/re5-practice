<?php
declare(strict_types=1);

namespace App\Form\Admin\Events;

use App\Form\Admin\ImportFormInterface;
use App\Form\Admin\ImportFormTrait;
use App\Form\AppForm;
use App\Model\Table\EventPlansTable;
use App\Model\Table\EventRemarksTable;
use App\Model\Table\EventSmartLocksTable;
use App\Model\Table\EventsTable;
use App\Model\Table\EventStockMarksTable;
use App\Utility\DateTimeUtility;
use App\Utility\SmartLock\SmartLockLinkage;
use Cake\Core\Configure;
use Cake\Datasource\EntityInterface;
use Cake\Utility\Hash;
use Cake\Validation\Validation;

/**
 * インポートフォーム
 */
class ImportForm extends AppForm implements ImportFormInterface
{
    use ImportFormTrait;

    /**
     * @inheritDoc
     */
    protected function createCsvHeader(): array
    {
        /** @var \App\Model\Table\EventsTable $eventsTable */
        $eventsTable = $this->getTableLocator()->get('Events');

        $csvHeader = $eventsTable->generateCsvHeader();

        return $csvHeader;
    }

    /**
     * @inheritDoc
     */
    protected function formatCsvData(array $data): array
    {
        $result = [];
        $asHeader = Configure::readOrFail('Setting.csv.download.event.associationsHeader');
        foreach ($data as $key => $value) {
            $result[$key] = null;
            if (Validation::notBlank($value)) {
                $format = [];
                switch ($key) {
                    case EventsTable::CSV_COLUMN_EVENT_PLANS:
                        foreach ($this->csvFormat()->inputsForHasMany($value) as $many) {
                            $eventPlans = $this->csvFormat()->inputsForMultiple($many);
                            $formatEventPlans = [];
                            foreach (array_keys($asHeader[$key]) as $eventPlanKey => $eventPlanHeader) {
                                if ($eventPlanHeader === EventPlansTable::CSV_COLUMN_PUBLIC_FLG) {
                                    $eventPlans[$eventPlanKey] = $this->csvFormat()->inputForId(
                                        Hash::get($eventPlans, (string)$eventPlanKey)
                                    );
                                }

                                $formatEventPlans[$eventPlanHeader] = Hash::get($eventPlans, (string)$eventPlanKey);
                            }
                            $format[] = $formatEventPlans;
                        }
                        $result[$key] = $format;

                        break;

                    case EventsTable::CSV_COLUMN_EVENT_STOCK_MARKS:
                        foreach ($this->csvFormat()->inputsForHasMany($value) as $eventStockIndex => $many) {
                            $eventStockMarks = $this->csvFormat()->inputsForMultiple($many);
                            $formatEventStockMarks = [];
                            foreach (array_keys($asHeader[$key]) as $eventSmKey => $eventSmHeader) {
                                if ($eventSmHeader === EventStockMarksTable::CSV_COLUMN_SYMBOLIC) {
                                    $eventStockMarks[$eventSmKey] = $this->csvFormat()->inputForId(
                                        Hash::get($eventStockMarks, (string)$eventSmKey)
                                    );
                                }

                                $formatEventStockMarks[$eventSmHeader] = Hash::get(
                                    $eventStockMarks,
                                    (string)$eventSmKey
                                );
                            }
                            $format[$eventStockIndex] = $formatEventStockMarks;
                        }
                        $result[$key] = $format;
                        break;
                    case EventsTable::CSV_COLUMN_EVENT_IMAGES:
                        foreach ($this->csvFormat()->inputsForHasMany($value) as $eventImagesIndex => $many) {
                            $format[$eventImagesIndex]['url'] = $this->csvFormat()->inputForId($many);
                        }
                        $result[$key] = $format;

                        break;
                    case EventsTable::CSV_COLUMN_EVENT_TAGS:
                        foreach ($this->csvFormat()->inputsForHasMany($value) as $eventTagIndex => $many) {
                            $format[$eventTagIndex]['tag_id'] = $this->csvFormat()->inputForId($many);
                        }
                        $result[$key] = $format;

                        break;
                    case EventsTable::CSV_COLUMN_EVENT_REMARKS:
                        $remarks = $this->csvFormat()->inputsForHasMany($value, count($asHeader[$key]));
                        foreach ($remarks as $eventRemarksIndex => $many) {
                            $eventRemarks = $this->csvFormat()->inputsForMultiple($many);
                            $formatEventRemarks = [];
                            foreach (array_keys($asHeader[$key]) as $eventRemarksKey => $eventRemarksHeader) {
                                if (
                                    $eventRemarksHeader === EventRemarksTable::CSV_COLUMN_DETAIL_DISPLAY_FLG
                                    || $eventRemarksHeader === EventRemarksTable::CSV_COLUMN_FORM_ITEM_ID
                                ) {
                                    $eventRemarks[$eventRemarksKey] = $this->csvFormat()->inputForId(
                                        Hash::get($eventRemarks, (string)$eventRemarksKey)
                                    );
                                }
                                $formatEventRemarks[$eventRemarksHeader] = Hash::get(
                                    $eventRemarks,
                                    (string)$eventRemarksKey
                                );
                            }
                            $format[$eventRemarksIndex] = $formatEventRemarks;
                        }
                        $result[$key] = $format;
                        break;
                    case EventsTable::CSV_COLUMN_EVENT_WEEKS:
                        foreach ($this->csvFormat()->inputsForHasMany($value) as $eventWeeksIndex => $many) {
                            $format[$eventWeeksIndex]['week'] = $this->csvFormat()->inputForId($many);
                        }
                        $result[$key] = $format;
                        break;
                    case EventsTable::CSV_COLUMN_BACKGROUND_COLOR_REPLACE_ADMIN:
                    case EventsTable::CSV_COLUMN_BACKGROUND_COLOR_REPLACE_FRONT:
                    case EventsTable::CSV_COLUMN_FORMAT_TYPE_DISPLAY:
                        foreach ($this->csvFormat()->inputsForMultiple($value) as $multi) {
                            $format[] = $this->csvFormat()->inputForId($multi);
                        }
                        $result[$key] = $format;
                        break;
                    case EventsTable::CSV_COLUMN_ORGANIZER_ID:
                    case EventsTable::CSV_COLUMN_LABEL_ID:
                    case EventsTable::CSV_COLUMN_TYPE:
                    case EventsTable::CSV_COLUMN_FORM_PATTERN_ID:
                    case EventsTable::CSV_COLUMN_RESERVATION_STATUS_ID:
                    case EventsTable::CSV_COLUMN_TIME_PLAN:
                    case EventsTable::CSV_COLUMN_MULTIPLE_TIME_PLAN_TYPE:
                    case EventsTable::CSV_COLUMN_BACKGROUND_COLOR_TYPE:
                    case EventsTable::CSV_COLUMN_COLOR_CHIP_ID:
                    case EventsTable::CSV_COLUMN_STOCK_DISPLAY_TYPE:
                    case EventsTable::CSV_COLUMN_USAGE_TIME_NOTATION:
                    case EventsTable::CSV_COLUMN_REGISTRATION_DEADLINE_TYPE:
                    case EventsTable::CSV_COLUMN_EDITING_DEADLINE_TYPE:
                    case EventsTable::CSV_COLUMN_CANCELLATION_DEADLINE_TYPE:
                    case EventsTable::CSV_COLUMN_WAITING_CANCELLATION_FLG:
                    case EventsTable::CSV_COLUMN_DUPLICATION_CHECK_FLG:
                    case EventsTable::CSV_COLUMN_PUBLIC_FLG:
                    case EventsTable::CSV_COLUMN_QR_CODE_FLG:
                    case EventsTable::CSV_COLUMN_REGISTRATION_DEADLINE_CRITERION:
                    case EventsTable::CSV_COLUMN_EDITING_DEADLINE_CRITERION:
                    case EventsTable::CSV_COLUMN_CANCELLATION_DEADLINE_CRITERION:
                        $result[$key] = $this->csvFormat()->inputForId($value);
                        break;

                    case EventsTable::CSV_COLUMN_ID:
                    case EventsTable::CSV_COLUMN_CREATED:
                    case EventsTable::CSV_COLUMN_MODIFIED:
                        break;

                    case EventsTable::CSV_COLUMN_TIME_FROM:
                    case EventsTable::CSV_COLUMN_TIME_TO:
                    case EventsTable::CSV_COLUMN_RECEPTION_PERIOD_TIME:
                    case EventsTable::CSV_COLUMN_REGISTRATION_DEADLINE_TIME:
                    case EventsTable::CSV_COLUMN_EDITING_DEADLINE_TIME:
                    case EventsTable::CSV_COLUMN_CANCELLATION_DEADLINE_TIME:
                        $result[$key] = DateTimeUtility::zeroPaddingTime($value);
                        break;

                    case EventsTable::CSV_COLUMN_DATE_FROM:
                    case EventsTable::CSV_COLUMN_DATE_TO:
                        $result[$key] = DateTimeUtility::zeroPaddingDate($value);
                        break;
                    case EventsTable::CSV_COLUMN_PUBLIC_FROM:
                    case EventsTable::CSV_COLUMN_PUBLIC_TO:
                        $result[$key] = DateTimeUtility::zeroPaddingDateTime($value);
                        break;

                    case EventsTable::CSV_COLUMN_EVENT_SMART_LOCK:
                        $eventSmartLock = $this->csvFormat()->inputsForMultiple($value);
                        $formatEventSmartLock = [];
                        /** @var \App\Model\Table\SystemSettingsTable $systemSettingsTable */
                        $systemSettingsTable = $this->getTableLocator()->get('SystemSettings');
                        if (!$systemSettingsTable->getData()->useSmartLock()) {
                            break;
                        }
                        $smartLock = new SmartLockLinkage();
                        if ($smartLock->useRemoteLock()) {
                            $header = $asHeader[$key]['remoteLock'];
                        } elseif ($smartLock->useAkerun()) {
                            $header = $asHeader[$key]['akerun'];
                        } else {
                            break;
                        }
                        foreach (array_keys($header) as $eventSmartLocksKey => $eventSmartLocksHeader) {
                            if ($eventSmartLocksHeader === EventSmartLocksTable::CSV_COLUMN_SMART_LOCK_KEY_URL_FLG) {
                                $eventSmartLock[$eventSmartLocksKey] = $this->csvFormat()->inputForId(
                                    Hash::get($eventSmartLock, (string)$eventSmartLocksKey)
                                );
                            }
                            $formatEventSmartLock[$eventSmartLocksHeader] = Hash::get(
                                $eventSmartLock,
                                (string)$eventSmartLocksKey
                            );
                        }
                        $result[$key] = $formatEventSmartLock;
                        break;

                    default:
                        $result[$key] = $value;
                        break;
                }
            }
        }

        return $result;
    }

    /**
     * @inheritDoc
     */
    protected function createEntity(array $data): ?EntityInterface
    {
        $eventsTable = $this->getTableLocator()->get('Events');

        $entity = $eventsTable->newEntity($data, [
            'associated' => [
                'EventWeeks',
                'EventTags',
                'EventPlans',
                'EventStockMarks',
                'EventImages',
                'EventRemarks',
                'EventHolidays',
                'EventHolidays.EventHolidayWeeks',
                'EventHolidays.EventHolidayExcludeDates',
                'EventStockSettings',
                'EventStockSettings.EventStockSettingWeeks',
                'EventStockSettings.EventStockSettingExcludeDates',
                'EventSmartLocks',
            ],
        ]);

        return $entity;
    }

    /**
     * @inheritDoc
     */
    protected function formatErrors(array $errors): array
    {
        $message = [];
        $asHeader = Configure::readOrFail('Setting.csv.download.event.associationsHeader');

        foreach ($errors as $messageKey => $value) {
            $subject = Hash::get((array)$this->csvHeader, (string)$messageKey);
            $asMessage = [];
            switch ($messageKey) {
                case EventsTable::CSV_COLUMN_EVENT_PLANS:
                case EventsTable::CSV_COLUMN_EVENT_STOCK_MARKS:
                case EventsTable::CSV_COLUMN_EVENT_REMARKS:
                    $depth = Hash::dimensions($value);
                    if (is_array($value) && $depth > 1) {
                        $asMessage['subject'] = $subject;
                        $asMessage['body'] = $this->formatErrorMessageForAssociation(
                            $subject,
                            $value,
                            $asHeader[$messageKey]
                        );
                        $message[] = $asMessage;
                    } elseif (is_array($value)) {
                        $body = implode(Configure::readOrFail('Setting.csv.import.error.delimiter'), $value);
                        $message[] = $subject . Configure::readOrFail('Setting.csv.import.error.separator') . $body;
                    }
                    break;
                case EventsTable::CSV_COLUMN_EVENT_WEEKS:
                case EventsTable::CSV_COLUMN_EVENT_TAGS:
                case EventsTable::CSV_COLUMN_EVENT_IMAGES:
                    $depth = Hash::dimensions($value);
                    if (is_array($value) && $depth > 1) {
                        $asMessage['subject'] = $subject;
                        $asMessage['body'] = $this->formatErrorMessageForAssociation($messageKey, $value);
                        $message[] = $asMessage;
                    } elseif (is_array($value)) {
                        $body = implode(Configure::readOrFail('Setting.csv.import.error.delimiter'), $value);
                        $message[] = $subject . Configure::readOrFail('Setting.csv.import.error.separator') . $body;
                    }
                    break;
                case EventsTable::CSV_COLUMN_EVENT_SMART_LOCK:
                    $depth = Hash::dimensions($value);
                    if (is_array($value) && $depth > 1) {
                        /** @var \App\Model\Table\SystemSettingsTable $systemSettingsTable */
                        $systemSettingsTable = $this->getTableLocator()->get('SystemSettings');
                        if (!$systemSettingsTable->getData()->useSmartLock()) {
                            break;
                        }
                        $smartLock = new SmartLockLinkage();
                        if ($smartLock->useRemoteLock()) {
                            $header = $asHeader[$messageKey]['remoteLock'];
                        } elseif ($smartLock->useAkerun()) {
                            $header = $asHeader[$messageKey]['akerun'];
                        } else {
                            break;
                        }
                        $asMessage['subject'] = $subject;
                        $associationMessages = [];
                        foreach ($value as $asKey => $asValue) {
                            if (!empty($header)) {
                                $asSubject = $header[$asKey];
                            } else {
                                $asSubject = '';
                            }
                            $asBody = implode(Configure::readOrFail('Setting.csv.import.error.delimiter'), $asValue);
                            $separator = Configure::readOrFail('Setting.csv.import.error.separator');
                            $associationMessages[] = $asSubject . $separator . $asBody;
                        }
                        $asMessage['body'] = $associationMessages;
                        $message[] = $asMessage;
                    } elseif (is_array($value)) {
                        $body = implode(Configure::readOrFail('Setting.csv.import.error.delimiter'), $value);
                        $message[] = $subject . Configure::readOrFail('Setting.csv.import.error.separator') . $body;
                    }
                    break;
                default:
                    $body = implode(Configure::readOrFail('Setting.csv.import.error.delimiter'), $value);
                    $message[] = $subject . Configure::readOrFail('Setting.csv.import.error.separator') . $body;
                    break;
            }
        }

        return $message;
    }
}
