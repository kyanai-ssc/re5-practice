<?php
use App\Model\Entity\ReservationStatus;
?>
<?php
    $tempApplicationCount = $eventUnit->getReservationCount(ReservationStatus::STATUS_TYPE_TENTATIVE, ReservationStatus::KEEP_STOCK_FLG_OFF);
    $reservationCount = $eventUnit->getReservationCount(ReservationStatus::STATUS_TYPE_FIXED, ReservationStatus::KEEP_STOCK_FLG_ON)
        + $eventUnit->getReservationCount(ReservationStatus::STATUS_TYPE_VISIT, ReservationStatus::KEEP_STOCK_FLG_ON);
    $tempReservationCount = $eventUnit->getReservationCount(ReservationStatus::STATUS_TYPE_TENTATIVE, ReservationStatus::KEEP_STOCK_FLG_ON)
?>
<?php if ($tempApplicationCount > 0): ?>[<?= h($tempApplicationCount) ?>]<?php endif; ?> <?= h($reservationCount) ?>件 <?php if ($tempReservationCount > 0): ?>(<?= h($tempReservationCount) ?>)<?php endif; ?><span>残<?= h($eventUnit->getDisplayRemainStock()) ?>/<?= h($eventUnit->getAllStock()) ?></span>
