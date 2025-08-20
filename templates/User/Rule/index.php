<?php

use App\Model\Entity\Term;

$this->assign('title', $this->Tr->t('pageTitle/rule'));
$this->Breadcrumbs->add(
    $this->Tr->t('pageTitle/rule')
);
?>
<section class="contents-area l-main">
    <h3 class="ttl-sec"><?= $this->Tr->h('pageTitle/rule') ?></h3>
    <div class="edit-area wysiwyg-area">
        <?= $terms[Term::TYPE_TERMS_TOP] ?>
    </div>
    <p class="cmn-txt mgt-40 tac mgb-20 btn-wrap clearfix">
        <?= $this->Template->userTopBtn() ?>
    </p>
</section>
