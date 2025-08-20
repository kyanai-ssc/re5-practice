<?php

use App\Locale\Message;

$this->extend('/layout/user/default');
$this->assign('robots', 'noindex,nofollow,noarchive')
?>
<section class="contents-area l-main">
    <aside class="cmn-msg is-err">
      <p><svg class="icon is-msg">
          <use xlink:href="#icon_clear"></use>
        </svg><?= $this->fetch('content') ?></p>
    </aside>
    <p class="cmn-txt mgt-20"></p>
    <p class="cmn-txt mgt-40 tac mgb-20">
          <?php if (
            $this->getRequest()->getParam('controller') === 'Reservations'
            && $error->getMessage() === Message::ERROR_PAYMENT
          ): ?>
            <?= $this->Html->link($this->Tr->t('common/backBtn'), [
              'prefix' => 'User',
              'controller' => 'Reservations',
              'action' => 'calendar',
            ], [
              'class' => ['cmn-btn', 'is-gray'],
            ]) ?>
          <?php else: ?>
            <?= $this->Html->link(
                $this->Tr->t('common/backBtn'),
                '#',
                ['class' => ['js_history_back','cmn-btn', 'is-gray']]
            ); ?>
          <?php endif; ?>
    </p>
</section>
