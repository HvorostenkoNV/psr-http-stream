<?php

declare(strict_types=1);

namespace HNV\Http\StreamTests;

use HNV\Http\Helper\Collection\Resource\AccessModeType;
use HNV\Http\Stream\Stream;

/**
 * PSR-7 StreamInterface implementation test.
 *
 * Testing stream seekable state info providing.
 *
 * @internal
 * @covers Stream
 * @small
 */
class StreamIsSeekableTest extends AbstractStreamTest
{
    /**
     * @covers          Stream::isSeekable
     * @dataProvider    dataProviderResourcesWithSeekingStateValues
     *
     * @param resource $resource recourse
     */
    public function testIsSeekable($resource, bool $stateExpected): void
    {
        $stateCaught = (new Stream($resource))->isSeekable();

        static::assertSame(
            $stateExpected,
            $stateCaught,
            "Action \"Stream->isSeekable\" returned unexpected result.\n".
            "Expected result is \"{$stateExpected}\".\n".
            "Caught result is \"{$stateCaught}\"."
        );
    }

    /**
     * @covers          Stream::isSeekable
     * @dataProvider    dataProviderResources
     *
     * @param resource $resource recourse
     */
    public function testIsSeekableInClosedState($resource): void
    {
        $stream = new Stream($resource);
        $stream->close();

        static::assertFalse(
            $stream->isSeekable(),
            "Action \"Stream->close->isSeekable\" returned unexpected result.\n".
            "Expected result is \"false\".\n".
            'Caught result is "NOT false".'
        );
    }

    /**
     * @covers          Stream::isSeekable
     * @dataProvider    dataProviderResources
     *
     * @param resource $resource recourse
     */
    public function testIsSeekableInDetachedState($resource): void
    {
        $stream = new Stream($resource);
        $stream->detach();

        static::assertFalse(
            $stream->isSeekable(),
            "Action \"Stream->detach->isSeekable\" returned unexpected result.\n".
            "Expected result is \"false\".\n".
            'Caught result is "NOT false".'
        );
    }

    /**
     * Data provider: resources with their seekable state.
     */
    public function dataProviderResourcesWithSeekingStateValues(): array
    {
        $result = [];

        foreach ($this->generateResources(AccessModeType::ALL) as $resource) {
            $result[] = [$resource, true];
        }

        return $result;
    }
}
