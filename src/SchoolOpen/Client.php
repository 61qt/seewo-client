<?php

namespace QT\Seewo\SchoolOpen;

use QT\Seewo\BaseClient;
use GuzzleHttp\Psr7\Response;

class Client extends BaseClient
{
    /**
     * 调用接口凭证
     *
     * @var string
     */
    protected $ticketId;

    /**
     * @param string $appid
     * @param string $secret
     * @param string $ticketId
     * @param array $options
     */
    public function __construct(string $appid, string $secret, string $ticketId, array $options = [])
    {
        $this->ticketId = $ticketId;

        parent::__construct($appid, $secret, $options);
    }

    /**
     * 获取区域看板统计数据
     *
     * https://open.seewo.com/#/service/1423/doc/1795
     * @param int $areaId
     * @return Response
     */
    public function getAreaRankingStatistics(int $areaId): Response
    {
        return $this->get('/school-open/area-ranking/statistics', [
            'areaId'   => $areaId,
            "ticketId" => $this->ticketId,
        ]);
    }
}
