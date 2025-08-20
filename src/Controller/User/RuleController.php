<?php
declare(strict_types=1);

namespace App\Controller\User;

use App\Controller\UserAppController;
use Cake\Event\EventInterface;

/**
 * Index Controller
 */
class RuleController extends UserAppController
{
    public const TOKEN_VALIDATION_ADD = 'user_inquiry';

    /**
     * @inheritDoc
     */
    public function beforeFilter(EventInterface $event)
    {
        $response = parent::beforeFilter($event);

        $this->isLoginRequire(['index']);
        $this->canUseAction('terms_flg');

        return $response;
    }

    /**
     * Index method
     *
     * @return \Cake\Http\Response|null|void
     */
    public function index()
    {
        $terms = $this->fetchTable('Terms')->find('Terms')->all()->combine('type', 'contents');

        $this->enableLoginRedirectBack();
        $this->set(['terms' => $terms->toArray()]);
    }
}
