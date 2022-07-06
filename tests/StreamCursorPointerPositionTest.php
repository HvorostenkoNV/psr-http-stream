<?php

declare(strict_types=1);

namespace HNV\Http\StreamTests;

use HNV\Http\Helper\Collection\Resource\AccessModeType;
use HNV\Http\Helper\Generator\Text as TextGenerator;
use HNV\Http\Stream\Stream;
use RuntimeException;

use function fseek;
use function fwrite;
use function strlen;

/**
 * PSR-7 StreamInterface implementation test.
 *
 * Testing stream current position pointer providing.
 *
 * @internal
 * @covers Stream
 * @small
 */
class StreamCursorPointerPositionTest extends AbstractStreamTest
{
    /**
     * @covers          Stream::tell
     * @dataProvider    dataProviderResourcesInValidState
     *
     * @param resource $resource recourse
     */
    public function testTell($resource, int $positionExpected): void
    {
        $positionCaught = (new Stream($resource))->tell();

        static::assertSame(
            $positionExpected,
            $positionCaught,
            "Action \"Stream->tell\" returned unexpected result.\n".
            "Expected result is \"{$positionExpected}\".\n".
            "Caught result is \"{$positionCaught}\"."
        );
    }

    /**
     * @covers          Stream::tell
     * @dataProvider    dataProviderResourcesInInvalidState
     *
     * @param resource $resource recourse
     */
    public function testTellThrowsException($resource): void
    {
        $this->expectException(RuntimeException::class);

        (new Stream($resource))->tell();

        static::fail(
            "Action \"Stream->tell\" threw no expected exception.\n".
            "Expects \"RuntimeException\" exception.\n".
            'Caught no exception.'
        );
    }

    /**
     * @covers          Stream::tell
     * @dataProvider    dataProviderResources
     *
     * @param resource $resource recourse
     */
    public function testTellInClosedState($resource): void
    {
        $this->expectException(RuntimeException::class);

        $stream = new Stream($resource);

        $stream->close();
        $stream->tell();

        static::fail(
            "Action \"Stream->close->tell\" threw no expected exception.\n".
            "Expects \"RuntimeException\" exception.\n".
            'Caught no exception.'
        );
    }

    /**
     * @covers          Stream::tell
     * @dataProvider    dataProviderResources
     *
     * @param resource $resource recourse
     */
    public function testTellInDetachedState($resource): void
    {
        $this->expectException(RuntimeException::class);

        $stream = new Stream($resource);

        $stream->detach();
        $stream->tell();

        static::fail(
            "Action \"Stream->detach->tell\" threw no expected exception.\n".
            "Expects \"RuntimeException\" exception.\n".
            'Caught no exception.'
        );
    }

    /**
     * Data provider: resources with cursor pointer in valid positions.
     */
    public function dataProviderResourcesInValidState(): array
    {
        $result = [];

        foreach ($this->generateResources(AccessModeType::READABLE) as $resource) {
            $result[]   = [$resource, 0];
        }
        foreach ($this->generateResources(AccessModeType::READABLE_AND_WRITABLE) as $resource) {
            $content    = (new TextGenerator())->generate();
            fwrite($resource, $content);
            $result[]   = [$resource, strlen($content)];
        }
        foreach ($this->generateResources(AccessModeType::READABLE_AND_WRITABLE) as $resource) {
            $content    = (new TextGenerator())->generate();
            $seekValue  = (int) (strlen($content) / 2);
            fwrite($resource, $content);
            fseek($resource, $seekValue);
            $result[]   = [$resource, $seekValue];
        }

        return $result;
    }

    /**
     * Data provider: resources with cursor pointer in invalid values.
     */
    public function dataProviderResourcesInInvalidState(): array
    {
        $result = [];

        foreach ($this->generateResources(AccessModeType::WRITABLE_ONLY) as $resource) {
            $result[] = [$resource];
        }

        return $result;
    }
}
