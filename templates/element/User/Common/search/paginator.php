<ul class="cmn-pager">
    <?= $this->Paginator->first($this->Tr->t('paginator/first')) ?>
    <?= $this->Paginator->prev($this->Tr->t('paginator/back'), [
        'disabledTitle' => false,
    ]) ?>
    <?= $this->Paginator->numbers(['modulus' => 4]) ?>
    <?= $this->Paginator->next($this->Tr->t('paginator/next'), [
        'disabledTitle' => false,
    ]) ?>
    <?= $this->Paginator->last($this->Tr->t('paginator/last')) ?>
</ul>
