<?php

declare(strict_types=1);

namespace HNV\Http\StreamTests;

use HNV\Http\Helper\Collection\Resource\AccessModeType;
use HNV\Http\Helper\Generator\Text as TextGenerator;
use HNV\Http\Stream\Stream;

use function fseek;
use function fwrite;
use function rewind;
use function strlen;
use function substr;

/**
 * PSR-7 StreamInterface implementation test.
 *
 * Testing stream to string converting behavior.
 *
 * @internal
 * @covers Stream
 * @small
 */
class StreamToStringConvertingTest extends AbstractStreamTest
{
    /**
     * @covers          Stream::__toString
     * @dataProvider    dataProviderResourcesWithContent
     *
     * @param resource $resource recourse
     */
    public function testRunProcess($resource, string $content): void
    {
        $stream         = new Stream($resource);
        $streamAsString = (string) $stream;

        static::assertSame(
            $content,
            $streamAsString,
            "Action \"Stream->__toString\" returned unexpected result.\n".
            "Expected result is \"{$content}\".\n".
            "Caught result is \"{$streamAsString}\"."
        );
    }

    /**
     * @covers          Stream::__toString
     * @dataProvider    dataProviderResources
     *
     * @param resource $resource recourse
     */
    public function testRunProcessInClosedState($resource): void
    {
        $stream = new Stream($resource);
        $stream->close();

        static::assertSame(
            '',
            (string) $stream,
            "Action \"Stream->close->__toString\" returned unexpected result.\n".
            "Expected result is \"empty string\".\n".
            'Caught result is "NOT empty string".'
        );
    }

    /**
     * @covers          Stream::__toString
     * @dataProvider    dataProviderResources
     *
     * @param resource $resource recourse
     */
    public function testRunProcessInDetachedState($resource): void
    {
        $stream = new Stream($resource);
        $stream->detach();

        static::assertSame(
            '',
            (string) $stream,
            "Action \"Stream->detach->__toString\" returned unexpected result.\n".
            "Expected result is \"empty string\".\n".
            'Caught result is "NOT empty string".'
        );
    }

    /**
     * Data provider: resources with data.
     */
    public function dataProviderResourcesWithContent(): array
    {
        $result = [];

        foreach ($this->generateResources(AccessModeType::ALL) as $resource) {
            $result[] = [$resource, '', ''];
        }
        foreach ($this->generateResources(AccessModeType::READABLE_AND_WRITABLE) as $resource) {
            $content    = (new TextGenerator())->generate();
            fwrite($resource, $content);
            $result[]   = [$resource, $content, ''];
        }
        foreach ($this->generateResources(AccessModeType::READABLE_AND_WRITABLE) as $resource) {
            $content    = (new TextGenerator())->generate();
            fwrite($resource, $content);
            rewind($resource);
            $result[]   = [$resource, $content, $content];
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
}
