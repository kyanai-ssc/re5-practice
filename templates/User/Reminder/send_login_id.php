<?php
$this->assign('title', $this->Tr->t('pageTitle/idReminder/finish'));
$this->Breadcrumbs->add(
    $this->Tr->t('pageTitle/idReminder/finish')
);
?>

<section class="contents-area l-remind">
    <fieldset class="input-remind mgb-40">
        <p class="mgt-10"><?= $this->Tr->nl2br('reminder/id/finish') ?></p>

        <p class="cmn-txt mgt-40 tac mgb-20">
            <?= $this->Html->link(
                $this->Tr->t('common/backBtn'),
                [
                    'prefix' => 'User',
                    'controller' => 'Auth',
                    'action' => 'login'
                ],
                ['class' => ['cmn-btn', 'is-blue']]
            ); ?>
        </p>
    </fieldset>
</section>

