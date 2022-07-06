<?php

declare(strict_types=1);

namespace HNV\Http\StreamTests;

use HNV\Http\Helper\Collection\Resource\AccessModeType;
use HNV\Http\Stream\Stream;

/**
 * PSR-7 StreamInterface implementation test.
 *
 * Testing stream readable state info providing.
 *
 * @internal
 * @covers Stream
 * @small
 */
class StreamIsReadableTest extends AbstractStreamTest
{
    /**
     * @covers          Stream::isReadable
     * @dataProvider    dataProviderResourcesWithReadableState
     *
     * @param resource $resource recourse
     */
    public function testIsReadable($resource, bool $stateExpected): void
    {
        $stateCaught = (new Stream($resource))->isReadable();

        static::assertSame(
            $stateExpected,
            $stateCaught,
            "Action \"Stream->isReadable\" returned unexpected result.\n".
            "Expected result is \"{$stateExpected}\".\n".
            "Caught result is \"{$stateCaught}\"."
        );
    }

    /**
     * @covers          Stream::isReadable
     * @dataProvider    dataProviderResources
     *
     * @param resource $resource recourse
     */
    public function testIsReadableInClosedState($resource): void
    {
        $stream = new Stream($resource);
        $stream->close();

        static::assertFalse(
            $stream->isReadable(),
            "Action \"Stream->close->isReadable\" returned unexpected result.\n".
            "Expected result is \"false\".\n".
            'Caught result is "NOT false".'
        );
    }

    /**
     * @covers          Stream::isReadable
     * @dataProvider    dataProviderResources
     *
     * @param resource $resource recourse
     */
    public function testIsReadableInDetachedState($resource): void
    {
        $stream = new Stream($resource);
        $stream->detach();

        static::assertFalse(
            $stream->isReadable(),
            "Action \"Stream->detach->isReadable\" returned unexpected result.\n".
            "Expected result is \"false\".\n".
            'Caught result is "NOT false".'
        );
    }

    /**
     * Data provider: resources with their readable state.
     */
    public function dataProviderResourcesWithReadableState(): array
    {
        $result = [];

        foreach ($this->generateResources(AccessModeType::READABLE_ONLY) as $resource) {
            $result[] = [$resource, true];
        }
        foreach ($this->generateResources(AccessModeType::WRITABLE_ONLY) as $resource) {
            $result[] = [$resource, false];
        }
        foreach ($this->generateResources(AccessModeType::READABLE_AND_WRITABLE) as $resource) {
            $result[] = [$resource, true];
        }

        return $result;
    }
}
