<!-- .search-group.-event-list -->
<?php if (!empty($eventNameList)) : ?>
    <div class="search-group -event-list">
        <fieldset>
            <legend><?= $this->Tr->h('search/eventName') ?></legend>
            <ul>
                <li>
                    <?= $this->Form->control('event_name', [
                        'type' => 'text',
                        'list' => 'eventName',
                    ]) ?>
                    <datalist id="eventName">
                        <?php foreach ($eventNameList as $eventName): ?>
                            <option value="<?= h($eventName) ?>"></option>
                        <?php endforeach; ?>
                    </datalist>
                </li>
            </ul>
        </fieldset>
    </div>
    <!-- search-group -event-list -->
    <hr class="cmn-hr">
<?php endif; ?>
