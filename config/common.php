<?php

return [
    'app_name'  => env('APP_NAME', ''),
    'app_code'  => env('APP_CODE', 0),
    'log_dir'   => env('LOG_DIR') ? env('LOG_DIR') . env('APP_NAME') : storage_path('logs/app'),
    'db_listen' => env('DB_LISTEN', false),
];
