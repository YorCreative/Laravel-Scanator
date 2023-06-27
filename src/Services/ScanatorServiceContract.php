<?php

namespace YorCreative\Scanator\Services;

use Illuminate\Support\Collection;
use YorCreative\Scanator\Support\DetectionManager;

interface ScanatorServiceContract
{
    public function init(): DetectionManager;

    public function getRegexCollection(): Collection;

    public function analyze(DetectionManager &$detectionManager, string $table_name, array|string $columns = '*'): void;
}
