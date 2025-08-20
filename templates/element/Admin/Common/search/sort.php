<div class="btn-sort">
    <?= $this->Paginator->sort($key, '▲', [
        'direction' => 'asc',
        'lock' => true,
    ]) ?>
    <?= $this->Paginator->sort($key, '▼', [
        'direction' => 'desc',
        'lock' => true,
    ]) ?>
</div>