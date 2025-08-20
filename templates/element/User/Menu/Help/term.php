<?php use App\Model\Entity\SiteSetting; ?>
<?php if ($this->Setting->getSiteSetting()->terms_flg === SiteSetting::COMMON_USE_FLG_ON
    && $this->Authority->isAuthority(true, 'Rule', 'index')): ?>
    <li class="d-flex icon">
        <svg class="icon-hNav">
            <use xlink:href="#icon_header_policy"/>
        </svg>
        <dl>
            <dt><?= $this->Tr->h('common/term') ?></dt>
            <dd><?= $this->Tr->h('help/term') ?></dd>
        </dl>
    </li>
<?php endif; ?>
