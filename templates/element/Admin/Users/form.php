<?= $this->Form->create($userForm, [
    'type' => 'post',
    'url' => [
        'prefix' => 'Admin',
        'controller' => 'Users',
        'action' => $mode,
        'id' => $userForm->getUserEntity()->get('id'),
        '?' => $userForm->getUserParameter(),
    ],
    'idPrefix' => 'users-' . $mode,
    'novalidate' => true,
    'class' => ['js_submit_once', 'js_user_form'],
]) ?>
<?= $this->Token->getTokenTag() ?>
<?= $this->Token->getTokenError() ?>
<?= $this->element('Admin/Users/fieldset', [
    'userForm' => $userForm,
    'valueOptions' => $valueOptions,
    'mode' => $mode,
]) ?>
<div class="btn-box mgt-20">
    <?= $this->Html->link('戻る', [
        'prefix' => 'Admin',
        'controller' => 'Users',
        'action' => 'list',
        '?' => $this->Configure->read('Setting.searchInput.searchQuery'),
    ], [
        'class' => ['cmn-btn', 'is-reset', 'is-gray'],
    ]) ?>
    <?= $this->Form->button('確認', [
        'type' => 'submit',
        'class' => ['cmn-btn', 'is-blue'],
    ]) ?>
</div>
<?= $this->Form->end() ?>
