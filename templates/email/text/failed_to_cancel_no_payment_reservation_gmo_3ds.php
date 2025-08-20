予約の自動キャンセルでエラーが発生しました。
GMO管理画面をご確認いただき未決済予約のキャンセル等をお願い致します。

<?php foreach ($reservationIds as $reservationId): ?>
予約ID：<?= $reservationId ?><?= "\n" ?>
<?php if (isset($errorCodes[$reservationId])): ?>
エラーコード：<?= $errorCodes[$reservationId] ?><?= "\n" ?>
<?php endif; ?>
<?= "\n" ?>
<?php endforeach; ?>
