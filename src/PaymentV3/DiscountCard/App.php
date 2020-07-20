<?php
/**
 * App.php
 *
 * Author: wangyi <chunhei2008@qq.com>
 *
 * Date:   2020/7/20 19:39
 * Copyright: (C) 2014, Guangzhou YIDEJIA Network Technology Co., Ltd.
 */

namespace EasyWeChat\PaymentV3\DiscountCard;


class App
{
    public function getCard(string $prepareCardToken):string
    {
        return '/pages/get-card/get-card?prepare_card_token=' . $prepareCardToken;
    }

    
}