<?php
/** @var array $info */
/** @var \Cake\I18n\FrozenTime $now */
/** @var \App\Model\Entity\Reservation $reservation */
$reservation = $info['reservation'];
/** @var \App\Model\Entity\Event $event */
$event = $reservation->get('event');
?>
<?php if(isset($info['afterRedirectPaymentFlg']) && $info['afterRedirectPaymentFlg']): ?>
RemoteLOCK連携で下記エラーになりました。
<?php else: ?>
RemoteLOCK連携で下記エラーになり、予約新規作成が行えませんでした。
<?php endif; ?>
下記の原因が考えられます。
・APIの利用制限に引っかかっている
・登録制限に引っかかっている

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

【RemoteLOCK側情報】
デバイスキー: <?= $event->event_smart_lock->smart_lock_device_key ?><?= PHP_EOL ?>
