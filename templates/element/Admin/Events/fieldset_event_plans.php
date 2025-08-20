<?php

use \App\Model\Entity\Event;
use \App\Model\Entity\EventPlan;

?>
<tr class="js_event_plans_container_<?= h($eventPlanIndex) ?>">
    <td>
        <input type="hidden" class="js_event_plans_index" value="<?= h($eventPlanIndex) ?>"/>
        <?= $this->Form->hidden('event_plans.' . $eventPlanIndex . '.id') ?>
        <?= $this->Template->checkbox('event_plans.' . $eventPlanIndex . '.public_flg', [
            'type' => 'checkbox',
            'label' => false,
            'id' => 'event_plans_public_flg_' . $eventPlanIndex,
            'label' => ['class' => 'cmn-check btn-tool no-txt-label', 'text' => '', 'title' => $valueOptions['publicFlg'][EventPlan::PUBLIC_FLG_ON]],
        ]) ?>
    </td>
    <td>
        <?= $this->Form->control('event_plans.' . $eventPlanIndex . '.name', [
            'type' => 'text',
            'label' => false,
        ]) ?>
    </td>
    <td>
        <?= $this->Form->control('event_plans.' . $eventPlanIndex . '.charge', [
            'type' => 'text',
            'label' => false,
        ]) ?>
    </td>
    <td>
        <?php if (isset($eventPlanData['id']) && $eventPlanData['id'] != "" && (isset($reserve[$event->id]['plan'][$eventPlanData['id']]['all']) && $reserve[$event->id]['plan'][$eventPlanData['id']]['all'])) : ?>
            <?php if ($event->get('type') === Event::TYPE_TIME) : ?>
                <?= h($eventPlanData['usage_time']); ?>
            <?php else : ?>
                <?= h($eventPlanData['usage_day']); ?>
            <?php endif; ?>
            <?= $this->Form->hidden('event_plans.' . $eventPlanIndex . '.usage_time') ?>
            <?= $this->Form->hidden('event_plans.' . $eventPlanIndex . '.usage_day') ?>
        <?php else : ?>
            <?php
            if ($type === Event::TYPE_TIME) {
                $addClassTime = '';
                $addClassDay = 'hidden';
            } else {
                $addClassTime = 'hidden';
                $addClassDay = '';
            }
            ?>

            <span class="js_disp_time <?= h($addClassTime) ?>">
            <?= $this->Form->control('event_plans.' . $eventPlanIndex . '.usage_time', [
                'type' => 'text',
                'label' => false,
                'class' => ['textbox_w150'],
            ]) ?>
            </span>
            <span class="js_disp_day <?= h($addClassDay) ?>">
            <?= $this->Form->control('event_plans.' . $eventPlanIndex . '.usage_day', [
                'type' => 'text',
                'label' => false,
                'class' => ['textbox_w150'],
            ]) ?>
           </span>
        <?php endif; ?>
    </td>
    <td>
        <?php if (empty($event->id) || empty($eventPlanData['id']) || (!isset($reserve[$event->id]['plan'][$eventPlanData['id']]['all']) || !$reserve[$event->id]['plan'][$eventPlanData['id']]['all'])) : ?>
            <?= $this->Form->button('<svg class="icon"><use xlink:href="#icon_tool_delete"/></svg>', [
                'type' => 'button',
                'class' => ['js_remove_input', 'btn-input', 'is-delete'],
                'title' => '削除',
                'data-selector' => '.js_event_plans_container_' . $eventPlanIndex,
                'data-context' => '.js_event_plans_container',
                'escapeTitle' => false,
            ]) ?>
        <?php endif; ?>
    </td>
</tr>

