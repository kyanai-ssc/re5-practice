<?php
$this->assign('title', $this->Tr->t('pageTitle/waitingCancellation/tokenFinish'));
$this->Breadcrumbs->add(
    $this->Tr->t('pageTitle/waitingCancellation/tokenFinish')
);
?>

<section class="contents-area l-remind">
    <h3 class="ttl-sec"><?= $this->Tr->t('pageTitle/waitingCancellation/Token') ?></h3>
    <fieldset class="input-remind mgb-40">
        <p class="mgt-10"><?= $this->Tr->nl2br('waitingCancellation/tokenFinish') ?></p>

        <p class="cmn-txt mgt-40 tac mgb-20">
            <?= $this->Template->userTopBtn() ?>
        </p>
    </fieldset>
</section>

