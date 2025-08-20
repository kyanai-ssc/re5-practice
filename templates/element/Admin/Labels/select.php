<?php if (!isset($isAjax) || !$isAjax): ?>
    <?php $this->Form->unlockField('select_parent_id'); ?>
    <?php $this->Form->unlockField($formType['search_id']); ?>
<?php endif; ?>
<ul class="labelInput js_label_select">
    <?php
    $_selected = '';
    $empty = '';
    ?>
    <?php if (isset($labelLists['list']) && is_array($labelLists['list'])) : ?>
        <?php foreach ($labelLists['list'] as $key => $data): ?>
            <?php
            if (isset($labelLists['selected'][$key])) {
                $_selected = $labelLists['selected'][$key];
            }
            ?>
            <?php
                $errorClass = '';
                if ($this->Form->error($formType['search_id'])) {
                    $errorClass = 'warning';
                }
            ?>
            <li>
                <?= $this->Form->control('select_parent_id[]', [
                    'type' => 'select',
                    'label' => '',
                    'class' => ['js_change_label_select', 'select', $errorClass],
                    'options' => [$empty => ''] + $data,
                    'default' => $_selected,
                ]) ?>
            </li>
            <?php $empty = $_selected; ?>
        <?php endforeach; ?>
    <?php endif; ?>
    <?= $this->Form->error($formType['search_id']); ?>

    <?= $this->Form->control($formType['search_id'], [
        'type' => 'hidden',
        'class' => 'js_label_select_id',
        'value' => $_selected,
    ]); ?>

    <?= $this->Form->control('label_select_type', [
        'type' => 'hidden',
        'class' => 'js_label_select_type',
        'value' => $formType['type'],
    ]); ?>

    <?= $this->Form->control('null', [
        'type' => 'hidden',
        'class' => 'js_label_select_exclude_id',
        'value' => $excludeId,
    ]); ?>
</ul>
