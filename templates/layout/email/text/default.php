<?php
use App\Utility\StringUtility;
?>
<?php $this->start('message'); ?>
<?= $this->fetch('content') ?>
<?php $this->end('message'); ?>
<?= StringUtility::mimeBase64Encode($this->fetch('message')) ?>
