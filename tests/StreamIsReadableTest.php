<?php
declare(strict_types=1);

namespace HNV\Http\StreamTests;

use Throwable;
use PHPUnit\Framework\TestCase;
use HNV\Http\StreamTests\Generator\{
    Resource\ReadableOnly           as ResourceGeneratorReadableOnly,
    Resource\WritableOnly           as ResourceGeneratorWritableOnly,
    Resource\ReadableAndWritable    as ResourceGeneratorReadableAndWritable,
    Resource\All                    as ResourceGeneratorAll
};
use HNV\Http\Stream\Stream;
/** ***********************************************************************************************
 * PSR-7 StreamInterface implementation test.
 *
 * Testing stream readable state info providing.
 *
 * @package HNV\Psr\Http\Tests\Stream
 * @author  Hvorostenko
 *************************************************************************************************/
class StreamIsReadableTest extends TestCase
{
    /** **********************************************************************
     * Test "Stream::isReadable" provides true if the stream is readable.
     *
     * @covers          Stream::isReadable
     * @dataProvider    dataProviderResourcesWithReadableState
     *
     * @param           resource    $resource           Recourse.
     * @param           bool        $stateExpected      Recourse is readable.
     *
     * @return          void
     * @throws          Throwable
     ************************************************************************/
    public function testIsReadable($resource, bool $stateExpected): void
    {
        $stateCaught = (new Stream($resource))->isReadable();

        self::assertEquals(
            $stateExpected,
            $stateCaught,
            "Action \"Stream->isReadable\" returned unexpected result.\n".
            "Expected result is \"$stateExpected\".\n".
            "Caught result is \"$stateCaught\"."
        );
    }
    /** **********************************************************************
     * Test "Stream::isReadable" behavior with stream in a closed state.
     *
     * @covers          Stream::isReadable
     * @dataProvider    dataProviderResources
     *
     * @param           resource $resource              Recourse.
     *
     * @return          void
     * @throws          Throwable
     ************************************************************************/
    public function testIsReadableInClosedState($resource): void
    {
        $stream = new Stream($resource);
        $stream->close();

        self::assertFalse(
            $stream->isReadable(),
            "Action \"Stream->close->isReadable\" returned unexpected result.\n".
            "Expected result is \"false\".\n".
            "Caught result is \"NOT false\"."
        );
    }
    /** **********************************************************************
     * Test "Stream::isReadable" behavior with stream in a detached state.
     *
     * @covers          Stream::isReadable
     * @dataProvider    dataProviderResources
     *
     * @param           resource $resource              Recourse.
     *
     * @return          void
     * @throws          Throwable
     ************************************************************************/
    public function testIsReadableInDetachedState($resource): void
    {
        $stream = new Stream($resource);
        $stream->detach();

        self::assertFalse(
            $stream->isReadable(),
            "Action \"Stream->detach->isReadable\" returned unexpected result.\n".
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
     * Data provider: resources with their readable state.
     *
     * @return  array                                   Data.
     ************************************************************************/
    public function dataProviderResourcesWithReadableState(): array
    {
        $result = [];

        foreach ((new ResourceGeneratorReadableOnly())->generate() as $resource) {
            $result[] = [$resource, true];
        }
        foreach ((new ResourceGeneratorWritableOnly())->generate() as $resource) {
            $result[] = [$resource, false];
        }
        foreach ((new ResourceGeneratorReadableAndWritable())->generate() as $resource) {
            $result[] = [$resource, true];
        }

        return $result;
    }
}