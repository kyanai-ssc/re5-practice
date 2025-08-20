<?php
if (!isset($params['escape']) || $params['escape'] !== false) {
    $message = h($message);
}
?>
<aside class="cmn-msg is-comp">
    <p><svg class="icon is-msg"><use xlink:href="#icon_check"></use></svg><?= h($message) ?></p>
</aside>
