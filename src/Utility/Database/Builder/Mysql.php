<?php
declare(strict_types=1);

namespace App\Utility\Database\Builder;

use App\Utility\Database\AbstractBuilder;
use Cake\Core\Exception\CakeException;
use Cake\Database\Connection;
use Cake\Database\Expression\FunctionExpression;
use Cake\Database\Expression\QueryExpression;
use Cake\Database\Expression\UnaryExpression;
use Cake\Database\Schema\TableSchema;

/**
 * Mysql Class.
 */
class Mysql extends AbstractBuilder
{
    /**
     * @var array
     */
    protected $typeMap = [
        TableSchema::TYPE_BINARY => 'BLOB',
        TableSchema::TYPE_DATE => 'DATE',
        TableSchema::TYPE_TIME => 'TIME',
        TableSchema::TYPE_TIMESTAMP => 'TIMESTAMP',
        TableSchema::TYPE_TEXT => 'TEXT',
        TableSchema::TYPE_SMALLINTEGER => 'SMALLINT',
        TableSchema::TYPE_INTEGER => 'INTEGER',
        TableSchema::TYPE_BIGINTEGER => 'BIGINT',
    ];

    /**
     * date_format
     *
     * @param string $column フィールド名
     * @param string $format フォーマット
     * @param string $separate 区切り文字
     * @return mixed
     */
    public function dateFormat(string $column, string $format = 'ymd', $separate = '/')
    {
        if ($format === 'ymd') {
            $formatString = "'" . '%Y' . $separate . '%m' . $separate . '%d' . "'";
        } else {
            $formatString = "'" . '%Y' . $separate . '%m' . $separate . '%d' . "'";
        }

        $types = [
            $column => 'identifier',
            $formatString => 'literal',
        ];

        return new FunctionExpression('date_format', $types);
    }

    /**
     * @inheritDoc
     */
    public function expressionDateAdd(string $expression, string $value, string $unit)
    {
        $dateAdd = new QueryExpression();
        $dateAdd->add(new FunctionExpression('DATE_ADD', [
            $expression => 'literal',
            $value => 'literal',
            $unit => 'string',
        ]));

        return $dateAdd;
    }

    /**
     * @inheritDoc
     */
    public function isoDayOfWeek(string $expression)
    {
        $dayOfWeek = new FunctionExpression('WEEKDAY', [
            $expression => 'literal',
        ]);

        return $dayOfWeek;
    }

    /**
     * @inheritDoc
     */
    public function cast(string $expression, string $type)
    {
        $cast = new FunctionExpression('CAST', [
            new UnaryExpression('AS ' . $this->typeMap[$type], $expression, UnaryExpression::POSTFIX),
        ]);

        return $cast;
    }

    /**
     * @inheritDoc
     */
    public function jsonValue(string $expression, string $path)
    {
        $jsonValue = new FunctionExpression('JSON_VALUE', [
            $expression => 'literal',
            '$.' . $path => 'string',
        ]);

        return $jsonValue;
    }

    /**
     * @inheritDoc
     */
    public function jsonArrayContains(string $expression, string $value)
    {
        $jsonContains = new QueryExpression(new FunctionExpression('JSON_CONTAINS', [
            $expression => 'literal',
            $value => 'string',
        ]));

        return $jsonContains;
    }

    /**
     * @inheritDoc
     */
    public function jsonObjectAgg(string $key, string $value)
    {
        throw new CakeException();
    }

    /**
     * @inheritDoc
     */
    public function createJson(array $objectValue)
    {
        throw new CakeException();
    }

    /**
     * @inheritDoc
     */
    public function tryLock(int $type, int $code)
    {
        throw new CakeException();
    }

    /**
     * @inheritDoc
     */
    public function getLock(int $type, int $code)
    {
        throw new CakeException();
    }

    /**
     * @inheritDoc
     */
    public function releaseLock(int $type, int $code)
    {
        throw new CakeException();
    }

    /**
     * @inheritDoc
     */
    public function setSequenceValue(string $sequence, string $value, bool $isCalled = true)
    {
        throw new CakeException();
    }

    /**
     * @inheritDoc
     */
    public function analyzeTable(string $table, Connection $connection)
    {
        throw new CakeException();
    }
}
