<?php

declare(strict_types=1);

namespace HNV\Http\StreamTests;

use HNV\Http\Helper\Collection\Resource\AccessModeType;
use HNV\Http\Helper\Generator\Text as TextGenerator;
use HNV\Http\Stream\Stream;
use PHPUnit\Framework\Attributes;

use function fwrite;
use function strlen;

/**
 * @internal
 */
#[Attributes\CoversClass(Stream::class)]
#[Attributes\Small]
class StreamSizeTest extends AbstractStreamTestCase
{
    /**
     * @param resource $resource
     */
    #[Attributes\Test]
    #[Attributes\DataProvider('dataProviderResourcesWithSizeValue')]
    public function getSize($resource, int $sizeExpected): void
    {
        $sizeCaught = (new Stream($resource))->getSize();

        static::assertSame($sizeExpected, $sizeCaught);
    }

    /**
     * @param resource $resource
     */
    #[Attributes\Test]
    #[Attributes\DataProvider('dataProviderResourcesWithDataToWrite')]
    public function getSizeOnClosedStream($resource, string $content): void
    {
        $stream = new Stream($resource);

        $stream->write($content);
        $stream->close();

        static::assertNull(
            $stream->getSize(),
            'Expects null during reading size of closed stream'
        );
    }

    /**
     * @param resource $resource
     */
    #[Attributes\Test]
    #[Attributes\DataProvider('dataProviderResourcesWithDataToWrite')]
    public function getSizeOnDetachedStream($resource, string $content): void
    {
        $stream = new Stream($resource);

        $stream->write($content);
        $stream->detach();

        static::assertNull(
            $stream->getSize(),
            'Expects null during reading size of detached stream'
        );
    }

    public function dataProviderResourcesWithSizeValue(): array
    {
        $result = [];

        foreach ($this->generateResources(AccessModeType::READABLE_ONLY) as $resource) {
            $result[] = [$resource, 0];
        }
        foreach ($this->generateResources(AccessModeType::WRITABLE_ONLY) as $resource) {
            $content    = (new TextGenerator())->generate();
            fwrite($resource, $content);
            $result[]   = [$resource, strlen($content)];
        }
        foreach ($this->generateResources(AccessModeType::READABLE_AND_WRITABLE) as $resource) {
            $content    = (new TextGenerator())->generate();
            fwrite($resource, $content);
            $result[]   = [$resource, strlen($content)];
        }

        return $result;
    }

    public function dataProviderResourcesWithDataToWrite(): array
    {
        $result = [];

        foreach ($this->generateResources(AccessModeType::WRITABLE) as $resource) {
            $content    = (new TextGenerator())->generate();
            $result[]   = [$resource, $content];
        }

        return $result;
    }
}
