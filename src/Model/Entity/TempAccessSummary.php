<?php
declare(strict_types=1);

namespace App\Model\Entity;

use App\Model\AppEntity;

/**
 * TempAccessSummary Entity
 *
 * @property int $id
 * @property \Cake\I18n\FrozenDate $access_date
 * @property int $calendar
 * @property \Cake\I18n\FrozenTime $created
 * @property \Cake\I18n\FrozenTime $modified
 */
class TempAccessSummary extends AppEntity
{
    /**
     * アクセスログ：ファイル名
     */
    public const ACCESS_LOG_FILENAME = 'access_log';

    /**
     * アクセスログ：カウントする画面URL
     */
    public const ACCESS_LOG_COUNT_PAGE = '/reservations/calendar';

    /**
     * アクセスログ：クライアントの配列キー
     */
    public const ACCESS_LOG_CLIENT = 0;

    /**
     * アクセスログ：URLの配列キー
     */
    public const ACCESS_LOG_CALENDAR = 5;

    /**
     * アクセスログ：ステータスコードの配列キー
     */
    public const ACCESS_LOG_STATUS = 6;

    /**
     * @inheritDoc
     */
    protected $_accessible = [
        'access_date' => true,
        'calendar' => true,
        'created' => false,
        'modified' => false,
    ];
}
