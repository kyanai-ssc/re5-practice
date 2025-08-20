<tr class="<?= h($wordIndex) ?>">
    <?= $this->Form->hidden('words.' . $wordIndex . '.id') ?>
    <td>
        <?php if ($errorWords === true) : ?>
            E-<?= h($wordData['code']) ?>
        <?php else : ?>
            <?= h($wordData['category']) ?>-<?= h($wordData['function']) ?>-<?= h($wordData['code']) ?>
        <?php endif; ?>
    </td>
    <td>
        <?= h($wordData['name']) ?>
    </td>
    <td>
        <?php if ((string)$wordData['multiple_row_flg'] === (string)\App\Model\Entity\Word::MULTIPLE_ROW_FLG_OFF) : ?>
            <?= h($wordData['word_default']) ?>
        <?php else: ?>
            <?= nl2br(h($wordData['word_default'])) ?>
        <?php endif; ?>
    </td>
    <td class="textarea">
        <?php if ((string)$wordData['multiple_row_flg'] === (string)\App\Model\Entity\Word::MULTIPLE_ROW_FLG_OFF) : ?>
            <?= $this->Form->control('words.' . $wordIndex . '.word', [
                'type' => 'text',
                'label' => false,
            ]) ?>
        <?php else : ?>
            <?= $this->Form->control('words.' . $wordIndex . '.word', [
                'type' => 'textarea',
                'label' => false,
                'colas' => 10,
            ]) ?>
        <?php endif; ?>
    </td>
</tr>

