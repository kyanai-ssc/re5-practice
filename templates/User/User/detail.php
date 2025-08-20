<?php
$this->assign('title', $this->Tr->t('pageTitle/userDetail'));

$this->Breadcrumbs->add(
    $this->Tr->t('pageTitle/userDetail')
);
?>

<?= $this->Flash->render('userFinish') ?>
<?= $this->Flash->render('usersError') ?>
<section class="contents-area l-main">
    <?= $this->element('User/User/detail', [
        'userForm' => $userForm,
        'valueOptions' => $valueOptions,
        'mode' => 'detail',
    ]) ?>
    <?php if ($userForm->getUserEntity()->canEdit()
        && $this->Authority->isAuthority(true, 'User', 'edit')): ?>
        <p class="cmn-txt mgt-40 tac mgb-20 btn-wrap clearfix">
            <?= $this->Form->button($this->Tr->t('user/detail/editBtn'), [
                'class' => ['cmn-btn', 'is-blue', 'js_change_url'],
                'type' => 'button',
                'data-url' => $this->Url->build([
                    'prefix' => 'User',
                    'controller' => 'User',
                    'action' => 'edit',
                    'id' => $userForm->getUserEntity()->get('id')
                ], ['escape' => false]),
            ]) ?>
        </p>
    <?php endif; ?>
    <?php if ($userForm->getUserEntity()->canWithdraw()
        && $this->Authority->isAuthority(true, 'User', 'withdraw')): ?>
        <div class="link-box">
            <p class="mgt-10">
                <?= $this->Html->link($this->Tr->t('user/detail/withdrawBtn'), [
                    'prefix' => 'User',
                    'controller' => 'User',
                    'action' => 'withdraw',
                    'id' => $userForm->getUserEntity()->get('id')
                ], [
                    'class' => ['link-txt'],
                ]) ?>
            </p>
        </div>
    <?php endif; ?>
</section>
