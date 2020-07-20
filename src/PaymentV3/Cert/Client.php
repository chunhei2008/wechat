<?php

declare(strict_types=1);
/**
 * This file is part of Open Work Wechat.
 *
 * @link     https://www.meimeifa.com
 * @document https://yapi.meimeifa.cn
 * @contact  group@meimeifa.com
 */
namespace EasyWechat\PaymentV3\Cert;

use EasyWechat\PaymentV3\Base\Client as BaseClient;
use Carbon\Carbon;
use EasyWeChat\Kernel\Exceptions\HttpException;
use EasyWeChat\Kernel\Exceptions\InvalidArgumentException;
use EasyWeChat\Kernel\Exceptions\RuntimeException;
use EasyWeChat\Kernel\Traits\InteractsWithCache;
use Psr\Http\Message\ResponseInterface;
use WechatPay\GuzzleMiddleware\Util\AesUtil;
use WechatPay\GuzzleMiddleware\Util\PemUtil;
use WechatPay\GuzzleMiddleware\WechatPayMiddleware;

class Client extends BaseClient
{
    use InteractsWithCache;

    /**
     * @var string
     */
    protected $requestMethod = 'GET';

    /**
     * @var string
     */
    protected $serialNoKey = 'serial_no';

    /**
     * @var string
     */
    protected $serialKey = 'serial';

    /**
     * @var string
     */
    protected $cachePrefix = 'easywechat.payment.v3.cert.';

    /**
     * @throws HttpException
     * @throws InvalidArgumentException
     * @throws RuntimeException
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Psr\SimpleCache\InvalidArgumentException
     *
     * Author: wangyi <chunhei2008@qq.com>
     */
    public function getRefreshedCert(): array
    {
        return $this->getCert(true);
    }

    /**
     * @throws HttpException
     * @throws InvalidArgumentException
     * @throws RuntimeException
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Psr\SimpleCache\InvalidArgumentException
     *
     * Author: wangyi <chunhei2008@qq.com>
     */
    public function getCert(bool $refresh = false): array
    {
        $cacheKey = $this->getCacheKey();
        $cache = $this->getCache();

        if (! $refresh && $cache->has($cacheKey)) {
            return $cache->get($cacheKey);
        }

        /** @var array $cert */
        $cert = $this->requestCert();

        $this->setCert($cert[$this->serialNoKey], $cert[$this->serialKey], $cert['expires_in'] ?? 7200);

        // $this->app->events->dispatch(new Events\AccessTokenRefreshed($this));

        return $cert;
    }

    /**
     * @throws InvalidArgumentException
     * @throws RuntimeException
     * @throws \Psr\SimpleCache\InvalidArgumentException
     *
     * Author: wangyi <chunhei2008@qq.com>
     * @return $this
     */
    public function setCert(string $serialNo, string $serial, int $lifetime = 7200)
    {
        $this->getCache()->set($this->getCacheKey(), [
            $this->getSerialNoKey() => $serialNo,
            $this->getSerialKey() => $serial,
            'expires_in' => $lifetime,
        ], $lifetime);

        if (! $this->getCache()->has($this->getCacheKey())) {
            throw new RuntimeException('Failed to cache serial.');
        }

        return $this;
    }

    /**
     * @throws HttpException
     * @throws InvalidArgumentException
     * @throws RuntimeException
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Psr\SimpleCache\InvalidArgumentException
     *
     * Author: wangyi <chunhei2008@qq.com>
     * @return $this
     */
    public function refresh()
    {
        $this->getCert(true);

        return $this;
    }

    /**
     * @throws HttpException
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     * @throws \GuzzleHttp\Exception\GuzzleException
     *
     * Author: wangyi <chunhei2008@qq.com>
     * @return array
     */
    public function requestCert()
    {
        $response = $this->sendRequest();
        $result = json_decode($response->getBody()->getContents(), true);

        if (empty($result['data'])) {
            throw new HttpException('Request api v3 certificates fail: ' . json_encode($result, JSON_UNESCAPED_UNICODE), $response);
        }

        $data = $result['data'];
        $aesUtil = new AesUtil($this->app['config']['key_v3'] ?? '');
        foreach ($data as $encryptedCert) {
            $serialNo = $encryptedCert['serial_no'];
            $expireTime = $encryptedCert['expire_time'];
            $associatedData = $encryptedCert['encrypt_certificate']['associated_data'];
            $nonceStr = $encryptedCert['encrypt_certificate']['nonce'];
            $ciphertext = $encryptedCert['encrypt_certificate']['ciphertext'];
            $decryptedCert = $aesUtil->decryptToString($associatedData, $nonceStr, $ciphertext);

            return [
                $this->serialNoKey => $serialNo,
                $this->serialKey => $decryptedCert,
                'expires_in' => Carbon::parse($expireTime)->diffInSeconds(Carbon::now()),
            ];
        }

        throw new HttpException('Request api v3 certificates fail: ' . json_encode($result, JSON_UNESCAPED_UNICODE), $response);
    }

    /**
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidArgumentException
     */
    public function getEndpoint(): string
    {
        if (empty($this->endpointToGetCertificates)) {
            throw new InvalidArgumentException('No endpoint for api v3 certificates request.');
        }

        return $this->endpointToGetCertificates;
    }

    /**
     * @return string
     */
    public function getSerialNoKey()
    {
        return $this->serialNoKey;
    }

    /**
     * @return string
     *
     * Author: wangyi <chunhei2008@qq.com>
     */
    public function getSerialKey()
    {
        return $this->serialKey;
    }

    /**
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     * @throws \GuzzleHttp\Exception\GuzzleException
     *
     * Author: wangyi <chunhei2008@qq.com>
     */
    protected function sendRequest(): ResponseInterface
    {
        $options = [
            'headers' => [
                'Accept' => 'application/json',
            ],
        ];

        return $this->request($this->wrap('v3/certificates'), [], $this->requestMethod, $options, true);
    }

    /**
     * @return string
     */
    protected function getCacheKey()
    {
        return $this->cachePrefix . $this->app['config']->get('mch_id');
    }

    /**
     * @return WechatPayMiddleware
     *
     * Author: wangyi <chunhei2008@qq.com>
     */
    protected function wechatPayMiddleware()
    {
        $mchId = $this->app['config']['mch_id'];
        $serialNo = $this->app['config']['serial_no'];
        $privateKeyPath = $this->app['config']['key_path'];
        $mchPrivateKey = PemUtil::loadPrivateKey($privateKeyPath);

        return WechatPayMiddleware::builder()
            ->withMerchant($mchId, $serialNo, $mchPrivateKey)
            ->withValidator(new NoopValidator())
            ->build();
    }
}
