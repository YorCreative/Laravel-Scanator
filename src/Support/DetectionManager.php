<?php

namespace YorCreative\Scanator\Support;

use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class DetectionManager
{
    protected Collection $detections;

    protected \DateTimeImmutable $scan_start;

    public function __construct()
    {
        $this->scan_start = Carbon::now()->toDateTimeImmutable();
        $this->detections = new Collection();
    }

    public function recordDetection(Detection $detection): void
    {
        $this->detections->getOrPut($detection->getSignature(), $detection);
    }

    public function getDetections(): Collection
    {
        return $this->detections;
    }

    public function getScanStart(): \DateTimeImmutable
    {
        return $this->scan_start;
    }
}
