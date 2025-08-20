<?php
declare(strict_types=1);

namespace App\Utility\Zip;

use Cake\Core\Exception\CakeException;
use Cake\Core\InstanceConfigTrait;
use Cake\Http\Client;
use Cake\Utility\Hash;

/**
 * ZipSearch Class.
 */
class ZipSearch
{
    use InstanceConfigTrait;

    public const ERROR_EMPTY = 1;
    public const ERROR_INVALID = 2;
    public const ERROR_NOT_EXISTS = 3;

    public const API_NAME_ZIP_CODE = 'zc';
    public const API_NAME_RETURN_TYPE = 'rt';

    public const API_RETURN_TYPE_XML = 1;
    public const API_RETURN_TYPE_JSON = 2;

    /**
     * @var array
     */
    protected $_defaultConfig = [
        'scheme' => 'https',
        'sslVerify' => true,
    ];

    /**
     * @var array
     */
    protected $resultCode = [
        1 => true,
        2 => self::ERROR_EMPTY,
        3 => self::ERROR_INVALID,
    ];

    /**
     * @var array
     */
    protected $dataNames = [
        'state',
        'state_kana',
        'state_id',
        'city',
        'city_kana',
        'address',
        'address_kana',
        'company',
        'company_kana',
    ];

    /**
     * Constructor.
     *
     * @param array $options オプション
     */
    public function __construct(array $options = [])
    {
        $this->setConfig($options);
    }

    /**
     * 郵便番号から住所を検索
     *
     * @param string $zipCode 郵便番号
     * @return array 住所
     */
    public function searchAddress(string $zipCode)
    {
        $response = $this->sendApi($zipCode, static::API_RETURN_TYPE_JSON);

        if (is_null($response->getJson())) {
            throw new CakeException($this->responseToString($response));
        }

        $resultCode = Hash::get($response->getJson(), 'result.result_code');
        if (!is_scalar($resultCode) || !isset($this->resultCode[$resultCode])) {
            throw new CakeException($this->responseToString($response));
        }
        if ($this->resultCode[$resultCode] !== true) {
            return [
                'data' => [],
                'error' => $this->resultCode[$resultCode],
            ];
        }

        $addressValue = Hash::get($response->getJson(), 'result.ADDRESS_value');
        if (!is_array($addressValue)) {
            throw new CakeException($this->responseToString($response));
        }
        if (count($addressValue) <= 0) {
            return [
                'data' => [],
                'error' => static::ERROR_NOT_EXISTS,
            ];
        }

        $data = [];
        foreach ($addressValue as $value) {
            if (!is_array($value)) {
                throw new CakeException($this->responseToString($response));
            }

            $address = [];
            foreach ($this->dataNames as $name) {
                $address[$name] = Hash::get($value, $name, '');
                if (isset($address[$name]) && !is_scalar($address[$name])) {
                    throw new CakeException($this->responseToString($response));
                }
            }
            $data[] = $address;
        }

        return [
            'data' => $data,
            'error' => null,
        ];
    }

    /**
     * APIの送信
     *
     * @param string $zipCode 郵便番号
     * @param int $returnType レスポンスタイプ
     * @return \Cake\Http\Client\Response
     */
    protected function sendApi($zipCode, $returnType)
    {
        $client = new Client([
            'adapter' => 'Cake\Http\Client\Adapter\Curl',
            'host' => $this->getConfig('host'),
            'scheme' => $this->getConfig('scheme'),
            'ssl_verify_peer' => $this->getConfig('sslVerify'),
            'ssl_verify_peer_name' => $this->getConfig('sslVerify'),
        ]);

        $response = $client->get($this->getConfig('url'), [
            static::API_NAME_ZIP_CODE => $zipCode,
            static::API_NAME_RETURN_TYPE => $returnType,
        ]);
        if (!$response->isOk()) {
            throw new CakeException($this->responseToString($response));
        }
        if (!is_array($response->getJson())) {
            throw new CakeException($this->responseToString($response));
        }

        return $response;
    }

    /**
     * レスポンスを文字列へ変換
     *
     * @param \Cake\Http\Client\Response $response レスポンス
     * @return string 変換後の文字列
     */
    protected function responseToString($response)
    {
        $lines = [];
        foreach ($response->getHeaders() as $key => $values) {
            $lines[] = $key . ': ' . implode(',', $values);
        }
        $lines[] = $response->getStringBody();

        return implode("\n", $lines);
    }
}
