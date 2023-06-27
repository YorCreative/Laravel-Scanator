<?php

namespace YorCreative\Scanator\Support;

use Carbon\Carbon;
use DateTimeImmutable;
use YorCreative\Scrubber\Interfaces\RegexCollectionInterface;

class Detection
{
    protected DateTimeImmutable $found_at;

    public function __construct(
        protected string $table,
        protected string $column,
        protected string $sample,
        protected RegexCollectionInterface $regexClass,
        protected DateTimeImmutable $scan_start
    ) {
        $this->found_at = Carbon::now()->toDateTimeImmutable();
    }

    public function getSignature(): string
    {
        return md5(json_encode(['table' => $this->table, 'column' => $this->column]));
    }

    public function toJson(): string
    {
        return json_encode($this->toArray());
    }

    public function getSample(): mixed
    {
        return $this->sample;
    }

    public function toArray(): array
    {
        return [
            'table' => $this->table,
            'column' => $this->column,
            'regex_class' => $this->regexClass,
            'found_at' => $this->found_at,
            'scan_start' => $this->scan_start,
        ];
    }

    public function getColumn(): string
    {
        return $this->column;
    }

    public function getTable(): string
    {
        return $this->table;
    }

    public function getRegexClass(): RegexCollectionInterface
    {
        return $this->regexClass;
    }

    public function getFoundAt(): DateTimeImmutable
    {
        return $this->found_at;
    }

    public function getScanStart(): DateTimeImmutable
    {
        return $this->scan_start;
    }
}
