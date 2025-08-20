<?php
declare(strict_types=1);

namespace App\Controller\User;

use App\Controller\UserAppController;
use App\Locale\Message;
use App\Model\Entity\RecaptchaSetting;
use Cake\Event\EventInterface;
use Cake\Http\Exception\BadRequestException;

/**
 * Index Controller
 */
class InquiryController extends UserAppController
{
    public const TOKEN_VALIDATION_ADD = 'user_inquiry';

    /**
     * @inheritDoc
     */
    public function beforeFilter(EventInterface $event)
    {
        $response = parent::beforeFilter($event);

        $this->isLoginRequire([
            'index',
            'conf',
            'finish',
        ]);
        $this->canUseAction('inquiry_flg');

        return $response;
    }

    /**
     * Index method
     *
     * @return \Cake\Http\Response|null|void
     */
    public function index()
    {
        /** @var \App\Model\Table\InquiriesTable $inquiriesTable */
        $inquiriesTable = $this->fetchTable('Inquiries');

        if ($this->getRequest()->getQuery('input') === 'back') {
            if (!$this->getRequest()->getSession()->check('user.inquiry')) {
                throw new BadRequestException();
            }
        }

        if ($this->getRequest()->is('post')) {
            $inquiry = $inquiriesTable->newEntity($this->getRequest()->getData());

            if (!$inquiry->getErrors() && $this->TokenValidation->validate(static::TOKEN_VALIDATION_ADD)) {
                $this->getRequest()->getSession()->write('user.inquiry', $this->getRequest()->getData());

                return $this->redirect([
                    'prefix' => 'User',
                    'controller' => 'Inquiry',
                    'action' => 'conf',
                ]);
            } else {
                $this->Flash->set((string)__(Message::INVALID_INPUT), [
                    'key' => 'inquiriesErrors',
                    'element' => 'error',
                ]);
            }
        } elseif ($this->getRequest()->getQuery('input') === 'back') {
            $inquiry = $inquiriesTable->newEntity($this->getRequest()->getSession()->read('user.inquiry'), [
                'validate' => false,
            ]);
        } else {
            $this->getRequest()->getSession()->delete('user.inquiry');
            $inquiry = $inquiriesTable->newEntity($inquiriesTable->getDefaultFieldValues(), [
                'validate' => false,
            ]);
        }

        $this->TokenValidation->generate(static::TOKEN_VALIDATION_ADD);

        $this->enableLoginRedirectBack();
        $this->set([
            'inquiry' => $inquiry,
            'valueOptions' => $inquiriesTable->getFieldValueOptions(),
        ]);
    }

    /**
     * Conf method
     *
     * @return \Cake\Http\Response|null|void
     */
    public function conf()
    {
        // reCATPTCHA
        if ($this->getRequest()->is('post')) {
            $this->Recaptcha->verify(RecaptchaSetting::ACTION_INQUIRY);
        }

        /** @var \App\Model\Table\InquiriesTable $inquiriesTable */
        $inquiriesTable = $this->fetchTable('Inquiries');

        $inquiryInputs = $this->getRequest()->getSession()->read('user.inquiry');
        if (empty($inquiryInputs)) {
            throw new BadRequestException();
        }

        if ($this->getRequest()->is('post')) {
            // ワンタイムトークンチェック
            $inquiry = $inquiriesTable->newEntity($inquiryInputs);

            $identity = $this->Authentication->getIdentity();
            $userId = null;
            if (isset($identity)) {
                /** @var \Authentication\Identity $identity */
                $userId = $identity->offsetGet('id');
            }
            if (
                $this->TokenValidation->validate(static::TOKEN_VALIDATION_ADD)
                && $inquiriesTable->inquirySend($inquiry, ['userId' => $userId])
            ) {
                $this->getRequest()->getSession()->delete('user.inquiry');

                return $this->redirect([
                    'prefix' => 'User',
                    'controller' => 'Inquiry',
                    'action' => 'finish',
                ]);
            } else {
                $this->Flash->set((string)__(Message::INVALID_INPUT), [
                    'key' => 'inquiriesErrors',
                    'element' => 'error',
                ]);
            }
        } else {
            // エンティティ生成
            $inquiry = $inquiriesTable->newEntity($inquiryInputs, ['validation' => true]);
        }

        $this->TokenValidation->generate(static::TOKEN_VALIDATION_ADD);

        // ビュー変数
        $this->set([
            'inquiry' => $inquiry,
            'valueOptions' => $inquiriesTable->getFieldValueOptions(),

        ]);
    }

    /**
     * Finish method
     *
     * @return \Cake\Http\Response|null|void
     */
    public function finish()
    {
    }
}
