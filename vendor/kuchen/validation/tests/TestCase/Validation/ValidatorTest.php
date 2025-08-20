<?php

declare(strict_types=1);

namespace Kuchen\Validation\Test\TestCase\Validation;

use Cake\TestSuite\TestCase;
use Cake\Validation\RulesProvider;
use Kuchen\Validation\Test\Mock\ValidatorMockForm;
use Kuchen\Validation\Validation\Validation;
use Kuchen\Validation\Validation\Validator;
use ReflectionException;
use ReflectionProperty;

/**
 * Class ValidatorTest
 */
class ValidatorTest extends TestCase
{
    /**
     * 検証 Table::_processRules()
     */
    public function testValid(): void
    {
        $form = new ValidatorMockForm();
        $form->validate([
            'field1' => 'test',
            'field2' => '',
        ]);

        /** @var Validator $validator */
        $validator = $form->getValidator();
        self::assertTrue($validator->hasError('field1'));
        self::assertTrue($validator->hasError('field2'));
        self::assertFalse($validator->isValid('field1'));
        self::assertFalse($validator->isValid('field2'));

        $form = new ValidatorMockForm();
        $form->validate([
            'field1' => 'A',
            'field2' => 'B',
        ]);

        /** @var Validator $validator */
        $validator = $form->getValidator();
        self::assertFalse($validator->hasError('field1'));
        self::assertFalse($validator->hasError('field2'));
        self::assertTrue($validator->isValid('field1'));
        self::assertTrue($validator->isValid('field2'));

        // 前回のバリデーション結果をリセット
        $form = new ValidatorMockForm();
        $validator = $form->getValidator();
        $form->validate([
            'field1' => '',
            'field2' => '',
        ]);

        $form->getValidator()
            ->add('field1', [
                'test' => [
                    'rule' => function () use ($validator) {
                        self::assertArrayNotHasKey('field1', $validator->data);
                        self::assertArrayNotHasKey('field1', $validator->errors);

                        return false;
                    },
                ],
            ]);

        $form->validate([
            'field1' => '1',
            'field2' => '2',
        ]);
    }

    /**
     * Validator::getProvider()
     *
     * @throws ReflectionException
     */
    public function testGetProvider(): void
    {
        $form = new ValidatorMockForm();

        $provider = $form->getValidator()->getProvider('default');
        self::assertInstanceOf(RulesProvider::class, $provider);

        $property = new ReflectionProperty($provider, '_class');
        $property->setAccessible(true);

        $class = $property->getValue($provider);
        self::assertEquals(Validation::class, $class);

        self::assertNull($form->getValidator()->getProvider('INVALID_PROVIDER'));
    }
}
