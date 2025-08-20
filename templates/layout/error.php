<!DOCTYPE html>
<html lang="ja">
<head>
    <?= $this->Html->charset() ?>
    <meta http-equiv="content-language" content="ja"/>
    <meta http-equiv="X-UA-Compatible" content="IE=11; IE=Edge"/>
    <?= $this->Html->meta('robots', 'noindex,nofollow,noarchive') ?>
    <?= $this->Html->meta('viewport', 'width=device-width, minimum-scale=1, maximum-scale=10,initial-scale=1.0') ?>
    <?= $this->Html->meta('format-detection', 'address=no,email=no,telephone=no') ?>
    <?= $this->Html->meta('icon'); ?>
    <?= $this->fetch('meta') ?>
    <title>エラー</title>
    <?= $this->Html->css('vendor/jquery-ui/jquery-ui.min') ?>
    <?= $this->Html->css('vendor/jquery-datetimepicker/jquery.datetimepicker.min') ?>
    <?= $this->Html->css('vendor/bx-slider/jquery.bxslider.min') ?>
    <?= $this->Html->css('user/base') ?>
    <?= $this->Html->css('common/common') ?>
    <?= $this->Html->css('user/parts_design') ?>
    <?= $this->fetch('css') ?>
    <?= $this->Html->script('vendor/jquery/jquery.min') ?>
    <?= $this->Html->script('vendor/jquery-ui/jquery-ui.min') ?>
    <?= $this->Html->script('vendor/jquery-datetimepicker/jquery.datetimepicker.full.min') ?>
    <?= $this->Html->script('vendor/jquery-ui-touch-punch/jquery.ui.touch-punch.min') ?>
    <?= $this->Html->script('vendor/axia/axia') ?>
    <?= $this->Html->script('vendor/stickyfill/stickyfill.min') ?>
    <?= $this->Html->script('vendor/bx-slider/jquery.bxslider.min') ?>
    <?= $this->Html->script('common/common') ?>
    <?= $this->Html->script('user/common') ?>
    <?= $this->fetch('script') ?>
</head>
<body>
    <div class="js_container">
        <?= $this->element('User/Common/layout/svg') ?>
        <header class="cmn-header">
            <div class="cmn-header-area l-main clearfix">
                <div class="box-logo f-l">
                    <h1 class="pc-only">
                    </h1>
                    <h1 class="sp-only">
                    </h1>
                </div>
            </div>
        </header>
        <div class="mgt-20"></div>
        <main class="cmn-main-area">
            <section class="contents-area l-main">
                <aside class="cmn-msg is-err">
                    <p>
                        <svg class="icon is-msg">
                            <use xlink:href="#icon_clear"></use>
                        </svg>
                        <?= $this->fetch('content') ?>
                    </p>
                </aside>
                <p class="cmn-txt mgt-20"></p>
                <p class="cmn-txt mgt-40 tac mgb-20">
                    <?= $this->Html->link(
                        $this->Tr->t('common/backBtn'),
                        '#',
                        ['class' => ['js_history_back','cmn-btn', 'is-gray']]
                    ) ?>
                </p>
            </section>
            <aside class="totopBtn icon">
                <a href="#">
                    <svg class="icon-btn">
                        <use xlink:href="#icon_arrow_up"/>
                    </svg>
                </a>
            </aside>
        </main>
    </div>
</body>
</html>
