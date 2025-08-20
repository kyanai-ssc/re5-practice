<?php
declare(strict_types=1);

namespace App\Controller\Traits;

use App\Form\User\WaitingCancellations\WaitingCancellationForm;
use Cake\Core\Exception\CakeException;
use Cake\Http\Exception\BadRequestException;
use Cake\Utility\Hash;

/**
 * WaitingCancellations trait.
 */
trait WaitingCancellationsTrait
{
    /**
     * Add action
     *
     * @return \App\Form\User\WaitingCancellations\WaitingCancellationForm
     */
    protected function addAction()
    {
        $waitingCancellationForm = new WaitingCancellationForm();
        $waitingCancellationForm->setParameter([
            'event_id' => $this->getRequest()->getQuery('event_id'),
            'usage_timestamp' => $this->getRequest()->getQuery('usage_timestamp'),
        ]);
        if (!$waitingCancellationForm->validateParameter()) {
            $errors = Hash::flatten($waitingCancellationForm->getErrors());
            if (!empty($errors)) {
                throw new BadRequestException(reset($errors));
            }
            throw new BadRequestException();
        }

        $finish = false;
        if ($this->getRequest()->is('post')) {
            $inputs = (array)$this->getRequest()->getData();
            $inputs['user_id'] = null;
            if ($this->commonData()->existsUserLoginData()) {
                $inputs['user_id'] = $this->commonData()->getUserLoginData()->get('id');
            }
            if ($waitingCancellationForm->execute($inputs)) {
                $waitingCancellation = $waitingCancellationForm->getWaitingCancellationEntity();
                if (!isset($waitingCancellation)) {
                    throw new CakeException();
                }
                if ($this->fetchTable('WaitingCancellations')->save($waitingCancellation)) {
                    $finish = true;
                }
            }
        }

        $this->set([
            'finish' => $finish,
            'waitingCancellationForm' => $waitingCancellationForm,
        ]);

        return $waitingCancellationForm;
    }
}
