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
use function rewind;
use function strlen;
use function substr;

/**
 * @internal
 */
#[Attributes\CoversClass(Stream::class)]
#[Attributes\Small]
class StreamGetContentTest extends AbstractStreamTestCase
{
    /**
     * @param resource $resource
     */
    #[Attributes\Test]
    #[Attributes\DataProvider('dataProviderResourcesWithReadParametersValid')]
    public function getContents($resource, string $contentFull, string $contentExpected): void
    {
        $stream = new Stream($resource);

        static::assertSame($contentExpected, $stream->getContents());

        $stream->rewind();
        static::assertSame($contentFull, $stream->getContents());
    }

    /**
     * @param resource $resource
     */
    #[Attributes\Test]
    #[Attributes\DataProvider('dataProviderResourcesWithReadParametersValid')]
    public function getContentsChangesSeekPosition($resource, string $content): void
    {
        $stream = new Stream($resource);

        $stream->rewind();
        static::assertSame($content, $stream->getContents());
        static::assertSame(
            '',
            $stream->getContents(),
            'Expects [getContents] WILL chang stream pointer position, '.
            'so after this method second usage you are going to get empty string'
        );
        $stream->rewind();
        static::assertSame($content, $stream->getContents());
    }

    /**
     * @param resource $resource
     */
    #[Attributes\Test]
    #[Attributes\DataProvider('dataProviderResourcesWithReadParametersInvalid')]
    public function getContentsThrowsException($resource): void
    {
        $this->expectException(RuntimeException::class);

        (new Stream($resource))->getContents();

        static::fail('Expects exception on reading unreadable stream');
    }

    /**
     * @param resource $resource
     */
    #[Attributes\Test]
    #[Attributes\DataProvider('dataProviderResources')]
    public function getContentsOnClosedStream($resource): void
    {
        $this->expectException(RuntimeException::class);

        $stream = new Stream($resource);

        $stream->close();
        $stream->getContents();

        static::fail('Expects exception on reading closed stream');
    }

    /**
     * @param resource $resource
     */
    #[Attributes\Test]
    #[Attributes\DataProvider('dataProviderResources')]
    public function getContentsOnDetachedStream($resource): void
    {
        $this->expectException(RuntimeException::class);

        $stream = new Stream($resource);

        $stream->detach();
        $stream->getContents();

        static::fail('Expects exception on reading detached stream');
    }

    public function dataProviderResourcesWithReadParametersValid(): array
    {
        $result = [];

        foreach ($this->generateResources(AccessModeType::READABLE) as $resource) {
            $result[] = [$resource, '', ''];
        }
        foreach ($this->generateResources(AccessModeType::READABLE_AND_WRITABLE) as $resource) {
            $content    = (new TextGenerator())->generate();
            fwrite($resource, $content);
            rewind($resource);
            $result[]   = [$resource, $content, $content];
        }
        foreach ($this->generateResources(AccessModeType::READABLE_AND_WRITABLE) as $resource) {
            $content    = (new TextGenerator())->generate();
            fwrite($resource, $content);
            $result[]   = [$resource, $content, ''];
        }
        foreach ($this->generateResources(AccessModeType::READABLE_AND_WRITABLE) as $resource) {
            $content            = (new TextGenerator())->generate();
            $seekValue          = (int) (strlen($content) / 2);
            $contentExpected    = substr($content, $seekValue);
            fwrite($resource, $content);
            fseek($resource, $seekValue);
            $result[]           = [$resource, $content, $contentExpected];
        }

        return $result;
    }

    public function dataProviderResourcesWithReadParametersInvalid(): array
    {
        $result = [];

        foreach ($this->generateResources(AccessModeType::WRITABLE_ONLY) as $resource) {
            $result[] = [$resource];
        }

        return $result;
    }
}
