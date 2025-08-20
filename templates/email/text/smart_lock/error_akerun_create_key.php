<?php
/** @var array $info */
/** @var \Cake\I18n\FrozenTime $now */
/** @var \App\Model\Entity\SmartLock $smartLock */
$smartLock = $info['smartLock'];
/** @var \App\Model\Entity\Reservation $reservation */
$reservation = $info['reservation'];
/** @var \App\Model\Entity\Event $event */
$event = $reservation->get('event');
/** @var \App\Model\Entity\Reservation $anotherReservation 変更前予約 (新規登録の場合は $reservation と同じ) */
$anotherReservation = $info['anotherReservation'];
/** @var \App\Model\Entity\Event $anotherEvent */
$anotherEvent = $anotherReservation->get('event');
?>
<?php if(isset($info['afterRedirectPaymentFlg']) && $info['afterRedirectPaymentFlg']): ?>
Akerun連携で下記エラーになりました。
<?php else: ?>
Akerun連携で下記エラーになり、予約が行えませんでした。
<?php endif; ?>
下記の原因が考えられます。
・APIの利用制限に引っかかっている

<?php if(isset($info['afterRedirectPaymentFlg']) && $info['afterRedirectPaymentFlg']): ?>
管理画面の予約詳細画面から再連携をすることができます。
<?= $this->Url->build(
    [
        'prefix' => 'Admin',
        'controller' => 'Reservations',
        'action' => 'view',
        'id' => $reservation->id,
    ],
    [
        'escape' => true,
        'fullBase' => true,
    ]
) ?>


予約の登録と決済連携は行われています。

<?php endif; ?>
不明点がありましたら、契約元に下記情報とあわせてお問い合わせください。

【基本情報】
発生日時：<?= $now ?><?= PHP_EOL ?>

【予約情報】
<?php if(
    !empty($reservation->id)
    && (
        (isset($info['afterRedirectPaymentFlg']) && $info['afterRedirectPaymentFlg'])
        || !$info['smartLockAddReservationFlg']
    )
): ?>
予約ID: <?= $reservation->id ?><?= PHP_EOL ?>
<?php endif; ?>
予約枠ID: <?= $reservation->event_id ?><?= PHP_EOL ?>
予約枠名: <?= $event->name ?><?= PHP_EOL ?>
予約開始日: <?= $info['dateTimeFrom'] ?><?= PHP_EOL ?>
予約終了日: <?= $info['dateTimeTo'] ?><?= PHP_EOL ?>

【Akerun側情報】
事務所ID: <?= $smartLock->organizations_id ?><?= PHP_EOL ?>
デバイスキー: <?= $anotherEvent->event_smart_lock->smart_lock_device_key ?><?= PHP_EOL ?>
