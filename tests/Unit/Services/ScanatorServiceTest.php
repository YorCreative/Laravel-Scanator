<?php

namespace YorCreative\Scanator\Test\Unit\Services;

use Illuminate\Database\ConnectionInterface;
use Illuminate\Support\Collection;
use Mockery;
use ReflectionClass;
use YorCreative\Scanator\Services\ScanatorService;
use YorCreative\Scanator\Services\ScanatorServiceContract;
use YorCreative\Scanator\Tests\TestCase;

class ScanatorServiceTest extends TestCase
{
    public function testItCanRetrieveTablesAndColumnsFromSqlSchema()
    {
        $connection = Mockery::mock(ConnectionInterface::class);

        $connection->expects('getDatabaseName')->andReturn('test_scanator_db');

        $connection->shouldReceive('select')->andReturn($this->getMockInformationSchema())->once();

        $this->mockConnection($connection);

        $service = $this->app->make(ScanatorServiceContract::class);

        $reflectionClass = new ReflectionClass(ScanatorService::class);

        $reflectionMethod = $reflectionClass->getMethod('retrieveSchema');

        $schema = $reflectionMethod->invoke($service);

        $this->assertCount(4, $schema);
    }

    public function testItCanRetrieveSampleData()
    {
        $connection = Mockery::mock(ConnectionInterface::class);

        $sampleMock = $this->getMockCleanSample();

        $connection->shouldReceive('select')->andReturn($sampleMock);

        $this->mockConnection($connection);

        $service = $this->app->make(ScanatorServiceContract::class);

        $reflectionClass = new ReflectionClass(ScanatorService::class);

        $reflectionMethod = $reflectionClass->getMethod('retrieveTestSample');

        $sample = $reflectionMethod->invoke($service, 'examples');

        $this->assertEquals(new Collection($sampleMock), $sample);
    }

    public function testItCanReturnDetections()
    {
        $connection = Mockery::mock(ConnectionInterface::class);

        $connection->expects('getDatabaseName')->andReturn('test_scanator_db');

        $connection->shouldReceive('select')->andReturn($this->getMockInformationSchema())->once();

        $connection->shouldReceive('select')->andReturn($this->getMockDirtySample())->once();

        $connection->shouldReceive('select')->andReturn($this->getMockCleanSample())->times(3);

        $this->mockConnection($connection);

        $service = $this->app->make(ScanatorServiceContract::class);

        $detectionManager = $service->init();

        $this->assertCount(1, $detectionManager->getDetections());
    }
}
