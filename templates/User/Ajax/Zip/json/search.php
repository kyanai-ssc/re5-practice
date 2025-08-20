<?php
$this->assign('ajax_html', null);
?>
<?php if (count($result['data']) > 1): ?>
    <?php $this->start('ajax_html'); ?>
        <div>
            <div class="cmn-list_head sticky pc-only">
                <ul>
                    <li>
                        <?= $this->Tr->h('form/pref') ?>
                    </li>
                    <li>
                        <?= $this->Tr->h('form/town1') ?>
                    </li>
                    <li>
                        <?= $this->Tr->h('form/town2') ?>
                    </li>
                    <li>
                    </li>
                </ul>
            </div>
            <div class="cmn-list_body">
                <ul>
                    <?php foreach ($result['data'] as $address): ?>
                        <li class="list_body_line_wrap">
                            <ul class="list_body_line">
                                <li>
                                    <?= h($address['prefectureName']) ?>
                                </li>
                                <li>
                                    <?= h($address['municipality']) ?>
                                </li>
                                <li>
                                    <?= h($address['town']) ?>
                                </li>
                                <li>
                                    <?= $this->Form->button($this->Tr->t('form/select'), [
                                        'type' => 'button',
                                        'class' => ['cmn-btn', 'is-blue', 'is-circle', 'js_search_address_select'],
                                        'data-address' => json_encode($address),
                                    ]) ?>
                                </li>
                            </ul>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    <?php $this->end('ajax_html'); ?>
<?php endif; ?>
<?= $this->Ajax->json([
    'data' => $result['data'],
    'html' => $this->fetch('ajax_html'),
]) ?>
