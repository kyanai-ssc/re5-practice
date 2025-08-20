<?php use App\Model\Entity\SiteSetting; ?>
<?php if ($this->Setting->getSiteSetting()->sctl_flg === SiteSetting::COMMON_USE_FLG_ON): ?>
    <li class="d-flex icon">
        <svg class="icon-hNav">
            <use xlink:href="#icon_header_policy"/>
        </svg>
        <dl>
            <dt><?= $this->Tr->h('common/sctl') ?></dt>
            <dd><?= $this->Tr->h('help/sctl') ?></dd>
        </dl>
    </li>
<?php endif; ?>
