<?php if (!isset($isAjax) || !$isAjax): ?>
    <?php $this->Form->unlockField('select_parent_id'); ?>
    <?php $this->Form->unlockField($formType['search_id']); ?>
<?php endif; ?>
<div class="js_label_select">
    <?php
    $_selected = '';
    $empty = '';
    $depth = 1;
    ?>
    <?php foreach ($labelLists['list'] as $key => $data): ?>
        <?php
        if (isset($labelLists['selected'][$key])) {
            $_selected = $labelLists['selected'][$key];
        }
        ?>
        <?= $this->Form->hidden('select_parent_id.' . $depth) ?>
        <?php if ($depth > 1) : ?>
            <div class="search-group -home-label-container childBox ">
                <fieldset>
                    <legend><?= $this->Tr->h('label/title' . $depth) ?></legend>
                    <?= $this->Form->control('select_parent_id.' . $depth, [
                        'type' => 'radio',
                        'class' => ['js_change_label_select', 'radio-label'],
                        'options' => $data,
                        'default' => $_selected,
                        'hiddenField' => false,
                        'templates' => [
                            'inputContainer' => '<ul>{{content}}</ul>',
                            'radioWrapper' => '<li>{{label}}</li>',
                            'nestingLabel' => '{{hidden}}{{input}}<label{{attrs}} class="retrieval-btn is-label">{{text}}</label>',
                        ],
                    ]) ?>
                </fieldset>
            </div>
        <?php else : ?>
            <div class="search-group -home-label">
                <fieldset>
                    <legend><?= $this->Tr->h('label/title1') ?></legend>
                    <?= $this->Form->control('select_parent_id.' . $depth, [
                        'type' => 'radio',
                        'class' => ['js_change_label_select', 'radio-label'],
                        'options' => $data,
                        'default' => $_selected,
                        'hiddenField' => false,
                        'templates' => [
                            'inputContainer' => '<ul>{{content}}</ul>',
                            'radioWrapper' => '<li>{{label}}</li>',
                            'nestingLabel' => '{{hidden}}{{input}}<label{{attrs}} class="retrieval-btn is-label">{{text}}</label>',
                        ],
                    ]) ?>
                </fieldset>
            </div>
        <?php endif; ?>
        <?php
        $empty = $_selected;
        $depth++;
        ?>
    <?php endforeach; ?>
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
</div>
