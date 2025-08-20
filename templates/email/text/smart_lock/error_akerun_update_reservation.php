<?php
/** @var array $info */
/** @var \Cake\I18n\FrozenTime $now */
/** @var \App\Model\Entity\SmartLock $smartLock */
$smartLock = $info['smartLock'];
/** @var \App\Model\Entity\Reservation $reservation 変更前予約情報 */
$reservation = $info['reservation'];
/** @var \App\Model\Entity\Event $event */
$event = $reservation->get('event');
/** @var \App\Model\Entity\Reservation $anotherReservation 変更後予約情報 */
$anotherReservation = $info['anotherReservation'];
/** @var \App\Model\Entity\Event $anotherEvent */
$anotherEvent = $anotherReservation->get('event');
?>
正常に予約が行えませんでした。

Akerun側の合鍵連携は成功しているため、
Akerun側の合鍵削除をお願いいたします。

【基本情報】
発生日時：<?= $now ?><?= PHP_EOL ?>

【予約情報】
予約枠ID: <?= $reservation->event_id ?><?= PHP_EOL ?>
予約枠名: <?= $event->name ?><?= PHP_EOL ?>
予約開始日: <?= $info['dateTimeFrom'] ?><?= PHP_EOL ?>
予約終了日: <?= $info['dateTimeTo'] ?><?= PHP_EOL ?>

【Akerun側情報】
事務所ID: <?= $smartLock->organizations_id ?><?= PHP_EOL ?>
デバイスキー: <?= $anotherEvent->event_smart_lock->smart_lock_device_key ?><?= PHP_EOL ?>
合鍵ID: <?= $anotherReservation->reservation_smart_lock->smart_lock_key_id ?><?= PHP_EOL ?>
