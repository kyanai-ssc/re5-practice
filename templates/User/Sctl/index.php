<?php
$this->assign('title', $this->Tr->t('common/sctl'));
$this->Breadcrumbs->add(
    $this->Tr->t('common/sctl')
);
?>
<section class="contents-area l-main">
    <h3 class="ttl-sec"><?= $this->Tr->h('common/sctl') ?></h3>
    <div class="edit-area wysiwyg-area">
        <?= $sctl ?>
    </div>
    <p class="cmn-txt mgt-40 tac mgb-20 btn-wrap clearfix">
        <?= $this->Template->userTopBtn() ?>
    </p>
</section>
