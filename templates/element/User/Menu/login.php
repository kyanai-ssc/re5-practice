<?php use App\Model\Entity\SiteSetting; ?>
<?php if ($this->Setting->getSiteSetting()->login_flg === SiteSetting::LOGIN_USE_FLG_ON): ?>
    <li class="login-link">
        <?= $this->Html->link($this->Tr->t('common/login'), [
            'prefix' => 'User',
            'controller' => 'Auth',
            'action' => 'login',
            '?' => [
                'redirect' => $this->Login->getLoginRedirectBack(),
            ],
        ], ['class' => ['link-txt']]) ?>
    </li>
<?php endif; ?>
