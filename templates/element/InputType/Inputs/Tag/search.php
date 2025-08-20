<?php $this->start('inputTypeTagSearch'); ?>
    <td>
        <div class="tagInput">
            <dl class="tagInput-box">
                <?php foreach ($formItem->getInputTypeItem()->getValueOptions() as $tagGroupId => $tagGroup): ?>
                    <dt>
                        <?= h($tagGroup['name']) ?>
                        <button type="button" class="showBtn"></button>
                    </dt>
                    <dd class="showWrap btn-inline">
                        <?= $this->Template->checkbox($formItem->getInputTypeItem()->getSearchInputKey() . '.' . $tagGroupId, [
                            'type' => 'multicheckbox',
                            'options' => $tagGroup['tag'],
                        ]) ?>
                    </dd>
                <?php endforeach; ?>
            </dl>
        </div>
    </td>
<?php $this->end('inputTypeTagSearch'); ?>
<?= $this->element('InputType/Inputs/search', [
    'formItem' => $formItem,
    'element' => $this->fetch('inputTypeTagSearch'),
]) ?>
