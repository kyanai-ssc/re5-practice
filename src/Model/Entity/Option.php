<?php
declare(strict_types=1);

namespace App\Model\Entity;

use App\Model\AppEntity;
use App\Utility\DateTimeUtility;
use Cake\Core\Configure;
use Cake\Core\Exception\CakeException;
use Cake\I18n\FrozenTime;

/**
 * Option Entity
 *
 * @property int $id
 * @property string $name
 * @property \Cake\I18n\FrozenTime|null $usage_timestamp_from
 * @property \Cake\I18n\FrozenTime|null $usage_timestamp_to
 * @property int|null $stock
 * @property string|null $stock_unit
 * @property int $charge
 * @property int $public_flg
 * @property string|null $description
 * @property \Cake\I18n\FrozenTime $created
 * @property \Cake\I18n\FrozenTime $modified
 * @property int $option_unit_time
 *
 * @property \App\Model\Entity\FormItemOption[] $form_item_options
 * @property \App\Model\Entity\OptionStockSetting[] $option_stock_settings
 * @property \App\Model\Entity\ReservationOption[] $reservation_options
 */
class Option extends AppEntity
{
    /**
     * 公開フラグ：オン
     */
    public const PUBLIC_FLG_ON = 1;

    /**
     * 公開フラグ：オフ
     */
    public const PUBLIC_FLG_OFF = 0;

    /**
     * @inheritDoc
     */
    protected $_accessible = [
        'name' => true,
        'usage_timestamp_from' => true,
        'usage_timestamp_to' => true,
        'stock' => true,
        'stock_unit' => true,
        'charge' => true,
        'public_flg' => true,
        'description' => true,
        'created' => false,
        'modified' => false,
        'form_item_options' => true,
        'option_stock_settings' => true,
        'reservation_options' => false,
        'option_unit_time' => true,
    ];

    /**
     * 削除可否判定
     *
     * @return bool
     */
    public function canDelete()
    {
        if (!empty($this->get('reservation_options')) || !empty($this->get('form_item_options'))) {
            return false;
        }

        return true;
    }

    /**
     * 残り在庫をチェック
     *
     * @param string|\DateTimeInterface|null $dateTimeFrom 開始日時
     * @param string|\DateTimeInterface|null $dateTimeTo 終了日時
     * @param int|null $excludeReservationId 除外する予約ID
     * @param array|null $reservationOptions 予約中のオプション
     * @param array|null $excludeDateTime 除外日時
     * @return bool
     */
    public function checkRemainStock(
        $dateTimeFrom = null,
        $dateTimeTo = null,
        ?int $excludeReservationId = null,
        ?array $reservationOptions = null,
        ?array $excludeDateTime = null
    ) {
        /** @var \App\Model\Table\ReservationOptionsTable $reservationOptionsTable */
        $reservationOptionsTable = $this->getTableLocator()->get('ReservationOptions');

        $dateTimeFrom = DateTimeUtility::convertToDateTimeObject($dateTimeFrom);
        $dateTimeTo = DateTimeUtility::convertToDateTimeObject($dateTimeTo);

        // 予約日時の最小値と最大値
        if (empty($reservationOptions)) {
            $reservationOptionMinMaxDateTime = $reservationOptionsTable->find('minMaxDateTime', [
                'inputs' => [
                    'option_id' => $this->get('id'),
                    'usage_timestamp_from' => $dateTimeFrom,
                    'usage_timestamp_to' => $dateTimeTo,
                ],
            ])->first();
            if (!isset($reservationOptionMinMaxDateTime)) {
                return true;
            }

            if (!isset($dateTimeFrom) || $dateTimeFrom < $reservationOptionMinMaxDateTime['min']) {
                $dateTimeFrom = $reservationOptionMinMaxDateTime['min'];
            }
            if (!isset($dateTimeTo) || $dateTimeTo > $reservationOptionMinMaxDateTime['max']) {
                $dateTimeTo = $reservationOptionMinMaxDateTime['max'];
            }
        }

        // 1ヶ月単位でチェック
        if (!isset($dateTimeFrom) || !isset($dateTimeTo)) {
            throw new CakeException();
        }
        $current = $dateTimeFrom;
        $limit = $dateTimeTo;
        while ($current < $limit) {
            $from = $current;
            $to = new FrozenTime($from->format('Y-m-01'));
            $to = $to->addMonths(1);
            if ($to > $limit) {
                $to = $limit;
            }

            $timetable = $this->createReservedTimetable(
                $from,
                $to,
                $excludeReservationId,
                $reservationOptions,
                $excludeDateTime
            );
            foreach ($timetable as $unit) {
                if (isset($unit['stock']) && $unit['stock'] < 0) {
                    return false;
                }
            }

            $current = $to;
        }

        return true;
    }

    /**
     * 予約済みのタイムテーブルを5分単位で生成
     *
     * @param string|\DateTimeInterface $dateTimeFrom 開始日時
     * @param string|\DateTimeInterface $dateTimeTo 終了日時
     * @param int|null $excludeReservationId 除外する予約ID
     * @param array|null $reservationOptions 予約中のオプション
     * @param array|null $excludeDateTime 除外日時
     * @return array
     */
    protected function createReservedTimetable(
        $dateTimeFrom,
        $dateTimeTo,
        $excludeReservationId = null,
        $reservationOptions = null,
        $excludeDateTime = null
    ) {
        /** @var \App\Model\Table\ReservationOptionsTable $reservationOptionsTable */
        $reservationOptionsTable = $this->getTableLocator()->get('ReservationOptions');

        // 在庫を反映する処理
        $timetable = [];
        $interval = Configure::readOrFail('Setting.option.interval');
        $applyStock = function ($data) use (&$timetable, $excludeDateTime, $interval) {
            $current = new FrozenTime($data['usage_timestamp_from']);
            $limit = new FrozenTime($data['usage_timestamp_to']);

            while ($current < $limit) {
                $from = $current;
                $to = clone $from;
                $to = $to->addMinutes($interval);

                $exclude = false;
                foreach ((array)$excludeDateTime as $dateTime) {
                    if ($from < $dateTime['to'] && $to > $dateTime['from']) {
                        $exclude = true;
                    }
                }

                if (!$exclude) {
                    $key = $current->format('Y-m-d H:i');
                    if (!isset($timetable[$key])) {
                        $timetable[$key] = $this->createUnit($from, $to);
                    }
                    if (isset($timetable[$key]['stock'])) {
                        $timetable[$key]['stock'] -= $data['number'];
                    }
                }

                $current = $to;
            }
        };

        // 予約済みの在庫を反映
        $reservedList = $reservationOptionsTable->find('calculateStock', [
            'inputs' => [
                'option_id' => $this->get('id'),
                'usage_timestamp_from' => $dateTimeFrom,
                'usage_timestamp_to' => $dateTimeTo,
                'exclude_reservation_id' => $excludeReservationId,
            ],
        ]);
        foreach ($reservedList as $data) {
            call_user_func($applyStock, $data);
        }

        // 予約中の在庫を反映
        foreach ((array)$reservationOptions as $reservationOption) {
            call_user_func($applyStock, $reservationOption);
        }

        return $timetable;
    }

    /**
     * 枠を生成
     *
     * @param \DateTimeInterface $dateTimeFrom 開始日時
     * @param \DateTimeInterface $dateTimeTo 終了日時
     * @return array
     */
    protected function createUnit($dateTimeFrom, $dateTimeTo)
    {
        $unit = [
            'stock' => $this->get('stock'),
        ];
        foreach ((array)$this->get('option_stock_settings') as $optionStockSetting) {
            if (
                DateTimeUtility::isOverlapDateTime(
                    $dateTimeFrom,
                    $dateTimeTo,
                    $optionStockSetting->get('usage_timestamp_from'),
                    $optionStockSetting->get('usage_timestamp_to')
                )
            ) {
                $unit['stock'] = $optionStockSetting->get('stock');
            }
        }

        return $unit;
    }
}
