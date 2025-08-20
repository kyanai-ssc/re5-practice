<?php
declare(strict_types=1);

namespace App\Utility\Database\Builder;

use App\Utility\Database\AbstractBuilder;
use Cake\Database\Connection;
use Cake\Database\Expression\ComparisonExpression;
use Cake\Database\Expression\FunctionExpression;
use Cake\Database\Expression\QueryExpression;
use Cake\Database\Expression\UnaryExpression;
use Cake\Database\Query;
use Cake\Database\Schema\TableSchema;

/**
 * Postgres Class.
 */
class Postgres extends AbstractBuilder
{
    /**
     * @var array
     */
    protected $typeMap = [
        TableSchema::TYPE_BINARY => 'BYTEA',
        TableSchema::TYPE_DATE => 'DATE',
        TableSchema::TYPE_TIME => 'TIME',
        TableSchema::TYPE_TIMESTAMP => 'TIMESTAMP',
        TableSchema::TYPE_TEXT => 'TEXT',
        TableSchema::TYPE_SMALLINTEGER => 'SMALLINT',
        TableSchema::TYPE_INTEGER => 'INTEGER',
        TableSchema::TYPE_BIGINTEGER => 'BIGINT',
        TableSchema::TYPE_JSON => 'JSON',
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
            $formatString = "'" . 'yyyy' . $separate . 'mm' . $separate . 'dd' . "'";
        } else {
            $formatString = "'" . 'yyyy' . $separate . 'mm' . $separate . 'dd' . "'";
        }

        $types = [
            $column => 'identifier',
            $formatString => 'literal',
        ];

        return new FunctionExpression('to_char', $types);
    }

    /**
     * @inheritDoc
     */
    public function expressionDateAdd(string $expression, string $value, string $unit)
    {
        $dateAdd = new QueryExpression();
        $dateAdd->add($expression . ' + CAST(' . $value . ' || ' . "'" . $unit . "'" . ' AS INTERVAL)');

        return $dateAdd;
    }

    /**
     * @inheritDoc
     */
    public function isoDayOfWeek(string $expression)
    {
        $dayOfWeek = new FunctionExpression('EXTRACT', [
            'ISODOW FROM ' . $expression => 'literal',
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
        $jsonValue = new FunctionExpression('JSON_EXTRACT_PATH_TEXT', [
            $this->cast($expression, TableSchema::TYPE_JSON),
            $path => 'literal',
        ]);

        return $jsonValue;
    }

    /**
     * @inheritDoc
     */
    public function jsonArrayContains(string $expression, string $value)
    {
        $jsonContains = new QueryExpression([
            function ($queryExpression) use ($expression, $value) {
                $field = new FunctionExpression('CAST', [
                    new UnaryExpression('AS JSONB', $expression, UnaryExpression::POSTFIX),
                ]);
                $queryExpression->add(new ComparisonExpression($field, $value, 'string', '@>'));

                return $queryExpression;
            },
        ]);

        return $jsonContains;
    }

    /**
     * @inheritDoc
     */
    public function jsonObjectAgg(string $key, string $value)
    {
        $jsonObjectAgg = new FunctionExpression('JSON_OBJECT_AGG', [
            $key => 'literal',
            $value => 'literal',
        ]);

        return $jsonObjectAgg;
    }

    /**
     * @inheritDoc
     */
    public function createJson(array $objectValue)
    {
        $jsonBuild = '';
        foreach ($objectValue as $key => $object) {
            $jsonBuild .= "'" . $key . "'," . $object . ',';
        }
        $jsonBuild = substr($jsonBuild, 0, -1);

        $jsonSql = new FunctionExpression('JSON_AGG', [
            new FunctionExpression(
                'JSON_BUILD_OBJECT',
                [
                    $jsonBuild => 'literal',
                ]
            ),
        ]);

        return $jsonSql;
    }

    /**
     * @inheritDoc
     */
    public function tryLock(int $type, int $code)
    {
        $functionExpression = new FunctionExpression('PG_TRY_ADVISORY_LOCK', [$type, $code]);

        return $functionExpression;
    }

    /**
     * @inheritDoc
     */
    public function getLock(int $type, int $code)
    {
        $functionExpression = new FunctionExpression('PG_ADVISORY_LOCK', [$type, $code]);

        return $functionExpression;
    }

    /**
     * @inheritDoc
     */
    public function releaseLock(int $type, int $code)
    {
        $functionExpression = new FunctionExpression('PG_ADVISORY_UNLOCK', [$type, $code]);

        return $functionExpression;
    }

    /**
     * @inheritDoc
     */
    public function setSequenceValue(string $sequence, string $value, bool $isCalled = true)
    {
        $isCalledValue = 'TRUE';
        if (!$isCalled) {
            $isCalledValue = 'FALSE';
        }
        $functionExpression = new FunctionExpression('SETVAL', [
            $sequence,
            '(' . $value . ')' => 'literal',
            $isCalledValue => 'literal',
        ]);

        return $functionExpression;
    }

    /**
     * @inheritDoc
     */
    public function analyzeTable(string $table, Connection $connection)
    {
        $query = new Query($connection);
        $query->epilog('VACUUM (FULL, ANALYZE) ' . $table);
        $query->execute();
    }
}
