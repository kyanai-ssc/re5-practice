<?php
use App\Model\Entity\Organizer;
?>
<div class="form-input-set">
    <fieldset>
        <table class="input-box">
            <tbody>
                <tr class="field-input">
                    <th class="ttl-input">
                        <div class="ttl-input-wrap">
                            主催者名
                            <?= $this->Template->isRequire('name') ?>
                        </div>
                    </th>
                    <td>
                        <?= $this->Form->control('name', [
                            'type' => 'text',
                        ]) ?>
                    </td>
                </tr>
                <tr class="field-input js_video_meeting_type_row" data-type-zoom="<?= h(Organizer::VIDEO_MEETING_TYPE_ZOOM) ?>"> 
                    <th class="ttl-input">
                        <div class="ttl-input-wrap">
                            ビデオ会議種別
                            <?= $this->Template->isRequire('video_meeting_type') ?>
                        </div>
                    </th>
                    <td>
                        <?php if ($organizer->isNew()): ?>
                            <?= $this->Template->radio('video_meeting_type', [
                                'type' => 'radio',
                                'options' => $valueOptions['videoMeetingType'],
                                'class' => ['js_video_meeting_type_radio'],
                            ]) ?>
                        <?php else: ?>
                            <?= h($this->Configure->read('Master.organizer.videoMeetingType.' . $organizer->get('video_meeting_type'))) ?>
                            <?= $this->Form->hidden('video_meeting_type', [
                                'value' => $organizer->get('video_meeting_type'),
                                'class' => ['js_video_meeting_type_hidden'],
                            ]) ?>
                        <?php endif; ?>
                    </td>
                </tr>
                <tr class="field-input hidden js_type_container js_type_container_<?= h(Organizer::VIDEO_MEETING_TYPE_ZOOM) ?>">
                    <th class="ttl-input">
                        <div class="ttl-input-wrap">
                            認証タイプ
                            <?= $this->Template->isRequire('zoom_connect_type', ['always' => true]) ?>
                        </div>
                    </th>
                    <td>
                        <?= $this->Template->radio('zoom_connect_type', [
                            'type' => 'radio',
                            'options' => $valueOptions['zoomConnectType'],
                            'class' => ['js_zoom_connect_type'],
                            'default' => Organizer::ZOOM_CONNECT_TYPE_OAUTH,
                        ]) ?>
                        <div class="desc-wrap">
                            <p>JWTの認証タイプは2023年6月をもって廃止となります。新規で登録する際はOauthタイプを選択してください。</p>
                        </div>
                    </td>
                </tr>
                <tr class="field-input hidden js_type_container js_type_container_<?= h(Organizer::VIDEO_MEETING_TYPE_ZOOM) ?> js_for_zoom_connect_type js_for_zoom_connect_type_<?= h(Organizer::ZOOM_CONNECT_TYPE_JWT) ?>">
                    <th class="ttl-input">
                        <div class="ttl-input-wrap">
                            API Key
                            <?php if ($organizer->isNew() || (string)$organizer->getOriginal('zoom_connect_type') !== (string)Organizer::ZOOM_CONNECT_TYPE_JWT): ?>
                                <?= $this->Template->isRequire('zoom_api_key', ['always' => true]) ?>
                            <?php endif; ?>
                        </div>
                    </th>
                    <td>
                        <?= $this->Form->control('zoom_api_key', [
                            'type' => 'text',
                        ]) ?>
                    </td>
                </tr>
                <tr class="field-input hidden js_type_container js_type_container_<?= h(Organizer::VIDEO_MEETING_TYPE_ZOOM) ?> js_for_zoom_connect_type js_for_zoom_connect_type_<?= h(Organizer::ZOOM_CONNECT_TYPE_JWT) ?>">
                    <th class="ttl-input">
                        <div class="ttl-input-wrap">
                            API Secret
                            <?php if ($organizer->isNew() || (string)$organizer->getOriginal('zoom_connect_type') !== (string)Organizer::ZOOM_CONNECT_TYPE_JWT): ?>
                                <?= $this->Template->isRequire('zoom_api_secret', ['always' => true]) ?>
                            <?php endif; ?>
                        </div>
                    </th>
                    <td>
                        <?= $this->Form->control('zoom_api_secret', [
                            'type' => 'text',
                        ]) ?>
                    </td>
                </tr>
                <tr class="field-input hidden js_type_container js_type_container_<?= h(Organizer::VIDEO_MEETING_TYPE_ZOOM) ?>">
                    <th class="ttl-input">
                        <div class="ttl-input-wrap">
                            ビデオ会議のホスト（メールアドレス）
                            <?= $this->Template->isRequire('zoom_host_email', ['always' => true]) ?>
                        </div>
                    </th>
                    <td>
                        <?= $this->Form->control('zoom_host_email', [
                            'type' => 'text',
                        ]) ?>
                    </td>
                </tr>
                <tr class="field-input hidden js_type_container js_type_container_<?= h(Organizer::VIDEO_MEETING_TYPE_MEET) ?>">
                    <th class="ttl-input">
                        <div class="ttl-input-wrap">
                            キー（JsonFile）
                            <?php if ($organizer->isNew()): ?>
                                <?= $this->Template->isRequire('meet_api_key', ['always' => true]) ?>
                            <?php endif; ?>
                        </div>
                    </th>
                    <td>
                        <?= $this->Form->control('meet_api_key', [
                            'type' => 'textarea',
                        ]) ?>
                    </td>
                </tr>
                <tr class="field-input hidden js_type_container js_type_container_<?= h(Organizer::VIDEO_MEETING_TYPE_MEET) ?>">
                    <th class="ttl-input">
                        <div class="ttl-input-wrap">
                            カレンダーID
                            <?= $this->Template->isRequire('meet_calendar_id', ['always' => true]) ?>
                        </div>
                    </th>
                    <td>
                        <?= $this->Form->control('meet_calendar_id', [
                            'type' => 'text',
                        ]) ?>
                    </td>
                </tr>
                <tr class="field-input">
                    <th class="ttl-input">
                        <div class="ttl-input-wrap">
                            並び順
                            <?= $this->Template->isRequire('sort_key') ?>
                        </div>
                    </th>
                    <td>
                        <?= $this->Form->control('sort_key', [
                            'type' => 'text',
                        ]) ?>
                    </td>
                </tr>
            </tbody>
        </table>
    </fieldset>
</div>