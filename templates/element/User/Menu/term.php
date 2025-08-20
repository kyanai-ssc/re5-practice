<?php use App\Model\Entity\SiteSetting; ?>
<?php if ($this->Setting->getSiteSetting()->terms_flg === SiteSetting::COMMON_USE_FLG_ON
    && $this->Authority->isAuthority(true, 'Rule', 'index')) : ?>
    <?php if (!isset($sp) || !$sp) : ?>
        <li title="<?= $this->Tr->h('common/term') ?>">
            <a href="<?= $this->Url->build([
                'prefix' => 'User',
                'controller' => 'Rule',
            ]) ?>" class="icon">
                <svg class="icon-hNav">
                    <use xlink:href="#icon_header_policy"/>
                </svg>
            </a>
        </li>
    <?php else: ?>
        <li>
            <a href="<?= $this->Url->build([
                'prefix' => 'User',
                'controller' => 'Rule',
            ]) ?>" class="icon d-flex">
                <svg class="icon-hNav">
                    <use xlink:href="#icon_header_policy"/>
                </svg>
                <span><?= $this->Tr->h('common/term') ?></span>
            </a>
        </li>
    <?php endif; ?>
<?php endif; ?>
