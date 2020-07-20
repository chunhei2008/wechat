<?php

declare(strict_types=1);
/**
 * This file is part of Open Work Wechat.
 *
 * @link     https://www.meimeifa.com
 * @document https://yapi.meimeifa.cn
 * @contact  group@meimeifa.com
 */
namespace EasyWechat\PaymentV3\SmartGuide;

use EasyWechat\PaymentV3\Base\Client as BaseClient;

class Client extends BaseClient
{
    /**
     * 服务人员注册.
     *
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     * @throws \GuzzleHttp\Exception\GuzzleException
     *
     * Author: wangyi <chunhei2008@qq.com>
     * @return array|\EasyWeChat\Kernel\Support\Collection|object|\Psr\Http\Message\ResponseInterface|string
     */
    public function register(array $info)
    {
        // 需要加密字段
        $needEncryptFields = [
            'name',
            'mobile',
        ];

        foreach ($needEncryptFields as $field) {
            $info[$field] = $this->getEncrypt($info[$field]);
        }

        return $this->request($this->wrap('v3/smartguide/guides'), [], 'POST', ['json' => $info, 'headers' => ['Accept' => 'application/json', 'Wechatpay-Serial' => $this->app['cert']->getCert()['serial_no']]]);
    }

    /**
     * 服务人员分配.
     *
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     *
     * Author: wangyi <chunhei2008@qq.com>
     * @return array|\EasyWeChat\Kernel\Support\Collection|object|\Psr\Http\Message\ResponseInterface|string
     */
    public function assign(string $guideId, string $outTradeNo, ?string $subMchId = null)
    {
        $params = [
            'out_trade_no' => $outTradeNo,
        ];

        if ($subMchId) {
            $params['sub_mchid'] = $subMchId;
        }

        return $this->httpPostJson($this->wrap(sprintf('v3/smartguide/guides/%s/assign', $guideId)), $params);
    }
}
