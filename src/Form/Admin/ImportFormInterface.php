<?php
declare(strict_types=1);

namespace App\Form\Admin;

/**
 * ImportForm interface.
 */
interface ImportFormInterface
{
    /**
     * CSVのヘッダーを設定
     *
     * @param array $header ヘッダー
     * @return void
     */
    public function setCsvHeader(array $header);

    /**
     * CSVのヘッダをチェック
     *
     * @return bool
     */
    public function checkCsvHeader();

    /**
     * CSVのデータを設定
     *
     * @param array $data データ
     * @return void
     */
    public function setCsvData(array $data);

    /**
     * エンティティを取得
     *
     * @return \Cake\Datasource\EntityInterface|null
     */
    public function getEntity();

    /**
     * エンティティの新規作成を判定
     *
     * @return bool
     */
    public function isNewEntity();

    /**
     * エラーメッセージを取得
     *
     * @return array
     */
    public function getMessages();

    /**
     * 登録後に出力するメッセージを取得
     *
     * @return array
     */
    public function getInfoMessages();

    /**
     * 文字コード等の不正なデータ
     *
     * @return bool
     */
    public function isInvalidData();
}
