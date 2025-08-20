<?php
use App\Model\Entity\FormGroup;
use App\Model\Entity\SystemSetting;

?><!DOCTYPE html>
<html lang="ja">
<head>
    <?= $this->Html->charset() ?>
    <meta http-equiv="content-language" content="ja"/>
    <meta http-equiv="X-UA-Compatible" content="IE=11; IE=Edge"/>
    <?= $this->Html->meta('robots', 'noindex,nofollow,noarchive') ?>
    <?= $this->Html->meta('format-detection', 'address=no,email=no,telephone=no') ?>
    <?= $this->Html->meta('icon'); ?>
    <?= $this->fetch('meta') ?>
    <title><?= h($this->fetch('title')) ?> | <?= $this->Tr->h('COMMON_TITLE') ?> 管理画面</title>
    <?= $this->Html->css('vendor/jquery-ui/jquery-ui.min') ?>
    <?= $this->Html->css('vendor/jquery-datetimepicker/jquery.datetimepicker.min') ?>
    <?= $this->Html->css('vendor/jpicker/jpicker-min') ?>
    <?= $this->Html->css('vendor/multiple-select/multiple-select.min') ?>
    <?= $this->Html->css('common/common') ?>
    <?= $this->Html->css('admin/base') ?>
    <?= $this->Html->css('admin/parts_design') ?>
    <?= $this->fetch('css') ?>
    <?= $this->Html->script('vendor/jquery/jquery.min') ?>
    <?= $this->Html->script('vendor/jquery-ui/jquery-ui.min') ?>
    <?= $this->Html->script('vendor/js-cookie/js.cookie.min') ?>
    <?= $this->Html->script('vendor/jquery-datetimepicker/jquery.datetimepicker.full.min') ?>
    <?= $this->Html->script('vendor/jquery-ui-touch-punch/jquery.ui.touch-punch.min') ?>
    <?= $this->Html->script('vendor/multiple-select/multiple-select.min') ?>
    <?= $this->Html->script('vendor/stickyfill/stickyfill.min') ?>
    <?= $this->Html->script('vendor/tinymce/tinymce.min') ?>
    <?= $this->Html->script('common/common') ?>
    <?= $this->Html->script('admin/common/common') ?>
    <?= $this->Html->script('admin/common/tableBtn') ?>
    <?= $this->Html->script('admin/common') ?>
    <?= $this->fetch('script') ?>
</head>
<body <?php if ($this->fetch('noNavi')) : ?>class="mgt-10 noNavi"<?php endif; ?>>
<div class="js_container">
    <?= $this->element('Admin/Common/layout/svg') ?>
    <div class="nav-layer">
    </div>
    <?php if (!$this->fetch('noNavi')) : ?>
        <header class="cmn-header">
            <?php if ($this->CommonData->existsAdminLoginData()) : ?>
                <div class="cmn-header-wrap clearfix js_global_navi">
                    <div class="f-l d-flex">
                        <div class="logo-box d-flex">
                            <h1 class="ttl-h1">
                                <?= $this->Html->link($this->Html->image('admin/logo_re5_white.png'), [
                                    'prefix' => 'Admin',
                                    'controller' => 'Index',
                                    'action' => 'index',
                                ], ['escapeTitle' => false]) ?>
                            </h1>
                        </div>
                        <div class="hNav-box">
                            <nav class="is-main">
                                <ul class="open-nav d-flex">
                                    <?php if ($this->Authority->isAuthority(true, 'Reservations', 'calendar')) : ?>
                                        <li class="btn-nav">
                                            <a href="<?= $this->Url->build([
                                                'prefix' => 'Admin',
                                                'controller' => 'Reservations',
                                                'action' => 'calendar',
                                            ]) ?>" class="d-flex nav-link">
                                                <svg class="icon is-header">
                                                    <use xlink:href="#icon_reservedata"/>
                                                </svg>
                                                予約台帳
                                            </a>
                                        </li>
                                    <?php endif; ?>
                                    <?php if ($this->CommonData->getAdminLoginData()->get('authority') !== \App\Model\Entity\Admin::AUTHORITY_OPERATOR) : ?>
                                        <?php if (!empty ($this->CommonData->getAdminLoginData()->get('admin_authority')) && !empty($this->CommonData->getAdminLoginData()->get('admin_authority')->get('access_setting'))) : ?>
                                            <li class="btn-nav">
                                                <a class="d-flex nav-link swich<?php if ($this->fetch('headerType') === 'master'): ?> selected<?php endif; ?>">各種設定メニュー</a>
                                                <div class="nav-list is-master">
                                                    <div class="nav-list-wrap">
                                                        <dl class="nav-contents">
                                                            <dt>
                                                                <a href="<?= $this->Url->build([
                                                                    'prefix' => 'Admin',
                                                                    'controller' => 'Index',
                                                                    'action' => 'index',
                                                                ]) ?>">
                                                                    <svg class="icon is-nav">
                                                                        <use xlink:href="#icon_breadC_home"/>
                                                                    </svg>
                                                                    各種設定メニューTOP
                                                                </a>
                                                            </dt>
                                                            <dd class="clearfix">
                                                                <div class="f-l">
                                                                    <?php if ($this->Authority->isAuthority(true, 'Events', 'list')
                                                                        || $this->Authority->isAuthority(true, 'Labels', 'list')
                                                                        || $this->Authority->isAuthority(true, 'Tags', 'list')
                                                                        || $this->Authority->isAuthority(true, 'Options', 'list')
                                                                        || $this->Authority->isAuthority(true, 'EventHolidays', 'list')
                                                                        || $this->Authority->isAuthority(true, 'Holidays', 'list')
                                                                        || $this->Authority->isAuthority(true, 'FormPatterns', 'list', null, FormGroup::FORM_TYPE_RESERVATION)
                                                                        || $this->Authority->isAuthority(true, 'FormGroups', 'edit', FormGroup::FORM_TYPE_RESERVATION, null)) : ?>
                                                                        <dl class="nav-contents-list">
                                                                            <dt>
                                                                                <svg class="icon is-nav">
                                                                                    <use xlink:href="#icon_menu_reserve"/>
                                                                                </svg>
                                                                                予約設定
                                                                            </dt>
                                                                            <dd>
                                                                                <ul class="nav-link-list">
                                                                                    <?php if ($this->Authority->isAuthority(true, 'Events', 'list')) : ?>
                                                                                        <li>
                                                                                            <?= $this->Html->link('予約枠設定', [
                                                                                                'prefix' => 'Admin',
                                                                                                'controller' => 'Events',
                                                                                                'action' => 'list',
                                                                                            ]) ?>
                                                                                        </li>
                                                                                    <?php endif; ?>
                                                                                    <?php if ($this->Authority->isAuthority(true, 'Labels', 'list')) : ?>
                                                                                        <li>
                                                                                            <?= $this->Html->link('カテゴリー設定', [
                                                                                                'prefix' => 'Admin',
                                                                                                'controller' => 'Labels',
                                                                                                'action' => 'list',
                                                                                            ]) ?>
                                                                                        </li>
                                                                                    <?php endif; ?>
                                                                                    <?php if ($this->Authority->isAuthority(true, 'Tags', 'list')) : ?>
                                                                                        <li>
                                                                                            <?= $this->Html->link('絞り込みキーワード設定', [
                                                                                                'prefix' => 'Admin',
                                                                                                'controller' => 'Tags',
                                                                                                'action' => 'list',
                                                                                            ]) ?>
                                                                                        </li>
                                                                                    <?php endif; ?>
                                                                                    <?php if ($this->Authority->isAuthority(true, 'Options', 'list')) : ?>
                                                                                        <li>
                                                                                            <?= $this->Html->link('オプション設定', [
                                                                                                'prefix' => 'Admin',
                                                                                                'controller' => 'Options',
                                                                                                'action' => 'list',
                                                                                            ]) ?>
                                                                                        </li>
                                                                                    <?php endif; ?>
                                                                                    <?php if ($this->Authority->isAuthority(true, 'EventHolidays', 'list')) : ?>
                                                                                        <li>
                                                                                            <?= $this->Html->link('休業設定', [
                                                                                                'prefix' => 'Admin',
                                                                                                'controller' => 'EventHolidays',
                                                                                                'action' => 'list',
                                                                                            ]) ?>
                                                                                        </li>
                                                                                    <?php endif; ?>
                                                                                    <?php if ($this->Authority->isAuthority(true, 'Holidays', 'edit')) : ?>
                                                                                        <li>
                                                                                            <?= $this->Html->link('祝日設定', [
                                                                                                'prefix' => 'Admin',
                                                                                                'controller' => 'Holidays',
                                                                                                'action' => 'edit',
                                                                                            ]) ?>
                                                                                        </li>
                                                                                    <?php endif; ?>
                                                                                    <?php if ($this->Authority->isAuthority(true, 'FormPatterns', 'list', null, FormGroup::FORM_TYPE_RESERVATION)) : ?>
                                                                                        <li>
                                                                                            <?= $this->Html->link('予約内容の表示パターン', [
                                                                                                'prefix' => 'Admin',
                                                                                                'controller' => 'FormPatterns',
                                                                                                'action' => 'list',
                                                                                                '_name' => 'formPatternsReserve'
                                                                                            ]) ?>
                                                                                        </li>
                                                                                    <?php endif; ?>
                                                                                    <?php if ($this->Authority->isAuthority(true, 'FormGroups', 'edit', FormGroup::FORM_TYPE_RESERVATION, null)) : ?>
                                                                                        <li>
                                                                                            <?= $this->Html->link('予約内容の項目設定', [
                                                                                                'prefix' => 'Admin',
                                                                                                'controller' => 'FormGroups',
                                                                                                'action' => 'edit',
                                                                                                'id' => FormGroup::FORM_TYPE_RESERVATION,
                                                                                            ]) ?>
                                                                                        </li>
                                                                                    <?php endif; ?>
                                                                                </ul>
                                                                            </dd>
                                                                        </dl>
                                                                    <?php endif; ?>
                                                                    <?php if ($this->Authority->isAuthority(true, 'UserAuthorities', 'list')
                                                                        || $this->Authority->isAuthority(true, 'FormGroups', 'list', FormGroup::FORM_TYPE_USER, null)
                                                                        || $this->Authority->isAuthority(true, 'FormPatterns', 'list', null, FormGroup::FORM_TYPE_USER)) : ?>
                                                                        <dl class="nav-contents-list">
                                                                            <dt>
                                                                                <svg class="icon is-nav">
                                                                                    <use xlink:href="#icon_menu_member"/>
                                                                                </svg>
                                                                                顧客設定
                                                                            </dt>
                                                                            <dd>
                                                                                <ul class="nav-link-list">
                                                                                    <?php if ($this->Authority->isAuthority(true, 'UserAuthorities', 'list')) : ?>
                                                                                        <li>
                                                                                            <?= $this->Html->link('顧客の権限設定', [
                                                                                                'prefix' => 'Admin',
                                                                                                'controller' => 'UserAuthorities',
                                                                                                'action' => 'list',
                                                                                            ]) ?>
                                                                                        </li>
                                                                                    <?php endif; ?>
                                                                                    <?php if ($this->Authority->isAuthority(true, 'FormGroups', 'list', FormGroup::FORM_TYPE_USER, null)) : ?>
                                                                                        <li>
                                                                                            <?= $this->Html->link('顧客情報の項目設定', [
                                                                                                'prefix' => 'Admin',
                                                                                                'controller' => 'FormGroups',
                                                                                                'action' => 'edit',
                                                                                                'id' => FormGroup::FORM_TYPE_USER,
                                                                                            ]) ?>
                                                                                        </li>
                                                                                    <?php endif; ?>
                                                                                    <?php if ($this->Authority->isAuthority(true, 'FormPatterns', 'list', null, FormGroup::FORM_TYPE_USER)) : ?>
                                                                                        <li>
                                                                                            <?= $this->Html->link('顧客情報の表示パターン', [
                                                                                                'prefix' => 'Admin',
                                                                                                'controller' => 'FormPatterns',
                                                                                                'action' => 'list',
                                                                                                '_name' => 'formPatternsUser'
                                                                                            ]) ?>
                                                                                        </li>
                                                                                    <?php endif; ?>
                                                                                </ul>
                                                                            </dd>
                                                                        </dl>
                                                                    <?php endif; ?>
                                                                    <?php if ($this->Authority->isAuthority(true, 'AutoReplyMails', 'list')) : ?>
                                                                        <dl class="nav-contents-list">
                                                                            <dt>
                                                                                <svg class="icon is-nav">
                                                                                    <use xlink:href="#icon_header_mail"/>
                                                                                </svg>
                                                                                メール設定
                                                                            </dt>
                                                                            <?php if ($this->Authority->isAuthority(true, 'AutoReplyMails', 'list')) : ?>
                                                                                <dd>
                                                                                    <ul class="nav-link-list">
                                                                                        <li>
                                                                                            <?= $this->Html->link('自動返信メール設定', [
                                                                                                'prefix' => 'Admin',
                                                                                                'controller' => 'AutoReplyMails',
                                                                                                'action' => 'list',
                                                                                            ]) ?>
                                                                                        </li>
                                                                                    </ul>
                                                                                </dd>
                                                                            <?php endif; ?>
                                                                        </dl>
                                                                    <?php endif; ?>
                                                                    <?php if ($this->Authority->isAuthority(true, 'SmartLocks', 'akerun')) : ?>
                                                                        <?php if ($this->SmartLock->useAkerun()) : ?>
                                                                            <dl class="nav-contents-list">
                                                                                <dt>
                                                                                    <svg class="icon is-nav">
                                                                                        <use xlink:href="#icon_btn_setting"/>
                                                                                    </svg>
                                                                                    スマートロック設定
                                                                                </dt>
                                                                                <dd>
                                                                                    <ul class="nav-link-list">
                                                                                        <li>
                                                                                            <?= $this->Html->link('Akerun設定', [
                                                                                                'prefix' => 'Admin',
                                                                                                'controller' => 'SmartLocks',
                                                                                                'action' => 'akerun',
                                                                                            ]) ?>
                                                                                        </li>
                                                                                    </ul>
                                                                                </dd>
                                                                            </dl>
                                                                        <?php endif; ?>
                                                                    <?php endif; ?>
                                                                </div>
                                                                <div class="f-r">
                                                                    <?php if ($this->Authority->isAuthority(true, 'Words', 'wordEdit')
                                                                        || $this->Authority->isAuthority(true, 'Words', 'errorWordEdit')
                                                                        || $this->Authority->isAuthority(true, 'Words', 'statusWordEdit')
                                                                        || $this->Authority->isAuthority(true, 'Words', 'prefWordEdit')
                                                                        || $this->Authority->isAuthority(true, 'Words', 'receptionStatusWordEdit')
                                                                        || $this->Authority->isAuthority(true, 'Words', 'paymentMethodWordEdit')
                                                                        || $this->Authority->isAuthority(true, 'Words', 'paymentStatusWordEdit')) : ?>
                                                                        <dl class="nav-contents-list">
                                                                            <dt>
                                                                                <svg class="icon is-nav">
                                                                                    <use xlink:href="#icon_menu_build"/>
                                                                                </svg>
                                                                                文言設定
                                                                            </dt>
                                                                            <dd>
                                                                                <ul class="nav-link-list">
                                                                                    <?php if ($this->Authority->isAuthority(true, 'Words', 'wordEdit')) : ?>
                                                                                        <li>
                                                                                            <?= $this->Html->link('文言設定', [
                                                                                                'prefix' => 'Admin',
                                                                                                'controller' => 'Words',
                                                                                                'action' => 'word-edit',
                                                                                            ]) ?>
                                                                                        </li>
                                                                                    <?php endif; ?>
                                                                                    <?php if ($this->Authority->isAuthority(true, 'Words', 'errorWordEdit')) : ?>
                                                                                        <li>
                                                                                            <?= $this->Html->link('エラー文言設定', [
                                                                                                'prefix' => 'Admin',
                                                                                                'controller' => 'Words',
                                                                                                'action' => 'error-word-edit',
                                                                                            ]) ?>
                                                                                        </li>
                                                                                    <?php endif; ?>
                                                                                    <?php if ($this->Authority->isAuthority(true, 'Words', 'statusWordEdit')) : ?>
                                                                                        <li>
                                                                                            <?= $this->Html->link('予約ステータス文言設定', [
                                                                                                'prefix' => 'Admin',
                                                                                                'controller' => 'Words',
                                                                                                'action' => 'status-word-edit',
                                                                                            ]) ?>
                                                                                        </li>
                                                                                    <?php endif; ?>
                                                                                    <?php if ($this->Authority->isAuthority(true, 'Words', 'prefWordEdit')) : ?>
                                                                                        <li>
                                                                                            <?= $this->Html->link('都道府県文言設定', [
                                                                                                'prefix' => 'Admin',
                                                                                                'controller' => 'Words',
                                                                                                'action' => 'pref-word-edit',
                                                                                            ]) ?>
                                                                                        </li>
                                                                                    <?php endif; ?>
                                                                                    <?php if ($this->Setting->getSystemSetting()->get('payment_use_flg') === SystemSetting::PAYMENT_USE_FLG_ON) : ?>
                                                                                        <?php if ($this->Authority->isAuthority(true, 'Words', 'paymentMethodWordEdit')) : ?>
                                                                                            <li>
                                                                                                <?= $this->Html->link("決済方法文言設定", [
                                                                                                    'prefix' => 'Admin',
                                                                                                    'controller' => 'Words',
                                                                                                    'action' => 'payment-method-word-edit',
                                                                                                ]) ?>
                                                                                            </li>
                                                                                        <?php endif; ?>
                                                                                        <?php if ($this->Authority->isAuthority(true, 'Words', 'paymentStatusWordEdit')) : ?>
                                                                                            <li>
                                                                                                <?= $this->Html->link("決済ステータス文言設定", [
                                                                                                    'prefix' => 'Admin',
                                                                                                    'controller' => 'Words',
                                                                                                    'action' => 'payment-status-word-edit',
                                                                                                ]) ?>
                                                                                            </li>
                                                                                        <?php endif; ?>
                                                                                    <?php endif; ?>
                                                                                    <?php if ($this->Authority->isAuthority(true, 'Words', 'receptionStatusWordEdit')) : ?>
                                                                                        <li>
                                                                                            <?= $this->Html->link('受付ステータス文言設定', [
                                                                                                'prefix' => 'Admin',
                                                                                                'controller' => 'Words',
                                                                                                'action' => 'reception-status-word-edit',
                                                                                            ]) ?>
                                                                                        </li>
                                                                                    <?php endif; ?>
                                                                                </ul>
                                                                            </dd>
                                                                        </dl>
                                                                    <?php endif; ?>
                                                                    <?php if ($this->Authority->isAuthority(true, 'SiteSetting', 'edit')
                                                                        || $this->Authority->isAuthority(true, 'Cms', 'edit')
                                                                        || $this->Authority->isAuthority(true, 'AnalysisTags', 'edit')
                                                                        || $this->Authority->isAuthority(true, 'Terms', 'edit')
                                                                        || $this->Authority->isAuthority(true, 'ColorChips', 'edit')
                                                                        || $this->Authority->isAuthority(true, 'Payment', 'all')) : ?>
                                                                        <dl class="nav-contents-list">
                                                                            <dt>
                                                                                <svg class="icon is-nav">
                                                                                    <use xlink:href="#icon_menu_calendar"/>
                                                                                </svg>
                                                                                予約サイト設定
                                                                            </dt>
                                                                            <dd>
                                                                                <ul class="nav-link-list">
                                                                                    <?php if ($this->Authority->isAuthority(true, 'SiteSetting', 'edit')) : ?>
                                                                                        <li>
                                                                                            <?= $this->Html->link('基本設定', [
                                                                                                'prefix' => 'Admin',
                                                                                                'controller' => 'SiteSetting',
                                                                                                'action' => 'edit',
                                                                                            ]) ?>
                                                                                        </li>
                                                                                    <?php endif; ?>
                                                                                    <?php if ($this->Authority->isAuthority(true, 'Cms', 'edit')) : ?>
                                                                                        <li>
                                                                                            <?= $this->Html->link('デザイン設定', [
                                                                                                'prefix' => 'Admin',
                                                                                                'controller' => 'Cms',
                                                                                                'action' => 'edit',
                                                                                            ]) ?>
                                                                                        </li>
                                                                                    <?php endif; ?>
                                                                                    <?php if ($this->Authority->isAuthority(true, 'AnalysisTags', 'edit')) : ?>
                                                                                        <li>
                                                                                            <?= $this->Html->link('計測タグ設定', [
                                                                                                'prefix' => 'Admin',
                                                                                                'controller' => 'AnalysisTags',
                                                                                                'action' => 'edit',
                                                                                            ]) ?>
                                                                                        </li>
                                                                                    <?php endif; ?>
                                                                                    <?php if ($this->Authority->isAuthority(true, 'Terms', 'edit')) : ?>
                                                                                        <li>
                                                                                            <?= $this->Html->link('利用規約・個人情報取り扱い・特商法 設定', [
                                                                                                'prefix' => 'Admin',
                                                                                                'controller' => 'Terms',
                                                                                                'action' => 'edit',
                                                                                            ], ['escapeTitle' => false]) ?>
                                                                                        </li>
                                                                                    <?php endif; ?>
                                                                                    <?php if ($this->Authority->isAuthority(true, 'ColorChips', 'edit')) : ?>
                                                                                        <li>
                                                                                            <?= $this->Html->link('予約枠のカラー設定', [
                                                                                                'prefix' => 'Admin',
                                                                                                'controller' => 'ColorChips',
                                                                                                'action' => 'edit',
                                                                                            ]) ?>
                                                                                        </li>
                                                                                    <?php endif; ?>
                                                                                    <?php if ($this->Authority->isAuthority(true, 'Payment', 'all')) : ?>
                                                                                        <?php if ($this->Setting->getSystemSetting()->get('payment_use_flg') === SystemSetting::PAYMENT_USE_FLG_ON) : ?>
                                                                                            <li>
                                                                                                <?= $this->Html->link('クレジット決済情報', [
                                                                                                    'prefix' => 'Admin',
                                                                                                    'controller' => 'Payment',
                                                                                                    'action' => 'view',
                                                                                                ]) ?>
                                                                                            </li>
                                                                                        <?php endif; ?>
                                                                                    <?php endif; ?>
                                                                                </ul>
                                                                            </dd>
                                                                        </dl>
                                                                    <?php endif; ?>
                                                                    <?php if ($this->Authority->isAuthority(true, 'Organizers', 'list')
                                                                        || $this->Authority->isAuthority(true, 'ZoomConnectUsers', 'view')) : ?>
                                                                        <dl class="nav-contents-list">
                                                                            <dt>
                                                                                <svg class="icon is-nav">
                                                                                    <use xlink:href="#icon_btn_setting"/>
                                                                                </svg>
                                                                                ビデオ会議設定
                                                                            </dt>
                                                                            <dd>
                                                                                <ul class="nav-link-list">
                                                                                    <?php if ($this->Authority->isAuthority(true, 'Organizers', 'list')) : ?>
                                                                                        <li>
                                                                                            <?= $this->Html->link('主催者設定', [
                                                                                                'prefix' => 'Admin',
                                                                                                'controller' => 'Organizers',
                                                                                                'action' => 'list',
                                                                                            ]) ?>
                                                                                        </li>
                                                                                    <?php endif; ?>
                                                                                    <?php if ($this->Authority->isAuthority(true, 'ZoomConnectUsers', 'view')) : ?>
                                                                                        <li>
                                                                                            <?= $this->Html->link('Zoom連携ユーザー管理', [
                                                                                                'prefix' => 'Admin',
                                                                                                'controller' => 'ZoomConnectUsers',
                                                                                                'action' => 'view',
                                                                                            ]) ?>
                                                                                        </li>
                                                                                    <?php endif; ?>
                                                                                </ul>
                                                                            </dd>
                                                                        </dl>
                                                                    <?php endif; ?>
                                                                    <?php if ($this->Authority->isAuthority(true, 'Recaptcha', 'edit')
                                                                        || ($this->Authority->isAuthority(true, 'PaymentErrors', 'list')
                                                                        && $this->Setting->getSystemSetting()->get('payment_use_flg') === SystemSetting::PAYMENT_USE_FLG_ON)) : ?>
                                                                        <dl class="nav-contents-list">
                                                                            <dt>
                                                                                <svg class="icon is-nav">
                                                                                    <use xlink:href="#icon_btn_setting"/>
                                                                                </svg>
                                                                                Bot対策設定
                                                                            </dt>
                                                                            <dd>
                                                                                <ul class="nav-link-list">
                                                                                    <?php if ($this->Authority->isAuthority(true, 'Recaptcha', 'edit')) : ?>
                                                                                        <li>
                                                                                            <?= $this->Html->link('reCAPTCHAv3設定', [
                                                                                                'prefix' => 'Admin',
                                                                                                'controller' => 'recaptcha',
                                                                                                'action' => 'edit',
                                                                                            ]) ?>
                                                                                        </li>
                                                                                    <?php endif; ?>
                                                                                    <?php if ($this->Authority->isAuthority(true, 'PaymentErrors', 'list')
                                                                                        && $this->Setting->getSystemSetting()->get('payment_use_flg') === SystemSetting::PAYMENT_USE_FLG_ON) : ?>
                                                                                        <li>
                                                                                            <?= $this->Html->link('決済エラー回数一覧', [
                                                                                                'prefix' => 'Admin',
                                                                                                'controller' => 'paymentErrors',
                                                                                                'action' => 'list',
                                                                                            ]) ?>
                                                                                        </li>
                                                                                    <?php endif; ?>
                                                                                </ul>
                                                                            </dd>
                                                                        </dl>
                                                                    <?php endif; ?>
                                                                </div>
                                                            </dd>
                                                        </dl>
                                                    </div>
                                                </div>
                                            </li>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                    <?php if (!empty($this->CommonData->getAdminLoginData()->get('admin_authority')) && !empty($this->CommonData->getAdminLoginData()->get('admin_authority')->get('access_operator'))) : ?>
                                        <li class="btn-nav">
                                            <a class="d-flex nav-link swich<?php if ($this->fetch('headerType') === 'data'): ?> selected<?php endif; ?>">運用メニュー</a>
                                            <div class="nav-list is-reserve">
                                                <div class="nav-list-wrap">
                                                    <dl class="nav-contents">
                                                        <?php if ($this->Authority->isAuthority(true, 'Reservations', 'calendar')) : ?>
                                                            <dt>
                                                                <a href="<?= $this->Url->build([
                                                                    'prefix' => 'Admin',
                                                                    'controller' => 'Reservations',
                                                                    'action' => 'calendar',
                                                                ]) ?>">
                                                                    <svg class="icon is-nav">
                                                                        <use xlink:href="#icon_breadC_home"/>
                                                                    </svg>
                                                                    予約台帳
                                                                </a>
                                                            </dt>
                                                        <?php endif; ?>
                                                        <dd class="clearfix">
                                                            <div class="f-l">
                                                                <?php if ($this->Authority->isAuthority(true, 'Reservations', 'list')
                                                                    || $this->Authority->isAuthority(true, 'ReceptionStatuses', 'list')
                                                                ) : ?>
                                                                    <dl class="nav-contents-list">
                                                                        <dt>
                                                                            <svg class="icon is-nav">
                                                                                <use xlink:href="#icon_menu_reserve"/>
                                                                            </svg>
                                                                            予約管理
                                                                        </dt>
                                                                        <dd>
                                                                            <ul class="nav-link-list">
                                                                                <?php if ($this->Authority->isAuthority(true, 'Reservations', 'list')) : ?>
                                                                                    <li>
                                                                                        <?= $this->Html->link('予約一覧', [
                                                                                            'prefix' => 'Admin',
                                                                                            'controller' => 'Reservations',
                                                                                            'action' => 'list',
                                                                                        ]) ?>
                                                                                    </li>
                                                                                <?php endif; ?>
                                                                                <?php if ($this->Authority->isAuthority(true, 'ReceptionStatuses', 'list')) : ?>
                                                                                    <li>
                                                                                        <?= $this->Html->link('受付状況一覧', [
                                                                                            'prefix' => 'Admin',
                                                                                            'controller' => 'ReceptionStatuses',
                                                                                            'action' => 'list',
                                                                                        ]) ?>
                                                                                    </li>
                                                                                <?php endif; ?>
                                                                            </ul>
                                                                        </dd>
                                                                    </dl>
                                                                <?php endif; ?>
                                                                <?php if ($this->Authority->isAuthority(true, 'Users', 'list')
                                                                    || $this->Authority->isAuthority(true, 'Users', 'add')) : ?>
                                                                    <dl class="nav-contents-list">
                                                                        <dt>
                                                                            <svg class="icon is-nav">
                                                                                <use xlink:href="#icon_menu_member"/>
                                                                            </svg>
                                                                            顧客管理
                                                                        </dt>
                                                                        <dd>
                                                                            <ul class="nav-link-list">
                                                                                <?php if ($this->Authority->isAuthority(true, 'Users', 'list')) : ?>
                                                                                    <li>
                                                                                        <?= $this->Html->link('顧客一覧', [
                                                                                            'prefix' => 'Admin',
                                                                                            'controller' => 'Users',
                                                                                            'action' => 'list',
                                                                                        ]) ?>
                                                                                    </li>
                                                                                <?php endif; ?>
                                                                                <?php if ($this->Authority->isAuthority(true, 'Users', 'add')) : ?>
                                                                                    <li>
                                                                                        <?= $this->Html->link('会員登録', [
                                                                                            'prefix' => 'Admin',
                                                                                            'controller' => 'Users',
                                                                                            'action' => 'add',
                                                                                        ]) ?>
                                                                                    </li>
                                                                                <?php endif; ?>
                                                                            </ul>
                                                                        </dd>
                                                                    </dl>
                                                                <?php endif; ?>
                                                            </div>
                                                            <div class="f-r">
                                                                <?php if ($this->Authority->isAuthority(true, 'MailDeliveries', 'list')
                                                                    || $this->Authority->isAuthority(true, 'BounceMails', 'list')) : ?>
                                                                    <dl class="nav-contents-list">
                                                                        <dt>
                                                                            <svg class="icon is-nav">
                                                                                <use xlink:href="#icon_header_mail"/>
                                                                            </svg>
                                                                            メール管理
                                                                        </dt>
                                                                        <dd>
                                                                            <ul class="nav-link-list">
                                                                                <?php if ($this->Authority->isAuthority(true, 'MailDeliveries', 'list')) : ?>
                                                                                    <li>
                                                                                        <?= $this->Html->link('メール配信履歴', [
                                                                                            'prefix' => 'Admin',
                                                                                            'controller' => 'MailDeliveries',
                                                                                            'action' => 'list',
                                                                                        ]) ?>
                                                                                    </li>
                                                                                <?php endif; ?>
                                                                                <?php if ($this->Authority->isAuthority(true, 'BounceMails', 'list')) : ?>
                                                                                    <li>
                                                                                        <?= $this->Html->link('不達メール', [
                                                                                            'prefix' => 'Admin',
                                                                                            'controller' => 'BounceMails',
                                                                                            'action' => 'list',
                                                                                        ]) ?>
                                                                                    </li>
                                                                                <?php endif; ?>
                                                                            </ul>
                                                                        </dd>
                                                                    </dl>
                                                                <?php endif; ?>
                                                                <?php if ($this->Authority->isAuthority(true, 'News', 'list')
                                                                    || $this->Authority->isAuthority(true, 'FileGroups', 'list')
                                                                    || $this->Authority->isAuthority(true, 'AdminOperationalLogs', 'list')) : ?>
                                                                    <dl class="nav-contents-list">
                                                                        <dt>
                                                                            <svg class="icon is-nav">
                                                                                <use xlink:href="#icon_menu_build"/>
                                                                            </svg>
                                                                            CMS管理
                                                                        </dt>
                                                                        <dd>
                                                                            <ul class="nav-link-list">
                                                                                <?php if ($this->Authority->isAuthority(true, 'News', 'list')) : ?>
                                                                                    <li>
                                                                                        <?= $this->Html->link('お知らせ', [
                                                                                            'prefix' => 'Admin',
                                                                                            'controller' => 'News',
                                                                                            'action' => 'list',
                                                                                        ]) ?>
                                                                                    </li>
                                                                                <?php endif; ?>
                                                                                <?php if ($this->Authority->isAuthority(true, 'FileGroups', 'list')) : ?>
                                                                                    <li>
                                                                                        <?= $this->Html->link('ファイル管理', [
                                                                                            'prefix' => 'Admin',
                                                                                            'controller' => 'FileGroups',
                                                                                            'action' => 'list',
                                                                                        ]) ?>
                                                                                    </li>
                                                                                <?php endif; ?>
                                                                                <?php if ($this->Authority->isAuthority(true, 'AdminOperationalLogs', 'list')) : ?>
                                                                                    <li>
                                                                                        <?= $this->Html->link('操作ログ', [
                                                                                            'prefix' => 'Admin',
                                                                                            'controller' => 'AdminOperationalLogs',
                                                                                            'action' => 'list',
                                                                                        ]) ?>
                                                                                    </li>
                                                                                <?php endif; ?>
                                                                            </ul>
                                                                        </dd>
                                                                    </dl>
                                                                <?php endif; ?>
                                                            </div>
                                                        </dd>
                                                    </dl>
                                                </div>
                                            </div>
                                        </li>
                                    <?php endif; ?>
                                </ul>
                            </nav>
                        </div>
                    </div>
                    <div class="f-r">
                        <div class="hNav-box">
                            <nav class="is-sub">
                                <ul class="d-flex">
                                    <li class="btn-nav">
                                        <a href="<?= $this->Url->build([
                                            'prefix' => 'Admin',
                                            'controller' => 'Admins',
                                            'action' => 'list',
                                        ]) ?>" id="admin" target="_self" class="d-flex nav-link">
                                            <svg class="icon is-header">
                                                <use xlink:href="#icon_mypage"/>
                                            </svg>
                                            管理者情報
                                        </a>
                                    </li>
                                    <li class="btn-nav">
                                        <a href=<?= h($this->Configure->read('Env.manual.domain')) . '/?manual' ?> id="manual"
                                           target="_blank" class="d-flex nav-link">
                                            <svg class="icon is-header">
                                                <use xlink:href="#icon_manual"/>
                                            </svg>
                                            マニュアル
                                        </a>
                                    </li>
                                    <li class="btn-nav">
                                        <a href="<?= $this->Url->build([
                                            'prefix' => 'Admin',
                                            'controller' => 'Auth',
                                            'action' => 'logout',
                                        ]) ?>" id="logout" class="d-flex nav-link">
                                            <svg class="icon is-header">
                                                <use xlink:href="#icon_logout"/>
                                            </svg>
                                            ログアウト
                                        </a>
                                    </li>
                                </ul>
                            </nav>
                        </div>
                    </div>
                </div>
            <?php else : ?>
                <div class="cmn-header-wrap clearfix">
                    <div class="f-l d-flex">
                        <div class="logo-box d-flex">
                            <h1 class="ttl-h1">
                                <?= $this->Html->link($this->Html->image('admin/logo_re5_white.png'), [
                                    'prefix' => 'Admin',
                                    'controller' => 'Auth',
                                    'action' => 'login',
                                ], ['escapeTitle' => false]) ?>
                            </h1>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </header>
        <aside class="cmn-lead">
            <div class="breadC-area">
                <div class="breadC-area-wrap l-main clearfix">
                    <?php if ($this->fetch('headerType') === 'master'): ?>
                        <?php $this->Template->setAdminBreadcrumbs('各種設定メニューTOP'); ?>
                        <?= $this->Breadcrumbs->render(['class' => ['breadC-list']], ['separator' => '>']) ?>
                    <?php endif; ?>
                    <?php if ($this->fetch('headerType') === 'data'): ?>
                        <?php $this->Template->setAdminBreadcrumbs('運用メニューTOP'); ?>
                        <?= $this->Breadcrumbs->render(['class' => ['breadC-list']], ['separator' => '>']) ?>
                    <?php endif; ?>

                    <?php if ($this->Setting->getManualLink() !== '') : ?>
                        <?= $this->Html->link('<svg class="icon">
                            <use xlink:href="#icon_help"/>
                        </svg>',
                            $this->Setting->getManualLink(), [
                                'target' => '_blank',
                                'class' => ['link-manual', 'tooltip'],
                                'escape' => false,
                                'title' => 'マニュアルを見る'
                            ]) ?>
                    <?php endif; ?>
                    <?php if ($this->CommonData->duringContinueReservation()) : ?>
                        <p class="unReserve f-r">
                            <a href="<?= $this->Url->build([
                                'prefix' => 'Admin',
                                'controller' => 'Reservations',
                                'action' => 'addConf',
                            ]) ?>">
                                <span class="icon"><svg class="icon-home"><use
                                            xlink:href="#icon_info"></use></svg></span>
                                未確定の予約があります
                                <span class="icon"><svg class="icon-left">
                                    <use xlink:href="#icon_arrow_left"></use>
                                </svg>
                            </span>
                            </a>
                        </p>
                    <?php endif; ?>
                    <?php
                        $isDisplaySearchPaymentExpiredLink
                         = $this->CommonData->isDisplaySearchPaymentExpiredLink();
                         $isDisplaySearchReservationUnlinkedSmartLockLink
                            = $this->CommonData->isDisplaySearchReservationUnlinkedSmartLockLink();
                    ?>
                    <?php if (
                        $isDisplaySearchPaymentExpiredLink
                        || $isDisplaySearchReservationUnlinkedSmartLockLink
                    ) : ?>
                        <p class="unPayment f-r">
                            <?php if ($isDisplaySearchPaymentExpiredLink) : ?>
                                <a href="<?= $this->Url->build([
                                    'prefix' => 'Admin',
                                    'controller' => 'Reservations',
                                    'action' => 'list',
                                    '?' => [
                                        'search_payment_expired' => true,
                                    ],
                                ]) ?>">
                                <span class="icon"><svg class="icon-home"><use
                                            xlink:href="#icon_info"></use></svg></span>
                                未決済の予約があります
                                <span class="icon"><svg class="icon-left">
                                    <use xlink:href="#icon_arrow_left"></use>
                                </svg>
                                </span>
                                </a>
                            <?php endif; ?>
                            <?php if ($isDisplaySearchReservationUnlinkedSmartLockLink): ?>
                                <?php if (
                                    $isDisplaySearchPaymentExpiredLink
                                    && $isDisplaySearchReservationUnlinkedSmartLockLink
                                ) : ?>
                                    <br/>
                                <?php endif; ?>
                                <a href="<?= $this->Url->build([
                                    'prefix' => 'Admin',
                                    'controller' => 'Reservations',
                                    'action' => 'list',
                                    '?' => [
                                        'search_smart_lock_unlinked' => true,
                                    ],
                                ]) ?>">
                                    <span class="icon"><svg class="icon-home"><use
                                                xlink:href="#icon_info"></use></svg></span>
                                    スマートロック未連携の予約があります
                                    <span class="icon">
                                        <svg class="icon-left">
                                            <use xlink:href="#icon_arrow_left"></use>
                                        </svg>
                                    </span>
                                </a>
                            <?php endif; ?>
                        </p>
                    <?php endif; ?>
                </div>
            </div>
        </aside>
    <?php endif; ?>
    <main id="page"
          class="cmn-contents<?php if ((string)$this->fetch('main_class') !== ''): ?> <?= h($this->fetch('main_class')) ?><?php endif; ?>">
        <div
            class="cmn-contents-wrap <?php if ($this->fetch('loginClass')): ?>l-login<?php else: ?>l-main<?php endif; ?>">
            <?= $this->fetch('content') ?>
        </div>
    </main>
    <?php if (!$this->fetch('noNavi')) : ?>
        <footer class="cmn-footer">
            <div class="cmn-footer-wrap l-main">
                <p class="logo-box tac">
                    Powered by
                    <?= $this->Html->image('admin/logo_re5.png') ?>
                </p>
            </div>
        </footer>
    <?php endif; ?>
    <div class="hidden">
        <?= $this->element('Admin/Common/form/post_link') ?>
        <?= $this->element('Admin/Common/dialog/confirm') ?>
        <?= $this->element('Admin/Common/dialog/error') ?>
        <?= $this->element('Admin/Common/layout/loading') ?>
    </div>
</div>
</body>
</html>
