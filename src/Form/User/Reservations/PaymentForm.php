<?php
declare(strict_types=1);

namespace App\Form\User\Reservations;

use App\Form\AppForm;
use App\Locale\Message;
use App\Model\Entity\PaymentMethod;
use App\Utility\StringUtility;
use Cake\Core\Configure;
use Cake\Core\Exception\CakeException;
use Cake\Form\Schema;
use Cake\Utility\Hash;
use Cake\Validation\Validator;
use Kuchen\Validation\Validation\Validator as KuchenValidator;

/**
 * 決済フォーム
 */
class PaymentForm extends AppForm
{
    public const PAYMENT_TOKEN_MAX = 1000;
    public const COUNTRY_CODE_MAX = 3;
    public const PHONE_NUMBER_MAX = 15;

    /**
     * @var int|null
     */
    protected $paymentTokenNumber = null;

    /**
     * @inheritDoc
     */
    protected function _buildSchema(Schema $schema): Schema
    {
        /** @var \App\Model\Table\PaymentSettingsTable $paymentSettingsTable */
        $paymentSettingsTable = $this->getTableLocator()->get('PaymentSettings');

        $schema
            ->addField('payment_method_id', 'string')
            ->addField('payment_tokens', 'array');

        $paymentSetting = $paymentSettingsTable->getData();
        if (isset($paymentSetting) && $paymentSetting->requiresKycGmo()) {
            $schema
                ->addField('payment_email_address', 'string')
                ->addField('payment_phone_type', 'string')
                ->addField('payment_phone_number', 'string');
        }

        return $schema;
    }

    /**
     * @inheritDoc
     */
    public function validationDefault(Validator $validator): Validator
    {
        /** @var \App\Model\Table\PaymentSettingsTable $paymentSettingsTable */
        $paymentSettingsTable = $this->getTableLocator()->get('PaymentSettings');

        $validator
            ->requirePresence('payment_method_id', true, __(Message::ERROR_NOT_EMPTY_SELECT))
            ->allowEmptyString('payment_method_id', __(Message::ERROR_NOT_EMPTY_SELECT), false)
            ->add('payment_method_id', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'inList' => [
                    'rule' => ['inList', array_keys($this->getFieldValueOptions('paymentMethodId'))],
                    'last' => true,
                    'message' => __(Message::ERROR_IN_LIST),
                ],
            ]);

        $paymentTokensIsRequired = function ($context) use ($validator) {
            /** @var \App\Model\Table\PaymentMethodsTable $paymentMethodsTable */
            $paymentMethodsTable = $this->getTableLocator()->get('PaymentMethods');

            if (!($validator instanceof KuchenValidator)) {
                throw new CakeException();
            }

            return $validator->isValid('payment_method_id')
                && $paymentMethodsTable->isApiPayment((int)$context['data']['payment_method_id']);
        };

        $tokensValidator = new KuchenValidator();
        $validator
            ->requirePresence('payment_tokens', $paymentTokensIsRequired, __(Message::ERROR_NOT_EMPTY))
            ->allowEmptyString(
                'payment_tokens',
                __(Message::ERROR_NOT_EMPTY),
                function ($context) use ($paymentTokensIsRequired) {
                    return !call_user_func($paymentTokensIsRequired, $context);
                }
            )
            ->add('payment_tokens', [
                'isArray' => [
                    'rule' => ['isArray'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
            ])
            ->addNested('payment_tokens', $tokensValidator);

        $paymentSetting = $paymentSettingsTable->getData();
        for ($i = 0; $i < $this->paymentTokenNumber; ++$i) {
            $field = 'value_' . $i;
            $tokenValidator = new KuchenValidator();
            $tokensValidator
                ->requirePresence($field, true, __(Message::ERROR_NOT_EMPTY))
                ->allowEmptyString($field, __(Message::ERROR_NOT_EMPTY), false)
                ->add($field, [
                    'isArray' => [
                        'rule' => ['isArray'],
                        'last' => true,
                        'message' => __(Message::ERROR_INVALID_VALUE),
                    ],
                ])
                ->addNested($field, $tokenValidator);

            $tokenValidator
                ->requirePresence('token', true, __(Message::ERROR_NOT_EMPTY))
                ->allowEmptyString('token', __(Message::ERROR_NOT_EMPTY), false)
                ->add('token', [
                    'isScalar' => [
                        'rule' => ['isScalar'],
                        'last' => true,
                        'message' => __(Message::ERROR_INVALID_VALUE),
                    ],
                    'lengthBetween' => [
                        'rule' => ['lengthBetween', 1, static::PAYMENT_TOKEN_MAX],
                        'last' => true,
                        'message' => __(Message::ERROR_INVALID_VALUE),
                    ],
                ]);

            if (isset($paymentSetting) && $paymentSetting->isPaymentServiceSb()) {
                $tokenValidator
                    ->requirePresence('token_key', true, __(Message::ERROR_NOT_EMPTY))
                    ->allowEmptyString('token_key', __(Message::ERROR_NOT_EMPTY), false)
                    ->add('token_key', [
                        'isScalar' => [
                            'rule' => ['isScalar'],
                            'last' => true,
                            'message' => __(Message::ERROR_INVALID_VALUE),
                        ],
                        'lengthBetween' => [
                            'rule' => ['lengthBetween', 1, static::PAYMENT_TOKEN_MAX],
                            'last' => true,
                            'message' => __(Message::ERROR_INVALID_VALUE),
                        ],
                    ]);

                if ($paymentSetting->requiresKycSb()) {
                    $tokenValidator
                        ->requirePresence('tds2info_token', true, __(Message::ERROR_NOT_EMPTY))
                        ->allowEmptyString('tds2info_token', __(Message::ERROR_NOT_EMPTY), false)
                        ->add('tds2info_token', [
                            'isScalar' => [
                                'rule' => ['isScalar'],
                                'last' => true,
                                'message' => __(Message::ERROR_INVALID_VALUE),
                            ],
                            'lengthBetween' => [
                                'rule' => ['lengthBetween', 1, static::PAYMENT_TOKEN_MAX],
                                'last' => true,
                                'message' => __(Message::ERROR_INVALID_VALUE),
                            ],
                        ]);

                    $tokenValidator
                        ->requirePresence('tds2info_token_key', true, __(Message::ERROR_NOT_EMPTY))
                        ->allowEmptyString('tds2info_token_key', __(Message::ERROR_NOT_EMPTY), false)
                        ->add('tds2info_token_key', [
                            'isScalar' => [
                                'rule' => ['isScalar'],
                                'last' => true,
                                'message' => __(Message::ERROR_INVALID_VALUE),
                            ],
                            'lengthBetween' => [
                                'rule' => ['lengthBetween', 1, static::PAYMENT_TOKEN_MAX],
                                'last' => true,
                                'message' => __(Message::ERROR_INVALID_VALUE),
                            ],
                        ]);
                }
            }
        }

        $isKycItemsRequired = function ($context) use ($validator) {
            /** @var \App\Model\Table\PaymentMethodsTable $paymentMethodsTable */
            $paymentMethodsTable = $this->getTableLocator()->get('PaymentMethods');
            if (!($validator instanceof KuchenValidator)) {
                throw new CakeException();
            }

            if (!$validator->isValid('payment_method_id')) {
                return false;
            }
            $paymentMethodId = $context['data']['payment_method_id'];
            $paymentMethodType = $paymentMethodsTable->getPaymentMethodType((int)$paymentMethodId);
            if (((string)$paymentMethodType) !== ((string)PaymentMethod::TYPE_CARD)) {
                return false;
            }

            return true;
        };
        if (isset($paymentSetting) && $paymentSetting->requiresKycGmo()) {
            $errMessageKyc = __(Message::ERROR_PAYMENT_KYC, __('payment/emailAddress'), __('payment/phoneNumber'));
            $validator
                ->requirePresence('payment_email_address', $isKycItemsRequired, __(Message::ERROR_NOT_EMPTY))
                ->allowEmptyString(
                    'payment_email_address',
                    $errMessageKyc,
                    function ($context) use ($isKycItemsRequired) {
                        $emailAddress = $context['data']['payment_email_address'] ?? null;
                        $phoneNumber = $context['data']['payment_phone_number'] ?? null;

                        // クレジットカード選択時、メールアドレスと電話番号が両者未入力の場合falseを返す
                        return !(
                            call_user_func($isKycItemsRequired, $context)
                            && empty($emailAddress)
                            && empty($phoneNumber)
                        );
                    }
                )
                ->add('payment_email_address', [
                    'isScalar' => [
                        'rule' => ['isScalar'],
                        'last' => true,
                        'message' => __(Message::ERROR_INVALID_VALUE),
                    ],
                    'isRfc5322' => [
                        'rule' => ['isRfc5322'],
                        'last' => true,
                        'message' => __(Message::ERROR_MAIL_ADDRESS),
                    ],
                ]);

            $validator
                ->requirePresence('payment_phone_type', $isKycItemsRequired, __(Message::ERROR_NOT_EMPTY_SELECT))
                ->allowEmptyString(
                    'payment_phone_type',
                    __(Message::ERROR_NOT_EMPTY_SELECT),
                    function ($context) use ($isKycItemsRequired) {
                        $phoneNumber = $context['data']['payment_phone_number'] ?? null;

                        // クレジットカード選択 かつ 電話番号が入力されている場合falseを返す
                        return !(call_user_func($isKycItemsRequired, $context) && !empty($phoneNumber));
                    }
                )
                ->add('payment_phone_type', [
                    'isScalar' => [
                        'rule' => ['isScalar'],
                        'last' => true,
                        'message' => __(Message::ERROR_INVALID_VALUE),
                    ],
                    'inList' => [
                        'rule' => [
                            'inList',
                            array_keys($this->getFieldValueOptions('paymentPhoneType')),
                        ],
                        'last' => true,
                        'message' => __(Message::ERROR_IN_LIST),
                    ],
                ]);

            $validator
                ->requirePresence('payment_phone_number', $isKycItemsRequired, __(Message::ERROR_NOT_EMPTY))
                ->allowEmptyString(
                    'payment_phone_number',
                    $errMessageKyc,
                    function ($context) use ($isKycItemsRequired) {
                        $emailAddress = $context['data']['payment_email_address'] ?? null;
                        $phoneNumber = $context['data']['payment_phone_number'] ?? null;

                        // クレジットカード選択時、メールアドレスと電話番号が両者未入力の場合falseを返す
                        return !(
                            call_user_func($isKycItemsRequired, $context)
                            && empty($emailAddress)
                            && empty($phoneNumber)
                        );
                    }
                )
                ->add('payment_phone_number', [
                    'isScalar' => [
                        'rule' => ['isScalar'],
                        'last' => true,
                        'message' => __(Message::ERROR_INVALID_VALUE),
                    ],
                    'custom' => [
                        'rule' => ['custom', '/^[0-9\+\s-]+$/'],
                        'last' => true,
                        'message' => __(Message::ERROR_INVALID_PHONE_NUMBER),
                    ],
                    'length' => [
                        'rule' => function ($value) {
                            $formatValues = StringUtility::formatCountryCodeAndPhoneNumber($value);

                            // 国コードチェック
                            $countryCode = Hash::get($formatValues, 'countryCode');
                            if (empty($countryCode) || mb_strlen((string)$countryCode) > static::COUNTRY_CODE_MAX) {
                                return false;
                            }
                            // 電話番号チェック
                            $number = Hash::get($formatValues, 'number');
                            if (empty($number) || mb_strlen((string)$number) > static::PHONE_NUMBER_MAX) {
                                return false;
                            }

                            return true;
                        },
                        'last' => true,
                        'message' => __(Message::ERROR_INVALID_PHONE_NUMBER),
                    ],
                ]);
        }

        return $validator;
    }

    /**
     * @inheritDoc
     */
    protected function buildFieldValueOptions()
    {
        /** @var \App\Model\Table\PaymentMethodsTable $paymentMethodsTable */
        $paymentMethodsTable = $this->getTableLocator()->get('PaymentMethods');

        $fieldValueOptions = [
            'paymentMethodId' => $paymentMethodsTable->getDisplayValueOptions(),
        ];

        $fieldValueOptions['paymentPhoneType'] = [];
        $paymentPhoneTypes = array_keys(Configure::readOrFail('Master.payment.credit.phoneType'));
        foreach ($paymentPhoneTypes as $paymentPhoneType) {
            $fieldValueOptions['paymentPhoneType'][$paymentPhoneType] = __(Configure::readOrFail(
                'Master.payment.credit.phoneType.' . $paymentPhoneType
            ));
        }

        return $fieldValueOptions;
    }

    /**
     * 決済トークンの数を設定
     *
     * @param int $paymentTokenNumber トークンの数
     * @return void
     */
    public function setPaymentTokenNumber(int $paymentTokenNumber)
    {
        $this->paymentTokenNumber = (int)$paymentTokenNumber;
    }

    /**
     * リンク型決済の判定
     *
     * @return bool
     */
    public function isLinkPayment()
    {
        /** @var \App\Model\Table\PaymentMethodsTable $paymentMethodsTable */
        $paymentMethodsTable = $this->getTableLocator()->get('PaymentMethods');

        return $paymentMethodsTable->isLinkPayment((int)$this->getData('payment_method_id'));
    }
}
