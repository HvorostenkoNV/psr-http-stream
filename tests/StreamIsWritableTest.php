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
 * Testing stream writable state info providing.
 *
 * @package HNV\Psr\Http\Tests\Stream
 * @author  Hvorostenko
 *************************************************************************************************/
class StreamIsWritableTest extends TestCase
{
    /** **********************************************************************
     * Test "Stream::isWritable" provides true if the stream is writable.
     *
     * @covers          Stream::isWritable
     * @dataProvider    dataProviderResourcesWithWritableState
     *
     * @param           resource    $resource           Recourse.
     * @param           bool        $stateExpected      Recourse is writable.
     *
     * @return          void
     * @throws          Throwable
     ************************************************************************/
    public function testIsWritable($resource, bool $stateExpected): void
    {
        $stateCaught = (new Stream($resource))->isWritable();

        self::assertEquals(
            $stateExpected,
            $stateCaught,
            "Action \"Stream->isWritable\" returned unexpected result.\n".
            "Expected result is \"$stateExpected\".\n".
            "Caught result is \"$stateCaught\"."
        );
    }
    /** **********************************************************************
     * Test "Stream::isWritable" behavior with stream in a closed state.
     *
     * @covers          Stream::isWritable
     * @dataProvider    dataProviderResources
     *
     * @param           resource $resource              Recourse.
     *
     * @return          void
     * @throws          Throwable
     ************************************************************************/
    public function testIsWritableInClosedState($resource): void
    {
        $stream = new Stream($resource);
        $stream->close();

        self::assertFalse(
            $stream->isWritable(),
            "Action \"Stream->close->isWritable\" returned unexpected result.\n".
            "Expected result is \"false\".\n".
            "Caught result is \"NOT false\"."
        );
    }
    /** **********************************************************************
     * Test "Stream::isWritable" behavior with stream in a detached state.
     *
     * @covers          Stream::isWritable
     * @dataProvider    dataProviderResources
     *
     * @param           resource $resource              Recourse.
     *
     * @return          void
     * @throws          Throwable
     ************************************************************************/
    public function testIsWritableInDetachedState($resource): void
    {
        $stream = new Stream($resource);
        $stream->detach();

        self::assertFalse(
            $stream->isWritable(),
            "Action \"Stream->detach->isWritable\" returned unexpected result.\n".
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
     * Data provider: resources with their writable state.
     *
     * @return  array                                   Data.
     ************************************************************************/
    public function dataProviderResourcesWithWritableState(): array
    {
        $result = [];

        foreach ((new ResourceGeneratorReadableOnly())->generate() as $resource) {
            $result[] = [$resource, false];
        }
        foreach ((new ResourceGeneratorWritableOnly())->generate() as $resource) {
            $result[] = [$resource, true];
        }
        foreach ((new ResourceGeneratorReadableAndWritable())->generate() as $resource) {
            $result[] = [$resource, true];
        }

        return $result;
    }
}