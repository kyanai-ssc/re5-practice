<?php
declare(strict_types=1);

namespace App\Command\Setting;

use App\Command\Command;
use App\Model\Entity\PaymentSetting;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;
use Cake\Datasource\EntityInterface;

/**
 * Class DeletePaymentSettingCommand
 */
class DeletePaymentSettingCommand extends Command
{
    public const SERVICE = [
        'gmo' => PaymentSetting::PAYMENT_SERVICE_GMO,
        'sb' => PaymentSetting::PAYMENT_SERVICE_SB,
    ];

    /**
     * @inheritDoc
     */
    protected function buildOptionParser(ConsoleOptionParser $parser): ConsoleOptionParser
    {
        $parser = parent::buildOptionParser($parser);

        $this->addClientOption($parser);

        $parser->addOption('service', [
            'name' => 'service',
            'choices' => array_keys(static::SERVICE),
        ]);

        $parser->addOption('shop-id', [
            'name' => 'shop-id',
        ]);

        $parser->addOption('merchant-id', [
            'name' => 'merchant-id',
        ]);
        $parser->addOption('service-id', [
            'name' => 'service-id',
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
            $this->errorExit('DeletePaymentSetting Error: invalid client.');
        }

        /** @var \App\Model\Table\PaymentSettingsTable $paymentSettingsTable */
        $paymentSettingsTable = $this->getTableLocator()->get('PaymentSettings');

        if (!isset(static::SERVICE[$args->getOption('service')])) {
            $this->errorExit('PaymentSetting Error: invlid service.');
        }
        $service = static::SERVICE[$args->getOption('service')];

        $paymentSetting = null;
        if ($service === PaymentSetting::PAYMENT_SERVICE_GMO) {
            $shopId = (string)$args->getOption('shop-id');
            if (preg_match('/^[\\x21-\\x7e]{1,1000}$/', $shopId) !== 1) {
                $this->errorExit('DeletePaymentSetting Error: invlid shop id.');
            }

            $paymentSetting = $paymentSettingsTable->find('delete', [
                'inputs' => [
                    'payment_service' => $service,
                    'shop_id' => $shopId,
                ],
            ])->first();
        } elseif ($service === PaymentSetting::PAYMENT_SERVICE_SB) {
            $merchantId = (string)$args->getOption('merchant-id');
            if (preg_match('/^[\\x21-\\x7e]{1,1000}$/', $merchantId) !== 1) {
                $this->errorExit('DeletePaymentSetting Error: invlid merchant id.');
            }
            $serviceId = (string)$args->getOption('service-id');
            if (preg_match('/^[\\x21-\\x7e]{1,1000}$/', $serviceId) !== 1) {
                $this->errorExit('DeletePaymentSetting Error: invlid service id.');
            }

            $paymentSetting = $paymentSettingsTable->find('edit', [
                'inputs' => [
                    'payment_service' => $service,
                    'merchant_id' => $merchantId,
                    'service_id' => $serviceId,
                ],
            ])->first();
        }

        if ($paymentSetting instanceof EntityInterface) {
            $paymentSettingsTable->deleteOrFail($paymentSetting);
        } else {
            $this->errorExit('DeletePaymentSetting Error: record not found.');
        }

        $this->outputEndMessage();
    }
}
