<?php use App\Model\Entity\SiteSetting; ?>
<?php if ($this->Setting->getSiteSetting()->login_flg === SiteSetting::LOGIN_USE_FLG_ON
    && $this->Authority->isAuthority(true, 'User', 'add')): ?>
    <li class="d-flex icon">
        <svg class="icon-hNav">
            <use xlink:href="#icon_header_logout"/>
        </svg>
        <dl>
            <dt><?= $this->Tr->h('common/logout') ?></dt>
            <dd><?= $this->Tr->h('help/logout') ?></dd>
        </dl>
    </li>
<?php endif; ?>
