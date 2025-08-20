<?php if ($this->Authority->isAuthority(true, 'User', 'detail')): ?>
    <li class="d-flex icon">
        <svg class="icon-hNav">
            <use xlink:href="#icon_header_mypage"/>
        </svg>
        <dl>
            <dt><?= $this->Tr->h('common/mypage') ?></dt>
            <dd><?= $this->Tr->h('help/mypage') ?></dd>
        </dl>
    </li>
<?php endif; ?>
