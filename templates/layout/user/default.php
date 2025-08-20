<?php

use \App\Model\Entity\AnalysisTag;

?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <?= $this->Setting->getAnalysisTagSetting(AnalysisTag::TYPE_AFTER_HEAD_OPEN); ?>
    <?= $this->Html->charset() ?>
    <meta http-equiv="content-language" content="ja"/>
    <meta http-equiv="X-UA-Compatible" content="IE=11; IE=Edge"/>
    <?= $this->Html->meta('robots', $this->fetch('robots', $this->Configure->read('Env.metaRobots', 'noindex,nofollow,noarchive'))) ?>
    <?= $this->Html->meta('viewport', 'width=device-width, minimum-scale=1, maximum-scale=10,initial-scale=1.0') ?>
    <?= $this->Html->meta('format-detection', 'address=no,email=no,telephone=no') ?>
    <?= $this->Html->meta('icon'); ?>
    <?= $this->Html->meta('keywords', $this->Setting->getSiteSetting()->get('meta_keyword')) ?>
    <?= $this->Html->meta('description', $this->Setting->getSiteSetting()->get('meta_description')) ?>
    <?= $this->fetch('meta') ?>
    <title><?= h($this->fetch('title')) ?> | <?= $this->Tr->h('COMMON_TITLE') ?></title>
    <?= $this->Html->css('vendor/jquery-ui/jquery-ui.min') ?>
    <?= $this->Html->css('vendor/jquery-datetimepicker/jquery.datetimepicker.min') ?>
    <?= $this->Html->css('vendor/bx-slider/jquery.bxslider.min') ?>
    <?= $this->Html->css('user/base') ?>
    <?= $this->Html->css('common/common') ?>
    <?= $this->Html->css('user/parts_design') ?>
    <link rel="stylesheet" href="/css/custom.css?<?= h($this->Setting->getCustomCssModified()) ?>">
    <?= $this->fetch('css') ?>
    <?= $this->Html->script('vendor/jquery/jquery.min') ?>
    <?= $this->Html->script('vendor/jquery-ui/jquery-ui.min') ?>
    <?= $this->Html->script('vendor/jquery-datetimepicker/jquery.datetimepicker.full.min') ?>
    <?= $this->Html->script('vendor/jquery-ui-touch-punch/jquery.ui.touch-punch.min') ?>
    <?= $this->Html->script('vendor/axia/axia') ?>
    <?= $this->Html->script('vendor/stickyfill/stickyfill.min') ?>
    <?= $this->Html->script('vendor/bx-slider/jquery.bxslider.min') ?>
    <?php if ($this->Recaptcha->isUseFlgOn()): ?>
        <?= $this->Html->script('https://www.google.com/recaptcha/api.js?render=' . rawurlencode($this->Recaptcha->getData()->get('site_key'))) ?>
    <?php endif; ?>
    <?= $this->Html->script('common/common') ?>
    <?= $this->Html->script('user/common') ?>
    <?= $this->fetch('script') ?>
    <?= $this->Setting->getAnalysisTagSetting(AnalysisTag::TYPE_BEFORE_HEAD_CLOSE); ?>
    <?= $this->fetch('analysisTag') ?>
</head>
<body<?php if ($this->CommonData->existsUserLoginData()): ?> id="login"<?php endif; ?>
    class="<?php if ($this->fetch('noNavi')): ?> noNavi <?php endif; ?>">
<?= $this->Setting->getAnalysisTagSetting(AnalysisTag::TYPE_AFTER_BODY_OPEN); ?>
<?php $this->assign('helpMenu', ''); ?>
<?php if (!$this->fetch('noNavi')): ?>
    <?php $this->start('helpMenu'); ?>
        <?php if ($this->CommonData->existsUserLoginData()): ?>
            <?= $this->element('User/Menu/Help/mypage') ?>
            <?= $this->element('User/Menu/Help/reservations_history') ?>
        <?php endif; ?>
        <?= $this->element('User/Menu/Help/waiting_cancellation') ?>
        <?= $this->element('User/Menu/Help/inquiry') ?>
        <?= $this->element('User/Menu/Help/term') ?>
        <?= $this->element('User/Menu/Help/sctl') ?>
        <?php if ($this->CommonData->existsUserLoginData()): ?>
            <?= $this->element('User/Menu/Help/user_add') ?>
        <?php endif; ?>
    <?php $this->end('helpMenu'); ?>
<?php endif; ?>
<?php $hasHelpMenu = false; ?>
<?php if (trim($this->fetch('helpMenu')) != '') : ?>
    <?php $hasHelpMenu = true; ?>
<?php endif; ?>
<div class="js_container">
    <?= $this->element('User/Common/layout/svg') ?>
    <?php if (!$this->fetch('noNavi')): ?>
        <header class="cmn-header">
            <div class="cmn-header-area l-main clearfix">
                <div class="box-logo f-l">
                    <h1 class="pc-only">
                        <?php if ($this->Setting->getSiteSetting()->header_logo_pc !== null) : ?>
                            <?php if ($this->Authority->isAuthority(true, 'Index', 'index')): ?>
                                <?= $this->Html->link(
                                    $this->Html->image($this->Setting->getSiteSetting()->header_logo_pc),
                                    $this->Template->getTopUrl(), ['escapeTitle' => false])
                                ?>
                            <?php else: ?>
                                <?= $this->Html->image($this->Setting->getSiteSetting()->header_logo_pc) ?>
                            <?php endif; ?>
                        <?php endif; ?>
                    </h1>
                    <h1 class="sp-only">
                        <?php if ($this->Setting->getSiteSetting()->header_logo_sp !== null) : ?>
                            <?php if ($this->Authority->isAuthority(true, 'Index', 'index')): ?>
                                <?= $this->Html->link(
                                    $this->Html->image($this->Setting->getSiteSetting()->header_logo_sp),
                                    $this->Template->getTopUrl(), ['escapeTitle' => false])
                                ?>
                            <?php else: ?>
                                <?= $this->Html->image($this->Setting->getSiteSetting()->header_logo_sp) ?>
                            <?php endif; ?>
                        <?php endif; ?>
                    </h1>
                </div>
                <?php if (!(isset($isError) && $isError)): ?>
                    <nav class="box-nav f-r clearfix">
                        <div class="pc-only">
                            <?php if (!$this->CommonData->existsUserLoginData()): ?>
                                <div class="before-login">
                                    <?php if ($hasHelpMenu) : ?>
                                        <aside id="btn" class="help-btn icon">
                                            <button type="button" class="helpBtnPC mgb-10"
                                                    title="<?= $this->Tr->h('common/help') ?>">
                                                <svg class="icon-help f-r">
                                                    <use xlink:href="#icon_header_help"/>
                                                </svg>
                                            </button>
                                        </aside>
                                    <?php endif; ?>
                                    <ul class="nav-icon f-l d-flex">
                                        <?= $this->element('User/Menu/waiting_cancellation') ?>
                                        <?= $this->element('User/Menu/inquiry') ?>
                                        <?= $this->element('User/Menu/term') ?>
                                        <?= $this->element('User/Menu/sctl') ?>
                                        <?= $this->element('User/Menu/login') ?>
                                        <?= $this->element('User/Menu/user_add') ?>
                                    </ul>
                                </div>
                            <?php else: ?>
                                <div class="after-login">
                                    <aside class="help-btn icon clearfix">
                                        <?php if ($hasHelpMenu) : ?>
                                            <button type="button" class="helpBtnPC f-r mgb-10"
                                                    title="<?= $this->Tr->h('common/help') ?>">
                                                <svg class="icon-help f-r">
                                                    <use xlink:href="#icon_header_help"/>
                                                </svg>
                                            </button>
                                        <?php endif; ?>
                                        <?php $loginName = $this->CommonData->getUserLoginData()->getLoginName(); ?>
                                        <?php if (((string)$loginName) !== ''): ?>
                                            <p class="txt-user-name f-r">
                                                <?= $this->Tr->h('common/hello') ?>
                                                <span
                                                    class="user-name"><?= h($loginName) ?></span>
                                                <?= $this->Tr->h('common/prefix') ?>
                                            </p>
                                        <?php endif; ?>
                                    </aside>
                                    <ul class="nav-icon f-l d-flex">
                                        <?= $this->element('User/Menu/mypage') ?>
                                        <?= $this->element('User/Menu/reservations_history') ?>
                                        <?= $this->element('User/Menu/waiting_cancellation') ?>
                                        <?= $this->element('User/Menu/inquiry') ?>
                                        <?= $this->element('User/Menu/term') ?>
                                        <?= $this->element('User/Menu/sctl') ?>
                                        <?= $this->element('User/Menu/logout') ?>
                                    </ul>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="sp-only">
                            <?php $this->start('spMenuLoginBox'); ?>
                                <?php if (!$this->CommonData->existsUserLoginData()): ?>
                                    <?= $this->element('User/Menu/login', [
                                        'sp' => true,
                                    ]) ?>
                                    <?= $this->element('User/Menu/user_add', [
                                        'sp' => true
                                    ]) ?>
                                <?php endif; ?>
                            <?php $this->end('spMenuLoginBox'); ?>
                            <?php $this->start('spMenuUserName'); ?>
                                <?php if ($this->CommonData->existsUserLoginData()): ?>
                                    <?= $this->Tr->h('common/hello') ?>
                                    <?php if (isset($loginName) && $loginName !== ''): ?>
                                        <span class="user-name">
                                            <?= h($loginName) ?>
                                        </span>
                                    <?php endif; ?>
                                    <?= $this->Tr->h('common/prefix') ?>
                                <?php endif; ?>
                            <?php $this->end('spMenuUserName'); ?>
                            <?php $this->start('spMenuNavi'); ?>
                                <?php if (!$this->CommonData->existsUserLoginData()): ?>
                                    <?= $this->element('User/Menu/waiting_cancellation', [
                                        'sp' => true,
                                    ]) ?>
                                    <?= $this->element('User/Menu/inquiry', [
                                        'sp' => true,
                                    ]) ?>
                                    <?= $this->element('User/Menu/term', [
                                        'sp' => true,
                                    ]) ?>
                                    <?= $this->element('User/Menu/sctl', [
                                        'sp' => true,
                                    ]) ?>
                                <?php else: ?>
                                    <?= $this->element('User/Menu/mypage', [
                                        'sp' => true,
                                    ]) ?>
                                    <?= $this->element('User/Menu/reservations_history', [
                                        'sp' => true,
                                    ]) ?>
                                    <?= $this->element('User/Menu/waiting_cancellation', [
                                        'sp' => true,
                                    ]) ?>
                                    <?= $this->element('User/Menu/inquiry', [
                                        'sp' => true,
                                    ]) ?>
                                    <?= $this->element('User/Menu/term', [
                                        'sp' => true,
                                    ]) ?>
                                    <?= $this->element('User/Menu/sctl', [
                                        'sp' => true,
                                    ]) ?>
                                    <?= $this->element('User/Menu/logout', [
                                        'sp' => true,
                                    ]) ?>
                                <?php endif; ?>
                            <?php $this->end('spMenuNavi'); ?>
                            <?php $hasSpMenuLoginBox = false; ?>
                            <?php if (trim($this->fetch('spMenuLoginBox')) != '') : ?>
                                <?php $hasSpMenuLoginBox = true; ?>
                            <?php endif; ?>
                            <?php $hasSpMenuUserName = false; ?>
                            <?php if (trim($this->fetch('spMenuUserName')) != '') : ?>
                                <?php $hasSpMenuUserName = true; ?>
                            <?php endif; ?>
                            <?php $hasSpMenuNavi = false; ?>
                            <?php if (trim($this->fetch('spMenuNavi')) != '') : ?>
                                <?php $hasSpMenuNavi = true; ?>
                            <?php endif; ?>
                            <?php if ($hasSpMenuLoginBox || $hasSpMenuUserName || $hasSpMenuNavi): ?>
                                <div class="btn-menu">
                                    <button class="icon btn-sp-menu">
                                        <svg class="icon-hNav">
                                            <use xlink:href="#icon_header_menu_sp"/>
                                        </svg>
                                    </button>
                                </div>
                                <div id="sp-menu" class="cmn-sp-menu">
                                    <div class="sp-menu-header">
                                        <div class="<?php if (!$this->CommonData->existsUserLoginData()): ?>before-login<?php else: ?>after-login<?php endif; ?>">
                                            <div class="close-box">
                                                <button class="icon btn-close btn-sp-menu">
                                                    <svg class="icon-spNav">
                                                        <use xlink:href="#icon_arrow_left"/>
                                                    </svg>
                                                </button>
                                            </div>
                                            <?php if (!$this->CommonData->existsUserLoginData()): ?>
                                                <div class="login-box">
                                                    <ul>
                                                        <?= $this->fetch('spMenuLoginBox') ?>
                                                    </ul>
                                                </div>
                                            <?php endif; ?>
                                            <div class="sp-midle-header<?php if ($this->CommonData->existsUserLoginData()): ?> clearfix<?php endif; ?>">
                                                <?php if ($this->CommonData->existsUserLoginData()): ?>
                                                    <p class="txt-user-name f-l">
                                                        <?= $this->fetch('spMenuUserName') ?>
                                                    </p>
                                                <?php endif; ?>
                                                <?php if ($hasHelpMenu) : ?>
                                                    <button class="help-btn icon helpBtnSP<?php if ($this->CommonData->existsUserLoginData()): ?> f-r<?php endif; ?>">
                                                        <svg class="icon-help">
                                                            <use xlink:href="#icon_header_help"/>
                                                        </svg>
                                                        <span><?= $this->Tr->h('common/help') ?></span>
                                                    </button>
                                                <?php endif; ?>
                                            </div>
                                            <?php if ($hasHelpMenu) : ?>
                                                <aside class="help-area is-sp">
                                                    <div class="help-area -header clearfix">
                                                        <p class="f-l"><?= $this->Tr->h('common/help') ?></p>
                                                        <button type="button" class="f-r helpBtnSP"></button>
                                                    </div>
                                                    <div class="help-area -body">
                                                        <ul class="help-area -list">
                                                            <?= $this->fetch('helpMenu'); ?>
                                                        </ul>
                                                    </div>
                                                </aside>
                                            <?php endif; ?>
                                            <div class="nav-box">
                                                <ul class="nav-icon">
                                                    <?= $this->fetch('spMenuNavi') ?>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </nav>
                <?php endif; ?>
            </div>
        </header>
    <?php else: ?>
        <div class="mgt-20"></div>
    <?php endif; ?>
    <?php if ($hasHelpMenu) : ?>
        <aside class="help-area is-pc">
            <div class="help-area -header clearfix">
                <div class="l-main">
                    <p class="f-l"><?= $this->Tr->h('common/help') ?></p>
                    <button type="button" class="f-r helpBtnPC"></button>
                </div>
            </div>
            <div class="help-area -body">
                <div class="l-main">
                    <ul class="help-area -list d-flex">
                        <?= $this->fetch('helpMenu'); ?>
                    </ul>
                </div>
            </div>
        </aside>
    <?php endif; ?>
    <main class="cmn-main-area">
        <?php if (!$this->fetch('noNavi')): ?>
            <section class="lead-area l-main">
                <div class="box-breadC clearfix">
                    <?php $this->Template->setUserBreadcrumbs(); ?>
                    <?= $this->Breadcrumbs->render(
                        ['class' => ['list-breadC', 'f-l']],
                        ['separator' => '>']) ?>
                    <?php if ($this->CommonData->duringContinueReservation()) : ?>
                        <p class="unReserve f-r">
                            <a href="<?= $this->Url->build([
                                'prefix' => 'User',
                                'controller' => 'Reservations',
                                'action' => 'addConf',
                            ]) ?>">
                                    <span class="icon">
                                        <svg class="icon-home">
                                            <use xlink:href="#icon_info"/>
                                        </svg>
                                    </span>
                                <?= $this->Tr->h('common/reserveContinue') ?>
                                <span class="icon">
                                        <svg class="icon-home">
                                            <use xlink:href="#icon_arrow_right"/>
                                        </svg>
                                    </span>
                            </a>
                        </p>
                    <?php endif; ?>
                </div>
            </section>
        <?php endif; ?>
        <?= $this->fetch('content') ?>
        <aside class="totopBtn icon">
            <a href="#">
                <svg class="icon-btn">
                    <use xlink:href="#icon_arrow_up"/>
                </svg>
            </a>
        </aside>
    </main>
    <?php if (!$this->fetch('noNavi')) : ?>
        <footer class="cmn-footer js_footer">
            <div class="cmn-footer-area l-main">
                <?php if ($this->Setting->getSystemSetting()->footer_logo_display_flg !== \App\Model\Entity\SystemSetting::FOOTER_LOGO_DISPLAY_FLG_OFF) : ?>
                    <p class="box-logo">
                        Powered by
                        <?= $this->Html->image('user/logo_re5.png') ?>
                    </p>
                <?php endif; ?>
                <?php if ($this->Recaptcha->isUseFlgOn()): ?>
                    <br />This site is protected by reCAPTCHA and the Google <a href="https://policies.google.com/privacy">Privacy Policy</a> and <a href="https://policies.google.com/terms">Terms of Service</a> apply.
                <?php endif; ?>
            </div>
        </footer>
    <?php endif; ?>
    <div class="hidden">
        <?= $this->element('User/Common/form/post_link') ?>
        <?= $this->element('User/Common/dialog/confirm') ?>
        <?= $this->element('User/Common/dialog/error') ?>
        <?= $this->element('User/Common/layout/loading') ?>
    </div>
</div>
<?= $this->Setting->getAnalysisTagSetting(AnalysisTag::TYPE_BEFORE_BODY_CLOSE); ?>
</body>
</html>
