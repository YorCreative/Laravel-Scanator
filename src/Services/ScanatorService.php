<?php

namespace YorCreative\Scanator\Services;

use Illuminate\Database\ConnectionInterface;
use Illuminate\Support\Collection;
use stdClass;
use YorCreative\Scanator\Configurations\ScanatorSqlConfiguration;
use YorCreative\Scanator\Support\Detection;
use YorCreative\Scanator\Support\DetectionManager;
use YorCreative\Scrubber\Interfaces\RegexCollectionInterface;
use YorCreative\Scrubber\Repositories\RegexRepository;

class ScanatorService implements ScanatorServiceContract
{
    public function __construct(
        protected ConnectionInterface $db_connection,
        protected RegexRepository $regexRepository,
        protected ScanatorSqlConfiguration $scanator_config
    ) {
        //
    }

    public function init(): DetectionManager
    {
        $manager = new DetectionManager();

        self::retrieveSchema()->each(function (array $table) use (&$manager) {
            $column_names = array_column($table['columns'], 'column_name');
            self::analyze($manager, $table['table_name'], $column_names);
        });

        return $manager;
    }

    protected function retrieveSchema(): Collection
    {
        $database_name = $this->db_connection->getDatabaseName();

        $tables = $this->db_connection->select($this->scanator_config->getSqlSchemaQuery(), [$database_name]);

        $tables_with_columns = [];

        foreach ($tables as $table) {

            if ($this->isTableIgnorable($table_name = $table->TABLE_NAME)) {
                continue;
            }

            if (! $this->hasTableAlready($tables_with_columns, $table_name)) {
                $tables_with_columns[$table_name] = [
                    'table_name' => $table_name,
                    'columns' => [],
                ];
            }

            if ($this->isColumnAndDataTypeIgnorable($table)) {
                continue;
            }

            $tables_with_columns[$table_name]['columns'][$columnName = $table->COLUMN_NAME] = [
                'column_name' => $columnName,
                'data_type' => $table->DATA_TYPE,
                'character_maximum_length' => $table->CHARACTER_MAXIMUM_LENGTH,
                'column_default' => $table->COLUMN_DEFAULT,
                'is_nullable' => $table->IS_NULLABLE === 'YES',
            ];
        }

        return new Collection(array_values($tables_with_columns));
    }

    private function hasTableAlready(array $tables, $table_name)
    {
        return isset($tables[$table_name]);
    }

    private function isTableIgnorable($table_name): bool
    {
        return in_array($table_name, $this->scanator_config->getSqlIgnoreTables());
    }

    private function isColumnAndDataTypeIgnorable(stdClass $table): bool
    {
        return
            $this->isColumnIgnorable($table->COLUMN_NAME)
            || $this->isDataTypeIgnorable($table->DATA_TYPE);
    }

    private function isColumnIgnorable($column_name): bool
    {
        return ! ctype_lower($column_name) || in_array($column_name, $this->scanator_config->getSqlIgnoreColumns());
    }

    private function isDataTypeIgnorable($data_type): bool
    {
        return in_array($data_type, $this->scanator_config->getSqlIgnoreTypes());
    }

    public function analyze(DetectionManager &$detectionManager, string $table_name, array|string $columns = '*'): void
    {
        self::retrieveTestSample($table_name, $columns)->each(function (stdClass $sample) use (&$detectionManager, $table_name) {
            $this->scanSample((array) $sample, $table_name, $detectionManager);
        });
    }

    protected function retrieveTestSample(string $table, array|string $columns = '*'): Collection
    {
        if (is_array($columns)) {
            $columns = implode(', ', $columns);
        }

        $query = sprintf($this->scanator_config->getSqlTableSampleQuery(), $columns, $table);
        $samples = $this->db_connection->select($query);

        return new Collection($samples);
    }

    private function scanSample(array $sample, string $table_name, DetectionManager &$detectionManager)
    {
        $this->regexRepository->getRegexCollection()->each(function (RegexCollectionInterface $regexClass) use ($sample, $table_name, &$detectionManager) {
            if ($this->checkSample($regexClass, json_encode($sample))) {
                $column = $this->determineColumn($sample);
                $detectionManager->recordDetection(
                    new Detection($table_name, $column, $sample[$column], $regexClass, $detectionManager->getScanStart())
                );
            }
        });
    }

    /**
     * @param  array  $sample
     */
    private function checkSample(RegexCollectionInterface $regexClass, string $sample): bool
    {
        return $this->regexRepository::check($regexClass->getPattern(), $sample) > 0;
    }

    public function determineColumn(array $sample): string
    {
        $confirmation = false;
        $hitColumn = '';

        foreach ($sample as $column => $value) {
            if (! $confirmation) {
                $this->getRegexCollection()->each(function (RegexCollectionInterface $regexClass) use (&$hitColumn, &$confirmation, $column, $value) {
                    if (! $confirmation) {

                        if ($this->checkSample($regexClass, json_encode($value))) {
                            $hitColumn = $column;
                            $confirmation = true;
                        }
                    }
                });
            }
        }

        return $hitColumn;
    }

    public function getRegexCollection(): Collection
    {
        return $this->regexRepository->getRegexCollection();
    }
}
