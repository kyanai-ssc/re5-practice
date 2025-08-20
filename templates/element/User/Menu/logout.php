<?php if (!isset($sp) || !$sp) : ?>
    <li title="<?= $this->Tr->h('common/logout') ?>">
        <a href="<?= $this->Url->build([
            'prefix' => 'User',
            'controller' => 'Auth',
            'action' => 'logout',
        ]) ?>" class="icon">
            <svg class="icon-hNav">
                <use xlink:href="#icon_header_logout"/>
            </svg>
        </a>
    </li>
<?php else: ?>
    <li>
        <a href="<?= $this->Url->build([
            'prefix' => 'User',
            'controller' => 'Auth',
            'action' => 'logout',
        ]) ?>" class="icon d-flex">
            <svg class="icon-hNav">
                <use xlink:href="#icon_header_logout"/>
            </svg>
            <span><?= $this->Tr->h('common/logout') ?></span>
        </a>
    </li>
<?php endif; ?>
