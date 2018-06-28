<?php

namespace Lilocon\PerformanceMonitor\Commands;

use Illuminate\Console\Command;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Filesystem\Filesystem;

class Analyze extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'lilocon:pm:analyze';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '分析数据';

    /**
     * @var string
     */
    protected $schemaName = 'lilocon_performance_monitor_analyze';

    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * Analyze constructor.
     * @param Filesystem $filesystem
     */
    public function __construct(Filesystem $filesystem)
    {
        parent::__construct();
        $this->filesystem = $filesystem;
    }


    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->createSchema();

        $this->import();

        $this->runAnalyze('hit', '访问量');
        $this->runAnalyze('exec_time_sum', '总执行时间');
        $this->runAnalyze('query_count_avg', '平均查询次数');
        $this->runAnalyze('query_count_sum', '总数据库查询次数');
        $this->runAnalyze('query_time_sum', '总数据库查询时间');

        $this->clear();

        return;
    }

    public function runAnalyze($orderColumn, $name)
    {
        $columns = [
            '路由名' => 'route_name',
            '访问量' => 'count(*) as hit',
            '总执行时间' => 'SUM(exec_time) as exec_time_sum',
            '平均查询次数' => 'SUM(query_count)/count(*) as query_count_avg',
            '总数据库查询次数' => 'SUM(query_count) as query_count_sum',
            '总数据库查询时间' => 'SUM(query_time) as query_time_sum',
        ];


        $rows = \DB::table($this->schemaName)
            ->select(\DB::raw(implode(', ', $columns)))
            ->groupBy('route_name')
            ->orderBy($orderColumn, 'desc')
            ->limit(20)
            ->get();

        $rows = array_map(function ($row) {
            return (array)$row;
        }, $rows->toArray());

        $this->output->title('按' . $name . '排序');

        $this->table(array_keys($columns), $rows);


    }


    private function createSchema()
    {
        $this->clear();
        \Schema::create($this->schemaName, function (Blueprint $table) {
            $table->float('exec_time');
            $table->integer('query_count');
            $table->float('query_time');
            $table->string('route_name');
        });
    }

    private function clear()
    {
        \Schema::dropIfExists($this->schemaName);
    }

    private function import()
    {
        $file = storage_path('logs/analyze.log');

        if (!$this->filesystem->exists($file)) {
            throw new \ErrorException('日志文件不存在');
        }
        if (!$this->filesystem->isFile($file)) {
            throw new \ErrorException('无法读取日志文件');
        }

        $fp = fopen($file, 'r');

        $this->output->writeln("正在导入数据");

        // 最大分析10w数据
        $max = $this->filelines($file);
        $progressBar = $this->output->createProgressBar($max);
        $i = 1;
        while ($line = fgets($fp)) {
            if ($i++ > $max) {
                break;
            }

            if ($i % 100 == 0) {
                $progressBar->setProgress($i);
            }

            $info = json_decode($line, true);

            if ($info['route_name'] == null) {
                $info['route_name'] = '';
            }

            \DB::table($this->schemaName)->insert([
                'exec_time' => $info['exec_time'],
                'query_count' => $info['query_count'],
                'query_time' => $info['query_time'],
                'route_name' => $info['route_name'],
            ]);

        }

        $this->output->newLine();
        $this->output->writeln("导入成功:共导入{$i}条数据");

        return;
    }


    private function filelines($file)
    {

        $getfp = fopen($file, 'r');
        $lines = 0;

        while (fgets($getfp)) {
            $lines++;
            if ($lines > 100000) {
                fclose($getfp); //关闭文件
                return $lines;
            }
        }
        fclose($getfp); //关闭文件

        return $lines;
    }

}
