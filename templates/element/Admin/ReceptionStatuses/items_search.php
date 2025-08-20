<div class="panel-show-set mgt-10">
    <fieldset>
        <legend class="ttl-search">
            <h3 class="ttl-sec mgt-10">予約内容</h3>
        </legend>
        <table class="input-box">
            <tbody>
            <tr class="field-input">
                <th class="ttl-input">
                    <div class="ttl-input-wrap">QRコード</div>
                </th>
                <td>
                    <?= $this->Form->control('qr_code', [
                        'type' => 'text',
                        'label' => false,
                        ]) ?>
                </td>
            </tr>
            <tr class="field-input">
                <th class="ttl-input">
                    <div class="ttl-input-wrap">カテゴリー</div>
                </th>
                <td>
                    <?= $this->Label->renderSelect([
                        'type' => $this->Configure->read('Master.label.type.other'),
                        ]) ?>
                </td>
            </tr>
            </tbody>
        </table>
    </fieldset>
</div>