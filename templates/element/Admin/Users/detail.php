<div>
    <?= $this->element('Admin/Common/fieldset/input_items_detail', [
        'formGroups' => $userForm->getUserFormGroups(),
        'options' => [
            'user' => $userForm->getUserEntity(),
            'mode' => $mode,
        ],
    ]) ?>
</div>
