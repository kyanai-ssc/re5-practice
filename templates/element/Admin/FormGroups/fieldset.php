<?php
$this->Form->unlockField('form_groups');
?>
<div class="js_form_groups_container js_sortable_group">
    <?php foreach ($formGroups as $formGroupIndex => $formGroup): ?>
        <?= $this->element('Admin/FormGroups/fieldset_group', [
            'formType' => $formType,
            'formGroups' => $formGroups,
            'formGroupIndex' => $formGroupIndex,
            'formGroup' => $formGroup,
        ]) ?>
    <?php endforeach; ?>
</div>
<?= $this->Form->button('グループ追加', [
    'type' => 'button',
    'class' => ['js_add_input','js_add_group', 'cmn-btn', 'is-formAdd'],
    'data-container' => '.js_form_groups_container',
    'data-html' => $this->element('Admin/FormGroups/fieldset_group', [
        'formType' => $formType,
        'formGroups' => $formGroups,
        'formGroupIndex' => '%INDEX%',
        'formGroup' => null,
    ]),
    'data-index-element' => '.js_form_groups_index',
    'data-index-replace' => '%INDEX%',
]) ?>

