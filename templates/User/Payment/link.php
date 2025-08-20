<?php
$this->assign('title', $this->Tr->t('pageTitle/paymentLink'));
$this->Breadcrumbs->add(
    $this->Tr->t('pageTitle/calendar'),
    [
        'prefix' => 'User',
        'controller' => 'Reservations',
        'action' => 'calendar',
    ]
);
$this->Breadcrumbs->add(
    $this->Tr->t('pageTitle/reservationsAdd')
);
?>
<section class="contents-area l-main">
    <p class="cmn-txt mgt-20">
        <?= $this->Tr->nl2br('paymentLink/message') ?>
    </p>
    <form
        method="post"
        class="js_submit_once js_auto_submit" action="<?= h($this->Payment->getLinkPaymentUrl()) ?>"
        accept-charset="Shift_JIS"
    >
        <div class="hidden">
            <?php foreach ($linkPaymentForm->createLinkParameter() as $key => $value): ?>
                <input type="hidden" name="<?= h($key) ?>" value="<?= h($value) ?>"/>
            <?php endforeach; ?>
        </div>
        <fieldset class="input-info">
            <p class="cmn-txt mgt-40 tac mgb-20 btn-wrap clearfix">
                <?= $this->Form->button($this->Tr->t('paymentLink/paymentBtn'), [
                    'type' => 'submit',
                    'class' => ['cmn-btn', 'is-blue'],
                ]) ?>
            </p>
        </fieldset>
    </form>
</section>
