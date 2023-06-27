<?php

return [
    'sql' => [
        'ignore_tables' => [
            'failed_jobs',
            'migrations',
        ],
        'ignore_columns' => [
            'id',
            'created_at',
            'updated_at',
        ],
        'ignore_types' => [
            'timestamp',
        ],
        'select' => [
            'low_limit' => 3,
            'high_limit' => 10,
        ],
    ],
];
