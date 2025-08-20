<?php
declare(strict_types=1);

namespace App\Command\Setting;

use App\Command\Command;
use App\Model\Entity\PaymentSetting;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;
use Cake\Utility\Hash;

/**
 * Class PaymentSettingCommand
 */
class PaymentSettingCommand extends Command
{
    public const SERVICE = [
        'gmo' => PaymentSetting::PAYMENT_SERVICE_GMO,
        'sb' => PaymentSetting::PAYMENT_SERVICE_SB,
    ];
    public const ENVIRONMENT = [
        'production' => PaymentSetting::ENVIRONMENT_PRODUCTION,
        'staging' => PaymentSetting::ENVIRONMENT_STAGING,
    ];
    public const JOB_CODE = [
        'immediate' => PaymentSetting::JOB_CODE_IMMEDIATE,
        'provisional' => PaymentSetting::JOB_CODE_PROVISIONAL,
    ];
    public const CARD_BRAND = [
        'jcb' => PaymentSetting::CARD_BLAND_JCB,
        'visa' => PaymentSetting::CARD_BLAND_VISA,
        'mastercard' => PaymentSetting::CARD_BLAND_MASTERCARD,
        'americanexpress' => PaymentSetting::CARD_BLAND_AMERICAN_EXPRESS,
        'dinersclub' => PaymentSetting::CARD_BLAND_DINERS_CLUB,
    ];
    public const THREE_D_SECURE_FLG = [
        'on' => PaymentSetting::THREE_D_SECURE_FLG_ON,
        'off' => PaymentSetting::THREE_D_SECURE_FLG_OFF,
    ];

    protected const ENCRYPTED_FIELDS = [
        PaymentSetting::PAYMENT_SERVICE_GMO => [
            'shop_password',
        ],
        PaymentSetting::PAYMENT_SERVICE_SB => [
            'hash_key',
            'basic_auth_password',
            'encrypt_key',
            'encrypt_iv',
        ],
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
        $parser->addOption('environment', [
            'name' => 'environment',
            'choices' => array_keys(static::ENVIRONMENT),
        ]);
        $parser->addOption('prefix', [
            'name' => 'prefix',
        ]);
        $parser->addOption('card', [
            'name' => 'card',
        ]);
        $parser->addOption('3d-secure', [
            'name' => '3d-secure',
            'choices' => array_keys(static::THREE_D_SECURE_FLG),
            'default' => 'off',
        ]);

        $parser->addOption('shop-id', [
            'name' => 'shop-id',
        ]);
        $parser->addOption('job', [
            'name' => 'job',
            'choices' => array_keys(static::JOB_CODE),
        ]);

        $parser->addOption('merchant-id', [
            'name' => 'merchant-id',
        ]);
        $parser->addOption('service-id', [
            'name' => 'service-id',
        ]);
        $parser->addOption('basic-auth-id', [
            'name' => 'basic-auth-id',
        ]);

        foreach (array_unique(Hash::flatten(static::ENCRYPTED_FIELDS)) as $field) {
            $name = 'encrypted-' . str_replace('_', '-', $field);
            $parser->addOption($name, [
                'name' => $name,
            ]);
        }

        return $parser;
    }

    /**
     * @inheritDoc
     */
    public function execute(Arguments $args, ConsoleIo $io)
    {
        $this->outputStartMessage();

        if (!$this->checkClientOption()) {
            $this->errorExit('PaymentSetting Error: invalid client.');
        }

        /** @var \App\Model\Table\PaymentSettingsTable $paymentSettingsTable */
        $paymentSettingsTable = $this->getTableLocator()->get('PaymentSettings');

        if (!isset(static::SERVICE[$args->getOption('service')])) {
            $this->errorExit('PaymentSetting Error: invlid service.');
        }
        $service = static::SERVICE[$args->getOption('service')];

        if (!isset(static::ENVIRONMENT[$args->getOption('environment')])) {
            $this->errorExit('PaymentSetting Error: invlid environment.');
        }
        $environment = static::ENVIRONMENT[$args->getOption('environment')];

        $prefix = (string)$args->getOption('prefix');
        if ($prefix !== '' && preg_match('/^[\\-0-9A-Za-z]{1,20}$/', $prefix) !== 1) {
            $this->errorExit('PaymentSetting Error: invlid prefix.');
        }

        $cardBrand = [];
        foreach ((array)preg_split('/,/', (string)$args->getOption('card')) as $value) {
            $value = strtolower((string)$value);
            if (!isset(static::CARD_BRAND[$value])) {
                $this->errorExit('PaymentSetting Error: invlid card.');
            }
            $cardBrand[] = static::CARD_BRAND[$value];
        }

        // 決済代行会社ごとの項目
        $paymentSetting = null;
        $data = [
            'payment_service' => $service,
            'environment' => $environment,
            'card_brand' => $cardBrand,
        ];
        if ($service === PaymentSetting::PAYMENT_SERVICE_GMO) {
            $shopId = (string)$args->getOption('shop-id');
            if (preg_match('/^[\\x21-\\x7e]{1,1000}$/', $shopId) !== 1) {
                $this->errorExit('PaymentSetting Error: invlid shop id.');
            }

            if (!isset(static::JOB_CODE[$args->getOption('job')])) {
                $this->errorExit('PaymentSetting Error: invlid job.');
            }
            $jobCode = static::JOB_CODE[$args->getOption('job')];

            if (!isset(static::THREE_D_SECURE_FLG[$args->getOption('3d-secure')])) {
                $this->errorExit('PaymentSetting Error: invalid 3d-secure.');
            }
            $threeDSecureFlg = static::THREE_D_SECURE_FLG[$args->getOption('3d-secure')];

            $paymentSetting = $paymentSettingsTable->find('edit', [
                'inputs' => [
                    'payment_service' => $service,
                    'shop_id' => $shopId,
                ],
            ])->first();
            $data += [
                'shop_id' => $shopId,
                'order_id_prefix' => $prefix,
                'job_code' => $jobCode,
                'three_d_secure_flg' => $threeDSecureFlg,
            ];
        } elseif ($service === PaymentSetting::PAYMENT_SERVICE_SB) {
            $merchantId = (string)$args->getOption('merchant-id');
            if (preg_match('/^[\\x21-\\x7e]{1,1000}$/', $merchantId) !== 1) {
                $this->errorExit('PaymentSetting Error: invlid merchant id.');
            }
            $serviceId = (string)$args->getOption('service-id');
            if (preg_match('/^[\\x21-\\x7e]{1,1000}$/', $serviceId) !== 1) {
                $this->errorExit('PaymentSetting Error: invlid service id.');
            }

            $basicAuthId = (string)$args->getOption('basic-auth-id');
            if (preg_match('/^[\\x21-\\x7e]{1,1000}$/', $basicAuthId) !== 1) {
                $this->errorExit('PaymentSetting Error: invlid basic auth id.');
            }

            $paymentSetting = $paymentSettingsTable->find('edit', [
                'inputs' => [
                    'payment_service' => $service,
                    'merchant_id' => $merchantId,
                    'service_id' => $serviceId,
                ],
            ])->first();
            $data += [
                'merchant_id' => $merchantId,
                'service_id' => $serviceId,
                'cust_code_prefix' => $prefix,
                'basic_auth_id' => $basicAuthId,
            ];
        }

        if (!($paymentSetting instanceof PaymentSetting)) {
            $paymentSetting = $paymentSettingsTable->newEntity($data, ['validate' => false]);
        } else {
            $paymentSettingsTable->patchEntity($paymentSetting, $data, ['validate' => false]);
        }

        // 暗号化する項目
        foreach (static::ENCRYPTED_FIELDS[$service] as $field) {
            $displayName = str_replace('_', ' ', $field);
            $encrypted = (string)$args->getOption('encrypted-' . str_replace('_', '-', $field));
            if ($encrypted === '') {
                $io->out(sprintf('%s:', $displayName));
                $value = $io->ask(sprintf('%s:', $displayName));
            } else {
                if (preg_match('/^[\\x21-\\x7e]{1,10000}$/', $encrypted) !== 1) {
                    $this->errorExit(sprintf('PaymentSetting Error: invlid encrypted %s.', $displayName));
                }
                $value = $paymentSetting->decryptApiInfo($encrypted);
            }
            if (preg_match('/^[\\x21-\\x7e]{1,1000}$/', $value) !== 1) {
                $this->errorExit(sprintf('PaymentSetting Error: invlid %s.', $displayName));
            }

            $paymentSetting->set($field, $value);
        }

        if (!$paymentSettingsTable->save($paymentSetting)) {
            foreach (Hash::flatten($paymentSetting->getErrors()) as $error) {
                $this->outputErrorMessage('PaymentSetting Error: ' . $error);
                $this->errorExit();
            }
        }

        $this->outputEndMessage();
    }
}
