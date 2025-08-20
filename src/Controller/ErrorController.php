<?php
declare(strict_types=1);

/**
 * CakePHP(tm) : Rapid Development Framework (https://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 * @link          https://cakephp.org CakePHP(tm) Project
 * @since         3.3.4
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 */
namespace App\Controller;

use App\View\JsonView;
use Cake\Event\EventInterface;

/**
 * Error Handling Controller
 *
 * Controller used by ExceptionRenderer to render error responses.
 */
class ErrorController extends AppController
{
    /**
     * Initialization hook method.
     *
     * @return void
     */
    public function initialize(): void
    {
        $this->loadComponent('Frame');

        if (!is_null(env(static::ENV_X_FRAME_OPTIONS_UNSET))) {
            $this->Frame->sameorigin();
        }

        if ($this->isAdmin()) {
            $this->viewBuilder()->setLayout('admin/error');
            $this->viewBuilder()->setTemplatePath('Error/Admin');
        } else {
            $this->viewBuilder()->setLayout('user/error');
            $this->viewBuilder()->setTemplatePath('Error/User');
        }

        $this->set([
            'isError' => true,
        ]);
    }

    /**
     * beforeFilter callback.
     *
     * @param \Cake\Event\EventInterface<\App\Controller\ErrorController> $event Event.
     * @return \Cake\Http\Response|null|void
     */
    public function beforeFilter(EventInterface $event)
    {
        if ($this->getResponse()->getStatusCode() < 500) {
            $this->setTranslate($this->isAdmin());
        }
    }

    /**
     * beforeRender callback.
     *
     * @param \Cake\Event\EventInterface<\App\Controller\ErrorController> $event Event.
     * @return \Cake\Http\Response|null|void
     */
    public function beforeRender(EventInterface $event)
    {
        parent::beforeRender($event);

        if (!$this->isAdmin() && $this->getResponse()->getStatusCode() >= 500) {
            $this->viewBuilder()->setLayout('error');
            $this->viewBuilder()->setTemplatePath('Error');
        }
    }

    /**
     * afterFilter callback.
     *
     * @param \Cake\Event\EventInterface<\App\Controller\ErrorController> $event Event.
     * @return \Cake\Http\Response|null|void
     */
    public function afterFilter(EventInterface $event)
    {
    }

    /**
     * @inheritDoc
     */
    public function viewClasses(): array
    {
        if ($this->getRequest()->is('ajax')) {
            return [JsonView::class];
        }

        return [];
    }

    /**
     * prefixから管理側かどうかを判定
     *
     * @return bool
     */
    protected function isAdmin()
    {
        $prefix = '';
        if (preg_match('/^\/(.*?)\//', $this->getRequest()->getPath(), $matches)) {
            $prefix = $matches[1];
        }

        if ($prefix === 'admin') {
            return true;
        }

        return false;
    }
}
