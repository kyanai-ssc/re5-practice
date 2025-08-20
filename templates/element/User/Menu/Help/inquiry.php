<?php use App\Model\Entity\SiteSetting; ?>
<?php if ($this->Setting->getSiteSetting()->inquiry_flg === SiteSetting::COMMON_USE_FLG_ON
    && $this->Authority->isAuthority(true, 'Inquiry', 'index')
    && $this->Authority->isMenuDisplayLogin()
): ?>

    <li class="d-flex icon">
        <svg class="icon-hNav">
            <use xlink:href="#icon_header_mail"/>
        </svg>
        <dl>
            <dt><?= $this->Tr->h('common/inquiry') ?></dt>
            <dd><?= $this->Tr->h('help/inquiry') ?></dd>
        </dl>
    </li>
<?php endif; ?>
