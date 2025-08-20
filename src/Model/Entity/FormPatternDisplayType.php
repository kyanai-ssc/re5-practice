<?php
declare(strict_types=1);

namespace App\Model\Entity;

use App\Model\AppEntity;
use App\Model\Table\ReservationsTable;
use Cake\Utility\Hash;

/**
 * FormPatternDisplayType Entity
 *
 * @property int $id
 * @property int $form_pattern_id
 * @property int $form_item_id
 * @property int $display_type
 * @property \Cake\I18n\FrozenTime $created
 * @property \Cake\I18n\FrozenTime $modified
 *
 * @property \App\Model\Entity\FormPattern $form_pattern
 * @property \App\Model\Entity\FormItem $form_item
 */
class FormPatternDisplayType extends AppEntity
{
    /**
     * 非表示
     */
    public const DISPLAY_TYPE_HIDE = 1;

    /**
     * 表示
     */
    public const DISPLAY_TYPE_DISPLAY = 2;

    /**
     * 管理者のみ表示
     */
    public const DISPLAY_TYPE_DISPLAY_ONLY_ADMIN = 3;

    /**
     * 会員登録しないで予約のみ表示
     */
    public const DISPLAY_TYPE_DISPLAY_ONLY_GUEST_RESERVE = 4;

    /**
     * 管理者のみ編集可能
     */
    public const DISPLAY_TYPE_DISPLAY_EDIT_ONLY_ADMIN = 5;

    /**
     * アプリに表示
     */
    public const APP_DISPLAY_FLG_OFF = 0;
    public const APP_DISPLAY_FLG_ON = 1;

    /**
     * 各項目ごとの選択肢を設定
     */
    public const DISPLAY_TYPE_SET_LIST = [
        'addition' => [
            self::DISPLAY_TYPE_HIDE,
            self::DISPLAY_TYPE_DISPLAY,
            self::DISPLAY_TYPE_DISPLAY_ONLY_ADMIN,
            self::DISPLAY_TYPE_DISPLAY_ONLY_GUEST_RESERVE,
            self::DISPLAY_TYPE_DISPLAY_EDIT_ONLY_ADMIN,
        ],
        'noAdminEdit' => [
            self::DISPLAY_TYPE_HIDE,
            self::DISPLAY_TYPE_DISPLAY,
            self::DISPLAY_TYPE_DISPLAY_ONLY_ADMIN,
            self::DISPLAY_TYPE_DISPLAY_ONLY_GUEST_RESERVE,
        ],
        'number' => [
            self::DISPLAY_TYPE_HIDE,
            self::DISPLAY_TYPE_DISPLAY,
        ],
        'displayOnly' => [
            self::DISPLAY_TYPE_DISPLAY,
        ],
        'onlyAdmin' => [
            self::DISPLAY_TYPE_DISPLAY_ONLY_ADMIN,
            self::DISPLAY_TYPE_DISPLAY_EDIT_ONLY_ADMIN,
        ],
        'displayAndOnlyAdmin' => [
            self::DISPLAY_TYPE_DISPLAY,
            self::DISPLAY_TYPE_DISPLAY_ONLY_ADMIN,
            self::DISPLAY_TYPE_DISPLAY_EDIT_ONLY_ADMIN,
        ],
    ];

    /**
     * アプリに表示する選択肢
     */
    public const APP_DISPLAY_FLG_LIST = [
        self::APP_DISPLAY_FLG_OFF,
        self::APP_DISPLAY_FLG_ON,
    ];

    /**
     * @inheritDoc
     */
    protected $_accessible = [
        'form_pattern_id' => false,
        'form_item_id' => true,
        'display_type' => true,
        'app_display_flg' => true,
        'created' => false,
        'modified' => false,
        'form_pattern' => false,
        'form_item' => false,
    ];

    /**
     * 表示タイプを判定
     *
     * @param int $formType フォームタイプ
     * @param array $options オプション引数
     * @return array
     */
    public function checkDisplayType(int $formType, array $options = [])
    {
        /** @var \App\Model\Table\UserAuthoritiesTable $userAuthoritiesTable */
        $userAuthoritiesTable = $this->getTableLocator()->get('UserAuthorities');

        $userAuthorityId = Hash::get($options, 'userAuthorityId');
        $reservationId = Hash::get($options, 'reservationId');
        $reservationType = Hash::get($options, 'reservationType');
        $adminFlg = Hash::get($options, 'adminFlg', false);
        $isApp = Hash::get($options, 'isApp', false);

        $guestAuthorityId = $userAuthoritiesTable->getGuestAuthority()->get('id');

        $result = [
            'canDisplay' => false,
            'canInput' => false,
            'hideEmpty' => false,
            'hideDescription' => false,
        ];

        // アプリに表示
        if ($isApp) {
            if ((string)$this->get('app_display_flg') === ((string)static::APP_DISPLAY_FLG_ON)) {
                $result['canDisplay'] = true;
                $result['canInput'] = false;
            }

            return $result;
        }

        if ((string)$this->get('display_type') === ((string)static::DISPLAY_TYPE_DISPLAY)) {
            $result['canDisplay'] = true;
            $result['canInput'] = true;
        } elseif ((string)$this->get('display_type') === ((string)static::DISPLAY_TYPE_DISPLAY_ONLY_ADMIN)) {
            if ($adminFlg) {
                $result['canDisplay'] = true;
                $result['canInput'] = true;
            }
        } elseif ((string)$this->get('display_type') === ((string)static::DISPLAY_TYPE_DISPLAY_ONLY_GUEST_RESERVE)) {
            if (
                ((string)$reservationType) === ((string)ReservationsTable::RESERVATION_TYPE_NON_USER)
                || isset($reservationId) && ((string)$userAuthorityId) === ((string)$guestAuthorityId)
            ) {
                $result['canDisplay'] = true;
                $result['canInput'] = true;
            }
        } elseif ((string)$this->get('display_type') === ((string)static::DISPLAY_TYPE_DISPLAY_EDIT_ONLY_ADMIN)) {
            $result['canDisplay'] = true;
            if ($adminFlg) {
                $result['canInput'] = true;
            } else {
                $result['hideEmpty'] = true;
            }
        }

        if (((string)$formType) === ((string)FormGroup::FORM_TYPE_USER)) {
            if (!isset($reservationId)) {
                if (((string)$reservationType) === ((string)ReservationsTable::RESERVATION_TYPE_EXISTING_USER)) {
                    $result['canInput'] = false;
                    $result['hideDescription'] = true;
                }
            } else {
                if (((string)$userAuthorityId) !== ((string)$guestAuthorityId) || !$adminFlg) {
                    $result['canInput'] = false;
                    $result['hideDescription'] = true;
                }
            }
        }

        return $result;
    }
}
