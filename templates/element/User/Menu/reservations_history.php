<?php if ($this->Authority->isAuthority(true, 'Reservations', 'history')): ?>
    <?php if (!isset($sp) || !$sp) : ?>
        <li title="<?= $this->Tr->h('common/history') ?>">
            <a href="<?= $this->Url->build([
                'prefix' => 'User',
                'controller' => 'Reservations',
                'action' => 'history',
            ]) ?>" class="icon">
                <svg class="icon-hNav">
                    <use xlink:href="#icon_header_history"/>
                </svg>
            </a>
        </li>
    <?php else: ?>
        <li>
            <a href="<?= $this->Url->build([
                'prefix' => 'User',
                'controller' => 'Reservations',
                'action' => 'history',
            ]) ?>" class="icon d-flex">
                <svg class="icon-hNav">
                    <use xlink:href="#icon_header_history"/>
                </svg>
                <span><?= $this->Tr->h('common/history') ?></span>
            </a>
        </li>
    <?php endif; ?>
<?php endif; ?>
