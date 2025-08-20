<fieldset class="input-info">
    <table class="input-box">
        <tr class="field-input">
            <th class="ttl-input">
                <div class="ttl-input-wrap">
                    <?= $this->Tr->h('waitingCancellation/eventName') ?>
                </div>
            </th>
            <td>
                <p class="cmn-txt">
                    <?= h($waitingCancellationForm->getEventEntity()->get('name')) ?>
                </p>
            </td>
        </tr>
        <tr class="field-input">
            <th class="ttl-input">
                <div class="ttl-input-wrap">
                    <?= $this->Tr->h('waitingCancellation/usageTimestamp') ?>
                </div>
            </th>
            <td>
                <p class="cmn-txt">
                    <?= h($this->Template->displayDayAndWeek($waitingCancellationForm->getParameter('usage_timestamp'), 'H:i')) ?>
                </p>
            </td>
        </tr>
        <?php if (!$this->CommonData->existsUserLoginData()): ?>
            <tr class="field-input">
                <th class="ttl-input">
                    <div class="ttl-input-wrap">
                        <?= $this->Tr->h('waitingCancellation/mail') ?>
                    </div>
                </th>
                <td>
                    <p class="cmn-txt">
                        <?= $this->Form->control('mail', [
                            'type' => 'text',
                        ]) ?>
                    </p>
                </td>
            </tr>
        <?php endif; ?>
    </table>
</fieldset>
<p class="cmn-txt mgt-10"><?= $this->Tr->h('waitingCancellation/description') ?></p>
<fieldset class="input-info">
    <p class="cmn-txt mgt-40 tac mgb-20 btn-wrap clearfix">
        <?= $this->Form->button($this->Tr->t('waitingCancellation/addBtn'), [
            'type' => 'submit',
            'class' => ['cmn-btn', 'is-blue'],
        ]) ?>
    </p>
</fieldset>
<?php foreach ($waitingCancellationForm->getParameter() as $key => $value): ?>
    <input type="hidden" class="js_waiting_cancellation_parameter" value="<?= h($value) ?>" data-name="<?= h($key) ?>"/>
<?php endforeach; ?>
