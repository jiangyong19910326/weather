<?php
/**
 * Created by PhpStorm.
 * User: 82683
 * Date: 2018/9/11 0011
 * Time: 下午 2:47
 */

namespace Jiangyong\Weather\Tests;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Psr7\Response;
use http\Env\Request;
use Mockery\Matcher\AnyArgs;
use Jiangyong\Weather\Exceptions\HttpException;
use Jiangyong\Weather\Exceptions\InvalidArgumentException;
use Jiangyong\Weather\Weather;

use PHPUnit\Framework\TestCase;
class WeatherTest extends TestCase
{
    // 检查 $type 参数
    public function testGetWeatherWithInvalidType()
    {
        $w = new Weather('mock-key');

//        var_dump($w);

        // 断言会抛出此异常类
        $this->expectException(InvalidArgumentException::class);

        // 断言异常消息为 'Invalid type value(base/all): foo'
        $this->expectExceptionMessage('Invalid type value(base/all): foo');

        $w->getWeather('深圳', 'foo');

        $this->fail('Faild to assert getWeather throw exception with invalid argument.');
    }

    //檢查 $format 參數
    public function testGetWeatherWithInvalidFormat()
    {
        $w = new Weather('mock-key');

        // 断言此异常抛出
        $this->expectException(InvalidArgumentException::class);

        // 断言异常消息是否一致

        $this->expectExceptionMessage('Invalid response format: array');

        $w->getWeather('深圳','base','array');

        $this->fail('Faild to assert getWeather throw exception with invalid argument');
    }

    // 檢查天氣接口的方法  使用依賴模擬的方法獲取響應的方法
    public function testGetWeather()
    {
        // json
        $response = new Response(200, [], '{"success": true}');
        $client = \Mockery::mock(Client::class);
        $client->allows()->get('https://restapi.amap.com/v3/weather/weatherInfo', [
            'query' => [
                'key' => 'mock-key',
                'city' => '深圳',
                'output' => 'json',
                'extensions' => 'base',
            ],
        ])->andReturn($response);

        $w = \Mockery::mock(Weather::class, ['mock-key'])->makePartial();
        $w->allows()->getHttpClient()->andReturn($client);

        $this->assertSame(['success' => true], $w->getWeather('深圳'));

        // xml
        $response = new Response(200,[],'<hello>content</hello>');

        $client = \Mockery::mock(Client::class);
        $client->allows()->get('https://restapi.amap.com/v3/weather/weatherInfo', [
            'query' => [
                'key' => 'mock-key',
                'city' => '深圳',
                'output' => 'xml',
                'extensions' => 'all',
            ],
        ])->andReturn($response);

        $w = \Mockery::mock(Weather::class,['mock-key'])->makePartial();
        $w->allows()->getHttpClient()->andReturn($client);

        $this->assertSame('<hello>content</hello>',$w->getWeather('深圳','all','xml'));
    }

    /**
     *
     */
    public function testGetWeatherWithGuzzleRuntimeException()
    {
        $client = \Mockery::mock(Client::class);
        $client->allows()->get(new AnyArgs())->andThrow(new \Exception('request timeout!'));
        $w = \Mockery::mock(Weather::class,['mock-key'])->makePartial();
        $w->allows()->getHttpClient()->andReturn($client);

        $this->expectException(HttpException::class);

        $this->expectExceptionMessage('request timeout!');
        $w->getWeather('深圳');
    }

    /**
     * 单元测试链接是否奏效
     */
    public function testGetHttpClient()
    {
        $w = new Weather('mock-key');
        $this->assertInstanceOf(ClientInterface::class,$w->getHttpClient());
    }

    /**
     * 单元测试设置链接的参数
     */
    public function testSetGuzzleOptions()
    {
        $w = new Weather('mock-key');
        $this->assertNull($w->getHttpClient()->getConfig('timeout'));

        $w->setGuzzleOptions(['timeout'=>5000]);
        $this->assertSame(5000,$w->getHttpClient()->getConfig('timeout'));

    }

    /**
     *  单元测试实时天气函数
     */
    public function testGetLiveWeather()
    {
        $w = \Mockery::mock(Weather::class)->makePartial();

        $w->expects()->getWeather('深圳','base','json')->andReturn(['success' => true]);

        $this->assertSame(['success' => true],$w->getLiveWeather('深圳'));
    }

    /**
     * 单元测试预报天气函数
     */
    public function testGetForecastsWeather()
    {
        $w = \Mockery::mock(Weather::class)->makePartial();

        $w->expects()->getWeather('深圳','all','json')->andReturn(['success' => true] );

        $this->assertSame(['success' => true],$w->getForecastsWeather('深圳'));
    }

}