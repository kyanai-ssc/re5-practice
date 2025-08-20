<?php

use App\Model\Entity\SiteSetting;

?>
<div class="form-input-set">
    <fieldset>
        <h3 class="ttl-s mgt-20 mgb-20">予約サイト_会員登録/ログイン</h3>
        <table class="input-box btn-inline">
            <tbody>
            <tr class="field-input">
                <th class="ttl-input">
                    <div class="ttl-input-wrap">予約者からの会員登録<?= $this->Template->isRequire('user_add_flg') ?></div>
                </th>
                <td>
                    <?php if ($siteSetting->canEdit('user_add_flg')) : ?>
                        <?= $this->Template->radio('user_add_flg', [
                            'type' => 'radio',
                            'options' => $valueOptions['common'],
                        ]) ?>
                    <?php else: ?>
                        <div class="cmn-txt">
                            <?= h($valueOptions['common'][$siteSetting->user_add_flg]) ?>
                            <?= $this->Form->hidden('user_add_flg'); ?>
                        </div>
                    <?php endif; ?>
                </td>
            </tr>
            <tr class="field-input">
                <th class="ttl-input">
                    <div class="ttl-input-wrap">予約者からの会員編集<?= $this->Template->isRequire('user_edit_flg') ?></div>
                </th>
                <td>
                    <?php if ($siteSetting->canEdit('user_edit_flg')) : ?>
                        <?= $this->Template->radio('user_edit_flg', [
                            'type' => 'radio',
                            'options' => $valueOptions['common'],
                        ]) ?>
                    <?php else: ?>
                        <div class="cmn-txt">
                            <?= h($valueOptions['common'][$siteSetting->user_edit_flg]) ?>
                            <?= $this->Form->hidden('user_edit_flg'); ?>
                        </div>
                    <?php endif; ?>
                </td>
            </tr>
            <tr class="field-input">
                <th class="ttl-input">
                    <div class="ttl-input-wrap">メールアドレス変更時のメール認証<?= $this->Template->isRequire('mail_edit_optin_flg') ?></div>
                </th>
                <td>
                    <?php if ($siteSetting->canEdit('mail_edit_optin_flg')) : ?>
                        <?= $this->Template->radio('mail_edit_optin_flg', [
                            'type' => 'radio',
                            'options' => $valueOptions['common'],
                        ]) ?>
                    <?php else: ?>
                        <div class="cmn-txt">
                            <?= h($valueOptions['common'][$siteSetting->mail_edit_optin_flg]) ?>
                            <?= $this->Form->hidden('mail_edit_optin_flg'); ?>
                        </div>
                    <?php endif; ?>
                </td>
            </tr>
            <tr class="field-input">
                <th class="ttl-input">
                    <div class="ttl-input-wrap">予約サイトログイン<?= $this->Template->isRequire('login_use_flg') ?></div>
                </th>
                <td>
                    <?php if ($siteSetting->canEdit('login_use_flg')) : ?>
                        <?= $this->Template->radio('login_use_flg', [
                            'type' => 'radio',
                            'options' => $valueOptions['loginFlg'],
                        ]) ?>
                    <?php else: ?>
                        <div class="cmn-txt">
                            <?= h($valueOptions['loginFlg'][$siteSetting->login_use_flg]) ?>
                            <?= $this->Form->hidden('login_use_flg'); ?>
                        </div>
                    <?php endif; ?>
                </td>
            </tr>
            <tr class="field-input">
                <th class="ttl-input">
                    <div class="ttl-input-wrap">
                        会員登録リンク<?= $this->Template->isRequire('login_display_add_user_flg') ?>
                    </div>
                </th>
                <td>
                    <?php if ($siteSetting->canEdit('login_display_add_user_flg')) : ?>
                        <?= $this->Template->radio('login_display_add_user_flg', [
                            'type' => 'radio',
                            'options' => $valueOptions['common'],
                        ]) ?>
                    <?php else: ?>
                        <div class="cmn-txt">
                            <?= h($valueOptions['common'][$siteSetting->login_display_add_user_flg]) ?>
                            <?= $this->Form->hidden('login_display_add_user_flg'); ?>
                        </div>
                    <?php endif; ?>
                </td>
            </tr>
            <tr class="field-input">
                <th class="ttl-input">
                    <div class="ttl-input-wrap">IDリマインダー<?= $this->Template->isRequire('id_reminder_flg') ?></div>
                </th>
                <td>
                    <?php if ($siteSetting->canEdit('id_reminder_flg')) : ?>
                        <?= $this->Template->radio('id_reminder_flg', [
                            'type' => 'radio',
                            'options' => $valueOptions['common'],
                        ]) ?>
                    <?php else: ?>
                        <div class="cmn-txt">
                            <?= h($valueOptions['common'][$siteSetting->id_reminder_flg]) ?>
                            <?= $this->Form->hidden('id_reminder_flg'); ?></div>
                    <?php endif; ?>
                </td>
            </tr>
            <tr class="field-input">
                <th class="ttl-input">
                    <div class="ttl-input-wrap">
                        パスワードリマインダー<?= $this->Template->isRequire('password_reminder_flg') ?></div>
                </th>
                <td>
                    <?php if ($siteSetting->canEdit('password_reminder_flg')) : ?>
                        <?= $this->Template->radio('password_reminder_flg', [
                            'type' => 'radio',
                            'options' => $valueOptions['common'],
                        ]) ?>
                    <?php else: ?>
                        <div class="cmn-txt">
                            <?= h($valueOptions['common'][$siteSetting->password_reminder_flg]) ?>
                            <?= $this->Form->hidden('password_reminder_flg'); ?></div>
                    <?php endif; ?>
                </td>
            </tr>
            </tbody>
        </table>
        <h3 class="ttl-s mgt-20 mgb-20">予約サイト_表示設定</h3>
        <table class="input-box btn-inline">
            <tbody>

            <tr class="field-input">
                <th class="ttl-input">
                    <div class="ttl-input-wrap">お問い合わせ<?= $this->Template->isRequire('inquiry_flg') ?></div>
                </th>
                <td>
                    <?php if ($siteSetting->canEdit('inquiry_flg')) : ?>
                        <?= $this->Template->radio('inquiry_flg', [
                            'type' => 'radio',
                            'options' => $valueOptions['common'],
                        ]) ?>
                    <?php else: ?>
                        <div class="cmn-txt">
                            <?= h($valueOptions['common'][$siteSetting->inquiry_flg]) ?>
                            <?= $this->Form->hidden('inquiry_flg'); ?>
                        </div>
                    <?php endif; ?>
                </td>
            </tr>
            <tr class="field-input">
                <th class="ttl-input">
                    <div class="ttl-input-wrap">利用規約<?= $this->Template->isRequire('terms_flg') ?></div>
                </th>
                <td>
                    <?php if ($siteSetting->canEdit('terms_flg')) : ?>
                        <?= $this->Template->radio('terms_flg', [
                            'type' => 'radio',
                            'options' => $valueOptions['common'],
                        ]) ?>
                    <?php else: ?>
                        <div class="cmn-txt">
                            <?= h($valueOptions['common'][$siteSetting->terms_flg]) ?>
                            <?= $this->Form->hidden('terms_flg'); ?>
                        </div>
                    <?php endif; ?>
                </td>
            </tr>
            <tr class="field-input">
                <th class="ttl-input">
                    <div class="ttl-input-wrap">特定商取引法<?= $this->Template->isRequire('sctl_flg') ?></div>
                </th>
                <td>
                    <?php if ($siteSetting->canEdit('sctl_flg')) : ?>
                        <?= $this->Template->radio('sctl_flg', [
                            'type' => 'radio',
                            'options' => $valueOptions['common'],
                        ]) ?>
                    <?php else: ?>
                        <div class="cmn-txt">
                            <?= h($valueOptions['common'][$siteSetting->sctl_flg]) ?>
                            <?= $this->Form->hidden('sctl_flg'); ?>
                        </div>
                    <?php endif; ?>
                </td>
            </tr>
            <tr class="field-input">
                <th class="ttl-input">
                    <div class="ttl-input-wrap">予約サイト公開<?= $this->Template->isRequire('front_public_flg') ?></div>
                </th>
                <td>
                    <?php if ($siteSetting->canEdit('frontPublicFlg')) : ?>
                        <?= $this->Template->radio('front_public_flg', [
                            'type' => 'radio',
                            'options' => $valueOptions['frontPublicFlg'],
                        ]) ?>
                    <?php else: ?>
                        <div class="cmn-txt">
                            <?= h($valueOptions['frontPublicFlg'][$siteSetting->frontPublicFlg]) ?>
                            <?= $this->Form->hidden('frontPublicFlg'); ?></div>
                    <?php endif; ?>
                </td>
            </tr>
            </tbody>
        </table>
        <h3 class="ttl-s mgt-20 mgb-20">予約サイト_予約枠絞込</h3>
        <table class="input-box btn-inline">
            <tbody>
            <tr class="field-input">
                <th class="ttl-input">
                    <div class="ttl-input-wrap">
                        カテゴリー(トップページ)<?= $this->Template->isRequire('top_search_label_flg') ?></div>
                </th>
                <td>
                    <?php if ($siteSetting->canEdit('top_search_label_flg')) : ?>
                        <?= $this->Template->radio('top_search_label_flg', [
                            'type' => 'radio',
                            'options' => $valueOptions['common'],
                        ]) ?>
                    <?php else: ?>
                        <div class="cmn-txt">
                            <?= h($valueOptions['common'][$siteSetting->top_search_label_flg]) ?>
                            <?= $this->Form->hidden('top_search_label_flg'); ?>
                        </div>
                    <?php endif; ?>

                </td>
            </tr>
            <tr class="field-input">
                <th class="ttl-input">
                    <div class="ttl-input-wrap">
                        絞り込みキーワード(トップページ)<?= $this->Template->isRequire('top_search_tag_flg') ?></div>
                </th>
                <td>
                    <?php if ($siteSetting->canEdit('top_search_tag_flg')) : ?>
                        <?= $this->Template->radio('top_search_tag_flg', [
                            'type' => 'radio',
                            'options' => $valueOptions['common'],
                        ]) ?>
                    <?php else: ?>
                        <div class="cmn-txt">
                            <?= h($valueOptions['common'][$siteSetting->top_search_tag_flg]) ?>
                            <?= $this->Form->hidden('top_search_tag_flg'); ?>
                        </div>
                    <?php endif; ?>
                </td>
            </tr>
            <tr class="field-input">
                <th class="ttl-input">
                    <div class="ttl-input-wrap">
                        予約枠名(トップページ)<?= $this->Template->isRequire('top_search_event_name_flg') ?></div>
                </th>
                <td>
                    <?php if ($siteSetting->canEdit('top_search_event_name_flg')) : ?>
                        <?= $this->Template->radio('top_search_event_name_flg', [
                            'type' => 'radio',
                            'options' => $valueOptions['common'],
                        ]) ?>
                    <?php else: ?>
                        <div class="cmn-txt">
                            <?= h($valueOptions['common'][$siteSetting->top_search_event_name_flg]) ?>
                            <?= $this->Form->hidden('top_search_event_name_flg'); ?>
                        </div>
                    <?php endif; ?>
                </td>
            </tr>
            <tr class="field-input">
                <th class="ttl-input">
                    <div class="ttl-input-wrap">
                        カテゴリー(予約状況)<?= $this->Template->isRequire('calendar_search_label_flg') ?>
                    </div>
                </th>
                <td>
                    <?php if ($siteSetting->canEdit('calendar_search_label_flg')) : ?>
                        <?= $this->Template->radio('calendar_search_label_flg', [
                            'type' => 'radio',
                            'options' => $valueOptions['common'],
                        ]) ?>
                    <?php else: ?>
                        <div class="cmn-txt">
                            <?= h($valueOptions['common'][$siteSetting->calendar_search_label_flg]) ?>
                            <?= $this->Form->hidden('calendar_search_label_flg'); ?>
                        </div>
                    <?php endif; ?>

                </td>
            </tr>
            <tr class="field-input">
                <th class="ttl-input">
                    <div class="ttl-input-wrap">
                        絞り込みキーワード(予約状況)<?= $this->Template->isRequire('calendar_search_tag_flg') ?>
                    </div>
                </th>
                <td>
                    <?php if ($siteSetting->canEdit('calendar_search_tag_flg')) : ?>
                        <?= $this->Template->radio('calendar_search_tag_flg', [
                            'type' => 'radio',
                            'options' => $valueOptions['common'],
                        ]) ?>
                    <?php else: ?>
                        <div class="cmn-txt">
                            <?= h($valueOptions['common'][$siteSetting->calendar_search_tag_flg]) ?>
                            <?= $this->Form->hidden('calendar_search_tag_flg'); ?>
                        </div>
                    <?php endif; ?>
                </td>
            </tr>
            <tr class="field-input">
                <th class="ttl-input">
                    <div class="ttl-input-wrap">
                        予約枠名(予約状況)<?= $this->Template->isRequire('calendar_search_event_name_flg') ?>
                    </div>
                </th>
                <td>
                    <?php if ($siteSetting->canEdit('calendar_search_event_name_flg')) : ?>
                        <?= $this->Template->radio('calendar_search_event_name_flg', [
                            'type' => 'radio',
                            'options' => $valueOptions['common'],
                        ]) ?>
                    <?php else: ?>
                        <div class="cmn-txt">
                            <?= h($valueOptions['common'][$siteSetting->calendar_search_event_name_flg]) ?>
                            <?= $this->Form->hidden('calendar_search_event_name_flg'); ?>
                        </div>
                    <?php endif; ?>
                </td>
            </tr>
            <tr class="field-input">
                <th class="ttl-input">
                    <div class="ttl-input-wrap">
                        絞り込みキーワードの検索方法<?= $this->Template->isRequire('tag_search_method') ?>
                    </div>
                </th>
                <td>
                    <?php if ($siteSetting->canEdit('tag_search_method')) : ?>
                        <?= $this->Template->radio('tag_search_method', [
                            'type' => 'radio',
                            'options' => $valueOptions['tagSearchMethod'],
                        ]) ?>
                    <?php else: ?>
                        <div class="cmn-txt">
                            <?= h($valueOptions['tagSearchMethod'][$siteSetting->tag_search_method]) ?>
                            <?= $this->Form->hidden('tag_search_method'); ?>
                        </div>
                    <?php endif; ?>
                </td>
            </tr>
            </tbody>
        </table>
        <h3 class="ttl-s mgt-20 mgb-20">予約登録</h3>
        <table class="input-box btn-inline">
            <tbody>
            <tr class="field-input">
                <th class="ttl-input">
                    <div class="ttl-input-wrap">
                        続けて予約<?= $this->Template->isRequire('reservation_continuous_flg') ?></div>
                </th>
                <td>
                    <?php if (
                        $this->Setting->getSystemSetting()->usePayment()
                        && $this->Setting->hasPaymentSetting()
                        && (
                            (
                                $this->Setting->getPaymentSetting()->isPaymentServiceGmo()
                                && $this->Setting->getPaymentSetting()->is3DSecureFlgOn()
                            )
                            || $this->Setting->getPaymentSetting()->isPaymentServiceSb()
                        )
                    ): ?>
                        <div class="cmn-txt">
                            <?= h($valueOptions['common'][$siteSetting::COMMON_USE_FLG_OFF]) ?>
                            <?= $this->Form->hidden('reservation_continuous_flg', ['value' => $siteSetting::COMMON_USE_FLG_OFF]); ?></div>
                    <?php elseif ($siteSetting->canEdit('reservation_continuous_flg')) : ?>
                        <?= $this->Template->radio('reservation_continuous_flg', [
                            'type' => 'radio',
                            'options' => $valueOptions['common'],
                        ]) ?>
                    <?php else: ?>
                        <div class="cmn-txt">
                            <?= h($valueOptions['common'][$siteSetting->reservation_continuous_flg]) ?>
                            <?= $this->Form->hidden('reservation_continuous_flg'); ?></div>
                    <?php endif; ?>
                </td>
            </tr>

            <tr class="field-input">
                <th class="ttl-input">
                    <div class="ttl-input-wrap">
                        会員登録と同時に予約<?= $this->Template->isRequire('reservation_add_user_flg') ?></div>
                </th>
                <td>
                    <?php if ($siteSetting->canEdit('reservation_add_user_flg')) : ?>
                        <?= $this->Template->radio('reservation_add_user_flg', [
                            'type' => 'radio',
                            'options' => $valueOptions['common'],
                        ]) ?>
                    <?php else: ?>
                        <div class="cmn-txt">
                            <?= h($valueOptions['common'][$siteSetting->reservation_add_user_flg]) ?>
                            <?= $this->Form->hidden('reservation_add_user_flg'); ?></div>
                    <?php endif; ?>
                </td>
            </tr>
            <tr class="field-input">
                <th class="ttl-input">
                    <div class="ttl-input-wrap">
                        ゲスト予約<?= $this->Template->isRequire('reservation_add_not_user_flg') ?></div>
                </th>
                <td>
                    <?php if ($siteSetting->canEdit('reservation_add_not_user_flg')) : ?>
                        <?= $this->Template->radio('reservation_add_not_user_flg', [
                            'type' => 'radio',
                            'options' => $valueOptions['common'],
                        ]) ?>
                    <?php else: ?>
                        <div class="cmn-txt">
                            <?= h($valueOptions['common'][$siteSetting->reservation_add_not_user_flg]) ?>
                            <?= $this->Form->hidden('reservation_add_not_user_flg'); ?></div>
                    <?php endif; ?>
                </td>
            </tr>
            <tr class="field-input">
                <th class="ttl-input">
                    <div class="ttl-input-wrap">
                        ゲスト予約編集<?= $this->Template->isRequire('reservation_edit_not_user_flg') ?></div>
                </th>
                <td>
                    <?php if ($siteSetting->canEdit('reservation_edit_not_user_flg')) : ?>
                        <?= $this->Template->radio('reservation_edit_not_user_flg', [
                            'type' => 'radio',
                            'options' => $valueOptions['common'],
                        ]) ?>
                        <div class="desc-wrap">
                            <p>顧客の権限設定の「利用できる機能」もあわせて設定下さい。</p>
                        </div>
                    <?php else: ?>
                        <div class="cmn-txt">
                            <?= h($valueOptions['common'][$siteSetting->reservation_edit_not_user_flg]) ?>
                            <?= $this->Form->hidden('reservation_edit_not_user_flg') ?></div>
                    <?php endif; ?>
                </td>
            </tr>
            <?php if ($this->Setting->getSystemSetting()->usePayment()) : ?>
                <tr class="field-input">
                    <th class="ttl-input">
                        <div class="ttl-input-wrap">GMO-PGクレジット決済済みの予約変更<?= $this->Template->isRequire('reservation_edit_payment_flg') ?></div>
                    </th>
                    <td>
                        <?php if ($this->Setting->hasPaymentSetting() && $this->Setting->getPaymentSetting()->isPaymentServiceSb()) : ?>
                            <div class="cmn-txt">
                                <?= h($valueOptions['reservationEditPaymentFlg'][$siteSetting::COMMON_USE_FLG_OFF]) ?>
                                <?= $this->Form->hidden('reservation_edit_payment_flg', ['value' => $siteSetting::COMMON_USE_FLG_OFF]); ?></div>
                        <?php elseif ($siteSetting->canEdit('reservation_edit_payment_flg')) : ?>
                            <?= $this->Template->radio('reservation_edit_payment_flg', [
                                'type' => 'radio',
                                'options' => $valueOptions['reservationEditPaymentFlg'],
                            ]) ?>
                        <?php else: ?>
                            <div class="cmn-txt">
                                <?= h($valueOptions['reservationEditPaymentFlg'][$siteSetting->reservation_edit_payment_flg]) ?>
                                <?= $this->Form->hidden('reservation_edit_payment_flg'); ?></div>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endif; ?>
            <tr class="field-input">
                <th class="ttl-input">
                    <div class="ttl-input-wrap">メール認証<?= $this->Template->isRequire('optin_flg') ?></div>
                </th>
                <td>
                    <?php if ($siteSetting->canEdit('optin_flg')) : ?>
                        <?= $this->Template->radio('optin_flg', [
                            'type' => 'radio',
                            'options' => $valueOptions['common'],
                        ]) ?>
                    <?php else: ?>
                        <div class="cmn-txt">
                            <?= h($valueOptions['common'][$siteSetting->optin_flg]) ?>
                            <?= $this->Form->hidden('optin_flg'); ?></div>
                    <?php endif; ?>
                </td>
            </tr>
            <tr class="field-input">
                <th class="ttl-input">
                    <div class="ttl-input-wrap">
                        予約リマインダーメール<?= $this->Template->isRequire('reservation_reminder_flg') ?>
                    </div>
                </th>
                <td>
                    <?php if ($siteSetting->canEdit('reservation_reminder_flg')) : ?>
                        <?= $this->Template->radio('reservation_reminder_flg', [
                            'type' => 'radio',
                            'options' => $valueOptions['reservationReminderFlg'],
                        ]) ?>
                    <?php else: ?>
                        <div class="cmn-txt">
                            <?= h($valueOptions['common'][$siteSetting->reservation_reminder_flg]) ?>
                            <?= $this->Form->hidden('reservation_reminder_flg'); ?>
                        </div>
                    <?php endif; ?>
                </td>
            </tr>
            <tr class="field-input">
                <th class="ttl-input">
                    <div class="ttl-input-wrap">
                        予約リマインダーメール配信時間
                        <?= $this->Template->isRequire('reservation_reminder_type') ?>
                    </div>
                </th>
                <td>
                    <?php if ($siteSetting->canEdit('reservation_reminder_type')) : ?>
                        <span class="js_reminder_mail_hour">
                    <?= $this->Form->control('reminder_mail_hour', [
                        'type' => 'select',
                        'label' => false,
                        'class' => ['select'],
                        'options' => $valueOptions['reservationReminderTime'],
                        'empty' => false,
                    ]) ?>
                    </span>
                        <span class="js_reminder_mail_day">
                    <?= $this->Form->control('reminder_mail_day', [
                        'type' => 'select',
                        'class' => ['select'],
                        'label' => false,
                        'options' => $valueOptions['reservationReminderDay'],
                        'empty' => false,
                    ]) ?>
                    </span>
                        <?= $this->Template->radio('reservation_reminder_type', [
                            'type' => 'radio',
                            'class' => 'reservation-reminder-type',
                            'options' => $valueOptions['reservationReminderType'],
                            'data-time' => SiteSetting::RESERVATION_REMINDER_TYPE_TIME,
                            'data-day' => SiteSetting::RESERVATION_REMINDER_TYPE_DAY,
                        ]) ?>
                    <?php else: ?>
                        <div class="cmn-txt">
                            <?php if ($siteSetting->reservation_reminder_type === SiteSetting::RESERVATION_REMINDER_TYPE_TIME) : ?>
                                <?php if (isset($siteSetting->reminder_mail_hour)) : ?>
                                    <?= h($valueOptions['reservationReminderTime'][$siteSetting->reminder_mail_hour]) ?>
                                <?php endif; ?>
                                <?= $this->Form->hidden('reminder_mail_hour') ?>
                            <?php else: ?>
                                <?php if (isset($siteSetting->reminder_mail_day)) : ?>
                                    <?= h($valueOptions['reservationReminderDay'][$siteSetting->reminder_mail_day]) ?>
                                <?php endif; ?>
                                <?= $this->Form->hidden('reminder_mail_day') ?>
                            <?php endif; ?>
                            <?php if (isset($siteSetting->reservation_close_reminder_mail_hour)) : ?>
                                <?= h($valueOptions['reservationReminderType'][$siteSetting->reservation_reminder_type]) ?>
                            <?php endif; ?>
                            <?= $this->Form->hidden('reservation_reminder_type') ?>
                        </div>
                    <?php endif; ?>
                </td>
            </tr>
            <tr class="field-input">
                <th class="ttl-input">
                    <div class="ttl-input-wrap">
                        利用終了リマインダーメール<?= $this->Template->isRequire('reservation_close_reminder_flg') ?>
                    </div>
                </th>
                <td>
                    <?php if ($siteSetting->canEdit('reservation_close_reminder_flg')) : ?>
                        <?= $this->Template->radio('reservation_close_reminder_flg', [
                            'type' => 'radio',
                            'options' => $valueOptions['reservationReminderFlg'],
                        ]) ?>
                    <?php else: ?>
                        <div class="cmn-txt">
                            <?= h($valueOptions['common'][$siteSetting->reservation_close_reminder_flg]) ?>
                            <?= $this->Form->hidden('reservation_close_reminder_flg') ?>
                        </div>
                    <?php endif; ?>
                </td>
            </tr>
            <tr class="field-input">
                <th class="ttl-input">
                    <div class="ttl-input-wrap">
                        利用終了リマインダーメール配信時間
                        <?= $this->Template->isRequire('reservation_close_reminder_type') ?>
                    </div>
                </th>
                <td>
                    <?php if ($siteSetting->canEdit('reservation_close_reminder_type')) : ?>
                        <span class="js_reservation_close_reminder_mail_hour">
                        <?= $this->Form->control('reservation_close_reminder_mail_hour', [
                            'type' => 'select',
                            'label' => false,
                            'class' => ['select'],
                            'options' => $valueOptions['reservationReminderTime'],
                            'empty' => false,
                        ]) ?>
                        </span>
                        <span class="js_reservation_close_reminder_mail_day">
                        <?= $this->Form->control('reservation_close_reminder_mail_day', [
                            'type' => 'select',
                            'class' => ['select'],
                            'label' => false,
                            'options' => $valueOptions['reservationReminderDay'],
                            'empty' => false,
                        ]) ?>
                        </span>
                        <?= $this->Template->radio('reservation_close_reminder_type', [
                            'type' => 'radio',
                            'class' => 'reservation-close-reminder-type',
                            'options' => $valueOptions['reservationReminderType'],
                            'data-time' => SiteSetting::RESERVATION_REMINDER_TYPE_TIME,
                            'data-day' => SiteSetting::RESERVATION_REMINDER_TYPE_DAY,
                        ]) ?>
                    <?php else: ?>
                        <div class="cmn-txt">
                            <?php if ($siteSetting->reservation_close_reminder_type === SiteSetting::RESERVATION_REMINDER_TYPE_TIME) : ?>
                                <?php if (isset($siteSetting->reservation_close_reminder_mail_hour)) : ?>
                                    <?= h($valueOptions['reservationReminderTime'][$siteSetting->reservation_close_reminder_mail_hour]) ?>
                                <?php endif; ?>
                                <?= $this->Form->hidden('reservation_close_reminder_mail_hour') ?>
                            <?php else: ?>
                                <?php if (isset($siteSetting->reservation_close_reminder_mail_day)) : ?>
                                    <?= h($valueOptions['reservationReminderDay'][$siteSetting->reservation_close_reminder_mail_day]) ?>
                                <?php endif; ?>
                                <?= $this->Form->hidden('reservation_close_reminder_mail_day') ?>
                            <?php endif; ?>
                            <?php if (isset($siteSetting->reservation_close_reminder_type)) : ?>
                                <?= h($valueOptions['reservationReminderType'][$siteSetting->reservation_close_reminder_type]) ?>
                            <?php endif; ?>
                            <?= $this->Form->hidden('reservation_close_reminder_type') ?>
                        </div>
                    <?php endif; ?>
                </td>
            </tr>
            </tbody>
        </table>
        <h3 class="ttl-s mgt-20 mgb-20">予約状況（カレンダー）</h3>
        <table class="input-box btn-inline">
            <tbody>

            <tr class="field-input">
                <th class="ttl-input">
                    <div class="ttl-input-wrap">
                        予約状況初期値表示日<?= $this->Template->isRequire('calendar_date_default') ?></div>
                </th>
                <td>
                    <?php if ($siteSetting->canEdit('calendar_date_default')) : ?>
                        <span class="txt mgl-10 mgr-10">現在日の</span>
                        <?= $this->Form->control('calendar_date_default', [
                        'type' => 'text',
                        'label' => false,
                        'class' => ['textbox_w50']
                    ]) ?>
                        <span class="txt mgl-10 mgr-10">日先から表示する</span>
                    <?php else: ?>
                        <div class="cmn-txt">
                            現在日の <?= h($siteSetting->calendar_date_default) ?>日先から表示する
                            <?= $this->Form->hidden('calendar_date_default'); ?>
                        </div>
                    <?php endif; ?>
                </td>
            </tr>
            <tr class="field-input">
                <th class="ttl-input">
                    <div class="ttl-input-wrap">予約状況表示時間<?= $this->Template->isRequire('calendar_time_from') ?></div>
                </th>
                <td class="cmn-txt">
                    <?php if ($siteSetting->canEdit('calendar_time_from')) : ?>
                        <?= $this->Form->control('calendar_time_from', [
                            'type' => 'text',
                            'class' => ['js-timepicker']
                        ]) ?>
                    <?php else: ?>
                        <?= h($siteSetting->calendar_time_from) ?>
                        <?= $this->Form->hidden('calendar_time_from'); ?>
                    <?php endif; ?>
                    <span class="txt mgl-10 mgr-10">から</span>
                    <?php if ($siteSetting->canEdit('calendar_time_to')) : ?>
                        <?= $this->Form->control('calendar_time_to', [
                            'type' => 'text',
                            'class' => ['js-timepicker']
                        ]) ?>
                    <?php else: ?>
                        <?= h($siteSetting->calendar_time_to) ?>
                        <?= $this->Form->hidden('calendar_time_to'); ?>
                    <?php endif; ?>
                </td>
            </tr>
            <tr class="field-input">
                <th class="ttl-input">
                    <div class="ttl-input-wrap">
                        1ヶ月表示の表示件数<?= $this->Template->isRequire('calendar_month_display_limit') ?></div>
                </th>
                <td>
                    <?php if ($siteSetting->canEdit('calendar_month_display_limit')) : ?>
                        <?= $this->Form->control('calendar_month_display_limit', [
                            'type' => 'text',
                            'label' => false,
                            'class' => ['textbox_w50']
                        ]) ?>
                    <?php else: ?>
                        <div class="cmn-txt">
                            <?= h($siteSetting->calendar_month_display_limit) ?>
                            <?= $this->Form->hidden('calendar_month_display_limit'); ?>
                        </div>
                    <?php endif; ?>
                </td>
            </tr>
            <tr class="field-input">
                <th class="ttl-input">
                    <div class="ttl-input-wrap">
                        予約状況初期表示時間<?= $this->Template->isRequire('calendar_time_default') ?>
                    </div>
                </th>
                <td>
                    <?php if ($siteSetting->canEdit('calendar_time_default')) : ?>
                        <?= $this->Form->control('calendar_time_default', [
                            'type' => 'text',
                            'label' => false,
                            'class' => ['js-timepicker'],
                            'default' => $valueOptions['calendarTimeDefault'],
                        ]) ?>
                        <span class="txt mgl-10 mgr-10">から表示する</span>
                    <?php else: ?>
                        <div class="cmn-txt">
                            <?= h($valueOptions['calendarTimeDefault'][$siteSetting->calendar_time_default]) ?>から表示する
                            <?= $this->Form->hidden('calendar_time_default'); ?>
                        </div>
                    <?php endif; ?>
                </td>
            </tr>
            <tr class="field-input">
                <th class="ttl-input">
                    <div class="ttl-input-wrap">
                        予約台帳初期表示<?= $this->Template->isRequire('admin_calendar_type_default') ?>
                    </div>
                </th>
                <td>
                    <?php if ($siteSetting->canEdit('admin_calendar_type_default')) : ?>
                        <?= $this->Template->radio('admin_calendar_type_default', [
                            'type' => 'radio',
                            'options' => $valueOptions['calendarType'],
                        ]) ?>
                    <?php else: ?>
                        <div class="cmn-txt">
                            <?= h($valueOptions['calendarType'][$siteSetting->admin_calendar_type_default]) ?>
                            <?= $this->Form->hidden('admin_calendar_type_default'); ?></div>
                    <?php endif; ?>
                </td>
            </tr>
            <tr class="field-input">
                <th class="ttl-input">
                    <div class="ttl-input-wrap">
                        予約受付締切を過ぎたコマ<?= $this->Template->isRequire('calendar_registration_deadline_display_flg') ?></div>
                </th>
                <td>
                    <?php if ($siteSetting->canEdit('calendar_registration_deadline_display_flg')) : ?>
                        <?= $this->Template->radio('calendar_registration_deadline_display_flg', [
                            'type' => 'radio',
                            'options' => $valueOptions['calendarRegistrationDeadlineDisplayFlg'],
                        ]) ?>
                    <?php else: ?>
                        <div class="cmn-txt">
                            <?= h($valueOptions['calendarRegistrationDeadlineDisplayFlg'][$siteSetting->calendar_registration_deadline_display_flg]) ?>
                            <?= $this->Form->hidden('calendar_registration_deadline_display_flg') ?>
                        </div>
                    <?php endif; ?>
                </td>
            </tr>
            </tbody>
        </table>
        <h3 class="ttl-s mgt-20 mgb-20">会員/予約項目</h3>
        <table class="input-box btn-inline">
            <tbody>
            <tr class="field-input">
                <th class="ttl-input">
                    <div class="ttl-input-wrap">
                        項目の並び順<?= $this->Template->isRequire('reservation_form_type_first') ?></div>
                </th>
                <td>
                    <?php if ($siteSetting->canEdit('reservation_form_type_first')) : ?>
                        <?= $this->Template->radio('reservation_form_type_first', [
                            'type' => 'radio',
                            'options' => $valueOptions['reservationFormTypeFirst'],
                        ]) ?>
                    <?php else: ?>
                        <div class="cmn-txt">
                            <?= h($valueOptions['reservationFormTypeFirst'][$siteSetting->reservation_form_type_first]) ?>
                            <?= $this->Form->hidden('reservation_form_type_first'); ?></div>
                    <?php endif; ?>
                </td>
            </tr>
            <tr class="field-input">
                <th class="ttl-input">
                    <div class="ttl-input-wrap">
                        予約の日時変更<?= $this->Template->isRequire('reservation_edit_event_flg') ?>
                    </div>
                </th>
                <td>
                    <?php if ($siteSetting->canEdit('reservation_edit_event_flg')) : ?>
                        <?= $this->Template->radio('reservation_edit_event_flg', [
                            'type' => 'radio',
                            'options' => $valueOptions['reservationEditEventFlg'],
                        ]) ?>
                    <?php else: ?>
                        <div class="cmn-txt">
                            <?= h($valueOptions['common'][$siteSetting->reservation_edit_event_flg]) ?>
                            <?= $this->Form->hidden('reservation_edit_event_flg'); ?></div>
                    <?php endif; ?>
                </td>
            </tr>
            <tr class="field-input">
                <th class="ttl-input">
                    <div class="ttl-input-wrap">利用規約（予約時）<?= $this->Template->isRequire('reservation_terms_flg') ?></div>
                </th>
                <td>
                    <?php if ($siteSetting->canEdit('reservation_terms_flg')) : ?>
                        <?= $this->Template->radio('reservation_terms_flg', [
                            'type' => 'radio',
                            'options' => $valueOptions['common'],
                        ]) ?>
                    <?php else: ?>
                        <div class="cmn-txt">
                            <?= h($valueOptions['common'][$siteSetting->reservation_terms_flg]) ?>
                            <?= $this->Form->hidden('reservation_terms_flg'); ?>
                        </div>
                    <?php endif; ?>
                </td>
            </tr>
            <tr class="field-input">
                <th class="ttl-input">
                    <div class="ttl-input-wrap">個人情報規約（会員登録時）<?= $this->Template->isRequire('user_terms_flg') ?></div>
                </th>
                <td>
                    <?php if ($siteSetting->canEdit('user_terms_flg')) : ?>
                        <?= $this->Template->radio('user_terms_flg', [
                            'type' => 'radio',
                            'options' => $valueOptions['common'],
                        ]) ?>
                    <?php else: ?>
                        <div class="cmn-txt">
                            <?= h($valueOptions['common'][$siteSetting->user_terms_flg]) ?>
                            <?= $this->Form->hidden('user_terms_flg'); ?>
                        </div>
                    <?php endif; ?>
                </td>
            </tr>
            <tr class="field-input">
                <th class="ttl-input">
                    <div class="ttl-input-wrap">特定商取引法（予約時）<?= $this->Template->isRequire('reservation_sctl_flg') ?></div>
                </th>
                <td>
                    <?php if ($siteSetting->canEdit('reservation_sctl_flg')) : ?>
                        <?= $this->Template->radio('reservation_sctl_flg', [
                            'type' => 'radio',
                            'options' => $valueOptions['common'],
                        ]) ?>
                    <?php else: ?>
                        <div class="cmn-txt">
                            <?= h($valueOptions['common'][$siteSetting->reservation_sctl_flg]) ?>
                            <?= $this->Form->hidden('reservation_sctl_flg'); ?>
                        </div>
                    <?php endif; ?>
                </td>
            </tr>
            <tr class="field-input">
                <th class="ttl-input">
                    <div class="ttl-input-wrap">内訳<?= $this->Template->isRequire('charge_breakdown_flg') ?></div>
                </th>
                <td>
                    <?php if ($siteSetting->canEdit('charge_breakdown_flg')) : ?>
                        <?= $this->Template->radio('charge_breakdown_flg', [
                            'type' => 'radio',
                            'options' => $valueOptions['common'],
                        ]) ?>
                    <?php else: ?>
                        <div class="cmn-txt">
                            <?= h($valueOptions['common'][$siteSetting->charge_breakdown_flg]) ?>
                            <?= $this->Form->hidden('charge_breakdown_flg'); ?>
                        </div>
                    <?php endif; ?>
                </td>
            </tr>
            </tbody>
        </table>
    </fieldset>
</div>
