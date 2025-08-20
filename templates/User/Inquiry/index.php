<?php
$this->assign('title', $this->Tr->t('pageTitle/inquiry'));
$this->Breadcrumbs->add(
    $this->Tr->t('pageTitle/inquiry')
);
?>
<?= $this->element('User/Common/form/step', [
    'stepTitle' => 'inquiry/stepTitle',
    'step1' => 'inquiry/step1',
    'step2' => 'inquiry/step2',
    'step3' => 'inquiry/step3',
    'stepNum' => 3,
    'currentStep' => 1,
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
    ]) ?>
    <?= $this->Token->getTokenError(); ?>
    <?= $this->Token->getTokenTag(); ?>
    <fieldset class="input-info">
        <table class="input-box mgt-20">
            <tbody>
            <?php if (!$this->CommonData->existsUserLoginData()): ?>
                <tr class="field-input">
                    <th class="ttl-input">
                        <div class="ttl-input-wrap">
                            <?= $this->Tr->h('inquiry/name') ?>
                            <?= $this->element('User/Common/form/require') ?>
                        </div>
                    </th>
                    <td>
                        <div class="cmnInput">
                            <?= $this->Form->control('name', [
                                'type' => 'text',
                                'class' => ['textbox_w300']
                            ]) ?>
                        </div>
                    </td>
                </tr>
                <tr class="field-input">
                    <th class="ttl-input">
                        <div class="ttl-input-wrap"><?= $this->Tr->h('inquiry/phone') ?></div>
                    </th>
                    <td>
                        <div class="cmnInput">
                            <?= $this->Form->control('phone_number', [
                                'type' => 'text',
                                'class' => ['textbox_w300']
                            ]) ?>
                        </div>
                    </td>
                </tr>
            <?php endif; ?>
            <tr class="field-input">
                <th class="ttl-input">
                    <div class="ttl-input-wrap">
                        <?= $this->Tr->h('inquiry/mail') ?>
                        <?= $this->element('User/Common/form/require') ?>
                    </div>
                </th>
                <td>
                    <div class="cmnInput">
                        <?= $this->Form->control('mail', [
                            'type' => 'text',
                            'class' => ['textbox_w300'],
                        ]) ?>
                    </div>
                </td>
            </tr>
            <tr class="field-input">
                <th class="ttl-input">
                    <div class="ttl-input-wrap">
                        <?= $this->Tr->h('inquiry/mail_confirm') ?>
                        <?= $this->element('User/Common/form/require') ?>
                    </div>
                </th>
                <td>
                    <div class="cmnInput">
                        <?= $this->Form->control('mail_confirm', [
                            'type' => 'text',
                            'class' => ['textbox_w300'],
                        ]) ?>
                    </div>
                </td>
            </tr>
            <tr class="field-input">
                <th class="ttl-input">
                    <div class="ttl-input-wrap">
                        <?= $this->Tr->h('inquiry/contents') ?>
                        <?= $this->element('User/Common/form/require') ?>
                    </div>
                </th>
                <td>
                    <div class="cmnInput">
                        <?= $this->Form->control('contents', [
                            'type' => 'textarea',
                            'rows' => 10,
                        ]) ?>
                    </div>
                </td>
            </tr>
            </tbody>
        </table>
        <p class="cmn-txt mgt-40 tac mgb-20 btn-wrap clearfix">
            <?= $this->Template->userTopBtn(true); ?>

            <?= $this->Form->button($this->Tr->t('inquiry/nextBtn'), [
                'type' => 'submit',
                'class' => ['cmn-btn', 'is-blue']
            ]) ?>
        </p>
        <?= $this->Form->end(); ?>
    </fieldset>
</section>
