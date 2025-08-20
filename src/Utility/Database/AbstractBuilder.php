<?php
declare(strict_types=1);

namespace App\Utility\Database;

use Cake\Core\Exception\CakeException;
use Cake\Database\Connection;

/**
 * Builder abstract Class.
 */
abstract class AbstractBuilder
{
    /**
     * LIKE検索のパラメータをエスケープする
     *
     * @param string $parameter パラメータ
     * @return string エスケープ後のパラメータ
     */
    public function escapeLike(string $parameter)
    {
        $targetList = ['%', '_'];
        $escape = '\\';

        $result = preg_replace('/(' . preg_quote($escape, '/') . ')/', '$1$1', $parameter);
        if (!is_string($result)) {
            throw new CakeException();
        }

        $replacement = preg_replace('/(\\\\)/', '$1$1', $escape) . '$1';
        foreach ($targetList as $target) {
            $result = preg_replace('/(' . preg_quote($target, '/') . ')/', $replacement, $result);
            if (!is_string($result)) {
                throw new CakeException();
            }
        }

        return $result;
    }

    /**
     * date_format
     *
     * @param string $column フィールド名
     * @param string $format フォーマット
     * @param string $separate 区切り文字
     * @return mixed
     */
    abstract public function dateFormat(string $column, string $format = 'ymd', $separate = '/');

    /**
     * SQL式で日付を加算
     *
     * @param string $expression 日付のSQL式
     * @param string $value 加算するSQL式
     * @param string $unit 加算する単位
     * @return \Cake\Database\Expression\QueryExpression SQL式
     */
    abstract public function expressionDateAdd(string $expression, string $value, string $unit);

    /**
     * ISO 8601の形式で日付から曜日番号を抽出する
     *
     * @param string $expression 日付のSQL式
     * @return \Cake\Database\Expression\FunctionExpression SQL式
     */
    abstract public function isoDayOfWeek(string $expression);

    /**
     * キャスト式を生成する
     *
     * @param string $expression キャストする値
     * @param string $type 型
     * @return \Cake\Database\Expression\FunctionExpression SQL式
     */
    abstract public function cast(string $expression, string $type);

    /**
     * JSON文字列から値を取得する
     *
     * @param string $expression JSON文字列の式
     * @param string $path パス
     * @return \Cake\Database\Expression\FunctionExpression SQL式
     */
    abstract public function jsonValue(string $expression, string $path);

    /**
     * JSON配列から値を検索する
     *
     * @param string $expression JSON配列の式
     * @param string $value 値
     * @return \Cake\Database\Expression\QueryExpression SQL式
     */
    abstract public function jsonArrayContains(string $expression, string $value);

    /**
     * 行をJSONへ集約
     *
     * @param string $key キー
     * @param string $value 値
     * @return \Cake\Database\Expression\FunctionExpression SQL式
     */
    abstract public function jsonObjectAgg(string $key, string $value);

    /**
     * テーブルデータをJSONに変換する
     *
     * @param array $objectValue JSON化する配列[エイリアス：カラム名]
     * @return \Cake\Database\Expression\FunctionExpression SQL式
     */
    abstract public function createJson(array $objectValue);

    /**
     * ロックを試行する
     *
     * @param int $type ロック種別
     * @param int $code ロックコード
     * @return \Cake\Database\Expression\FunctionExpression SQL式
     */
    abstract public function tryLock(int $type, int $code);

    /**
     * ロックを取得する
     *
     * @param int $type ロック種別
     * @param int $code ロックコード
     * @return \Cake\Database\Expression\FunctionExpression SQL式
     */
    abstract public function getLock(int $type, int $code);

    /**
     * ロックを解放する
     *
     * @param int $type ロック種別
     * @param int $code ロックコード
     * @return \Cake\Database\Expression\FunctionExpression SQL式
     */
    abstract public function releaseLock(int $type, int $code);

    /**
     * シーケンスの値を設定する
     *
     * @param string $sequence シーケンス名
     * @param string $value 値
     * @param bool $isCalled is_calledフィールド
     * @return \Cake\Database\Expression\FunctionExpression SQL式
     */
    abstract public function setSequenceValue(string $sequence, string $value, bool $isCalled = true);

    /**
     * 統計情報の更新を行う
     *
     * @param string $table テーブル名
     * @param \Cake\Database\Connection $connection DB接続
     * @return void
     */
    abstract public function analyzeTable(string $table, Connection $connection);
}
