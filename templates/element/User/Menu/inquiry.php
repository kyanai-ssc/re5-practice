<?php use App\Model\Entity\SiteSetting; ?>
<?php if ($this->Setting->getSiteSetting()->inquiry_flg === SiteSetting::COMMON_USE_FLG_ON
    && $this->Authority->isAuthority(true, 'Inquiry', 'index')
    && $this->Authority->isMenuDisplayLogin()
) : ?>

    <?php if (!isset($sp) || !$sp) : ?>
        <li title="<?= $this->Tr->h('common/inquiry') ?>">
            <a href="<?= $this->Url->build([
                'prefix' => 'User',
                'controller' => 'Inquiry',
                'action' => 'index',
            ]) ?>" class="icon">
                <svg class="icon-hNav">
                    <use xlink:href="#icon_header_mail"/>
                </svg>
            </a>
        </li>
    <?php else: ?>
        <li>
            <a href="<?= $this->Url->build([
                'prefix' => 'User',
                'controller' => 'Inquiry',
                'action' => 'index',
            ]) ?>" class="icon d-flex">
                <svg class="icon-hNav">
                    <use xlink:href="#icon_header_mail"/>
                </svg>
                <span><?= $this->Tr->h('common/inquiry') ?></span>
            </a>
        </li>
    <?php endif; ?>
<?php endif; ?>
