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

use WechatPay\GuzzleMiddleware\Validator;

class NoopValidator implements Validator
{
    public function validate(\Psr\Http\Message\ResponseInterface $response)
    {
        return true;
    }
}
