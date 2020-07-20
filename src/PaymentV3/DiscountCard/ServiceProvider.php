<?php
/**
 * ServiceProvider.php
 *
 * Author: wangyi <chunhei2008@qq.com>
 *
 * Date:   2020/7/20 19:06
 * Copyright: (C) 2014, Guangzhou YIDEJIA Network Technology Co., Ltd.
 */

namespace EasyWeChat\PaymentV3\DiscountCard;


use Pimple\Container;
use Pimple\ServiceProviderInterface;

class ServiceProvider implements ServiceProviderInterface
{
    public function register(Container $pimple)
    {
        $pimple['discount_card'] = function ($app) {
            return new Client($app);
        };

        $pimple['discount_card.notify'] = function ($app) {
            return new Serve($app);
        };
    }
}