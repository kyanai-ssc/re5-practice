<?php if (!$isEmpty): ?>
    <?php
        $url = $this->Url->build([
            'prefix' => 'Admin',
            'controller' => 'Reservations',
            'action' => 'add-conf',
            '?' => [
                'key' => $reloadKey,
            ],
        ], ['escape' => false]);
    ?>
<?php else: ?>
    <?php
        $url = $this->Url->build([
            'prefix' => 'Admin',
            'controller' => 'Reservations',
            'action' => 'calendar',
            '?' => $this->Configure->read('Setting.searchInput.searchQuery'),
        ], ['escape' => false]);
    ?>
<?php endif; ?>
<?= $this->Ajax->json([
    'url' => $url,
]) ?>
