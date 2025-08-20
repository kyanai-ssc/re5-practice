<?php if ($this->Authority->isAuthority(true, 'WaitingCancellation', 'token')
    && $this->Authority->isMenuDisplayLogin()) : ?>
    <?php if (!isset($sp) || !$sp) : ?>
        <li title="<?= $this->Tr->h('common/cancelWait') ?>">
            <?php if ($this->CommonData->existsUserLoginData()) : ?>
            <a href="<?= $this->Url->build([
                'prefix' => 'User',
                'controller' => 'WaitingCancellation',
                'action' => 'list',
            ]) ?>" class="icon">
                <?php else : ?>
                <a href="<?= $this->Url->build([
                    'prefix' => 'User',
                    'controller' => 'WaitingCancellation',
                    'action' => 'token',
                ]) ?>" class="icon">
                    <?php endif; ?>
                    <svg class="icon-hNav">
                        <use xlink:href="#icon_header_cancelWait"></use>
                    </svg>
                </a>
        </li>
    <?php else: ?>
        <li>
            <?php if ($this->CommonData->existsUserLoginData()) : ?>
                <a href="<?= $this->Url->build(['prefix' => 'User',
                    'controller' => 'WaitingCancellation',
                    'action' => 'list',]) ?>" class="icon">
                    <svg class="icon-hNav">
                        <use xlink:href="#icon_header_cancelWait"></use>
                    </svg>
                    <span><?= $this->Tr->h('common/cancelWait') ?></span>
                </a>
            <?php else : ?>
                <a href="<?= $this->Url->build(['prefix' => 'User',
                    'controller' => 'WaitingCancellation',
                    'action' => 'token',]) ?>" class="icon">
                    <svg class="icon-hNav">
                        <use xlink:href="#icon_header_cancelWait"></use>
                    </svg>
                    <span><?= $this->Tr->h('common/cancelWait') ?></span>
                </a>
            <?php endif; ?>

        </li>
    <?php endif; ?>
<?php endif; ?>
