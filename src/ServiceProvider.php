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

/**
 * Class ServiceProvider.
 */
class ServiceProvider extends \Illuminate\Support\ServiceProvider
{
    /**
     * @var bool
     *           标记服务提供器延迟加载 true 来声明延迟加载
     */
    protected $defer = true;

    /**
     *  return void
     *  注册服务提供者.
     */
    public function register()
    {
        $this->app->singleton(Weather::class, function () {
            return new Weather(config('services.weather.key'));
        });

        $this->app->alias(Weather::class, 'weather');
    }

    /**
     * @return array
     *               取得提供者的服务
     */
    public function provides()
    {
        return [Weather::class, 'weather'];
    }
}
