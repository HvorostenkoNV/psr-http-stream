<?php
declare(strict_types=1);

namespace HNV\Http\StreamTests;

use Throwable;
use PHPUnit\Framework\TestCase;
use HNV\Http\StreamTests\Generator\Resource\All as ResourceGeneratorAll;
use HNV\Http\Stream\Stream;
/** ***********************************************************************************************
 * PSR-7 StreamInterface implementation test.
 *
 * Testing stream seekable state info providing.
 *
 * @package HNV\Psr\Http\Tests\Stream
 * @author  Hvorostenko
 *************************************************************************************************/
class StreamIsSeekableTest extends TestCase
{
    /** **********************************************************************
     * Test "Stream::isSeekable" provides true if the stream is seekable.
     *
     * @covers          Stream::isSeekable
     * @dataProvider    dataProviderResourcesWithSeekingStateValues
     *
     * @param           resource    $resource           Recourse.
     * @param           bool        $stateExpected      Recourse is seekable.
     *
     * @return          void
     * @throws          Throwable
     ************************************************************************/
    public function testIsSeekable($resource, bool $stateExpected): void
    {
        $stateCaught = (new Stream($resource))->isSeekable();

        self::assertEquals(
            $stateExpected,
            $stateCaught,
            "Action \"Stream->isSeekable\" returned unexpected result.\n".
            "Expected result is \"$stateExpected\".\n".
            "Caught result is \"$stateCaught\"."
        );
    }
    /** **********************************************************************
     * Test "Stream::isSeekable" behavior with stream in a closed state.
     *
     * @covers          Stream::isSeekable
     * @dataProvider    dataProviderResources
     *
     * @param           resource $resource              Recourse.
     *
     * @return          void
     * @throws          Throwable
     ************************************************************************/
    public function testIsSeekableInClosedState($resource): void
    {
        $stream = new Stream($resource);
        $stream->close();

        self::assertFalse(
            $stream->isSeekable(),
            "Action \"Stream->close->isSeekable\" returned unexpected result.\n".
            "Expected result is \"false\".\n".
            "Caught result is \"NOT false\"."
        );
    }
    /** **********************************************************************
     * Test "Stream::isSeekable" behavior with stream in a detached state.
     *
     * @covers          Stream::isSeekable
     * @dataProvider    dataProviderResources
     *
     * @param           resource $resource              Recourse.
     *
     * @return          void
     * @throws          Throwable
     ************************************************************************/
    public function testIsSeekableInDetachedState($resource): void
    {
        $stream = new Stream($resource);
        $stream->detach();

        self::assertFalse(
            $stream->isSeekable(),
            "Action \"Stream->detach->isSeekable\" returned unexpected result.\n".
            "Expected result is \"false\".\n".
            "Caught result is \"NOT false\"."
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
     * Data provider: resources with their seekable state.
     *
     * @return  array                                   Data.
     ************************************************************************/
    public function dataProviderResourcesWithSeekingStateValues(): array
    {
        $result = [];

        foreach ((new ResourceGeneratorAll())->generate() as $resource) {
            $result[] = [$resource, true];
        }

        return $result;
    }
}