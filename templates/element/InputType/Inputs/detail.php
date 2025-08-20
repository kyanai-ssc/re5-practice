<?php if ($formItem->getInputTypeItem()->canDisplayDetail($detailValue)): ?>
    <tr class="field-input">
        <th class="ttl-input">
            <div class="ttl-input-wrap">
                <?= h($formItem->get('name')) ?>
            </div>
        </th>
        <td>
            <div class="cmn-txt <?= h(isset($divAddClass) ? $divAddClass : ''); ?>">
                <?php if ($escape): ?>
                    <?= nl2br(h($detailValue)) ?>
                <?php else: ?>
                    <?= $detailValue ?>
                <?php endif; ?>
                <?php if (isset($additionHtml)): ?>
                    <?= $additionHtml ?>
                <?php endif; ?>
            </div>
            <?php if ($formItem->getInputTypeItem()->canDisplayDetailDescription($options)): ?>
                <?= $this->element('InputType/Inputs/description', [
                    'formItem' => $formItem,
                ]) ?>
            <?php endif; ?>
        </td>
    </tr>
<?php endif; ?>
