<?= $this->Form->create($userForm, [
    'type' => 'post',
    'url' => [
        'prefix' => 'User',
        'controller' => 'User',
        'action' => $mode,
        'id' => $userForm->getUserEntity()->get('id'),
    ],
    'idPrefix' => 'user-' . $mode,
    'novalidate' => true,
    'class' => ['js_submit_once', 'js_user_form'],
]) ?>
<?= $this->Token->getTokenError(); ?>
<?= $this->Token->getTokenTag(); ?>
<?= $this->element('User/User/fieldset', [
    'userForm' => $userForm,
    'valueOptions' => $valueOptions,
    'mode' => $mode,
]) ?>
<fieldset class="input-info">
    <p class="cmn-txt mgt-40 tac mgb-20 btn-wrap clearfix">
        <?= $this->Html->link(
            $this->Tr->t('common/backBtn'),
            [
                'prefix' => 'User',
                'controller' => 'User',
                'action' => 'detail',
                'id' => $userForm->getUserEntity()->get('id'),
            ],
            ['class' => ['cmn-btn', 'is-gray', 'is-reset']]
        ); ?>
        <?= $this->Form->button($this->Tr->t('user/nextBtn'), [
            'type' => 'submit',
            'class' => ['cmn-btn', 'is-blue'],
        ]) ?>
    </p>
</fieldset>
<?= $this->Form->end() ?>
