<?php if (!empty($tagLists)) : ?>
    <div class="tagInput">
        <dl class="tagInput-box">
            <?php $i = 0; ?>
            <?php foreach ($tagLists as $gkey => $tagGroup): ?>
                <dt><?= h($tagGroup['name']) ?>
                    <button type="button" class="showBtn"></button>
                </dt>
                <dd class="showWrap">
                    <ul>
                        <?php foreach ($tagGroup['tag'] as $tagKey => $tagName) : ?>
                            <li>
                                <?= $this->Template->checkbox('event_tags.' . $i . '.tag_id', [
                                    'type' => 'checkbox',
                                    'value' => $tagKey,
                                    'label' => ['class' => ['cmn-check', 'btn-tool'], 'text' => $tagName],
                                ]) ?>
                                <?php $i++; ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </dd>
            <?php endforeach; ?>
        </dl>
    </div>
<?php else : ?>
    <?= $this->Form->hidden('event_tags'); ?>
<?php endif; ?>
