<?php
declare(strict_types=1);

namespace App\Command\Setting;

use App\Command\Command;
use App\Model\Entity\PaymentMethod;
use App\Model\Entity\SystemSetting;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;

/**
 * Class UsingPaymentCommand
 */
class UsingPaymentCommand extends Command
{
    public const PAYMENT_USE_FLG = [
        'on' => SystemSetting::PAYMENT_USE_FLG_ON,
        'off' => SystemSetting::PAYMENT_USE_FLG_OFF,
    ];
    public const PAYMENT_METHOD = [
        'card' => PaymentMethod::TYPE_CARD,
        'cash' => PaymentMethod::TYPE_CASH,
        'bank' => PaymentMethod::TYPE_BANK,
        'paypay' => PaymentMethod::TYPE_PAYPAY,
        'applepay' => PaymentMethod::TYPE_APPLE_PAY,
        'aupay' => PaymentMethod::TYPE_AU_PAY,
    ];

    /**
     * @inheritDoc
     */
    protected function buildOptionParser(ConsoleOptionParser $parser): ConsoleOptionParser
    {
        $parser = parent::buildOptionParser($parser);

        $this->addClientOption($parser);
        $parser->addOption('using', [
            'name' => 'using',
            'choices' => array_keys(static::PAYMENT_USE_FLG),
        ]);
        $parser->addOption('payment-method', [
            'name' => 'payment-method',
        ]);

        return $parser;
    }

    /**
     * @inheritDoc
     */
    public function execute(Arguments $args, ConsoleIo $io)
    {
        $this->outputStartMessage();

        if (!$this->checkClientOption()) {
            $this->errorExit('UsingPayment Error: invalid client.');
        }

        /** @var \App\Model\Table\SystemSettingsTable $systemSettingsTable */
        $systemSettingsTable = $this->getTableLocator()->get('SystemSettings');

        if (!isset(static::PAYMENT_USE_FLG[$args->getOption('using')])) {
            $this->errorExit('UsingPayment Error: invlid using.');
        }
        $paymentUseFlg = static::PAYMENT_USE_FLG[$args->getOption('using')];

        $paymentMethod = [];
        foreach ((array)preg_split('/,/', (string)$args->getOption('payment-method')) as $value) {
            $value = strtolower((string)$value);
            if (!isset(static::PAYMENT_METHOD[$value])) {
                $this->errorExit('UsingPayment Error: invlid payment method.');
            }
            $paymentMethod[] = static::PAYMENT_METHOD[$value];
        }

        $systemSettingsTable->updatePayment($paymentUseFlg, $paymentMethod);

        $this->outputEndMessage();
    }
}
