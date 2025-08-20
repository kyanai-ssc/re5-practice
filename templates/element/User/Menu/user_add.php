<?php use App\Model\Entity\SiteSetting; ?>
<?php if ($this->Setting->getSiteSetting()->user_add_flg === SiteSetting::COMMON_USE_FLG_ON
    && $this->Authority->isAuthority(true, 'User', 'add')): ?>
    <?php if (!isset($sp) || !$sp) : ?>
        <?php if ($this->Setting->getSiteSetting()->login_flg === SiteSetting::LOGIN_USE_FLG_ON): ?>
            <li class="login-txt">
                <?= $this->Tr->h('common/or') ?>
            </li>
        <?php endif; ?>
        <li class="login-btn">
            <?= $this->Html->link($this->Tr->t('common/userAdd'), [
                'prefix' => 'User',
                'controller' => 'User',
                'action' => 'add',
            ], ['class' => ['cmn-btn', 'btn', 'is-blue']]) ?>
        </li>
    <?php else: ?>
        <li class="login-btn">
            <?= $this->Html->link($this->Tr->t('common/userAdd'), [
                'prefix' => 'User',
                'controller' => 'User',
                'action' => 'add',
            ], ['class' => ['cmn-btn', 'btn', 'is-blue']]) ?>
        </li>
    <?php endif; ?>
<?php endif; ?>
