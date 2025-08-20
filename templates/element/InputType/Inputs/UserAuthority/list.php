<?php if (isset($listValue['user'])): ?>
    <?php if ($listValue['updateAuthority'] && !$listValue['user']->isGuest()): ?>
        <a href="#" class="js_update_authority" data-user-id="<?= h($listValue['user']->get('id')) ?>">
            <?= h($listValue['user']->get('user_authority')->get('name')) ?>
        </a>
    <?php else: ?>
        <?= h($listValue['user']->get('user_authority')->get('name')) ?>
    <?php endif; ?>
<?php endif; ?>
