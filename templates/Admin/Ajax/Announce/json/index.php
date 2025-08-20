<?= $this->Ajax->json([
    'success' => !(is_null($announce)),
    'data' => $announce,
]) ?>
