<?php
declare(strict_types=1);

namespace App\Utility\VideoMeeting;

use App\Utility\OAuth\Token;
use App\Utility\StringUtility;
use Cake\Core\Configure;
use Cake\Core\Exception\CakeException;
use Cake\Datasource\FactoryLocator;

class VideoMeetingFactory
{
    public const TIMEZONE_NAME = 'Asia/Tokyo';
    public const TIMEZONE_OFFSET = '+09:00';

    /**
     * Zoom API のモジュールを生成
     *
     * @param int|null $authorizationType 認証タイプ
     * @param string|null $apiKey API Key
     * @param string|null $apiSecret API Secret
     * @return \App\Utility\VideoMeeting\ZoomApi
     */
    public static function createZoomApiModule(
        ?int $authorizationType = null,
        ?string $apiKey = null,
        ?string $apiSecret = null
    ) {
        $config = [
            'errorLog' => 'zoom',
            'timeZone' => static::TIMEZONE_NAME,
            'apiKey' => $apiKey,
            'apiSecret' => $apiSecret,
            'clientId' => static::decryptApiInfo(Configure::readOrFail('Env.zoomApi.clientId')),
            'clientSecret' => static::decryptApiInfo(Configure::readOrFail('Env.zoomApi.clientSecret')),
            'redirectUri' => Configure::readOrFail('Env.zoomApi.redirectUri'),
        ] + Configure::readOrFail('Env.videoMeeting.zoom');

        $module = new ZoomApi($config);
        if (isset($authorizationType)) {
            $module->setAuthorizationType($authorizationType);
            if ($authorizationType === ZoomApi::AUTHORIZATION_TYPE_OAUTH) {
                /** @var \Cake\ORM\Locator\LocatorInterface $tableLocator */
                $tableLocator = FactoryLocator::get('Table');
                /** @var \App\Model\Table\ZoomConnectUsersTable $zoomConnectUsersTable */
                $zoomConnectUsersTable = $tableLocator->get('ZoomConnectUsers');

                $zoomConnectUser = $zoomConnectUsersTable->getData();
                if (isset($zoomConnectUser)) {
                    $module->setToken(new Token(
                        $zoomConnectUser->decryptApiInfo($zoomConnectUser->get('access_token')),
                        $zoomConnectUser->get('at_expiration_timestamp'),
                        $zoomConnectUser->decryptApiInfo($zoomConnectUser->get('refresh_token'))
                    ));

                    // リフレッシュトークン利用時に取得したトークンを保存
                    $module->getEventManager()->on(
                        'Model.ZoomApi.afterUseRefreshToken',
                        function ($event, $token) use ($zoomConnectUser) {
                            /** @var \Cake\ORM\Locator\LocatorInterface $tableLocator */
                            $tableLocator = FactoryLocator::get('Table');
                            /** @var \App\Model\Table\ZoomConnectUsersTable $zoomConnectUsersTable */
                            $zoomConnectUsersTable = $tableLocator->get('ZoomConnectUsers');

                            $zoomConnectUsersTable->updateToken($zoomConnectUser, $token);
                        }
                    );
                }
            }
        }

        return $module;
    }

    /**
     * Meet API のモジュールを生成
     *
     * @param array $secretJson SecretJson
     * @return \App\Utility\VideoMeeting\MeetApi
     */
    public static function createMeetApiModule(array $secretJson)
    {
        $config = [
            'errorLog' => 'meet',
            'timeZoneName' => static::TIMEZONE_NAME,
            'timeZoneOffset' => static::TIMEZONE_OFFSET,
            'secretJson' => $secretJson,
        ] + Configure::readOrFail('Env.videoMeeting.meet');

        return new MeetApi($config);
    }

    /**
     * API情報を暗号化
     *
     * @param string $apiInfo API情報
     * @return string
     */
    public static function encryptApiInfo(string $apiInfo)
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
     * @return string|null
     */
    public static function decryptApiInfo(?string $crypt)
    {
        if (!isset($crypt)) {
            return null;
        }

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
}
