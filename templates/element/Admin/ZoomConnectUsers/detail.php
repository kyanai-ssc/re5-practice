<?php
use Cake\Utility\Hash;
?>
<div class="panel-show-set">
    <fieldset>
        <table class="input-box">
            <tbody>
                <tr class="field-input">
                    <th class="ttl-input">
                        <div class="ttl-input-wrap">
                            連携ステータス
                        </div>
                    </th>
                    <td>
                        <p class="cmn-txt">
                            <?php if (isset($zoomConnectUser)): ?>
                                <?php if (!empty($zoomConnectUser->getZoomUser())): ?>
                                    連携済み
                                <?php else: ?>
                                    連携済み（Zoom連携不可）
                                <?php endif; ?>
                            <?php else: ?>
                                未連携
                            <?php endif; ?>
                        </p>
                    </td>
                </tr>
                <?php if (isset($zoomConnectUser) && !empty($zoomConnectUser->getZoomUser())): ?>
                    <tr class="field-input">
                        <th class="ttl-input">
                            <div class="ttl-input-wrap">
                                Zoom連携ユーザー名
                            </div>
                        </th>
                        <td>
                            <p class="cmn-txt">
                                <?= h($zoomConnectUser->get('name')) ?>
                            </p>
                        </td>
                    </tr>
                    <tr class="field-input">
                        <th class="ttl-input">
                            <div class="ttl-input-wrap">
                                Zoomアカウントメールアドレス
                            </div>
                        </th>
                        <td>
                            <p class="cmn-txt">
                                <?= h(Hash::get($zoomConnectUser->getZoomUser(), 'email')) ?>
                            </p>
                        </td>
                    </tr>
                    <tr class="field-input">
                        <th class="ttl-input">
                            <div class="ttl-input-wrap">
                                Zoom表示名
                            </div>
                        </th>
                        <td>
                            <p class="cmn-txt">
                                <?= h(Hash::get($zoomConnectUser->getZoomUser(), 'display_name')) ?>
                            </p>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </fieldset>
</div>
