<?php
/**
 * Application.php
 *
 * Author: wangyi <chunhei2008@qq.com>
 *
 * Date:   2020/7/20 18:56
 * Copyright: (C) 2014, Guangzhou YIDEJIA Network Technology Co., Ltd.
 */

namespace EasyWeChat\PaymentV3;


use EasyWeChat\Kernel\ServiceContainer;
use EasyWechat\PaymentV3\Cert\ServiceProvider;

class Application extends ServiceContainer
{
    protected $providers = [
        ServiceProvider::class,
        \EasyWechat\PaymentV3\SmartGuide\ServiceProvider::class,
        \EasyWechat\PaymentV3\DiscountCard\ServiceProvider::class,
    ];
}