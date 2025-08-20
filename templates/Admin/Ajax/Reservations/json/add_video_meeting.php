<?= $this->Ajax->json([
    'url' => $this->Url->build([
        'prefix' => 'Admin',
        'controller' => 'Reservations',
        'action' => 'view',
        'id' => $reservationId,
    ], ['escape' => false]),
]) ?>
