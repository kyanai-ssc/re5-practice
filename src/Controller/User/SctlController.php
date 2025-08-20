<?php
declare(strict_types=1);

namespace App\Controller\User;

use App\Controller\UserAppController;
use App\Model\Entity\Term;
use Cake\Event\EventInterface;

/**
 * Sctl Controller
 */
class SctlController extends UserAppController
{
    /**
     * @inheritDoc
     */
    public function beforeFilter(EventInterface $event)
    {
        $response = parent::beforeFilter($event);

        $this->isLoginRequire(['index']);
        $this->canUseAction('sctl_flg');

        return $response;
    }

    /**
     * Index method
     *
     * @return \Cake\Http\Response|null|void
     */
    public function index()
    {
        /** @var \App\Model\Table\TermsTable $termsTable */
        $termsTable = $this->fetchTable('Terms');

        $this->enableLoginRedirectBack();
        $this->set([
            'sctl' => $termsTable->getData(Term::TYPE_SCTL_TOP),
        ]);
    }
}
