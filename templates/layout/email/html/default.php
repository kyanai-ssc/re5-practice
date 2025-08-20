<?php
use App\Utility\StringUtility;
?>
<?php $this->start('message'); ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN">
<html>
<head>
<meta http-equiv="Content-Language" content="ja"/>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<meta http-equiv="Content-Style-Type" content="text/css"/>
<meta http-equiv="Content-Script-Type" content="text/javascript"/>
<?= $this->Html->meta('viewport', 'width=device-width, initial-scale=1') ?>

</head>
<body>
<div>
<?= $this->fetch('content') ?>

</div>
</body>
</html>
<?php $this->end('message'); ?>
<?= StringUtility::mimeBase64Encode($this->fetch('message')) ?>
