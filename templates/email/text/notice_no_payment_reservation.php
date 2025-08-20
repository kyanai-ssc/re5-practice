<?php
use Cake\Routing\Router;
?>
期限までにApplePay/auPAYでの決済が行われませんでした。
決済管理ツールをご確認いただき未決済予約のキャンセル等をお願い致します。

予約ID：<?= implode('、', $reservationIds) ?>
未決済予約一覧：<?= Router::url([
    'prefix' => 'Admin',
    'controller' => 'Reservations',
    'action' => 'list',
    '?' => [
        'search_payment_expired' => true,
    ],
], true) ?>
