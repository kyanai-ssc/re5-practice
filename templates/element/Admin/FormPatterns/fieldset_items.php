<?php

use \App\Model\Entity\FormPatternDisplayType;

?>
<?php foreach ($forms as $key => $form) : ?>
    <?php if ((string)$form->form_group_id === (string)$groupId) : ?>
        <?php if ($labelDisplay) : ?>
            <tr class="form-pattern-item">
            <td><?= h($form->name) ?></td>
            <td><?= h($this->Configure->read('Master.form.inputType.' . $form->input_type)) ?></td>
        <?php else : ?>
            <tr class="parent form-pattern-item">
        <?php endif; ?>
        <?php if (!empty($formPatterns)) : ?>
            <?php foreach ($formPatterns as $index => $formPattern) : ?>
                <?php
                $fieldPrefix = 'form_patterns.' . $index . '.';
                if (isset($optionChecked[$index])) {
                    $optionCheckedData = $optionChecked[$index];
                } else {
                    $optionCheckedData[$index] = [];
                }
                ?>
                <td class="js_together_set">
                    <?= $this->element('Admin/FormPatterns/fieldset_form', [
                        'key' => $key,
                        'form' => $form,
                        'fieldPrefix' => $fieldPrefix,
                        'optionChecked' => $optionCheckedData,
                        'className' => ['js_form_pattern_display_types'],
                        'mode' => $mode,
                        'default' => FormPatternDisplayType::DISPLAY_TYPE_HIDE,
                        'pattern' => $index,
                        'hiddenField' => false,
                    ]) ?>
                </td>
            <?php endforeach; ?>
        <?php else : ?>
            <?php if ($mode === 'togetherEdit') : ?>
                <td>
                    <?= $this->element('Admin/FormPatterns/fieldset_form', [
                        'key' => $key,
                        'form' => $form,
                        'fieldPrefix' => 'together_setting.',
                        'optionCheckedData' => [],
                        'className' => ['js_together_setting', 'js_form_pattern_display_types'],
                        'mode' => $mode,
                        'default' => '',
                        'pattern' => 'all',
                        'hiddenField' => false,
                    ]) ?>
                </td>
            <?php else : ?>
                <td>
                    <?= $this->element('Admin/FormPatterns/fieldset_form', [
                        'key' => $key,
                        'form' => $form,
                        'fieldPrefix' => $fieldPrefix,
                        'className' => [],
                        'mode' => $mode,
                        'default' => FormPatternDisplayType::DISPLAY_TYPE_HIDE,
                    ]) ?>
                </td>
            <?php endif; ?>
        <?php endif; ?>
        </tr>
    <?php endif; ?>
<?php endforeach; ?>
