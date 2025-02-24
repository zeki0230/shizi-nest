<?php

namespace App\Providers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register()
    {
        
    }

    /**
     * Bootstrap any application services.
     */
    public function boot()
    {
        DB::listen(function ($query) {
            if (config('common.db_listen', false)) {
                $conf = $query->connection->getConfig();
                // sql 拼接
                $sql     = str_replace(array('%', '?'), array('%%', '%s'), $query->sql);
                $sql     = vsprintf($sql, $query->bindings);
                $db_conf = [
                    'log_id'   => config('common.request_id'),
                    'driver'   => $conf['driver'],
                    'host'     => $conf['host'],
                    'port'     => $conf['port'],
                    'database' => $conf['database'],
                    'db_time'  => bcdiv($query->time, 1000, 5),
                    'sql'      => $sql,
                    'bindings' => $query->bindings,
                ];

                // 记录日志
                Log::channel('db')->info('', $db_conf);
            }

            // 慢SQL监听
            if ($query->time > 1000) {
                Log::channel('db')->error('Slow query', [
                    'log_id'   => config('common.request_id', ''),
                    'query'    => $query->sql,
                    'bindings' => $query->bindings,
                    'time'     => $query->time,
                ]);
            }
        });
    }
}
