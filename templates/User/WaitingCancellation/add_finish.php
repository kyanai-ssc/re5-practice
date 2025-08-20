<?php
$this->assign('title', $this->Tr->t('pageTitle/waitingCancellation/addFinish'));
$this->Breadcrumbs->add(
    $this->Tr->t('pageTitle/waitingCancellation/addFinish')
);
$this->assign('noNavi', true);
?>
<section class="contents-area l-main">
    <h3 class="ttl-sec"><?= $this->Tr->t('pageTitle/waitingCancellation/addFinish') ?></h3>
    <fieldset class="input-remind mgb-40">
        <p class="mgt-10"><?= $this->Tr->nl2br('waitingCancellation/finishMessage') ?></p>
        <p class="cmn-txt mgt-40 tac mgb-20">
            <?= $this->Form->button($this->Tr->t('waitingCancellation/closeButton'), [
                'type' => 'submit',
                'class' => ['cmn-btn', 'is-blue', 'js_close_window'],
            ]) ?>
        </p>
    </fieldset>
</section>
