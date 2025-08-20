<?php if ($reservationForm->requiredReservationSctl()): ?>
    <h3 class="ttl-sec mgt-40"><?= $this->Tr->h('common/sctl') ?></h3>
    <fieldset class="input-info">
        <div class="userPolicy wysiwyg-area">
            <?= $reservationForm->getReservationSctl() ?>
        </div>
    </fieldset>
<?php endif; ?>
