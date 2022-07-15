<?php

declare(strict_types=1);

namespace HNV\Http\StreamTests;

use HNV\Http\Helper\Collection\Resource\AccessModeType;
use HNV\Http\Helper\Generator\Text as TextGenerator;
use HNV\Http\Stream\Stream;
use PHPUnit\Framework\Attributes;
use RuntimeException;

use function fwrite;
use function rewind;
use function strlen;
use function substr;

/**
 * @internal
 */
#[Attributes\CoversClass(Stream::class)]
#[Attributes\Small]
class StreamReadTest extends AbstractStreamTestCase
{
    /**
     * @param resource $resource
     */
    #[Attributes\Test]
    #[Attributes\DataProvider('dataProviderResourcesWithReadParametersValid')]
    public function read($resource, int $length, string $contentExpected): void
    {
        $contentCaught = (new Stream($resource))->read($length);

        static::assertSame($contentExpected, $contentCaught);
    }

    /**
     * @param resource $resource
     */
    #[Attributes\Test]
    #[Attributes\DataProvider('dataProviderResourcesWithReadParametersInvalid')]
    public function readThrowsException($resource, int $length): void
    {
        $this->expectException(RuntimeException::class);

        (new Stream($resource))->read($length);

        static::fail('Expects exception on reading unreadable stream');
    }

    /**
     * @param resource $resource
     */
    #[Attributes\Test]
    #[Attributes\DataProvider('dataProviderResources')]
    public function readOnClosedStream($resource): void
    {
        $this->expectException(RuntimeException::class);

        $stream = new Stream($resource);
        $stream->close();
        $stream->read(0);

        static::fail('Expects exception on reading closed stream');
    }

    /**
     * @param resource $resource
     */
    #[Attributes\Test]
    #[Attributes\DataProvider('dataProviderResources')]
    public function readOnDetachedStream($resource): void
    {
        $this->expectException(RuntimeException::class);

        $stream = new Stream($resource);
        $stream->detach();
        $stream->read(0);

        static::fail('Expects exception on reading detached stream');
    }

    public function dataProviderResourcesWithReadParametersValid(): array
    {
        $result = [];

        foreach ($this->generateResources(AccessModeType::READABLE) as $resource) {
            $result[] = [$resource, 0, ''];
        }
        foreach ($this->generateResources(AccessModeType::READABLE_AND_WRITABLE) as $resource) {
            $content    = (new TextGenerator())->generate();
            fwrite($resource, $content);
            rewind($resource);
            $result[]   = [$resource, strlen($content), $content];
        }
        foreach ($this->generateResources(AccessModeType::READABLE_AND_WRITABLE) as $resource) {
            $content            = (new TextGenerator())->generate();
            $readLength         = (int) (strlen($content) / 2);
            $expectedContent    = substr($content, 0, $readLength);
            fwrite($resource, $content);
            rewind($resource);
            $result[]           = [$resource, $readLength, $expectedContent];
        }
        foreach ($this->generateResources(AccessModeType::READABLE_AND_WRITABLE) as $resource) {
            $content    = (new TextGenerator())->generate();
            fwrite($resource, $content);
            $this->reachResourceEnd($resource);
            $result[]   = [$resource, strlen($content), ''];
        }

        return $result;
    }

    public function dataProviderResourcesWithReadParametersInvalid(): array
    {
        $result = [];

        foreach ($this->generateResources(AccessModeType::WRITABLE_ONLY) as $resource) {
            $result[] = [$resource, 1];
        }
        foreach ($this->generateResources(AccessModeType::READABLE_ONLY) as $resource) {
            $result[] = [$resource, -1];
        }

        return $result;
    }
}
