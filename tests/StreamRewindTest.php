<?php
declare(strict_types=1);

namespace HNV\Http\StreamTests;

use Throwable;
use RuntimeException;
use PHPUnit\Framework\TestCase;
use HNV\Http\StreamTests\Generator\Resource\All as ResourceGeneratorAll;
use HNV\Http\Stream\Stream;

use function ftell;
/** ***********************************************************************************************
 * PSR-7 StreamInterface implementation test.
 *
 * Testing stream rewinding behavior.
 *
 * @package HNV\Psr\Http\Tests\Stream
 * @author  Hvorostenko
 *************************************************************************************************/
class StreamRewindTest extends TestCase
{
    /** **********************************************************************
     * Test "Stream::rewind" seeks the stream to the beginning.
     *
     * @covers          Stream::rewind
     * @dataProvider    dataProviderResourcesSeekable
     *
     * @param           resource $resource              Recourse.
     *
     * @return          void
     * @throws          Throwable
     ************************************************************************/
    public function testRewind($resource): void
    {
        $stream = new Stream($resource);
        $stream->rewind();

        self::assertEquals(
            0,
            ftell($resource),
            "Action \"Stream->rewind\" showed unexpected behavior.\n".
            "Expects underlying resource is \"seeked to the beginning\".\n".
            "Underlying resource is \"NOT rewound\"."
        );
    }
    /** **********************************************************************
     * Test "Stream::rewind" behavior with stream in a closed state.
     *
     * @covers          Stream::rewind
     * @dataProvider    dataProviderResources
     *
     * @param           resource $resource              Recourse.
     *
     * @return          void
     * @throws          Throwable
     ************************************************************************/
    public function testRewindInClosedState($resource): void
    {
        $this->expectException(RuntimeException::class);

        $stream = new Stream($resource);
        $stream->close();
        $stream->rewind();

        self::fail(
            "Action \"Stream->close->rewind\" threw no expected exception.\n".
            "Expects \"RuntimeException\" exception.\n".
            'Caught no exception.'
        );
    }
    /** **********************************************************************
     * Test "Stream::rewind" behavior with stream in a detached state.
     *
     * @covers          Stream::rewind
     * @dataProvider    dataProviderResources
     *
     * @param           resource $resource              Recourse.
     *
     * @return          void
     * @throws          Throwable
     ************************************************************************/
    public function testRewindInDetachedState($resource): void
    {
        $this->expectException(RuntimeException::class);

        $stream = new Stream($resource);
        $stream->detach();
        $stream->rewind();

        self::fail(
            "Action \"Stream->detach->rewind\" threw no expected exception.\n".
            "Expects \"RuntimeException\" exception.\n".
            'Caught no exception.'
        );
    }
    /** **********************************************************************
     * Data provider: resources, readable and writable.
     *
     * @return  array                                   Data.
     ************************************************************************/
    public function dataProviderResources(): array
    {
        $result = [];

        foreach ((new ResourceGeneratorAll())->generate() as $resource) {
            $result[] = [$resource];
        }

        return $result;
    }
    /** **********************************************************************
     * Data provider: seekable resources.
     *
     * @return  array                                   Data.
     ************************************************************************/
    public function dataProviderResourcesSeekable(): array
    {
        $result = [];

        foreach ((new ResourceGeneratorAll())->generate() as $resource) {
            $result[] = [$resource];
        }

        return $result;
    }
}