<ul class="cmn-pager">
    <?= $this->Paginator->first('<<') ?>
    <?= $this->Paginator->prev('<', [
        'disabledTitle' => false,
    ]) ?>
    <?= $this->Paginator->numbers(['modulus' => 4]) ?>
    <?= $this->Paginator->next('>', [
        'disabledTitle' => false,
    ]) ?>
    <?= $this->Paginator->last('>>') ?>
</ul>
