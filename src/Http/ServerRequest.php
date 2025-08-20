<?php
declare(strict_types=1);

namespace App\Http;

use Cake\Core\Configure;
use Cake\Http\ServerRequest as CakeServerRequest;

/**
 * ServerRequest class. wrapper
 */
class ServerRequest extends CakeServerRequest
{
    /**
     * @inheritDoc
     */
    public function __construct($config = [])
    {
        parent::__construct($config);

        if (Configure::check('Access.trustedProxies')) {
            $trustedProxies = Configure::readOrFail('Access.trustedProxies');
            if (!empty($trustedProxies)) {
                $this->setTrustedProxies($trustedProxies);
            }
        }
    }

    /**
     * IP取得
     *
     * @return string ip
     */
    public function clientIp(): string
    {
        if ($this->trustProxy && $this->getEnv('HTTP_X_FORWARDED_FOR')) {
            /** @var string $xForwarded */
            $xForwarded = $this->getEnv('HTTP_X_FORWARDED_FOR', '');
            $addresses = array_map('trim', explode(',', $xForwarded));
            $trusted = (count($this->trustedProxies) > 0);
            $n = count($addresses);

            if ($trusted) {
                $diff = $addresses;
                foreach ($diff as $index => $remoteIp) {
                    foreach ($this->trustedProxies as $address) {
                        if (strpos($address, '/') !== false) {
                            [$acceptIp, $mask] = explode('/', $address);
                            $acceptLong = ip2long($acceptIp) >> 32 - (int)$mask;
                            $remoteLong = ip2long($remoteIp) >> 32 - (int)$mask;
                            if ($acceptLong === $remoteLong) {
                                unset($diff[$index]);
                            }
                        } else {
                            if ($address === $remoteIp) {
                                unset($diff[$index]);
                            }
                        }
                    }
                }
                $trusted = (count($diff) === 1);
            }

            if ($trusted) {
                return $addresses[0];
            }

            return $addresses[$n - 1];
        }

        /** @var string $ipaddr */
        $ipaddr = $this->getEnv('REMOTE_ADDR', '');

        return trim($ipaddr);
    }

    /**
     * IP制限
     *
     * @param array $addresses ip
     * @return bool
     */
    public function canAccessIp(array $addresses)
    {
        $remoteIp = $this->clientIp();
        foreach ($addresses as $address) {
            if (strpos($address, '/') !== false) {
                [$acceptIp, $mask] = explode('/', $address);
                $acceptLong = ip2long($acceptIp) >> 32 - (int)$mask;
                $remoteLong = ip2long($remoteIp) >> 32 - (int)$mask;
                if ($acceptLong === $remoteLong) {
                    return true;
                }
            } else {
                if ($address === $remoteIp) {
                    return true;
                }
            }
        }

        return false;
    }
}
