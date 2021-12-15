<?php

namespace QT\Seewo\MisRemote;

use DateTime;
use QT\Seewo\BaseClient;
use GuzzleHttp\Psr7\Response;

/**
 * 希沃集控Api
 *
 * @package QT\Seewo\MisRemote
 */
class Client extends BaseClient
{
    /**
     * 根据省份编码获取学校信息
     *
     * @see https://open.seewo.com/#/service/1315/doc/1950
     * @param string $code
     * @return Response
     */
    public function getSchoolsByProvince(string $code): Response
    {
        return $this->post('mis-remote/ucp-school-service/query-school-by-province', [
            'provinceCode' => $code,
        ]);
    }

    /**
     * 根据城市编码获取学校信息
     *
     * @see https://open.seewo.com/#/service/1315/doc/1935
     * @param string $code
     * @return Response
     */
    public function getSchoolsByCity(string $code): Response
    {
        return $this->post('mis-remote/ucp-school-service/query-school-by-city', [
            'cityCode' => $code,
        ]);
    }

    /**
     * 根据县区编码获取学校信息
     *
     * @see https://open.seewo.com/#/service/1315/doc/1934
     * @param string $code
     * @return Response
     */
    public function getSchoolsByDistrict(string $code): Response
    {
        return $this->post('mis-remote/ucp-school-service/query-school-by-district', [
            'districtCode' => $code,
        ]);
    }

    /**
     * 根据学校编码获取学校信息
     *
     * @see https://open.seewo.com/#/service/1315/doc/1945
     * @param array<string> $codes
     * @return Response
     */
    public function getSchools(array $codes): Response
    {
        return $this->post('mis-remote/ucp-school-service/query-school', [
            'schoolCodes' => $codes,
        ]);
    }

    /**
     * 根据学校编码获取学校内设备监管情况
     *
     * @see https://open.seewo.com/#/service/1315/doc/1946
     * @param array<string> $codes
     * @return Response
     */
    public function getSchoolDevices(array $codes): Response
    {
        return $this->post('mis-remote/ucp-device-service/query-device-manage', [
            'schoolCodes' => $codes,
        ]);
    }

    /**
     * 根据省市县code查询特定时间段下开机的设备列表
     * 因为要求时间范围使用的微秒进行查询,所以根据DateTime进行转换
     *
     * @see https://open.seewo.com/#/service/1315/doc/1975
     * @param DateTime $startAt
     * @param DateTime $endAt
     * @param string $provinceCode
     * @param string $cityCode
     * @param string $districtCode
     * @param array $schoolCodes
     * @return Response
     */
    public function getOnlineDevices(
        DateTime $startAt,
        DateTime $endAt,
        string $provinceCode,
        string $cityCode = null,
        string $districtCode = null,
        array $schoolCodes = [],
    ): Response {
        return $this->post('mis-remote/ucp-device-service/query-online-device', [
            'startDate' => $startAt->format('U') * 1000,
            'endDate'   => $endAt->format('U') * 1000,
            'queryDto'  => array_filter([
                'provinceCode' => $provinceCode,
                'cityCode'     => $cityCode,
                'districtCode' => $districtCode,
                'schoolCodes'  => $schoolCodes,
            ]),
        ]);
    }

    /**
     * 根据学校编码查询软件使用Top榜
     * 因为要求时间范围使用的微秒进行查询,所以根据DateTime进行转换
     *
     * @see https://open.seewo.com/#/service/1315/doc/2082
     * @param DateTime $startAt
     * @param DateTime $endAt
     * @param array<string> $districtCode
     * @param int $top
     * @return Response
     */
    public function getTopSoftwareBySchool(
        DateTime $startAt,
        DateTime $endAt,
        array $schoolCodes,
        int $top = 50
    ): Response {
        return $this->post('mis-remote/ucp-software-service/query-top-software-by-district', [
            'startDate'   => $startAt->format('U') * 1000,
            'endDate'     => $endAt->format('U') * 1000,
            'schoolCodes' => $schoolCodes,
            'top'         => $top,
        ]);
    }

    /**
     * 根据区域编码查询软件使用Top榜
     * 因为要求时间范围使用的微秒进行查询,所以根据DateTime进行转换
     *
     * @see https://open.seewo.com/#/service/1315/doc/2058
     * @param DateTime $startAt
     * @param DateTime $endAt
     * @param string $districtCode
     * @param int $top
     * @return Response
     */
    public function getTopSoftwareByDistrict(
        DateTime $startAt,
        DateTime $endAt,
        string $districtCode,
        int $top = 50
    ): Response {
        return $this->post('mis-remote/ucp-software-service/query-top-software-by-district', [
            'startDate'    => $startAt->format('U') * 1000,
            'endDate'      => $endAt->format('U') * 1000,
            'districtCode' => $districtCode,
            'top'          => $top,
        ]);
    }

    /**
     * 根据城市编码查询软件使用Top榜
     * 因为要求时间范围使用的微秒进行查询,所以根据DateTime进行转换
     *
     * @see https://open.seewo.com/#/service/1315/doc/2059
     * @param DateTime $startAt
     * @param DateTime $endAt
     * @param string $cityCode
     * @param int $top
     * @return Response
     */
    public function getTopSoftwareByCity(
        DateTime $startAt,
        DateTime $endAt,
        string $cityCode,
        int $top = 50
    ): Response {
        return $this->post('mis-remote/ucp-software-service/query-top-software-by-city', [
            'startDate' => $startAt->format('U') * 1000,
            'endDate'   => $endAt->format('U') * 1000,
            'cityCode'  => $cityCode,
            'top'       => $top,
        ]);
    }

    /**
     * 根据省份编码查询软件使用Top榜
     * 因为要求时间范围使用的微秒进行查询,所以根据DateTime进行转换
     *
     * @see https://open.seewo.com/#/service/1315/doc/2060
     * @param DateTime $startAt
     * @param DateTime $endAt
     * @param string $provinceCode
     * @param int $top
     * @return Response
     */
    public function getTopSoftwareByProvince(
        DateTime $startAt,
        DateTime $endAt,
        string $provinceCode,
        int $top = 50
    ): Response {
        return $this->post('mis-remote/ucp-software-service/query-top-software-by-province', [
            'startDate'    => $startAt->format('U') * 1000,
            'endDate'      => $endAt->format('U') * 1000,
            'provinceCode' => $provinceCode,
            'top'          => $top,
        ]);
    }

    /**
     * 查询学校绑定的设备
     *
     * @see https://open.seewo.com/#/service/1315/doc/1936
     * @param array<string> $codes
     * @return Response
     */
    public function getSchoolBindDevices(array $codes): Response
    {
        return $this->post('mis-remote/ucp-device-service/query-device-bind', [
            'schoolCodes' => $codes,
        ]);
    }

    /**
     * 查询设备基本信息
     *
     * @see https://open.seewo.com/#/service/1315/doc/1937
     * @param array<string> $codes
     * @return Response
     */
    public function getSchoolDeviceBaseInfo(array $codes): Response
    {
        return $this->post('mis-remote/ucp-device-service/query-device-base-info', [
            'schoolCodes' => $codes,
        ]);
    }

    /**
     * 查询设备运行中信息
     *
     * @see https://open.seewo.com/#/service/1315/doc/1938
     * @param array<string> $codes
     * @return Response
     */
    public function getSchoolDeviceRuntimeInfo(array $codes): Response
    {
        return $this->post('mis-remote/ucp-device-service/query-run-attribute-by-school-code', [
            'schoolCodes' => $codes,
        ]);
    }

    /**
     * 查询设备使用率
     *
     * @see https://open.seewo.com/#/service/1315/doc/1939
     * @param array<string> $codes
     * @param DateTime $startAt
     * @param DateTime $endAt
     * @return Response
     */
    public function getSchoolDeviceUsage(array $codes, DateTime $startAt, DateTime $endAt): Response
    {
        return $this->post('mis-remote/ucp-device-service/query-device-usage-by-school-code', [
            'schoolCodes' => $codes,
            'startDate'   => $startAt->format('U') * 1000,
            'endDate'     => $endAt->format('U') * 1000,
        ]);
    }

    /**
     * 查询设备使用详情
     *
     * @see https://open.seewo.com/#/service/1315/doc/1940
     * @param array<string> $codes
     * @param DateTime $startAt
     * @param DateTime $endAt
     * @return Response
     */
    public function getSchoolDeviceStatistic(array $codes, DateTime $startAt, DateTime $endAt): Response
    {
        return $this->post('mis-remote/ucp-device-service/query-device-statistic', [
            'schoolCodes' => $codes,
            'startDate'   => $startAt->format('U') * 1000,
            'endDate'     => $endAt->format('U') * 1000,
        ]);
    }

    /**
     * 查询设备下某软件的使用详情
     *
     * @see https://open.seewo.com/#/service/1315/doc/1942
     * @param array<string> $codes
     * @param string $name
     * @param DateTime $startAt
     * @param DateTime $endAt
     * @return Response
     */
    public function getSchoolSoftwareUseInfo(array $codes, string $name, DateTime $startAt, DateTime $endAt): Response
    {
        return $this->post('mis-remote/ucp-software-service/query-software-use', [
            'schoolCodes'  => $codes,
            'softwareName' => $name,
            'startDate'    => $startAt->format('U') * 1000,
            'endDate'      => $endAt->format('U') * 1000,
        ]);
    }
}
