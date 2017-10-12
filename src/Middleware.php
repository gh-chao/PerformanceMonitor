<?php

namespace Lilocon\PerformanceMonitor;

use Closure;

class Middleware
{
    /**
     * @var Collector
     */
    private $collector;

    /**
     * Middleware constructor.
     * @param Collector $collector
     */
    public function __construct(Collector $collector)
    {
        $this->collector = $collector;
    }
    
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        return $next($request);
    }

    /**
     * 终止请求
     * @param \Illuminate\Http\Request  $request
     * @param \Illuminate\Http\Response $response
     */
    public function terminate($request, $response)
    {
        $this->collector->terminate();
    }
}
