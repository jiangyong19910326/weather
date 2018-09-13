<?php

/*
 * This file part of the jiangyong/weather
 *
 * (c) jiangyong<i@jiangyong.me>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Jiangyong\Weather;

use GuzzleHttp\Client;
use Jiangyong\Weather\Exceptions\InvalidArgumentException;
use Jiangyong\Weather\Exceptions\HttpException;

class Weather
{
    /**
     * @var string|string
     * @var array|array
     */
    protected $key;

    protected $guzzleOptions = [];

    /**
     * Weather constructor.
     *
     * @param string $key
     *                    初始化获取api key
     */
    public function __construct($key)
    {
        $this->key = $key;
    }

    /**
     * @return Client
     *                获取连接客户端
     */
    public function getHttpClient()
    {
        return new Client($this->guzzleOptions);
    }

    /**
     * @param array $options
     *                       设置链接guzzle的可选参数
     */
    public function setGuzzleOptions(array $options)
    {
        $this->guzzleOptions = $options;
    }

    /**
     * @param $city
     * @param string $type
     * @param string $format
     *
     * @return mixed|string
     *
     * @throws HttpException
     * @throws InvalidArgumentException
     *                                  获取天气状况，包括实时天气和预报天气
     */
    public function getWeather($city, $type = 'base', $format = 'json')
    {
        {$url = 'https://restapi.amap.com/v3/weather/weatherInfo';

        if (!\in_array(\strtolower($format), ['xml', 'json'])) {
            throw new InvalidArgumentException('Invalid response format: '.$format);
        }

        if (!\in_array(\strtolower($type), ['base', 'all'])) {
            throw new InvalidArgumentException('Invalid type value(base/all): '.$type);
        }

        $query = array_filter([
            'key' => $this->key,
            'city' => $city,
            'output' => $format,
            'extensions' => $type,
        ]);

        try {
            $response = $this->getHttpClient()->get($url, [
                'query' => $query,
            ])->getBody()->getContents();

            return 'json' === $format ? \json_decode($response, true) : $response;
        } catch (\Exception $e) {
            throw new HttpException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * @param $city
     * @param string $format
     *
     * @return mixed|string
     *
     * @throws HttpException
     * @throws InvalidArgumentException
     *                                  获取实时天气
     */
    public function getLiveWeather($city, $format = 'json')
    {
        return $this->getWeather($city, 'base', $format);
    }

    /**
     * @param $city
     * @param string $format
     *
     * @return mixed|string
     *
     * @throws HttpException
     * @throws InvalidArgumentException
     *                                  获取预报天气
     */
    public function getForecastsWeather($city, $format = 'json')
    {
        return $this->getWeather($city, 'all', $format);
    }
}
