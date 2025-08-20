<span
    class="<?= h($this->Configure->read('Master.reservation.statusClass.' . $this->Master->getReservationStatusType($reservationStatusId))) ?> status-cahge-btn2 <?php if ($canChangeStatus): ?>js_update_status<?php endif; ?>"
    data-reservation-id="<?= h($reservationId) ?>"
>
    <?= h($this->Master->getReservationStatusName($reservationStatusId)) ?>
</span>
