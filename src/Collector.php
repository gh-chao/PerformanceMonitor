<?php

namespace Lilocon\PerformanceMonitor;

use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Filesystem\Filesystem;

/**
 * 应用信息收集
 * Class Analyze
 * @package App\Util
 */
class Collector
{

    /**
     * @var array
     */
    private $queries = [];

    /**
     * @var
     */
    private $terminatedAt;

    /**
     * @var Filesystem
     */
    private $filesystem;

    private $startAt;

    /**
     * Collector constructor.
     * @param Filesystem $filesystem
     */
    public function __construct(Filesystem $filesystem)
    {
        $this->filesystem = $filesystem;
    }

    public function terminate()
    {
        $this->terminatedAt = microtime(true);
        $this->filesystem->append(storage_path('logs/analyze.log'), json_encode($this->toArray()) . "\n");
    }

    public function start()
    {
        $this->startAt = microtime(true);
    }

    public function toArray()
    {
        $query_time = 0;

        foreach ($this->queries as $query) {
            $query_time += $query['time'];
        }

        return [
            'route_name' => \Route::currentRouteName(),
            'url' => request()->url(),
            'exec_time' => $this->terminatedAt - $this->startAt, // 整个请求的时间
            'query_count' => count($this->queries), // 一共多少sql
            'query_time' => $query_time, // 数据库总时间
            'queries' => $this->queries, // sql
            'time' => time(),
        ];
    }

    /**
     * @param $query
     */
    public function addQuery(QueryExecuted $query)
    {
        $this->queries[] = [
            'sql' => $query->sql,
            'time' => $query->time,
            'bindings' => $query->bindings,
            'connectionName' => $query->connectionName,
        ];
    }

}
