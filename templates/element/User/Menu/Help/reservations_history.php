<?php if ($this->Authority->isAuthority(true, 'Reservations', 'history')): ?>
    <li class="d-flex icon">
        <svg class="icon-hNav">
            <use xlink:href="#icon_header_history"/>
        </svg>
        <dl>
            <dt><?= $this->Tr->h('common/history') ?></dt>
            <dd><?= $this->Tr->h('help/history') ?></dd>
        </dl>
    </li>
<?php endif; ?>
