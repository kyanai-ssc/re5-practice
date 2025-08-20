<div>
    <?= $this->element('Admin/Common/fieldset/input_items_fieldset', [
        'formGroups' => $userForm->getUserFormGroups(),
        'options' => [
            'user' => $userForm->getUserEntity(),
            'mode' => $mode,
        ],
    ]) ?>
    <div class="hidden">
        <?php foreach ($userForm->getUserParameter() as $key => $value): ?>
            <input type="hidden" class="js_user_parameter" value="<?= h($value) ?>" data-name="<?= h($key) ?>"/>
        <?php endforeach; ?>
        <input type="hidden" class="js_user_parameter" value="<?= h($mode) ?>" data-name="mode"/>
        <input type="hidden" class="js_user_parameter" value="<?= h($userForm->getUserEntity()->get('id')) ?>" data-name="user_id"/>
    </div>
</div>
