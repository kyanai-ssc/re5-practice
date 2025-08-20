<?php

declare(strict_types=1);

namespace Kuchen\Validation\Test\TestCase\Form;

use Cake\TestSuite\TestCase;
use Kuchen\Validation\Test\Mock\MockForm;
use Kuchen\Validation\Validation\Validator;

/**
 * Class FormTest
 */
class FormTest extends TestCase
{
    /**
     * Form::validator()
     */
    public function testValidator(): void
    {
        $form = new MockForm();

        self::assertInstanceOf(Validator::class, $form->getValidator());
    }

    /**
     * Form::validate()
     */
    public function testValidate(): void
    {
        $form = new MockForm();
        $form->validate([
            'field1' => 'data1',
            'field2' => 'data2',
            'field3' => 'data3',
        ]);

        $expected = [
            'field1' => 'data1',
            'field2' => 'data2',
        ];
        self::assertEquals($expected, $form->getData());
        self::assertEquals($expected['field1'], $form->getData('field1'));

        $form->validate([
            'field1' => 'data1',
        ]);
        $expected = [
            'field1' => 'data1',
        ];
        self::assertEquals($expected, $form->getData());
    }

    /**
     * 入力値未設定でもデータ取得ができる
     */
    public function testDataInitialValue(): void
    {
        $form = new MockForm();
        self::assertNull($form->getData('field1'));
    }
}
