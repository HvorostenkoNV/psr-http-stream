<?php

declare(strict_types=1);

namespace HNV\Http\StreamTests;

use HNV\Http\Helper\Collection\Resource\AccessModeType;
use HNV\Http\Helper\Generator\Text as TextGenerator;
use HNV\Http\Stream\Stream;
use PHPUnit\Framework\Attributes;
use RuntimeException;

use function fseek;
use function fwrite;
use function strlen;

/**
 * @internal
 */
#[Attributes\CoversClass(Stream::class)]
#[Attributes\Small]
class StreamCursorPointerPositionTest extends AbstractStreamTestCase
{
    /**
     * @param resource $resource
     */
    #[Attributes\Test]
    #[Attributes\DataProvider('dataProviderResourcesInValidState')]
    public function tell($resource, int $positionExpected): void
    {
        $positionCaught = (new Stream($resource))->tell();

        static::assertSame($positionExpected, $positionCaught);
    }

    /**
     * @param resource $resource
     */
    #[Attributes\Test]
    #[Attributes\DataProvider('dataProviderResourcesInInvalidState')]
    public function tellThrowsException($resource): void
    {
        $this->expectException(RuntimeException::class);

        (new Stream($resource))->tell();

        static::fail('Expects exception on [tell] unreachable resource');
    }

    /**
     * @param resource $resource
     */
    #[Attributes\Test]
    #[Attributes\DataProvider('dataProviderResources')]
    public function tellOnClosedStream($resource): void
    {
        $this->expectException(RuntimeException::class);

        $stream = new Stream($resource);

        $stream->close();
        $stream->tell();

        static::fail('Expects exception with [tell] on closed stream');
    }

    /**
     * @param resource $resource
     */
    #[Attributes\Test]
    #[Attributes\DataProvider('dataProviderResources')]
    public function tellOnDetachedStream($resource): void
    {
        $this->expectException(RuntimeException::class);

        $stream = new Stream($resource);

        $stream->detach();
        $stream->tell();

        static::fail('Expects exception with [tell] on detached stream');
    }

    public function dataProviderResourcesInValidState(): iterable
    {
        foreach ($this->generateResources(AccessModeType::READABLE) as $resource) {
            yield [$resource, 0];
        }

        foreach ($this->generateResources(AccessModeType::READABLE_AND_WRITABLE) as $resource) {
            $content = (new TextGenerator())->generate();
            fwrite($resource, $content);

            yield [$resource, strlen($content)];
        }

        foreach ($this->generateResources(AccessModeType::READABLE_AND_WRITABLE) as $resource) {
            $content    = (new TextGenerator())->generate();
            $seekValue  = (int) (strlen($content) / 2);
            fwrite($resource, $content);
            fseek($resource, $seekValue);

            yield [$resource, $seekValue];
        }
    }

    public function dataProviderResourcesInInvalidState(): iterable
    {
        foreach ($this->generateResources(AccessModeType::WRITABLE_ONLY) as $resource) {
            yield [$resource];
        }
    }
}
