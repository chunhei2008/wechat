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

use Pimple\Container;
use Pimple\ServiceProviderInterface;

class ServiceProvider implements ServiceProviderInterface
{
    /**
     * {@inheritdoc}.
     */
    public function register(Container $app)
    {
        $app['smart_guide'] = function ($app) {
            return new Client($app);
        };
    }
}
