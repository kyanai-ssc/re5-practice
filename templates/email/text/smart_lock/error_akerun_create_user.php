<?php
/** @var array $info */
/** @var \Cake\I18n\FrozenTime $now */
/** @var \App\Model\Entity\SmartLock $smartLock */
$smartLock = $info['smartLock'];
?>
Akerun連携でエラーになり、会員登録が行えませんでした。
下記の原因が考えられます。
・APIの利用制限に引っかかっている
不明点がありましたら、契約元に下記情報とあわせてお問い合わせください。

【基本情報】
発生日時：<?= $now ?><?= PHP_EOL ?>

【登録しようとした情報】
メールアドレス: <?= $info['user_mail'] ?><?= PHP_EOL ?>
ユーザー名：<?= $info['user_name'] ?><?= PHP_EOL ?>

【Akerun側情報】
事務所ID: <?= $smartLock->organizations_id ?><?= PHP_EOL ?>
