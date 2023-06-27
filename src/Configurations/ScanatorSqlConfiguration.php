<?php

namespace YorCreative\Scanator\Configurations;

class ScanatorSqlConfiguration implements ScanatorConfigurationContract
{
    protected int $sql_limit_high;

    protected int $sql_limit_low;

    protected array $sql_ignore_tables;

    protected array $sql_ignore_columns;

    protected array $sql_ignore_types;

    public function __construct()
    {
        //
    }

    public function getSqlSchemaQuery(): string
    {
        return '
        SELECT
            t.table_name,
            c.column_name,
            c.data_type,
            c.character_maximum_length,
            c.column_default,
            c.is_nullable
        FROM
            information_schema.tables AS t
        INNER JOIN
            information_schema.columns AS c ON t.table_name = c.table_name
        WHERE
            t.table_schema = ?';
    }

    public function getSqlTableSampleQuery(): string
    {
        return 'SELECT %s FROM %s LIMIT '.$this->getSqlLimitLow().','.$this->getSqlLimitHigh();
    }

    public function getSqlLimitLow(): int
    {
        return $this->sql_limit_low;
    }

    public function setSqlLimitLow(int $sql_limit_low): self
    {
        $this->sql_limit_low = $sql_limit_low;

        return $this;
    }

    public function getSqlLimitHigh(): int
    {
        return $this->sql_limit_high;
    }

    public function setSqlLimitHigh(int $sql_limit_high): self
    {
        $this->sql_limit_high = $sql_limit_high;

        return $this;
    }

    public function getSqlIgnoreTypes(): array
    {
        return $this->sql_ignore_types;
    }

    public function setSqlIgnoreTypes(array $sql_ignore_types): self
    {
        $this->sql_ignore_types = $sql_ignore_types;

        return $this;
    }

    public function getSqlIgnoreColumns(): array
    {
        return $this->sql_ignore_columns;
    }

    public function setSqlIgnoreColumns(array $sql_ignore_columns): self
    {
        $this->sql_ignore_columns = $sql_ignore_columns;

        return $this;
    }

    public function getSqlIgnoreTables(): array
    {
        return $this->sql_ignore_tables;
    }

    public function setSqlIgnoreTables(array $sql_ignore_tables): self
    {
        $this->sql_ignore_tables = $sql_ignore_tables;

        return $this;
    }
}
