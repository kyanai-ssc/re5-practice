<?php
use App\Model\Entity\AdminPassResetToken;
?>
管理者パスワードのリセット処理を受け付けました。
下記、URLへ遷移してパスワードリセット処理を承認してください。

<?= $this->Url->build(
    [
        'controller' => 'PassReset',
        'action' => 'token',
        '?' => [
            'token' => $token,
        ],
    ],
    [
        'escape' => true,
        'fullBase' => true,
    ]
) ?>

※パスワードの有効期限は<?= h(AdminPassResetToken::EXPIRATION_ADD_HOUR)?>時間となります。
