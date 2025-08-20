<?php
$this->assign('title', $this->Tr->t('pageTitle/reservationsMailAdd'));
$this->Breadcrumbs->add(
    $this->Tr->t('pageTitle/reservationsMailAdd')
);
?>
<section class="contents-area l-main">
    <h3 class="ttl-sec"><?= $this->Tr->h('pageTitle/reservationsMailAdd') ?></h3>
    <p class="cmn-txt fwb"><?= $this->Tr->nl2br('reservationsMailAdd/message') ?></p>
    <?php if ($this->Setting->getSiteSetting()->isUseFlgOn('login_flg')): ?>
        <?= $this->Html->link(
            $this->Tr->t('reservationsMailAdd/loginBtn'),
            [
                'prefix' => 'User',
                'controller' => 'Auth',
                'action' => 'login',
                '?' => [
                    'redirect' => $this->Url->build([
                        'prefix' => 'User',
                        'controller' => 'Reservations',
                        'action' => 'add',
                        '?' => $reservationForm->getReservationParameter(),
                    ], ['escape' => false]),
                ],
            ],
            [
                'class' => ['link-txt', 'mgt-20', 'mgb-20'],
            ]
        ); ?>
    <?php endif; ?>
    <?= $this->Flash->render('reservationsMailAddErrors') ?>
    <?= $this->Form->create($optinToken, [
        'type' => 'post',
        'url' => [
            'controller' => 'Reservations',
            'action' => 'mail-add',
            '?' => $reservationForm->getReservationParameter(),
        ],
        'idPrefix' => 'reservations-mail-add',
        'novalidate' => true,
        'class' => ['js_submit_once'],
    ]) ?>
    <fieldset class="input-info">
        <table class="input-box">
            <tr class="field-input">
                <th class="ttl-input">
                    <div class="ttl-input-wrap">
                        <?= $this->Tr->h('reservationsMailAdd/mail') ?>
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
                        <?= $this->Tr->h('reservationsMailAdd/mailConfirm') ?>
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
            <?php if ($this->Authority->isAuthority(true, 'Reservations', 'calendar')) : ?>
                <?= $this->Html->link(
                    $this->Tr->t('common/backBtn'),
                    [
                        'prefix' => 'User',
                        'controller' => 'Reservations',
                        'action' => 'calendar',
                        '?' => $this->Configure->read('Setting.searchInput.searchQuery'),
                    ],
                    ['class' => ['cmn-btn', 'is-gray', 'is-reset']]
                ); ?>
            <?php endif; ?>
            <?= $this->Form->button($this->Tr->t('reservationsMailAdd/nextBtn'), [
                'type' => 'submit',
                'class' => ['cmn-btn', 'is-blue'],
            ]) ?>
        </p>
    </fieldset>
    <?= $this->Form->end() ?>
</section>
