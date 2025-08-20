<?php

declare(strict_types=1);

namespace Kuchen\Validation\Test\TestCase\Validation;

use Cake\TestSuite\TestCase;
use DateTime;
use Kuchen\Validation\Validation\Validation;
use stdClass;

/**
 * Class ValidationTest
 */
class ValidationTest extends TestCase
{
    /**
     * Validation::alphaNumeric()
     */
    public function testAlphaNumeric(): void
    {
        self::assertTrue(Validation::alphaNumeric('A'));
        self::assertTrue(Validation::alphaNumeric('0'));
        self::assertFalse(Validation::alphaNumeric('あ'));
        self::assertFalse(Validation::alphaNumeric('aあ'));
        self::assertFalse(Validation::alphaNumeric('🌀'));
    }

    /**
     * Validation::email()
     */
    public function testEmail(): void
    {
        self::assertTrue(Validation::email('sample@example.com'));
        self::assertFalse(Validation::email('sample'));
        self::assertFalse(Validation::email('sampleああ@example.com'));
        self::assertFalse(Validation::email('sample🌀@example.com'));
        self::assertFalse(Validation::email('ああ@example.com'));
        self::assertFalse(Validation::email('sample@あ.com'));
        self::assertFalse(Validation::email('sample@example.あ'));
    }

    /**
     * Validation::date()
     */
    public function testDate(): void
    {
        self::assertTrue(Validation::date('2017/10/10'));
        self::assertTrue(Validation::date(new DateTime('2017/10/10')));
        self::assertFalse(Validation::date(new stdClass()));
        self::assertTrue(Validation::date(['year' => '2017', 'month' => '10', 'day' => '10']));

        self::assertTrue(Validation::date('2017-10-10'));
        self::assertFalse(Validation::date('2017.10.10'));
        self::assertFalse(Validation::date('2017 10 10'));

        self::assertFalse(Validation::date('2017/10/10', 'ymd', '/^[0-9]+$/'));
    }
}
