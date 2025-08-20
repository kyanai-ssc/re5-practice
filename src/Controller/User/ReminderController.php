<?php
declare(strict_types=1);

namespace App\Controller\User;

use App\Controller\UserAppController;
use App\Form\User\Reminder\PasswordForm;
use App\Form\User\Reminder\ReminderForm;
use App\Locale\Message;
use Cake\Datasource\EntityInterface;
use Cake\Event\EventInterface;
use Cake\Http\Exception\BadRequestException;
use Cake\Http\Exception\NotFoundException;
use Cake\Utility\Hash;

/**
 * Index Controller
 */
class ReminderController extends UserAppController
{
    /**
     * @inheritDoc
     */
    public function beforeFilter(EventInterface $event)
    {
        $response = parent::beforeFilter($event);

        $this->Authentication->addUnauthenticatedActions([
            'loginId',
            'sendLoginId',
            'password',
            'passwordFinish',
            'token',
            'passwordEdit',
            'passwordEditFinish',
        ]);

        $this->alreadyLoggedRedirect();

        return $response;
    }

    /**
     * LoginId method
     *
     * @return \Cake\Http\Response|null|void
     */
    public function loginId()
    {
        /** @var \App\Model\Table\SiteSettingsTable $siteSettingsTable */
        $siteSettingsTable = $this->fetchTable('SiteSettings');
        /** @var \App\Model\Table\AutoReplyMailHistoriesTable $autoReplyMailHistoriesTable */
        $autoReplyMailHistoriesTable = $this->fetchTable('AutoReplyMailHistories');

        if (!$siteSettingsTable->getData()->isUseFlgOn('id_reminder_flg')) {
            throw new NotFoundException(Message::ERROR_NOT_FOUND);
        }

        $reminderForm = new ReminderForm();
        if ($this->getRequest()->is('post')) {
            $reminderInputs = (array)$this->getRequest()->getData();
            if ($reminderForm->execute($reminderInputs)) {
                $user = $this->fetchTable('Users')->find('reminder', ['mail' => $reminderInputs['mail']])->first();

                if (!empty($user) && $user instanceof EntityInterface) {
                    $autoReplyMailHistoriesTable->sendIdReminder($user);
                }

                return $this->redirect([
                    'prefix' => 'User',
                    'controller' => 'Reminder',
                    'action' => 'sendLoginId',
                ]);
            }
        }

        $this->set([
            'reminderForm' => $reminderForm,
        ]);
    }

    /**
     * SendLoginId method
     *
     * @return \Cake\Http\Response|null|void
     */
    public function sendLoginId()
    {
        /** @var \App\Model\Table\SiteSettingsTable $siteSettingsTable */
        $siteSettingsTable = $this->fetchTable('SiteSettings');

        if (!$siteSettingsTable->getData()->isUseFlgOn('id_reminder_flg')) {
            throw new NotFoundException(Message::ERROR_NOT_FOUND);
        }
    }

    /**
     * Password method
     *
     * @return \Cake\Http\Response|null|void
     */
    public function password()
    {
        /** @var \App\Model\Table\SiteSettingsTable $siteSettingsTable */
        $siteSettingsTable = $this->fetchTable('SiteSettings');

        if (!$siteSettingsTable->getData()->isUseFlgOn('password_reminder_flg')) {
            throw new NotFoundException(Message::ERROR_NOT_FOUND);
        }

        if ($this->getRequest()->is('post')) {
            $reminder = $this->fetchTable('UserPasswordReminderTokens')->newEntity($this->getRequest()->getData());

            if ($this->fetchTable('UserPasswordReminderTokens')->save($reminder)) {
                return $this->redirect([
                    'prefix' => 'User',
                    'controller' => 'Reminder',
                    'action' => 'passwordFinish',
                ]);
            }
        } else {
            $reminder = $this->fetchTable('UserPasswordReminderTokens')->newEntity([], ['validate' => false]);
        }

        $this->set([
            'reminder' => $reminder,
        ]);
    }

    /**
     * Password method
     *
     * @return \Cake\Http\Response|null|void
     */
    public function passwordFinish()
    {
        /** @var \App\Model\Table\SiteSettingsTable $siteSettingsTable */
        $siteSettingsTable = $this->fetchTable('SiteSettings');

        if (!$siteSettingsTable->getData()->isUseFlgOn('password_reminder_flg')) {
            throw new NotFoundException(Message::ERROR_NOT_FOUND);
        }
    }

    /**
     * Token method
     *
     * @return \Cake\Http\Response|null|void
     */
    public function token()
    {
        /** @var \App\Model\Table\SiteSettingsTable $siteSettingsTable */
        $siteSettingsTable = $this->fetchTable('SiteSettings');

        if (!$siteSettingsTable->getData()->isUseFlgOn('password_reminder_flg')) {
            throw new NotFoundException(Message::ERROR_NOT_FOUND);
        }

        $token = null;
        $query = (array)$this->getRequest()->getQuery();
        if (isset($query['token']) && is_scalar($query['token'])) {
            $token = $this->fetchTable('UserPasswordReminderTokens')->find('token', [
                'token' => $query['token'],
            ])->first();
        }

        if (!$token instanceof EntityInterface) {
            throw new BadRequestException(Message::ERROR_INVALID_URL);
        }

        $this->getRequest()->getSession()->write('reminder.token', [
            'token' => $token->get('token'),
        ]);

        return $this->redirect([
            'prefix' => 'User',
            'controller' => 'Reminder',
            'action' => 'password-edit',
        ]);
    }

    /**
     * passwordEdit method
     *
     * @return \Cake\Http\Response|null|void
     */
    public function passwordEdit()
    {
        /** @var \App\Model\Table\SiteSettingsTable $siteSettingsTable */
        $siteSettingsTable = $this->fetchTable('SiteSettings');
        /** @var \App\Model\Table\UsersTable $usersTable */
        $usersTable = $this->fetchTable('Users');

        if (!$siteSettingsTable->getData()->isUseFlgOn('password_reminder_flg')) {
            throw new NotFoundException(Message::ERROR_NOT_FOUND);
        }

        $data = $this->getRequest()->getSession()->read('reminder.token');
        $token = $this->fetchTable('UserPasswordReminderTokens')->find('token', [
            'token' => Hash::get((array)$data, 'token'),
        ])->first();

        if (!$token instanceof EntityInterface) {
            throw new BadRequestException(Message::ERROR_INVALID_URL);
        }

        $passwordForm = new PasswordForm();
        $passwordForm->setUserId($token->get('user_id'));

        if ($this->getRequest()->is('post')) {
            $passwordInputs = (array)$this->getRequest()->getData();
            if ($passwordForm->execute($passwordInputs)) {
                if ($usersTable->updatePassword($token->get('user'), $passwordInputs['password'])) {
                    return $this->redirect([
                        'prefix' => 'User',
                        'controller' => 'Reminder',
                        'action' => 'passwordEditFinish',
                    ]);
                }
            } else {
                $this->Flash->set((string)__(Message::INVALID_INPUT), [
                    'key' => 'reminderPwEditErrors',
                    'element' => 'error',
                ]);
            }
        }

        $this->set([
            'passwordForm' => $passwordForm,
        ]);
    }

    /**
     * Password method
     *
     * @return \Cake\Http\Response|null|void
     */
    public function passwordEditFinish()
    {
        /** @var \App\Model\Table\SiteSettingsTable $siteSettingsTable */
        $siteSettingsTable = $this->fetchTable('SiteSettings');

        if (!$siteSettingsTable->getData()->isUseFlgOn('password_reminder_flg')) {
            throw new NotFoundException(Message::ERROR_NOT_FOUND);
        }
    }
}
