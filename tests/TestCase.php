<?php

namespace YorCreative\Scanator\Tests;

use Illuminate\Database\ConnectionInterface;
use Illuminate\Support\Str;
use Mockery\MockInterface;
use YorCreative\Scanator\ScanatorProvider;

class TestCase extends \Orchestra\Testbench\TestCase
{
    public function setUp(): void
    {
        parent::setUp();

    }

    protected function getPackageProviders($app)
    {
        return [
            ScanatorProvider::class,
        ];
    }

    protected function mockConnection(MockInterface $connection)
    {
        $this->app->instance(ConnectionInterface::class, $connection);
    }

    protected function getMockFor($filename)
    {
        if (! Str::endsWith($filename, '.json')) {
            $filename .= '.json';
        }

        return json_decode(file_get_contents(__DIR__.'/Mocks/'.$filename), true);
    }

    protected function getMockInformationSchema()
    {
        $expectedTables = $this->getMockFor('SelectInformationSchemaResponse');

        foreach ($expectedTables as $key => $table) {
            $expectedTables[$key] = (object) $table;
        }

        return $expectedTables;
    }

    protected function getMockCleanSample()
    {
        return $this->transformToObject($this->getMockFor('ExampleSampleResponse'));
    }

    protected function getMockDirtySample()
    {
        return $this->transformToObject($this->getMockFor('DirtyExampleSampleResponse'));
    }

    protected function transformToObject(array $samples)
    {
        foreach ($samples as $key => $sample) {
            $samples[$key] = (object) $sample;
        }

        return $samples;
    }
}
