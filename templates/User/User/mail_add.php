<?php
$this->assign('title', $this->Tr->t('pageTitle/userMailAdd'));
$this->Breadcrumbs->add(
    $this->Tr->t('pageTitle/userMailAdd')
);
?>
<section class="contents-area l-main">
    <h3 class="ttl-sec"><?= $this->Tr->h('pageTitle/userMailAdd') ?></h3>
    <p class="cmn-txt fwb"><?= $this->Tr->nl2br('userMailAdd/message') ?></p>
    <?= $this->Flash->render('userMailAddErrors') ?>
    <?= $this->Form->create($optinToken, [
        'type' => 'post',
        'url' => [
            'controller' => 'User',
            'action' => 'mail-add',
        ],
        'idPrefix' => 'user-mail-add',
        'novalidate' => true,
        'class' => ['js_submit_once'],
    ]) ?>
        <fieldset class="input-info mgt-20">
            <table class="input-box">
                <tr class="field-input">
                    <th class="ttl-input">
                        <div class="ttl-input-wrap">
                            <?= $this->Tr->h('userMailAdd/mail') ?>
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
                <tr class="field-input">
                    <th class="ttl-input">
                        <div class="ttl-input-wrap">
                            <?= $this->Tr->h('userMailAdd/mailConfirm') ?>
                        </div>
                    </th>
                    <td>
                        <p class="cmn-txt">
                            <?= $this->Form->control('mail_confirm', [
                                'type' => 'text',
                            ]) ?>
                        </p>
                    </td>
                </tr>
            </table>
        </fieldset>
        <fieldset class="input-info">
            <p class="cmn-txt mgt-40 tac mgb-20 btn-wrap clearfix">
            <?= $this->Template->userTopBtn(true); ?>
            <?= $this->Form->button($this->Tr->t('userMailAdd/nextBtn'), [
                'type' => 'submit',
                'class' => ['cmn-btn', 'is-blue'],
            ]) ?>
            </p>
        </fieldset>
    <?= $this->Form->end() ?>
</section>
