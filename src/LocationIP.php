<?php


namespace zjj;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class LocationIP
{

    public $pt = '';

    public $key = '';

    public $config = [];

    public function __construct($config)
    {
        $this->config = $config;
        $this->pt = $config['pt'];
        $this->key = $config['key'];

    }

    /**
     * 根据IP获取定位
     * @param $ip
     * @return array
     * @throws GuzzleException
     */
    public function getLocation($ip)
    {
        // 判断是使用那个服务商进行地址定位
        $res = [
            'code' => 0,
            'msg' => 'fail'
        ];
        switch ($this->pt) {
            case 'tx': $res = $this->txMapApi($ip);break;
            case 'gd': $res = $this->gdMapApi($ip);break;
            default:break;
        }

        return $res;
    }

    /**
     * 腾讯地图
     * https://lbs.qq.com/service/webService/webServiceGuide/webServiceIp
     * @param $ip
     * @return array
     * @throws GuzzleException
     */
    private function txMapApi($ip)
    {
        $response = (new Client())->request('get', 'http://apis.map.qq.com/ws/location/v1/ip', [
            'query' => [
                'ip' => $ip,
                'key' => $this->key,
            ],
        ]);

        if (200 == $response->getStatusCode()) {
            $body = $response->getBody()->getContents();
            $json = json_decode($body, true);
            if (0 == $json['status']) {
                $result = $json['result'];
                return [
                    'code' => 1,
                    'msg' => 'success',
                    'data' => [
                        'province' => $result['ad_info']['province'] ?? '',
                        'city' => $result['ad_info']['city'] ?? '',
                        'district' => $result['ad_info']['district'] ?? '',
                        'adcode' => $result['ad_info']['adcode'] ?? '',
                    ]
                ];
            } else {
                $msg = "腾讯IP定位数据返回异常，返回状态码={$json['status']}，错误信息={$json['message']}";
                return [
                    'code' => 0,
                    'msg' => $msg,
                ];
            }
        } else {
            $msg = "腾讯IP定位接口返回异常，请求状态码=" . $response->getStatusCode();
            return [
                'code' => 0,
                'msg' => $msg,
            ];
        }
    }


    /**
     * 高德地图
     * https://lbs.amap.com/api/webservice/guide/api/ipconfig
     * @param $ip
     * @return array
     * @throws GuzzleException
     */
    private function gdMapApi($ip)
    {
        $response = (new Client())->request('get', 'https://restapi.amap.com/v3/ip', [
            'query' => [
                'ip' => $ip,
                'key' => $this->key,
            ],
        ]);

        if (200 == $response->getStatusCode()) {
            $body = $response->getBody()->getContents();
            $result = json_decode($body, true);
            if (1 == $result['status'] && $result['infocode'] == 10000) {
                return [
                    'code' => 1,
                    'msg' => 'success',
                    'data' => [
                        'province' => $result['province'] ?? '',
                        'city' => $result['city'] ?? '',
                        'district' => '',
                        'adcode' => $result['adcode'] ?? '',
                    ]
                ];
            } else {
                $msg = "高德IP定位数据返回异常，返回状态码={$result['infocode']}，错误信息={$result['info']}";
                return [
                    'code' => 0,
                    'msg' => $msg,
                ];
            }
        } else {
            $msg = "高德IP定位接口返回异常，请求状态码=" . $response->getStatusCode();
            return [
                'code' => 0,
                'msg' => $msg,
            ];
        }
    }


}