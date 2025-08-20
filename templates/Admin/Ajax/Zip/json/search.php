<?php
$this->assign('ajax_html', null);
?>
<?php if (count($result['data']) > 0): ?>
    <?php $this->start('ajax_html'); ?>
        <div>
            <table width="550">
                <thead>
                    <th>
                        都道府県
                    </th>
                    <th>
                        市区町村
                    </th>
                    <th>
                        町域
                    </th>
                    <th>
                    </th>
                </thead>
                <tbody>
                    <?php foreach ($result['data'] as $address): ?>
                        <tr>
                            <td>
                                <?= h($address['prefectureName']) ?>
                            </td>
                            <td>
                                <?= h($address['municipality']) ?>
                            </td>
                            <td>
                                <?= h($address['town']) ?>
                            </td>
                            <td>
                                <?= $this->Form->button('選択', [
                                    'type' => 'button',
                                    'class' => ['js_search_address_select'],
                                    'data-address' => json_encode($address),
                                ]) ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php $this->end('ajax_html'); ?>
<?php endif; ?>
<?= $this->Ajax->json([
    'data' => $result['data'],
    'html' => $this->fetch('ajax_html'),
]) ?>
