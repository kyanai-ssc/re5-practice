<?php
/** @var array $info */
/** @var \Cake\I18n\FrozenTime $now */
/** @var \App\Model\Entity\SmartLock $smartLock */
$smartLock = $info['smartLock'];
/** @var \App\Model\Entity\User $user */
$user = $info['user'];
?>
正常に会員情報の更新が行えませんでした。

※Akerun側のユーザー連携は成功済みで、
　メールアドレスが登録されている場合は、Akerun側から招待メールがユーザーへ送信済みです。

【基本情報】
発生日時：<?= $now ?><?= PHP_EOL ?>

【会員情報】
会員ID: <?= $user->id ?><?= PHP_EOL ?>

【Akerun側情報】
メールアドレス: <?= $info['user_mail'] ?><?= PHP_EOL ?>
ユーザー名：<?= $info['user_name'] ?><?= PHP_EOL ?>
