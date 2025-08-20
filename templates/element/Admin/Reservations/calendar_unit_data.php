<?php if (!$calendar->isReserveDataDisplay($eventUnit)): ?>
    <?= $this->element('Admin/Reservations/calendar_unit_stock', [
        'eventUnit' => $eventUnit,
    ]) ?>
<?php else: ?>
    <?= h($eventUnit->getReservationData()) ?>
<?php endif; ?>
