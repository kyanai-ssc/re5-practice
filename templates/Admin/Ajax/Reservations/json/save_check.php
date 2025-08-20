<?= $this->Ajax->json([
    'result' => true,
    'searchPaymentExpired' => $searchPaymentExpiredFlg,
    'searchSmartLockUnlinked' => $searchSmartLockUnlinkedFlg,
]) ?>
