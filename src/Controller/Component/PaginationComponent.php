<?php
declare(strict_types=1);

namespace App\Controller\Component;

use App\Exception\PageNotFoundException;
use App\Locale\Message;
use Cake\Controller\Component;
use Cake\Core\Configure;
use Cake\Http\Exception\NotFoundException;
use Cake\Routing\Router;

/**
 * PaginationComponent class.
 */
class PaginationComponent extends Component
{
    /**
     * Handles pagination of records in Table objects.
     *
     * @param \Cake\ORM\Table|\Cake\ORM\Query|string|null $object Table to paginate
     * @param array<string, mixed> $settings The settings/configuration used for pagination.
     * @return \Cake\ORM\ResultSet|\Cake\Datasource\ResultSetInterface Query results
     */
    public function paginate($object = null, array $settings = [])
    {
        try {
            return $this->getController()->paginate($object, $settings);
        } catch (NotFoundException $e) {
            throw new PageNotFoundException([
                'message' => __(Message::ERROR_PAGE_NOT_FOUND),
                'backUrl' => Router::url([
                    'prefix' => $this->getController()->getRequest()->getParam('prefix'),
                    'controller' => $this->getController()->getName(),
                    'action' => $this->getController()->getRequest()->getParam('action'),
                    '?' => Configure::readOrFail('Setting.searchInput.searchQuery'),
                ]),
            ], null, $e);
        }
    }
}
