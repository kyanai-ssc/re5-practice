<?= $this->Ajax->json([
    'url' => $this->Url->build([
        'prefix' => 'Admin',
        'controller' => 'Reservations',
        'action' => 'calendar',
        '?' => $this->Configure->read('Setting.searchInput.searchQuery') + [
            'user_id' => $userId,
        ],
    ], ['escape' => false]),
]) ?>
