<?php

use Carbon\Carbon;
use YorCreative\Scanator\Scanator;
use YorCreative\Scanator\Support\Detection;
use YorCreative\Scanator\Support\DetectionManager;
use YorCreative\Scanator\Tests\TestCase;
use YorCreative\Scrubber\Interfaces\RegexCollectionInterface;

class DetectionManagerTest extends TestCase
{
    protected DetectionManager $manager;

    protected Detection $detection;

    public function setUp(): void
    {
        parent::setUp();

        $this->manager = new DetectionManager();

        $this->detection = new Detection(
            'table',
            'column',
            'sample',
            Scanator::getRegexCollection()->get('google_api'),
            Carbon::now()->toDateTimeImmutable()
        );
    }

    public function testItCanGetScanStartTime()
    {
        $this->assertInstanceOf(DateTimeImmutable::class, $this->manager->getScanStart());
    }

    public function testItCanGetDetections()
    {
        $this->assertEmpty($this->manager->getDetections());

        $this->manager->recordDetection($this->detection);

        $this->assertCount(1, $this->manager->getDetections());
    }

    public function testDetectionProvidesInformation()
    {
        $this->assertInstanceOf(DateTimeImmutable::class, $this->detection->getScanStart());
        $this->assertInstanceOf(DateTimeImmutable::class, $this->detection->getFoundAt());

        $this->assertEquals('table', $this->detection->getTable());
        $this->assertEquals('column', $this->detection->getColumn());
        $this->assertEquals('sample', $this->detection->getSample());
        $this->assertInstanceOf(RegexCollectionInterface::class, $this->detection->getRegexClass());

        $this->assertIsArray($this->detection->toArray());
        $this->assertIsString($this->detection->toJson());
    }
}
