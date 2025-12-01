<?php
declare(strict_types=1);

namespace App\Controller\User;

use App\Controller\Traits\ReservationsTrait;
use App\Controller\UserAppController;
use App\Form\User\Guest\CodeForm;
use App\Form\User\Guest\LoginForm;
use App\Form\User\Reservations\CancelForm;
use App\Form\User\Reservations\ReservationForm;
use App\Locale\Message;
use Cake\Core\Configure;
use Cake\Event\EventInterface;
use Cake\Http\Exception\BadRequestException;
use Cake\Http\Exception\NotFoundException;

/**
 * GuestController
 *
 * @property \App\Controller\Component\FileUploadComponent $FileUpload
 */
class GuestController extends UserAppController
{
    use ReservationsTrait;

    public const TOKEN_VALIDATION_EDIT = 'reservations_edit';

    /**
     * @inheritDoc
     */
    public function beforeFilter(EventInterface $event)
    {
        $response = parent::beforeFilter($event);

        $this->isLoginRequire([
            'login',
            'code',
            'reservationDetail',
            'reservationCancel',
            'reservationCancelFinish',
            'reservationEdit',
            'reservationEditConf',
            'reservationEditFinish',
        ]);

        $this->canUseAction('reservation_add_not_user_flg');

        //ログイン情報があれば404
        if ($this->commonData()->existsUserLoginData()) {
            throw new NotFoundException(Message::ERROR_NOT_FOUND);
        }

        $this->FormProtection->setConfig('unlockedActions', [
            'reservationCancel',
        ]);

        return $response;
    }

    /**
     * Index method
     *
     * @return \Cake\Http\Response|null|void
     */
    public function login()
    {
        $this->getRequest()->getSession()->delete('guest');

        /** @var \App\Model\Table\ReservationGuestCodesTable $reservationGuestCodesTable */
        $reservationGuestCodesTable = $this->fetchTable('ReservationGuestCodes');

        $identity = $this->Authentication->getIdentity();
        if (isset($identity)) {
            throw new NotFoundException(Message::ERROR_NOT_FOUND);
        }

        $loginForm = new LoginForm();
        if ($this->getRequest()->is('post')) {
            $loginInputs = (array)$this->getRequest()->getData();
            if ($loginForm->execute($loginInputs)) {
                $entity = $reservationGuestCodesTable->newEntity($loginInputs);

                if ($reservationGuestCodesTable->createCode($entity, $loginInputs['mail'])) {
                    $this->getRequest()->getSession()->write('guest.login', $loginForm->getData('reservation_id'));

                    return $this->redirect([
                        'prefix' => 'User',
                        'controller' => 'Guest',
                        'action' => 'code',
                        'id' => $entity->get('reservation_id'),
                    ]);
                }
            }
            $this->Flash->set((string)__(Message::INVALID_INPUT), [
                'key' => 'guestLoginErrors',
                'element' => 'error',
            ]);
        }

        $this->set([
            'loginForm' => $loginForm,
        ]);
    }

    /**
     * Code method
     *
     * @param int|null $reId Reservations.id
     * @return \Cake\Http\Response|null|void
     */
    public function code($reId = null)
    {
        if ($this->getRequest()->getSession()->read('guest.login') !== (string)$reId) {
            return $this->redirect([
                'prefix' => 'User',
                'controller' => 'Guest',
                'action' => 'login',
            ]);
        }

        $codeForm = new CodeForm();
        $codeForm->setReId((int)$reId);
        if ($this->getRequest()->is('post')) {
            if ($codeForm->execute((array)$this->getRequest()->getData())) {
                $this->getRequest()->getSession()->write('guest.code', $codeForm->getData('code'));

                return $this->redirect([
                    'prefix' => 'User',
                    'controller' => 'Guest',
                    'action' => 'reservationDetail',
                    'id' => $codeForm->getReId(),
                ]);
            } else {
                $this->Flash->set((string)__(Message::INVALID_INPUT), [
                    'key' => 'guestCodeErrors',
                    'element' => 'error',
                ]);
            }
        }

        $this->set([
            'codeForm' => $codeForm,
        ]);
    }

    /**
     * Reservation detail method
     *
     * @param int|null $reId Reservations.id
     * @return \Cake\Http\Response|null|void
     */
    public function reservationDetail($reId = null)
    {
        if (!isset($reId)) {
            throw new BadRequestException(Message::ERROR_INVALID_URL);
        }

        /** @var \App\Model\Table\ReservationGuestCodesTable $reservationGuestCodesTable */
        $reservationGuestCodesTable = $this->fetchTable('ReservationGuestCodes');

        if (!$reservationGuestCodesTable->existsReId($reId, $this->getRequest()->getSession()->read('guest.code'))) {
            return $this->redirect([
                'prefix' => 'User',
                'controller' => 'Guest',
                'action' => 'login',
            ]);
        }

        $reservationForm = new ReservationForm();
        $this->viewAction($reId, $reservationForm);
        $reservationForm->guestReservationIsCancel();
    }

    /**
     * Reservation cancel method
     *
     * @param int|null $reId 予約ID
     * @return \Cake\Http\Response|null|void
     */
    public function reservationCancel($reId = null)
    {
        if (!isset($reId)) {
            throw new BadRequestException();
        }

        /** @var \App\Model\Table\ReservationGuestCodesTable $reservationGuestCodesTable */
        $reservationGuestCodesTable = $this->fetchTable('ReservationGuestCodes');

        if (!$reservationGuestCodesTable->existsReId($reId, $this->getRequest()->getSession()->read('guest.code'))) {
            return $this->redirect([
                'prefix' => 'User',
                'controller' => 'Guest',
                'action' => 'login',
            ]);
        }

        $cancelForm = new CancelForm();
        $this->cancelAction($reId, $cancelForm, Configure::readOrFail('Master.common.flg.on'));

        return $this->redirect([
            'prefix' => 'User',
            'controller' => 'Guest',
            'action' => 'reservationCancelFinish',
            'id' => $reId,
        ]);
    }

    /**
     * Reservation cancel finish method
     *
     * @param int $id 予約ID
     * @return \Cake\Http\Response|null|void
     */
    public function reservationCancelFinish($id = null)
    {
        if (!isset($id)) {
            throw new BadRequestException();
        }

        /** @var \App\Model\Table\ReservationsTable $reservationsTable */
        $reservationsTable = $this->fetchTable('Reservations');
        /** @var \App\Model\Table\ReservationGuestCodesTable $reservationGuestCodesTable */
        $reservationGuestCodesTable = $this->fetchTable('ReservationGuestCodes');

        if (!$reservationsTable->validatePrimaryKey($id)) {
            throw new BadRequestException();
        }

        if (!$reservationGuestCodesTable->existsReId($id, $this->getRequest()->getSession()->read('guest.code'))) {
            return $this->redirect([
                'prefix' => 'User',
                'controller' => 'Guest',
                'action' => 'login',
            ]);
        }

        $this->set([
            'reservationId' => $id,
        ]);
    }

    /**
     * Reservation edit method
     *
     * @param int $id 予約ID
     * @return \Cake\Http\Response|null|void
     */
    public function reservationEdit($id = null)
    {
        if (!isset($id)) {
            throw new BadRequestException(Message::ERROR_INVALID_URL);
        }

        /** @var \App\Model\Table\ReservationGuestCodesTable $reservationGuestCodesTable */
        $reservationGuestCodesTable = $this->fetchTable('ReservationGuestCodes');

        if (!$reservationGuestCodesTable->existsReId($id, $this->getRequest()->getSession()->read('guest.code'))) {
            return $this->redirect([
                'prefix' => 'User',
                'controller' => 'Guest',
                'action' => 'login',
            ]);
        }

        $reservationForm = new ReservationForm();
        $result = $this->editAction($id, $reservationForm, static::TOKEN_VALIDATION_EDIT);

        if ($result) {
            return $this->redirect([
                'prefix' => 'User',
                'controller' => 'Guest',
                'action' => 'reservationEditConf',
                'id' => $reservationForm->getReservationEntity()->get('id'),
            ]);
        }
    }

    /**
     * Reservation edit conf method
     *
     * @param int $id 予約ID
     * @return \Cake\Http\Response|null|void
     */
    public function reservationEditConf($id = null)
    {
        if (!isset($id)) {
            throw new BadRequestException(Message::ERROR_INVALID_URL);
        }

        /** @var \App\Model\Table\ReservationGuestCodesTable $reservationGuestCodesTable */
        $reservationGuestCodesTable = $this->fetchTable('ReservationGuestCodes');

        if (!$reservationGuestCodesTable->existsReId($id, $this->getRequest()->getSession()->read('guest.code'))) {
            return $this->redirect([
                'prefix' => 'User',
                'controller' => 'Guest',
                'action' => 'login',
            ]);
        }

        $reservationForm = new ReservationForm();

        $result = $this->editConfAction(
            $id,
            $reservationForm,
            static::TOKEN_VALIDATION_EDIT,
            Configure::readOrFail('Master.common.flg.on')
        );
        if ($result) {
            return $this->redirect([
                'prefix' => 'User',
                'controller' => 'Guest',
                'action' => 'reservationEditFinish',
                'id' => $reservationForm->getReservationEntity()->get('id'),
            ]);
        }
    }

    /**
     * Reservation edit finish method
     *
     * @param int $id 予約ID
     * @return \Cake\Http\Response|null|void
     */
    public function reservationEditFinish($id = null)
    {
        if (!isset($id)) {
            throw new BadRequestException(Message::ERROR_INVALID_URL);
        }

        /** @var \App\Model\Table\ReservationGuestCodesTable $reservationGuestCodesTable */
        $reservationGuestCodesTable = $this->fetchTable('ReservationGuestCodes');

        if (!$reservationGuestCodesTable->existsReId($id, $this->getRequest()->getSession()->read('guest.code'))) {
            return $this->redirect([
                'prefix' => 'User',
                'controller' => 'Guest',
                'action' => 'login',
            ]);
        }

        $this->editFinishAction($id);
    }
}
