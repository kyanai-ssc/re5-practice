<?php
declare(strict_types=1);

namespace App\Model\Entity;

use App\Model\AppEntity;

/**
 * AnalysisTag Entity
 *
 * @property int $id
 * @property int $type
 * @property string|null $contents
 * @property \Cake\I18n\FrozenTime $created
 * @property \Cake\I18n\FrozenTime $modified
 */
class AnalysisTag extends AppEntity
{
    /**
     * 設定箇所のタイプ：head開始タグ直後
     */
    public const TYPE_AFTER_HEAD_OPEN = 1;

    /**
     * 設定箇所のタイプ：head閉じタグ直前
     */
    public const TYPE_BEFORE_HEAD_CLOSE = 2;

    /**
     * 設定箇所のタイプ：body開始タグ直後
     */
    public const TYPE_AFTER_BODY_OPEN = 3;

    /**
     * 設定箇所のタイプ：body閉じタグ直前
     */
    public const TYPE_BEFORE_BODY_CLOSE = 4;

    /**
     * 設定箇所のタイプ：予約完了ページ
     */
    public const TYPE_RESERVATION_FINISH = 5;

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
