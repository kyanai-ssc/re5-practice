<?php

use App\Model\Entity\RecaptchaSetting;

$this->assign('title', $this->Tr->t('pageTitle/inquiry'));
$this->Breadcrumbs->add(
    $this->Tr->t('pageTitle/inquiry')
);
$this->Html->script('common/recaptcha', [
    'block' => true,
]);
?>
<?= $this->element('User/Common/form/step', [
    'stepTitle' => 'inquiry/stepTitle',
    'step1' => 'inquiry/step1',
    'step2' => 'inquiry/step2',
    'step3' => 'inquiry/step3',
    'stepNum' => 3,
    'currentStep' => 2,
]); ?>
<section class="contents-area l-main">
    <?= $this->Flash->render('inquiriesErrors') ?>
    <?= $this->Form->create($inquiry, [
        'type' => 'post',
        'url' => [
            'prefix' => 'User',
            'controller' => 'Inquiry',
        ],
        'idPrefix' => 'inquiry',
        'novalidate' => true,
        'class' => array_merge(['js_submit_once'], $this->Recaptcha->getFormClass()),
    ] + $this->Recaptcha->getFormAttribute(RecaptchaSetting::ACTION_INQUIRY)) ?>
    <?= $this->Token->getTokenError(); ?>
    <?= $this->Token->getTokenTag(); ?>
    <?= $this->Recaptcha->getTokenElement() ?>
    <fieldset class="input-info">
        <table class="input-box mgt-20">
            <tbody>
            <?php if (!$this->CommonData->existsUserLoginData()): ?>
                <tr class="field-input">
                    <th class="ttl-input">
                        <div class="ttl-input-wrap">
                            <?= $this->Tr->h('inquiry/name') ?>
                        </div>
                    </th>
                    <td>
                        <p class="cmn-txt"><?= h($inquiry->name) ?></p>
                    </td>
                </tr>
                <tr class="field-input">
                    <th class="ttl-input">
                        <div class="ttl-input-wrap"><?= $this->Tr->h('inquiry/phone') ?></div>
                    </th>
                    <td>
                        <p class="cmn-txt"><?= h($inquiry->phone_number) ?></p>
                    </td>
                </tr>
            <?php endif; ?>
            <tr class="field-input">
                <th class="ttl-input">
                    <div class="ttl-input-wrap">
                        <?= $this->Tr->h('inquiry/mail') ?>

                    </div>
                </th>
                <td>
                    <p class="cmn-txt"><?= h($inquiry->mail) ?></p>
                </td>
            </tr>
            <tr class="field-input">
                <th class="ttl-input">
                    <div class="ttl-input-wrap">
                        <?= $this->Tr->h('inquiry/contents') ?>
                    </div>
                </th>
                <td>
                    <p class="cmn-txt"><?= nl2br(h($inquiry->contents)) ?></p>
                </td>
            </tr>
            </tbody>
        </table>
        <p class="cmn-txt mgt-40 tac mgb-20 btn-wrap clearfix">
            <?= $this->Html->link(
                $this->Tr->t('inquiry/backBtn'),
                ['prefix' => 'User', 'controller' => 'Inquiry', 'action' => 'index', '?' => $this->Configure->read('Setting.formInput.backQuery'),],
                ['class' => ['cmn-btn', 'is-gray',]]
            ); ?>

            <?= $this->Form->button($this->Tr->t('inquiry/finishBtn'), [
                'type' => 'submit',
                'class' => ['cmn-btn', 'is-blue']
            ]) ?>
        </p>
        <?= $this->Form->end(); ?>
    </fieldset>
</section>
