<?php
use Cake\Utility\Hash;

$this->assign('title', '祝日設定');
$this->assign('headerType', 'master');

$this->Breadcrumbs->add(
    '祝日設定'
);
?>



<?= $this->Flash->render('holidaysFinish') ?>
<?= $this->Flash->render('holidaysErrors') ?>
<section class="holiday-input">
    <aside class="search-parts clearfix mgb-20">
        <div class="select-day f-l mgr-10">
            <?= $this->Form->button('', [
                'type' => 'button',
                'class' => ['js_change_url', 'btn-calender', ' is-prev'],
                'data-url' => $this->Url->build([
                    'prefix' => 'Admin',
                    'controller' => 'Holidays',
                    'action' => 'edit',
                    '?' => ['month' => $month->subMonths(1)->firstOfMonth()->format('Y/m/d')]
                ], ['escape' => false]),
            ]) ?>
            <button type="button" class="current-day js-datepicker tooltip" title="日付を変える" data-toggle data-param="month"
                  value="<?= h($month->format('Y/m/01')) ?>" data-url="<?= $this->Url->build([
                'prefix' => 'Admin',
                'controller' => 'Holidays',
                'action' => 'edit',
            ], ['escape' => false]) ?>">
                <span class="icon"><svg class="icon-calendar">
                        <use xlink:href="#icon_calendar"></use>
                    </svg></span>
                <?= h($month->format('Y/m')) ?>
            </button>
            <?= $this->Form->button('', [
                'type' => 'button',
                'class' => ['js_change_url', 'btn-calender', ' is-next'],
                'data-url' => $this->Url->build([
                    'prefix' => 'Admin',
                    'controller' => 'Holidays',
                    'action' => 'edit',
                    '?' => ['month' => $month->addMonths(1)->firstOfMonth()->format('Y/m/d')]
                ], ['escape' => false]),
            ]) ?>
        </div>
        <div class="search-parts-right f-r">
            <ul class="d-flex">
                <li>
                    <?= $this->element('Admin/Common/form/import', ['importController' => 'Holidays', 'uploadTitle' => '祝日設定アップロード']) ?>
                </li>
                <li>
                    <?= $this->Form->button('<svg class="icon"><use xlink:href="#icon_btn_download"/></svg>', [
                        'type' => 'button',
                        'class' => ['js_post_link_plural', 'btn-list', 'is-download', 'fileDown', 'tooltip'],
                        'title' => '祝日設定ダウンロード',
                        'data-url' => $this->Url->build([
                            'prefix' => 'Admin',
                            'controller' => 'Holidays',
                            'action' => 'download',
                        ], ['escape' => false]),
                        'escapeTitle' => false,
                    ]) ?>
                </li>
            </ul>
        </div><!-- .search-parts-right -->
    </aside><!-- .search-parts -->
    <?= $this->Form->create(new ArrayObject(['holidays' => $holidays]), [
        'type' => 'post',
        'url' => [
            'controller' => 'Holidays',
            'action' => 'edit',
            '?' => ['month' => $month->format('Y/m/d')]
        ],
        'idPrefix' => 'holidays-edit',
        'novalidate' => true,
        'class' => ['js_submit_confirm'],
        'context' => ['table' => 'Holidays'],
        'data-confirm-title' => '祝日の登録',
        'data-confirm-message' => '祝日の登録をおこなってよろしいですか',
    ]) ?>

    <div class="d-flex holiday-wrap">
        <div class="holiday-box mgr-20">
            <div id="calender_list" class="for_calender_list">
                <table class="month_table is-holiday">
                    <thead>
                    <?= $this->Html->tableHeaders(['日', '月', '火', '水', '木', '金', '土']) ?>
                    </thead>
                    <tbody>
                    <?php foreach ($calender as $index => $day): ?>
                        <?php if ($day->day === $day->startOfMonth() || $day->dayOfWeek === 7): ?>
                            <tr>
                        <?php endif ?>
                        <?php if ($day->day === 1 && $day->dayOfWeek !== 7): ?>
                            <?php for ($i = 0; $i < $day->dayOfWeek; $i++): ?>
                                <td class="exclude"></td>
                            <?php endfor ?>
                        <?php endif ?>
                        <td><span class="date"><?= h($day->format('j')) ?></span>
                            <div class="calender_list holidayBtn">
                                <dl class="switchArea-wrap ">
                                    <dd class="switchArea">
                                        <?php $holidaysId = Hash::get($checkDate, $day->format('Y/m/d')); ?>
                                        <?= $this->Form->control('holidays.' . $index . '.date', [
                                            'type' => 'checkbox',
                                            'value' => $day->format('Y/m/d'),
                                            'label' => [],
                                            'hiddenField' => false,
                                            'error' => false,
                                            'checked' => array_key_exists($day->format('Y/m/d'), $checkDate),
                                            'templates' => ['nestingLabel' => '{{hidden}}{{input}}<label{{attrs}}><span></span></label>',],
                                        ]) ?>
                                        <?php $this->Form->unlockField('holidays.' . $index . '.date'); ?>
                                        <?php if (!is_null($holidaysId)) : ?>
                                            <?= $this->Form->hidden('holidays.' . $index . '.id', [
                                                'value' => $holidaysId
                                            ]); ?>
                                        <?php endif ?>
                                        <div class="swImg"></div>
                                    </dd>
                                </dl>
                            </div>
                        </td>
                        <?php if ($day->daysInMonth === $day->format('d')): ?>
                            <?php for ($i = $day->dayOfWeek; $i < 6; $i++): ?>
                                <td class="exclude"></td>
                            <?php endfor; ?>
                        <?php endif ?>
                        <?php if ($day->dayOfWeek === 6 || $day->daysInMonth === $day->format('d')): ?>
                            </tr>
                        <?php endif ?>
                    <?php endforeach ?>
                    </tbody>
                </table>
                <!-- #calender_list -->
            </div>
            <div class="btn-box tac mgt-40">
                <?= $this->Form->button('登録', [
                    'type' => 'submit',
                    'class' => ['cmn-btn', 'is-blue'],
                ]) ?>
            </div>
        </div><!-- .holiday-box -->
        <div class="holidayLi-box">
            <h3 class="ttl-s mgb-20">設定中の祝日(表示年から前後3年)</h3>
            <table class="cmn-table result-table">
                <thead>
                <tr>
                    <th><?= h($holidayList['previous']) ?></th>
                    <th><?= h($holidayList['current']) ?></th>
                    <th><?= h($holidayList['next']) ?></th>
                </tr>
                </thead>
                <tbody>
                <?php for ($i = 0; $i < $holidayList['count']; $i++) : ?>
                    <tr class="parent">
                        <td>
                            <?php if (isset($holidayList[$holidayList['previous']]) && is_array($holidayList[$holidayList['previous']])) : ?>
                                <?= h(array_shift($holidayList[$holidayList['previous']])) ?>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if (isset($holidayList[$holidayList['current']]) && is_array($holidayList[$holidayList['current']])) : ?>
                                <?= h(array_shift($holidayList[$holidayList['current']])) ?>

                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if (isset($holidayList[$holidayList['next']]) && is_array($holidayList[$holidayList['next']])) : ?>
                                <?= h(array_shift($holidayList[$holidayList['next']])) ?>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endfor; ?>
            </table>
        </div>
        <?= $this->Form->end() ?>
    </div>
</section>

