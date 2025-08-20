<?php use App\Model\Entity\SiteSetting; ?>
<?php if ($this->Setting->getSiteSetting()->sctl_flg === SiteSetting::COMMON_USE_FLG_ON) : ?>
    <?php if (!isset($sp) || !$sp) : ?>
        <li>
            <?= $this->Html->link($this->Tr->t('common/sctl'), [
                'prefix' => 'User',
                'controller' => 'Sctl',
                'action' => 'index',
            ], ['class' => ['link-txt']]) ?>
        </li>
    <?php else: ?>
        <li>
            <a href="<?= $this->Url->build([
                'prefix' => 'User',
                'controller' => 'Sctl',
            ]) ?>" class="icon d-flex">
                <svg class="icon-hNav">
                    <use xlink:href="#icon_header_policy"/>
                </svg>
                <span><?= $this->Tr->h('common/sctl') ?></span>
            </a>
        </li>
    <?php endif; ?>
<?php endif; ?>
