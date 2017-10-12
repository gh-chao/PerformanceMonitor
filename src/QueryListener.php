<?php

namespace Lilocon\PerformanceMonitor;

use Illuminate\Database\Events\QueryExecuted;

class QueryListener
{

    /**
     * @var Collector
     */
    private $collector;

    /**
     * QueryListener constructor.
     * @param Collector $collector
     */
    public function __construct(Collector $collector)
    {

        $this->collector = $collector;
    }

    /**
     * Handle the event.
     *
     * @param  QueryExecuted $event
     * @return void
     */
    public function handle(QueryExecuted $event)
    {
        $this->collector->addQuery($event);
    }
}
