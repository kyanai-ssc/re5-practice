<?php
declare(strict_types=1);

namespace App\Model\Entity;

use App\Model\AppEntity;
use App\Utility\StringUtility;
use App\Utility\VideoMeeting\VideoMeetingFactory;
use App\Utility\VideoMeeting\ZoomApi;
use Cake\Core\Configure;
use Cake\Core\Exception\CakeException;

/**
 * ZoomConnectUser Entity
 *
 * @property int $id
 * @property string $name
 * @property string $refresh_token
 * @property string $access_token
 * @property \Cake\I18n\FrozenTime $at_expiration_timestamp
 * @property \Cake\I18n\FrozenTime $created
 * @property \Cake\I18n\FrozenTime $modified
 */
class ZoomConnectUser extends AppEntity
{
    /**
     * @inheritDoc
     */
    protected $_accessible = [
        'name' => true,
        'refresh_token' => false,
        'access_token' => false,
        'at_expiration_timestamp' => false,
        'created' => false,
        'modified' => false,
        'code' => true,
    ];

    /**
     * @inheritDoc
     */
    protected $_virtual = [
        'code',
    ];

    /**
     * @var array|null|false
     */
    protected $zoomUser = false;

    /**
     * API情報を暗号化
     *
     * @param string $apiInfo API情報
     * @return string
     */
    public function encryptApiInfo(string $apiInfo)
    {
        return StringUtility::encrypt(
            $apiInfo,
            Configure::readOrFail('Env.videoMeeting.salt'),
            Configure::readOrFail('Env.videoMeeting.salt')
        );
    }

    /**
     * API情報を復号化
     *
     * @param string $crypt 暗号化文字列
     * @return string
     */
    public function decryptApiInfo(string $crypt)
    {
        $value = StringUtility::decrypt(
            $crypt,
            Configure::readOrFail('Env.videoMeeting.salt'),
            Configure::readOrFail('Env.videoMeeting.salt')
        );
        if (!isset($value)) {
            throw new CakeException();
        }

        return $value;
    }

    /**
     * リフレッシュトークンのミューテータ
     *
     * @param string|null $data データ
     * @return string|null
     */
    protected function _setRefreshToken($data)
    {
        if (!is_string($data) || $data === '') {
            return null;
        }

        return $this->encryptApiInfo($data);
    }

    /**
     * アクセストークンのミューテータ
     *
     * @param string|null $data データ
     * @return string|null
     */
    protected function _setAccessToken($data)
    {
        if (!is_string($data) || $data === '') {
            return null;
        }

        return $this->encryptApiInfo($data);
    }

    /**
     * Zoomのユーザー情報を取得
     *
     * @return array|null
     */
    public function getZoomUser()
    {
        if ($this->zoomUser === false) {
            $videoMeeting = VideoMeetingFactory::createZoomApiModule(ZoomApi::AUTHORIZATION_TYPE_OAUTH);

            $this->zoomUser = $videoMeeting->getZoomUser();
        }

        return $this->zoomUser;
    }
}
