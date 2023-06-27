<?php

namespace YorCreative\Scanator;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Facade;
use YorCreative\Scanator\Services\ScanatorServiceContract;
use YorCreative\Scanator\Support\DetectionManager;

/**
 * @method static DetectionManager init()
 * @method static DetectionManager analyze(DetectionManager &$detectionManager, string $tableName, array|string $columns = '*')
 * @method static Collection getRegexCollection()
 */
class Scanator extends Facade
{
    protected static function getFacadeAccessor()
    {
        return ScanatorServiceContract::class;
    }
}
