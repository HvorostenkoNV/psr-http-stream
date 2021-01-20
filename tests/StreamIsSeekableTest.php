<?php
declare(strict_types=1);

namespace HNV\Http\StreamTests;

use Throwable;
use PHPUnit\Framework\TestCase;
use HNV\Http\StreamTests\Generator\Resource as ResourceGenerator;
use HNV\Http\Stream\Stream;
use HNV\Http\Stream\Collection\{
    ResourceAccessMode\ReadableOnly         as AccessModeReadableOnly,
    ResourceAccessMode\WritableOnly         as AccessModeWritableOnly,
    ResourceAccessMode\ReadableAndWritable  as AccessModeReadableAndWritable,
    ResourceAccessMode\NonSuitable          as AccessModeNonSuitable
};

use function array_diff;
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
     * @dataProvider    dataProviderResourcesSeekable
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
     * @dataProvider    dataProviderResourcesSeekable
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
     * Data provider: seekable resources.
     *
     * @return  array                                   Data.
     ************************************************************************/
    public function dataProviderResourcesSeekable(): array
    {
        $modesReadableOnly          = AccessModeReadableOnly::get();
        $modesWritableOnly          = AccessModeWritableOnly::get();
        $modesReadableAndWritable   = AccessModeReadableAndWritable::get();
        $modesNonSuitable           = AccessModeNonSuitable::get();
        $result                     = [];

        foreach (array_diff($modesReadableOnly, $modesNonSuitable) as $mode) {
            $resource   = (new ResourceGenerator($mode))->generate();
            $result[]   = [$resource];
        }
        foreach (array_diff($modesWritableOnly, $modesNonSuitable) as $mode) {
            $resource   = (new ResourceGenerator($mode))->generate();
            $result[]   = [$resource];
        }
        foreach (array_diff($modesReadableAndWritable, $modesNonSuitable) as $mode) {
            $resource   = (new ResourceGenerator($mode))->generate();
            $result[]   = [$resource];
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
        $modesReadableOnly          = AccessModeReadableOnly::get();
        $modesWritableOnly          = AccessModeWritableOnly::get();
        $modesReadableAndWritable   = AccessModeReadableAndWritable::get();
        $modesNonSuitable           = AccessModeNonSuitable::get();
        $result                     = [];

        foreach (array_diff($modesReadableOnly, $modesNonSuitable) as $mode) {
            $resource   = (new ResourceGenerator($mode))->generate();
            $result[]   = [$resource, true];
        }
        foreach (array_diff($modesWritableOnly, $modesNonSuitable) as $mode) {
            $resource   = (new ResourceGenerator($mode))->generate();
            $result[]   = [$resource, true];
        }
        foreach (array_diff($modesReadableAndWritable, $modesNonSuitable) as $mode) {
            $resource   = (new ResourceGenerator($mode))->generate();
            $result[]   = [$resource, true];
        }

        return $result;
    }
}