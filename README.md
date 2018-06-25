# PerformanceMonitor

## 安装

``` bash
$ composer require lilocon/performance-monitor
```


## 配置

注册 `ServiceProvider`:

```php
\Lilocon\PerformanceMonitor\PerformanceMonitorServiceProvider::class,
```


## 使用

``` bash
php artisan lilocon:pm:analyze
```

注意：开启ServiceProvider需要让应用跑一段时间生成日志才能分析

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
