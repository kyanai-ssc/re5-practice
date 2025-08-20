<?php
$this->assign('ajax_list', null);
?>

<?= $this->Ajax->json([
    'result' => $checkResult,
]) ?>
