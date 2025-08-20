<?php
declare(strict_types=1);

namespace App\Controller\Admin;

use App\Controller\AdminAppController;
use App\Form\Admin\PassReset\ApprovalForm;
use App\Form\Admin\PassReset\CertificationForm;
use App\Locale\Message;
use Cake\Event\EventInterface;
use Cake\Http\Exception\BadRequestException;

/**
 * PassReset Controller
 */
class PassResetController extends AdminAppController
{
    /**
     * @inheritDoc
     */
    public function beforeFilter(EventInterface $event)
    {
        $response = parent::beforeFilter($event);

        $this->Authentication->addUnauthenticatedActions([
            'certification',
            'certificationFinish',
            'token',
            'approval',
            'approvalFinish',
        ]);

        return $response;
    }

    /**
     * Index method
     *
     * @return \Cake\Http\Response|null|void
     */
    public function index()
    {
        return $this->redirect([
            'prefix' => 'Admin',
            'controller' => 'PassReset',
            'action' => 'certification',
        ]);
    }

    /**
     * Index method
     *
     * @return \Cake\Http\Response|null|void
     */
    public function certification()
    {
        $passResetForm = new CertificationForm();

        if ($this->getRequest()->is('post')) {
            $passResetInputs = (array)$this->getRequest()->getData();
            if ($passResetForm->execute($passResetInputs)) {
                //管理者が存在する場合のみ
                $admin = $this->fetchTable('Admins')->find('mail', [
                    'login_id' => $passResetInputs['login_id'],
                ])->first();
                if (!empty($admin)) {
                    $this->fetchTable('AdminPassResetTokens')->save(
                        $this->fetchTable('AdminPassResetTokens')->newEntity([]),
                        ['admin' => $admin]
                    );
                }

                return $this->redirect([
                    'prefix' => 'Admin',
                    'controller' => 'PassReset',
                    'action' => 'certificationFinish',
                ]);
            }
        }

        $this->set([
            'passResetForm' => $passResetForm,
        ]);
    }

    /**
     * CertificationFinish method
     *
     * @return \Cake\Http\Response|null|void
     */
    public function certificationFinish()
    {
    }

    /**
     * Token method
     *
     * @return \Cake\Http\Response|null|void
     */
    public function token()
    {
        $passResetForm = new ApprovalForm();
        if (!$passResetForm->execute((array)$this->getRequest()->getQuery())) {
            throw new BadRequestException(Message::ERROR_INVALID_URL);
        }

        $this->getRequest()->getSession()->write('passReset.token', $passResetForm->getData());

        return $this->redirect([
            'prefix' => 'Admin',
            'controller' => 'PassReset',
            'action' => 'approval',
        ]);
    }

    /**
     * approval method
     *
     * @return \Cake\Http\Response|null|void
     */
    public function approval()
    {
        /** @var \App\Model\Table\AdminPassResetTokensTable $adminPassResetTokensTable */
        $adminPassResetTokensTable = $this->fetchTable('AdminPassResetTokens');

        $data = (array)$this->getRequest()->getSession()->read('passReset.token');

        $passResetForm = new ApprovalForm();
        if (!$passResetForm->execute($data)) {
            throw new BadRequestException(Message::ERROR_INVALID_URL);
        }

        if ($this->getRequest()->is('post')) {
            if (!$adminPassResetTokensTable->resetPassword($data['token'])) {
                throw new BadRequestException(Message::ERROR_ILLEGAL_TRANSITION);
            }
            $this->getRequest()->getSession()->delete('passReset');

            return $this->redirect([
                'prefix' => 'Admin',
                'controller' => 'PassReset',
                'action' => 'approvalFinish',
            ]);
        }

        $this->set([
            'passResetForm' => $passResetForm,
        ]);
    }

    /**
     * ApprovalFinish method
     *
     * @return \Cake\Http\Response|null|void
     */
    public function approvalFinish()
    {
    }
}
