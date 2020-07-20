<?php
/**
 * Client.php
 *
 * Author: wangyi <chunhei2008@qq.com>
 *
 * Date:   2020/7/20 18:58
 * Copyright: (C) 2014, Guangzhou YIDEJIA Network Technology Co., Ltd.
 */

namespace EasyWeChat\PaymentV3\Base;


use EasyWeChat\Kernel\BaseClient;
use GuzzleHttp\MessageFormatter;
use GuzzleHttp\Middleware;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use WechatPay\GuzzleMiddleware\Util\PemUtil;
use WechatPay\GuzzleMiddleware\WechatPayMiddleware;

class Client extends BaseClient
{
    /**
     * JSON request.
     *
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     * @throws \GuzzleHttp\Exception\GuzzleException
     *
     * Author: wangyi <chunhei2008@qq.com>
     * @return array|\EasyWeChat\Kernel\Support\Collection|object|ResponseInterface|string
     */
    public function httpPostJson(string $url, array $data = [], array $query = [])
    {
        return $this->request($url, [], 'POST', ['query' => $query, 'json' => $data, 'headers' => ['Accept' => 'application/json']]);
    }

    /**
     * Make a API request.
     *
     * @param string $method
     * @param bool $returnResponse
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     * @throws \GuzzleHttp\Exception\GuzzleException
     *
     * Author: wangyi <chunhei2008@qq.com>
     * @return array|\EasyWeChat\Kernel\Support\Collection|object|ResponseInterface|string
     */
    protected function request(string $endpoint, array $params = [], $method = 'post', array $options = [], $returnResponse = false)
    {
        if (empty($this->middlewares)) {
            $this->registerHttpMiddlewares();
        }

        $response = $this->performRequest($endpoint, $method, $options);

        return $returnResponse ? $response : $this->castResponseToType($response, $this->app->config->get('response_type'));
    }

    /**
     * Register Guzzle middlewares.
     */
    protected function registerHttpMiddlewares()
    {
        // retry
        $this->pushMiddleware($this->retryMiddleware(), 'retry');
        // wechat pay
        $this->pushMiddleware($this->wechatPayMiddleware(), 'wechat_pay');
        // log
        $this->pushMiddleware($this->logMiddleware(), 'log');
    }

    /**
     * Log the request.
     *
     * @return \Closure
     */
    protected function logMiddleware()
    {
        $formatter = new MessageFormatter($this->app['config']['http.log_template'] ?? MessageFormatter::DEBUG);

        return Middleware::log($this->app['logger'], $formatter);
    }

    /**
     * Return retry middleware.
     *
     * @return \Closure
     */
    protected function retryMiddleware()
    {
        return Middleware::retry(function (
            $retries,
            RequestInterface $request,
            ResponseInterface $response = null
        ) {
            // Limit the number of retries to 2
            if ($retries < $this->app->config->get('http.retries', 1) && $response && $code = $response->getStatusCode()) {
                // Retry on server errors
                if ($code >= 400) {
                    return true;
                }
            }

            return false;
        }, function () {
            return abs($this->app->config->get('http.retry_delay', 500));
        });
    }

    /**
     * @return WechatPayMiddleware
     *
     * Author: wangyi <chunhei2008@qq.com>
     */
    protected function wechatPayMiddleware()
    {
        $mchId = $this->app['config']['mch_id'];
        $certSerialNo = $this->app['config']['serial_no'];
        $privateKeyPath = $this->app['config']['key_path'];

        $mchPrivateKey = PemUtil::loadPrivateKey($privateKeyPath);

        // 微信支付平台公钥
        $wechatPayCertificate = \openssl_x509_read($this->app['cert']->getCert()['serial']);

        return WechatPayMiddleware::builder()
            ->withMerchant($mchId, $certSerialNo, $mchPrivateKey)
            ->withWechatPay([$wechatPayCertificate])
            ->build();
    }

    /**
     * @param $str
     * @return string
     *
     * Author: wangyi <chunhei2008@qq.com>
     */
    protected function getEncrypt(string $str)
    {
        // 微信支付平台证书公钥
        $publicKey = $this->app['cert']->getCert()['serial'];
        $encrypted = '';
        if (openssl_public_encrypt($str, $encrypted, $publicKey, OPENSSL_PKCS1_OAEP_PADDING)) {
            //base64编码
            $sign = base64_encode($encrypted);
        } else {
            throw new \RuntimeException('encrypt failed');
        }

        return $sign;
    }
}
