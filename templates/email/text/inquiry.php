お客様より、お問い合わせがございました。

お問い合わせ番号： <?= $inquiry->id ?><?= PHP_EOL ?>
<?php if(is_null($inquiry->user_id)) :?>
氏名： <?= $inquiry->name ?><?= PHP_EOL ?>
電話番号： <?= $inquiry->phone_number ?><?= PHP_EOL ?>
<?php else : ?>
顧客ID： <?= $inquiry->user_id ?><?= PHP_EOL ?>
<?php endif ?>
メールアドレス：<?= $inquiry->mail ?><?= PHP_EOL ?>
お問い合わせ日時：<?= $inquiry->created->format('Y/m/d H:i:s') ?><?= PHP_EOL ?>

---------
<?= $inquiry->contents ?>
