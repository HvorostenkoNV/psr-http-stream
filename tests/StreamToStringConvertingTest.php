<?php

declare(strict_types=1);

namespace HNV\Http\StreamTests;

use HNV\Http\Helper\Collection\Resource\AccessModeType;
use HNV\Http\Helper\Generator\Text as TextGenerator;
use HNV\Http\Stream\Stream;
use PHPUnit\Framework\Attributes;

use function fseek;
use function fwrite;
use function rewind;
use function strlen;
use function substr;

/**
 * @internal
 */
#[Attributes\CoversClass(Stream::class)]
#[Attributes\Small]
class StreamToStringConvertingTest extends AbstractStreamTestCase
{
    /**
     * @param resource $resource
     */
    #[Attributes\Test]
    #[Attributes\DataProvider('dataProviderResourcesWithContent')]
    public function toStringCast($resource, string $content): void
    {
        $stream         = new Stream($resource);
        $streamAsString = (string) $stream;

        static::assertSame($content, $streamAsString);
    }

    /**
     * @param resource $resource
     */
    #[Attributes\Test]
    #[Attributes\DataProvider('dataProviderResources')]
    public function toStringOnClosedStream($resource): void
    {
        $stream = new Stream($resource);
        $stream->close();

        static::assertSame(
            '',
            (string) $stream,
            'Expects empty string on casting to string closed stream'
        );
    }

    /**
     * @param resource $resource
     */
    #[Attributes\Test]
    #[Attributes\DataProvider('dataProviderResources')]
    public function toStringOnDetachedStream($resource): void
    {
        $stream = new Stream($resource);
        $stream->detach();

        static::assertSame(
            '',
            (string) $stream,
            'Expects empty string on casting to string detached stream'
        );
    }

    public function dataProviderResourcesWithContent(): iterable
    {
        foreach ($this->generateResources(AccessModeType::ALL) as $resource) {
            yield [$resource, '', ''];
        }

        foreach ($this->generateResources(AccessModeType::READABLE_AND_WRITABLE) as $resource) {
            $content = (new TextGenerator())->generate();
            fwrite($resource, $content);

            yield [$resource, $content, ''];
        }

        foreach ($this->generateResources(AccessModeType::READABLE_AND_WRITABLE) as $resource) {
            $content = (new TextGenerator())->generate();
            fwrite($resource, $content);
            rewind($resource);

            yield [$resource, $content, $content];
        }

        foreach ($this->generateResources(AccessModeType::READABLE_AND_WRITABLE) as $resource) {
            $content            = (new TextGenerator())->generate();
            $seekValue          = (int) (strlen($content) / 2);
            $contentExpected    = substr($content, $seekValue);
            fwrite($resource, $content);
            fseek($resource, $seekValue);

            yield [$resource, $content, $contentExpected];
        }
    }
}
