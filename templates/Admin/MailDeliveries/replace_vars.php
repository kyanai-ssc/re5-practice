<?php
$this->assign('title', 'メール配信 置き換え文言一覧');
$this->assign('noNavi', true);
?>

<h1>メール配信 置き換え文言一覧</h1>
<div class="form-input-set">
    <fieldset>
        <table class="input-box">
            <tbody>
                <?php if(!empty($replaceVars['new'])) :?>
                    <?php foreach($replaceVars['new'] as $vars) :?>
                        <?php foreach($vars as $var) :?>
                            <tr class="field-input">
                                <th class="ttl-input">
                                    <div class="ttl-input-wrap"><?= h($var['label']) ?>
                                </th>
                                <td>
                                    <div class="ttl-input-wrap"><?= h($var['token']) ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endforeach; ?>
                <?php endif;?>
                <?php if(!empty($replaceVars['old'])) :?>
                    <?php foreach($replaceVars['old'] as $vars) :?>
                        <?php foreach($vars as $var) :?>
                            <tr class="field-input">
                                <th class="ttl-input">
                                    <div class="ttl-input-wrap"><?= h($var['label']) ?>
                                </th>
                                <td>
                                    <div class="ttl-input-wrap"><?= h($var['token']) ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endforeach; ?>
                <?php endif;?>
            </tbody>
        </table>
    </fieldset>
</div>
