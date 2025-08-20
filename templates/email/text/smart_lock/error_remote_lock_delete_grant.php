<?php
/** @var array $info */
/** @var \Cake\I18n\FrozenTime $now */
/** @var \App\Model\Entity\Reservation $reservation */
$reservation = $info['reservation'];
/** @var \App\Model\Entity\Event $event */
$event = $reservation->get('event');
?>
RemoteLOCK連携で下記エラーになり、予約変更が行えませんでした。
下記の原因が考えられます。
・APIの利用制限に引っかかっている
・登録制限に引っかかっている

不明点がありましたら、契約元に下記情報とあわせてお問い合わせください。

【基本情報】
発生日時：<?= $now ?><?= PHP_EOL ?>

【予約情報】
予約枠ID: <?= $reservation->event_id ?><?= PHP_EOL ?>
予約枠名: <?= $event->name ?><?= PHP_EOL ?>
予約開始日: <?= $info['dateTimeFrom'] ?><?= PHP_EOL ?>
予約終了日: <?= $info['dateTimeTo'] ?><?= PHP_EOL ?>

【RemoteLOCK側情報】
デバイスキー: <?= $event->event_smart_lock->smart_lock_device_key ?><?= PHP_EOL ?>


