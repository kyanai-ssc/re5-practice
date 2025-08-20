<?php if ($successInfo['validHeader']): ?>
新規登録：<?= h($successInfo['create']) ?>件
<?php if (!$noModify) : ?>
更新：<?= h($successInfo['modify']) ?>件
<?php endif; ?>
エラー：<?= h($successInfo['error']) ?>件

<?php if (!empty($successInfo['errorMessage'])) : ?>
<?php foreach ($successInfo['errorMessage'] as $line => $messages) : ?>
**** <?= h($line) ?>行目 ****
<?php if (is_array($messages)) : ?>
<?php foreach ($messages as $message) : ?>
<?php if (is_array($message)) : ?>

<?= h($message['subject']) ?>
 <?php foreach ($message['body'] as $asLine => $asMessage) : ?>

<?= h($asLine) ?>行目：<?= h($asMessage) ?>
<?php endforeach; ?>

<?php else : ?>
<?= h($message) ?>

<?php endif; ?>
<?php endforeach; ?>
<?php endif; ?>

<?php endforeach; ?>
<?php endif; ?>
<?php if($successInfo['errorLimit'] != 0) :?>

エラー件数が上限に達したため、アップロード処理を中断しました。
最終処理行：<?= h($successInfo['errorLimit']) ?>
<?php endif ;?>
<?php if($successInfo['invalidRow'] != 0) :?>

<?= h($successInfo['invalidRow']) ?>行目で不正な値を検出したので処理を中断しました。
<?php endif ;?>
<?php else: ?>
アップロード形式が正しくありません。再度アップロードフォーマットをダウンロードしてください。
<?php endif ;?>
