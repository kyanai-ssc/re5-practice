<?php
/** @var array $info */
/** @var \Cake\I18n\FrozenTime $now */
/** @var \App\Model\Entity\SmartLock $smartLock */
$smartLock = $info['smartLock'];
/** @var \App\Model\Entity\Reservation $reservation */
$reservation = $info['reservation'];
/** @var \App\Model\Entity\Event $event */
$event = $reservation->get('event');
?>
Akerun連携で下記エラーになり、予約削除が行えませんでした。
Akerun側のエラー確認をお願いいたします。

不明点がありましたら、契約元に下記情報とあわせてお問い合わせください。

【基本情報】
発生日時：<?= $now ?><?= PHP_EOL ?>

【予約情報】
予約枠ID: <?= $reservation->event_id ?><?= PHP_EOL ?>
予約枠名: <?= $event->name ?><?= PHP_EOL ?>
予約開始日: <?= $info['dateTimeFrom'] ?><?= PHP_EOL ?>
予約終了日: <?= $info['dateTimeTo'] ?><?= PHP_EOL ?>

【Akerun側情報】
事務所ID: <?= $smartLock->organizations_id ?><?= PHP_EOL ?>
デバイスキー: <?= $event->event_smart_lock->smart_lock_device_key ?><?= PHP_EOL ?>
合鍵ID: <?= $reservation->reservation_smart_lock->smart_lock_key_id ?><?= PHP_EOL ?>
