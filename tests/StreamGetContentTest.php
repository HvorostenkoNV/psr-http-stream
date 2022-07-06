<?php

declare(strict_types=1);

namespace HNV\Http\StreamTests;

use HNV\Http\Helper\Collection\Resource\AccessModeType;
use HNV\Http\Helper\Generator\Text as TextGenerator;
use HNV\Http\Stream\Stream;
use RuntimeException;

use function fseek;
use function fwrite;
use function rewind;
use function strlen;
use function substr;

/**
 * PSR-7 StreamInterface implementation test.
 *
 * Testing stream content info providing.
 *
 * @internal
 * @covers Stream
 * @small
 */
class StreamGetContentTest extends AbstractStreamTest
{
    /**
     * @covers          Stream::getContents
     * @dataProvider    dataProviderResourcesWithReadParametersValid
     *
     * @param resource $resource resource
     */
    public function testGetContents($resource, string $contentFull, string $contentExpected): void
    {
        $stream = new Stream($resource);

        $contentCaught = $stream->getContents();
        static::assertSame(
            $contentExpected,
            $contentCaught,
            "Action \"Stream->getContents\" returned unexpected result.\n".
            "Expected result is \"{$contentExpected}\".\n".
            "Caught result is \"{$contentCaught}\"."
        );

        $stream->rewind();
        $contentCaught = $stream->getContents();
        static::assertSame(
            $contentFull,
            $contentCaught,
            "Action \"Stream->getContents->rewind->getContents\" returned unexpected result.\n".
            "Expected result is \"{$contentFull}\".\n".
            "Caught result is \"{$contentCaught}\"."
        );
    }

    /**
     * @covers          Stream::getContents
     * @dataProvider    dataProviderResourcesWithReadParametersValid
     *
     * @param resource $resource recourse
     */
    public function testGetContentsChangesSeekPosition($resource, string $content): void
    {
        $stream = new Stream($resource);

        $stream->rewind();
        static::assertSame(
            $content,
            $stream->getContents(),
            "Action \"Stream->rewind->getContents\" returned unexpected result.\n".
            "Expected result is \"same content as was set\".\n".
            'Caught result is "NOT the same".'
        );
        static::assertSame(
            '',
            $stream->getContents(),
            "Action \"Stream->rewind->getContents->getContents\" returned unexpected result.\n".
            "Expected result is \"empty string\".\n".
            'Caught result is "NOT empty string".'
        );
        $stream->rewind();
        static::assertSame(
            $content,
            $stream->getContents(),
            'Action "Stream->rewind->getContents->getContents->rewind->getContents"'.
            " returned unexpected result.\n".
            "Expected result is \"same content as was set\".\n".
            'Caught result is "NOT the same".'
        );
    }

    /**
     * @covers          Stream::getContents
     * @dataProvider    dataProviderResourcesWithReadParametersInvalid
     *
     * @param resource $resource resource
     */
    public function testGetContentsThrowsException($resource): void
    {
        $this->expectException(RuntimeException::class);

        (new Stream($resource))->getContents();

        static::fail(
            "Action \"Stream->getContents\" threw no expected exception.\n".
            "Expects \"RuntimeException\" exception.\n".
            'Caught no exception.'
        );
    }

    /**
     * @covers          Stream::getContents
     * @dataProvider    dataProviderResources
     *
     * @param resource $resource resource
     */
    public function testGetContentsInClosedState($resource): void
    {
        $this->expectException(RuntimeException::class);

        $stream = new Stream($resource);

        $stream->close();
        $stream->getContents();

        static::fail(
            "Action \"Stream->close->getContents\" threw no expected exception.\n".
            "Expects \"RuntimeException\" exception.\n".
            'Caught no exception.'
        );
    }

    /**
     * @covers          Stream::getContents
     * @dataProvider    dataProviderResources
     *
     * @param resource $resource resource
     */
    public function testGetContentsInDetachedState($resource): void
    {
        $this->expectException(RuntimeException::class);

        $stream = new Stream($resource);

        $stream->detach();
        $stream->getContents();

        static::fail(
            "Action \"Stream->detach->getContents\" threw no expected exception.\n".
            "Expects \"RuntimeException\" exception.\n".
            'Caught no exception.'
        );
    }

    /**
     * Data provider: resources with data to read valid value.
     */
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

    /**
     * Data provider: resources with data to read invalid value.
     */
    public function dataProviderResourcesWithReadParametersInvalid(): array
    {
        $result = [];

        foreach ($this->generateResources(AccessModeType::WRITABLE_ONLY) as $resource) {
            $result[] = [$resource];
        }

        return $result;
    }
}
