<?php
declare(strict_types=1);

namespace App\Model\Table\Traits;

use App\Locale\Message;
use Cake\Http\Exception\BadRequestException;

/**
 * payment trait.
 */
trait PaymentTrait
{
    /**
     * 決済ロックの判定
     *
     * @return void
     */
    protected function checkPaymentLock(): void
    {
        /** @var \App\Model\Table\PaymentErrorsTable $paymentErrorsTable */
        $paymentErrorsTable = $this->getTableLocator()->get('PaymentErrors');

        if ($paymentErrorsTable->getData()->isLock()) {
            throw new BadRequestException(Message::ERROR_PAYMENT);
        }
    }

    /**
     * 決済エラーを加算
     *
     * @return void
     */
    protected function addPaymentError(): void
    {
        /** @var \App\Model\Table\PaymentErrorsTable $paymentErrorsTable */
        $paymentErrorsTable = $this->getTableLocator()->get('PaymentErrors');

        $paymentErrorsTable->addError();
        $this->checkPaymentLock();
    }

    /**
     * 決済エラーをリセット
     *
     * @return void
     */
    protected function resetPaymentError(): void
    {
        /** @var \App\Model\Table\PaymentErrorsTable $paymentErrorsTable */
        $paymentErrorsTable = $this->getTableLocator()->get('PaymentErrors');

        $paymentErrorsTable->resetError();
    }
}
