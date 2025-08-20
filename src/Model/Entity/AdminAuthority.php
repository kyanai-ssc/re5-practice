<?php
declare(strict_types=1);

namespace App\Model\Entity;

use App\Model\AppEntity;

/**
 * AdminAuthority Entity
 *
 * @property int $id
 * @property string $name
 * @property string|null $access_setting
 * @property string|null $access_operator
 * @property int $default_flg
 * @property \Cake\I18n\FrozenTime $created
 * @property \Cake\I18n\FrozenTime $modified
 */
class AdminAuthority extends AppEntity
{
    /**
     * デフォルトID
     */
    public const DEFAULT_ID = 1;

    /**
     * デフォルトフラグ:オフ
     */
    public const DEFAULT_FLG_OFF = 0;

    /**
     * 検索：全ての選択肢のキー
     */
    public const SELECT_ALL = -1;

    /**
     * @inheritDoc
     */
    protected $_accessible = [
        'name' => true,
        'access_setting' => true,
        'access_operator' => true,
        'default_flg' => false,
        'created' => false,
        'modified' => false,
    ];

    /**
     * 削除可否
     *
     * @return bool
     */
    public function cnaDelete()
    {
        if ($this->get('default_flg') === static::DEFAULT_FLG_OFF) {

            /** @var \App\Model\Table\AdminsTable $adminsTable */
            $adminsTable = $this->getTableLocator()->get('Admins');

            $count = $adminsTable->find('countAuthorityId', [
                'inputs' => ['admin_authority_id' => $this->get('id')],
            ])->count();

            if ($count === 0) {
                return true;
            }
        }

        return false;
    }
}
