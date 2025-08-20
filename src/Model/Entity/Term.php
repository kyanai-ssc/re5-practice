<?php
declare(strict_types=1);

namespace App\Model\Entity;

use App\Model\AppEntity;

/**
 * Term Entity
 *
 * @property int $id
 * @property int $type
 * @property string|null $contents
 * @property \Cake\I18n\FrozenTime $created
 * @property \Cake\I18n\FrozenTime $modified
 */
class Term extends AppEntity
{
    /**
     * タイプ：会員規約
     */
    public const TYPE_TERMS_TOP = 1;

    /**
     * タイプ：予約規約
     */
    public const TYPE_TERMS_RESERVE = 2;

    /**
     * タイプ：個人情報規約
     */
    public const TYPE_TERMS_USER = 3;

    /**
     * タイプ：特定商取引法(トップページ)
     */
    public const TYPE_SCTL_TOP = 4;

    /**
     * タイプ：特定商取引法(予約時)
     */
    public const TYPE_SCTL_RESERVE = 5;

    /**
     * @inheritDoc
     */
    protected $_accessible = [
        'type' => false,
        'contents' => true,
        'created' => false,
        'modified' => false,
    ];
}
