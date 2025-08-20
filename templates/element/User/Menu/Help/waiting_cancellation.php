<?php if ($this->Authority->isAuthority(true, 'WaitingCancellation', 'token')
    && $this->Authority->isMenuDisplayLogin()
): ?>
    <li class="d-flex icon">
        <svg class="icon-hNav">
            <use xlink:href="#icon_header_cancelWait"></use>
        </svg>
        <dl>
            <dt><?= $this->Tr->h('common/cancelWait') ?></dt>
            <dd><?= $this->Tr->h('help/cancelWait') ?></dd>
        </dl>
    </li>
<?php endif; ?>
