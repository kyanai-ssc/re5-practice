<?php
/** @var array $info */
/** @var \Cake\I18n\FrozenTime $now */
/** @var \App\Model\Entity\Reservation $reservation */
$reservation = $info['reservation'];
/** @var \App\Model\Entity\Event $event */
$event = $reservation->get('event');
/** @var \App\Model\Entity\Reservation $anotherReservation */
$anotherReservation = $info['anotherReservation'];
/** @var \App\Model\Entity\Event $oldEvent */
$oldEvent = $anotherReservation->get('event');
?>
正常に予約が行えませんでした。

予約データの登録や更新は行われておりませんが、RemoteLOCK側の連携は成功しているため、
RemoteLOCK側のスケジュールの調整をお願いいたします。

不明点がありましたら、契約元に下記情報とあわせてお問い合わせください。

【基本情報】
発生日時：<?= $now ?><?= PHP_EOL ?>

【予約情報】
<?php if(!empty($reservation->id) && $anotherReservation !== $reservation) :?>
予約ID: <?= $reservation->id ?><?= PHP_EOL ?>
<?php endif; ?>
予約枠ID: <?= $reservation->event_id ?><?= PHP_EOL ?>
予約枠名: <?= $event->name ?><?= PHP_EOL ?>
予約開始日: <?= $info['dateTimeFrom'] ?><?= PHP_EOL ?>
予約終了日: <?= $info['dateTimeTo'] ?><?= PHP_EOL ?>

【RemoteLOCK側情報】
デバイスキー: <?= $oldEvent->event_smart_lock->smart_lock_device_key ?><?= PHP_EOL ?>
名前: <?= $oldEvent->name ?>_<?= $reservation->id ?><?= PHP_EOL ?>
<?php if(!empty($reservation->reservation_smart_lock->smart_lock_key_id)) :?>
ブッキングID: <?= $reservation->reservation_smart_lock->smart_lock_key_id ?><?= PHP_EOL ?>
<?php endif; ?>
