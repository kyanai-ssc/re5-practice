<?php
declare(strict_types=1);

namespace App\Model\InputType\Item\Type;

use Cake\ORM\Query;
use Cake\Validation\Validator;

/**
 * SearchDisplay interface.
 */
interface SearchDisplayInterface
{
    /**
     * 検索条件のキー名を取得
     *
     * @return string キー名
     */
    public function getSearchInputKey();

    /**
     * 検索時のバリデータを構築
     *
     * @param \Cake\Validation\Validator $validator バリデータ
     * @return \Cake\Validation\Validator バリデータ
     */
    public function buildSearchValidator(Validator $validator);

    /**
     * 検索条件を設定
     *
     * @param \Cake\ORM\Query $query クエリ
     * @param array $inputs 入力値
     * @return \Cake\ORM\Query クエリ
     */
    public function buildSearchQuery(Query $query, array $inputs);
}
