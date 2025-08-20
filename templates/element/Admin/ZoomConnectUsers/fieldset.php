<div class="form-input-set">
    <fieldset>
        <table class="input-box">
            <tbody>
                <tr class="field-input">
                    <th class="ttl-input">
                        <div class="ttl-input-wrap">
                            Zoom連携ユーザー名
                            <?= $this->Template->isRequire('name') ?>
                        </div>
                    </th>
                    <td>
                        <?= $this->Form->control('name', [
                            'type' => 'text',
                        ]) ?>
                    </td>
                </tr>
                <tr class="field-input">
                    <th class="ttl-input">
                        <div class="ttl-input-wrap">
                            Code
                            <?php if ($zoomConnectUser->isNew()): ?>
                                <?= $this->Template->isRequire('code', ['always' => true]) ?>
                            <?php endif; ?>
                        </div>
                    </th>
                    <td>
                        <?= $this->Form->control('code', [
                            'type' => 'text',
                        ]) ?>
                        <div class="desc-wrap">
                            <p>
                                <a href="<?= h($this->Configure->read('Env.zoomApi.addUrl')) ?>" target="_blank">こちら</a>のZoom連携用アプリを追加していただき、追加後表示される連携用コードを入力してください。<br />
                                同じアカウントですでに連携済み（他契約のリザエンを含む）の場合にコードを入力して登録を実施すると、過去利用した連携ができなくなりますのでご注意ください。<br />
                                連携時のコードが分からなくなった場合は、アプリを解除し再度追加いただくとコードを発行可能です。
                            </p>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </fieldset>
</div>
