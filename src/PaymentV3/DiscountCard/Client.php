<?php
/**
 * Client.php
 *
 * Author: wangyi <chunhei2008@qq.com>
 *
 * Date:   2020/7/20 19:06
 * Copyright: (C) 2014, Guangzhou YIDEJIA Network Technology Co., Ltd.
 */

namespace EasyWeChat\PaymentV3\DiscountCard;

use \EasyWeChat\PaymentV3\Base\Client as BaseClient;

class Client extends BaseClient
{
    /**
     * @param array $params
     * @return array|\EasyWeChat\Kernel\Support\Collection|object|\Psr\Http\Message\ResponseInterface|string
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     * @throws \GuzzleHttp\Exception\GuzzleException
     *
     * Author: wangyi <chunhei2008@qq.com>
     */
    public function cards(array $params)
    {
        return $this->httpPostJson($this->wrap('v3/discount-card/cards'), $params);
    }

    /**
     * @param string $outCardCode
     * @param string $cardTemplateId
     * @param array  $objectiveCompletionRecords
     * @param array  $rewardUsageRecords
     * @return array|\EasyWeChat\Kernel\Support\Collection|object|\Psr\Http\Message\ResponseInterface|string
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     * @throws \GuzzleHttp\Exception\GuzzleException
     *
     * Author: wangyi <chunhei2008@qq.com>
     */
    public function addUserRecords(string $outCardCode, string $cardTemplateId, array $objectiveCompletionRecords = [], array $rewardUsageRecords = [])
    {
        $params = [
            'card_template_id'             => $cardTemplateId,
            'objective_completion_records' => $objectiveCompletionRecords,
            'reward_usage_records'         => $rewardUsageRecords,
        ];

        return $this->httpPostJson($this->wrap(sprintf('v3/discount-card/cards/%s/add-user-records', $outCardCode)), $params);
    }

    /**
     * @param string $outCardCode
     * @return array|\EasyWeChat\Kernel\Support\Collection|object|\Psr\Http\Message\ResponseInterface|string
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     * @throws \GuzzleHttp\Exception\GuzzleException
     *
     * Author: wangyi <chunhei2008@qq.com>
     */
    public function query(string $outCardCode)
    {
        return $this->httpPostJson($this->wrap(sprintf('v3/discount-card/cards/%s', $outCardCode)));
    }

}