<?php if ($this->Authority->isAuthority(true, 'User', 'detail')): ?>
    <?php if (!isset($sp) || !$sp) : ?>
        <li title="<?= $this->Tr->h('common/mypage') ?>">
            <a href="<?= $this->Url->build([
                'prefix' => 'User',
                'controller' => 'User',
                'action' => 'detail',
                'id' => $this->CommonData->getUserLoginData()->get('id')
            ]) ?>" class="icon">
                <svg class="icon-hNav">
                    <use xlink:href="#icon_header_mypage"/>
                </svg>
            </a>
        </li>
    <?php else: ?>
        <li>
            <a href="<?= $this->Url->build([
                'prefix' => 'User',
                'controller' => 'User',
                'action' => 'detail',
                'id' => $this->CommonData->getUserLoginData()->get('id')
            ]) ?>" class="icon d-flex">
                <svg class="icon-hNav">
                    <use xlink:href="#icon_header_mypage"/>
                </svg>
                <span><?= $this->Tr->h('common/mypage') ?></span>
            </a>
        </li>
    <?php endif; ?>
<?php endif; ?>
