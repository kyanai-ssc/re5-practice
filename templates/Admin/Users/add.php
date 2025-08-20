<?php
$this->assign('title', '会員 登録');
$this->assign('headerType', 'data');
$this->Html->script('admin/users/fieldset', [
    'block' => true,
]);
$this->Breadcrumbs->add(
    '顧客一覧',
    ['prefix' => 'Admin', 'controller' => 'Users', 'action' => 'list']
);
$this->Breadcrumbs->add(
    '会員登録'
);

?>
<section class="form-input">
    <aside class="cmn-msg is-caution">
        <p>
            <svg class="icon is-msg">
                <use xlink:href="#icon_info"></use>
            </svg>
            ブラウザのオートコンプリート機能を利用している場合、パスワードおよび1つの上の項目へ意図しない値が入っている可能性がありますのでご注意ください
        </p>
    </aside>
    <?= $this->Flash->render('usersError') ?>

    <?= $this->element('Admin/Users/form', [
        'userForm' => $userForm,
        'valueOptions' => $valueOptions,
        'mode' => 'add',
    ]) ?>
</section>
