<?php
use \App\Model\Entity\FormPattern;
use \App\Model\Entity\FormItem;
use App\Model\Entity\FormPatternDisplayType;
?>
<?php if($mode === 'togetherEdit') :?>
    <?= $this->Form->hidden($fieldPrefix .'id', ['class' => ['js_form_pattern_display_types']])?>
<?php endif;?>

<?= $this->Form->hidden($fieldPrefix . 'form_pattern_display_types.' . $key . '.form_item_id', [
    'value' => $form->id,
    'class' => ['js_form_pattern_display_types'],
]) ?>

<?php $className += ['js_form_pattern_display_types']; ?>
<?php if ($form->input_type === FormItem::INPUT_TYPE_RESERVATION_OPTION): ?>
    <?= $this->Template->radio($fieldPrefix . 'form_pattern_display_types.' . $key . '.display_type', [
        'type' => 'radio',
        'label' => false,
        'options' => $valueOptions['displayTypeAddition'],
        'default' => $default,
        'class' => $className,
        'hiddenField' => false,
    ]) ?>
    <dl class="inputWrap mgt-10">
      <dt class='fwb'>オプション</dt>
      <dd>
        <div>
        <?php foreach($form->form_item_option_group->form_item_options as $formItemOption) : ?>
            <?php
                if ($mode === 'togetherEdit') {
                    $formItemOptionIndex = $this->fetch('formItemOptionIndex_' . $pattern);
                } else {
                    $formItemOptionIndex = $this->fetch('formItemOptionIndex');
                }
            ?>
            <?= $this->Form->hidden($fieldPrefix . 'form_pattern_options.' .$formItemOptionIndex .'.form_item_id', [
                'value' => $form->id,
                'class' => ['js_form_pattern_display_types'],
            ]) ?>
            
            <?php if(!empty($optionChecked) && array_search($formItemOption->id, $optionChecked) !== false) {
                $checked['checked'] = $formItemOption->id;
            } else {
                $checked = [];
            }
            ?>
            <?= $this->Template->checkbox($fieldPrefix . 'form_pattern_options.' .$formItemOptionIndex .'.form_item_option_id', [
                'type' => 'checkbox',
                'label' => ['text' => $formItemOption->option->name, 'class' => ['cmn-check']],
                'value' => $formItemOption->id,
                'class' => $className,
                'hiddenField' => false,
            ] + $checked )  ?>
            <?php $formItemOptionIndex++; ?>
            <?php
                if ($mode === 'togetherEdit') {
                    $this->assign('formItemOptionIndex_' . $pattern, $formItemOptionIndex);
                } else {
                    $this->assign('formItemOptionIndex', $formItemOptionIndex);
                }
            ?>
        <?php endforeach ;?>
        </div>
      </dd>
    </dl>
<?php else: ?>
    <?php if ($form->default_flg === FormPattern::DEFAULT_FLG_ON) : ?>
        <?php if($form->input_type === FormItem::INPUT_TYPE_RESERVATION_NUMBER) :?>
            <?= $this->Template->radio($fieldPrefix . 'form_pattern_display_types.' . $key . '.display_type' , [
                'type' => 'radio',
                'label' => false,
                'options' => $valueOptions['displayTypeNumber'],
                'default' => $default,
                'class' => $className,
                'hiddenField' => false,
            ]) ?>
        <?php elseif(isset($valueOptions['displayTypeOnlyItems'][$form->input_type]) && $valueOptions['displayTypeOnlyItems'][$form->input_type] === true) : ?>
            <?= $this->Template->radio($fieldPrefix . 'form_pattern_display_types.' . $key . '.display_type' , [
                'type' => 'radio',
                'label' => false,
                'options' => $valueOptions['displayTypeOnly'],
                'default' => $default,
                'class' => $className,
                'hiddenField' => false,
            ]) ?>
        <?php elseif(isset($valueOptions['displayTypeOnlyAdminItems'][$form->input_type]) && $valueOptions['displayTypeOnlyAdminItems'][$form->input_type] === true) : ?>
            <?= $this->Template->radio($fieldPrefix . 'form_pattern_display_types.' . $key . '.display_type' , [
                'type' => 'radio',
                'label' => false,
                'options' => $valueOptions['displayTypeOnlyAdmin'],
                'default' => $default,
                'class' => $className,
                'hiddenField' => false,
            ]) ?>
        <?php elseif(isset($valueOptions['displayAndOnlyAdminItems'][$form->input_type]) && $valueOptions['displayAndOnlyAdminItems'][$form->input_type] === true) : ?>
            <?= $this->Template->radio($fieldPrefix . 'form_pattern_display_types.' . $key . '.display_type' , [
                'type' => 'radio',
                'label' => false,
                'options' => $valueOptions['displayAndOnlyAdmin'],
                'default' => $default,
                'class' => $className,
                'hiddenField' => false,
            ]) ?>
        <?php else : ?>
            <?= $this->Template->radio($fieldPrefix . 'form_pattern_display_types.' . $key . '.display_type' , [
                'type' => 'radio',
                'label' => false,
                'options' => $valueOptions['displayTypeNoAdminEdit'],
                'default' => $default,
                'class' => $className,
                'hiddenField' => false,
            ]) ?>
        <?php endif; ?>
    <?php else : ?>
        <?php if ($form->input_type === FormItem::INPUT_TYPE_EVENT_REMARK) : ?>
            <?= $this->Template->radio($fieldPrefix . 'form_pattern_display_types.' . $key . '.display_type', [
                'type' => 'radio',
                'label' => false,
                'options' => $valueOptions['displayTypeNoAdminEdit'],
                'default' => $default,
                'class' => $className,
                'hiddenField' => false,
            ]) ?>
        <?php else : ?>
            <?= $this->Template->radio($fieldPrefix . 'form_pattern_display_types.' . $key . '.display_type', [
                'type' => 'radio',
                'label' => false,
                'options' => $valueOptions['displayTypeAddition'],
                'default' => $default,
                'class' => $className,
                'hiddenField' => false,
            ]) ?>
        <?php endif; ?>
    <?php endif; ?>
<?php endif; ?>
<div>
    <?php if ($form->input_type !== formItem::INPUT_TYPE_MAIL_CONFIRM
        && $form->input_type !== formItem::INPUT_TYPE_PASSWORD
        && $form->input_type !== formItem::INPUT_TYPE_PASSWORD_CONFIRM
        && $form->input_type !== formItem::INPUT_TYPE_EXPIRATION_DATE
        && $form->input_type !== formItem::INPUT_TYPE_AKERUN_USER_ID
        && $form->input_type !== formItem::INPUT_TYPE_FILE
    ) :?>
        <?= $this->Template->checkbox($fieldPrefix . 'form_pattern_display_types.' . $key . '.app_display_flg', [
            'type' => 'checkbox',
            'label' => ['class' => ['cmn-check'], 'text' =>'アプリに表示'],
            'templates' => ['nestingLabel' => '{{hidden}}{{input}}<label{{attrs}}>{{text}}</label>',],
            'value' => FormPatternDisplayType::APP_DISPLAY_FLG_ON,
            'class' => $className,
            'hiddenField' => false,
        ]) ?>
    <?php endif; ?>
</div>
<?php if ($form->input_type === formItem::INPUT_TYPE_AKERUN_USER_ID) :?>
    <div>
        <p>※会員にAkerunユーザーIDが紐づいている場合は、予約サイトではテキスト表示され編集不可となります。</p>
    </div>
<?php endif; ?>
