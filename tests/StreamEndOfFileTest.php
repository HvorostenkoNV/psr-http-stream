<?php

declare(strict_types=1);

namespace HNV\Http\StreamTests;

use HNV\Http\Helper\Collection\Resource\AccessModeType;
use HNV\Http\Helper\Generator\Text as TextGenerator;
use HNV\Http\Stream\Stream;

use function fwrite;

/**
 * PSR-7 StreamInterface implementation test.
 *
 * Testing stream end of file info providing.
 *
 * @internal
 * @covers Stream
 * @small
 */
class StreamEndOfFileTest extends AbstractStreamTest
{
    /**
     * @covers          Stream::eof
     * @dataProvider    dataProviderResourcesWithValues
     *
     * @param resource $resource recourse
     */
    public function testEof($resource, bool $endOfFileExpected): void
    {
        $endOfFileCaught = (new Stream($resource))->eof();

        static::assertSame(
            $endOfFileExpected,
            $endOfFileCaught,
            "Action \"Stream->eof\" returned unexpected result.\n".
            "Expected result is \"{$endOfFileExpected}\".\n".
            "Caught result is \"{$endOfFileCaught}\"."
        );
    }

    /**
     * @covers          Stream::eof
     * @dataProvider    dataProviderResources
     *
     * @param resource $resource recourse
     */
    public function testEofInClosedState($resource): void
    {
        $stream = new Stream($resource);

        $stream->rewind();
        $stream->close();

        static::assertTrue(
            $stream->eof(),
            "Action \"Stream->close->eof\" returned unexpected result.\n".
            "Expected result is \"true\".\n".
            'Caught result is "NOT true".'
        );
    }

    /**
     * @covers          Stream::eof
     * @dataProvider    dataProviderResources
     *
     * @param resource $resource recourse
     */
    public function testEofInDetachedState($resource): void
    {
        $stream = new Stream($resource);

        $stream->rewind();
        $stream->detach();

        static::assertTrue(
            $stream->eof(),
            "Action \"Stream->detach->eof\" returned unexpected result.\n".
            "Expected result is \"true\".\n".
            'Caught result is "NOT true".'
        );
    }

    /**
     * Data provider: resources with cursor pointer in the end.
     */
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
