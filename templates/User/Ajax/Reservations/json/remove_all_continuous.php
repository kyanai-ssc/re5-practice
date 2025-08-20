<?= $this->Ajax->json([
    'url' => $this->Url->build([
        'prefix' => 'User',
        'controller' => 'Reservations',
        'action' => 'calendar',
        '?' => $this->Configure->read('Setting.searchInput.searchQuery'),
    ], ['escape' => false]),
]) ?>
