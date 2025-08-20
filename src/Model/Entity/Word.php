<?php
declare(strict_types=1);

namespace App\Model\Entity;

use App\Model\AppEntity;

/**
 * Word Entity
 *
 * @property int $id
 * @property int $type
 * @property string $category
 * @property int $code
 * @property string $translate_key
 * @property string $name
 * @property string|null $word
 * @property string|null $word_default
 * @property int $multiple_row_flg
 * @property int $admin_flg
 * @property \Cake\I18n\FrozenTime $created
 * @property \Cake\I18n\FrozenTime $modified
 */
class Word extends AppEntity
{
    /**
     * タイプ： 通常文言
     */
    public const TYPE_WORD = 1;

    /**
     * タイプ：エラー文言
     */
    public const TYPE_ERROR = 2;

    /**
     * カテゴリー：共通
     */
    public const CATEGORY_COMMON = 1;

    /**
     * カテゴリー：会員
     */
    public const CATEGORY_USER = 2;

    /**
     * カテゴリー：予約
     */
    public const CATEGORY_RESERVE = 3;

    /**
     * デフォルトフラグ：オン
     */
    public const WORD_DEFAULT_FLG_ON = 1;

    /**
     * デフォルトフラグ：オフ
     */
    public const WORD_DEFAULT_FLG_OFF = 0;

    /**
     * 複数行フラグ：オン
     */
    public const MULTIPLE_ROW_FLG_ON = 1;

    /**
     * 複数行フラグ：オフ
     */
    public const MULTIPLE_ROW_FLG_OFF = 0;

    /**
     * 管理側フラグ：オン
     */
    public const ADMIN_FLG_ON = 1;

    /**
     * 管理側フラグ：オフ
     */
    public const ADMIN_FLG_OFF = 0;

    /**
     * @inheritDoc
     */
    protected $_accessible = [
        'type' => false,
        'category' => false,
        'code' => false,
        'translate_key' => false,
        'name' => false,
        'word' => true,
        'word_default' => false,
        'multiple_row_flg' => false,
        'admin_flg' => false,
        'created' => false,
        'modified' => false,
    ];
}
