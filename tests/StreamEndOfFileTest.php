<?php

declare(strict_types=1);

namespace HNV\Http\StreamTests;

use HNV\Http\Helper\Collection\Resource\AccessModeType;
use HNV\Http\Helper\Generator\Text as TextGenerator;
use HNV\Http\Stream\Stream;
use PHPUnit\Framework\Attributes;

use function fwrite;

/**
 * @internal
 */
#[Attributes\CoversClass(Stream::class)]
#[Attributes\Small]
class StreamEndOfFileTest extends AbstractStreamTestCase
{
    /**
     * @param resource $resource
     */
    #[Attributes\Test]
    #[Attributes\DataProvider('dataProviderResourcesWithValues')]
    public function eof($resource, bool $endOfFileExpected): void
    {
        $endOfFileCaught = (new Stream($resource))->eof();

        static::assertSame($endOfFileExpected, $endOfFileCaught);
    }

    /**
     * @param resource $resource
     */
    #[Attributes\Test]
    #[Attributes\DataProvider('dataProviderResources')]
    public function eofOnClosedStream($resource): void
    {
        $stream = new Stream($resource);

        $stream->rewind();
        $stream->close();

        static::assertTrue(
            $stream->eof(),
            'Expects [close] operation WILL NOT change stream pointer position'
        );
    }

    /**
     * @param resource $resource
     */
    #[Attributes\Test]
    #[Attributes\DataProvider('dataProviderResources')]
    public function eofOnDetachedStream($resource): void
    {
        $stream = new Stream($resource);

        $stream->rewind();
        $stream->detach();

        static::assertTrue(
            $stream->eof(),
            'Expects [detach] operation WILL NOT change stream pointer position'
        );
    }

    public function dataProviderResourcesWithValues(): array
    {
        $result = [];

        foreach ($this->generateResources(AccessModeType::ALL) as $resource) {
            $result[] = [$resource, false];
        }
        foreach ($this->generateResources(AccessModeType::WRITABLE) as $resource) {
            $content    = (new TextGenerator())->generate();
            fwrite($resource, $content);
            $result[]   = [$resource, false];
        }
        foreach ($this->generateResources(AccessModeType::WRITABLE_ONLY) as $resource) {
            $content    = (new TextGenerator())->generate();
            fwrite($resource, $content);
            $this->reachResourceEnd($resource);
            $result[]   = [$resource, false];
        }
        foreach ($this->generateResources(AccessModeType::READABLE_AND_WRITABLE) as $resource) {
            $content    = (new TextGenerator())->generate();
            fwrite($resource, $content);
            $this->reachResourceEnd($resource);
            $result[]   = [$resource, true];
        }

        return $result;
    }
}
