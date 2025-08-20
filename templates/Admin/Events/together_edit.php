<?php
$this->assign('title', '予約枠設定 一括編集');
$this->assign('headerType', 'master');

$this->Breadcrumbs->add(
    '予約枠設定',
    ['controller' => 'Events', 'action' => 'list']
);
$this->Breadcrumbs->add(
    '一括編集'
);
?>
<section class="form-input">
    <?= $this->Flash->render('eventsErrors') ?>
    <?= $this->Flash->render('eventsTokenError') ?>

    <div class="form-input-set">
        <fieldset>
            <table class="input-box">
                <tbody>
                <tr class="field-input">
                    <th class="ttl-input">
                        <div class="ttl-input-wrap">選択中の予約枠</div>
                    </th>
                    <td>
                        <table class="cmn-table inputIn-table">
                            <thead>
                            <tr>
                                <th class="w100">予約枠ID</th>
                                <th>予約枠名</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($eventList as $eventId => $eventData) : ?>
                                <tr class="parent">
                                    <td><?= h($eventData->get('id')) ?></td>
                                    <td><?= h($eventData->get('name')) ?></td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </td>
                </tr>
                </tbody>
            </table>

            <?= $this->Form->create($togetherEditForm, [
                'type' => 'post',
                'url' => [
                    'controller' => 'Events',
                    'action' => 'together-edit',
                ],
                'idPrefix' => 'events-together-edit',
                'novalidate' => true,
                'class' => ['js_submit_confirm', 'js_event_together_form'],
                'data-confirm-title' => '予約枠の編集を実施します',
                'data-confirm-message' => '予約枠の編集をおこなってよろしいですか？',
            ]) ?>
            <?= $this->Form->control($tokenName, [
                'type' => 'hidden',
                'value' => $token,

            ]); ?>

            <?php foreach ($fields as $field) : ?>
                <?php $this->Form->unlockField($field); ?>
            <?php endforeach; ?>

            <?php $this->Form->unlockField('select_parent_id'); ?>
            <?php $this->Form->unlockField('label_select_type'); ?>
            <?php $this->Form->unlockField('null'); ?>

            <?= $this->FormError->errorWithoutNested('update') ?>

            <?= $this->element('Admin/Events/fieldset_together', [
                'event' => $togetherEditForm,
                'eventsInputs' => $eventsInputs,
                'valueOptions' => $valueOptions,
                'mode' => 'edit',
            ]) ?>
            <div class="btn-box mgt-20">
                <?= $this->Html->link('戻る', [
                    'prefix' => 'Admin',
                    'controller' => 'Events',
                    'action' => 'list',
                    '?' => $this->Configure->read('Setting.searchInput.searchQuery')
                ],
                    [
                        'class' => ['cmn-btn', 'is-reset', 'is-gray'],
                    ]) ?>

                <?= $this->Form->button('一括編集', [
                    'type' => 'submit',
                    'class' => ['cmn-btn', 'is-blue'],
                ]) ?>
            </div>
            <?= $this->Form->end() ?>
