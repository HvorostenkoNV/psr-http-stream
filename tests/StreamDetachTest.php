<?php
declare(strict_types=1);

namespace HNV\Http\StreamTests;

use Throwable;
use PHPUnit\Framework\TestCase;
use HNV\Http\StreamTests\Generator\Resource as ResourceGenerator;
use HNV\Http\Stream\Stream;
use HNV\Http\Stream\Collection\{
    ResourceAccessMode\ReadableAndWritable  as AccessModeReadableAndWritable,
    ResourceAccessMode\NonSuitable          as AccessModeNonSuitable
};

use function array_diff;
/** ***********************************************************************************************
 * PSR-7 StreamInterface implementation test.
 *
 * Testing stream detaching behavior.
 *
 * @package HNV\Psr\Http\Tests\Stream
 * @author  Hvorostenko
 *************************************************************************************************/
class StreamDetachTest extends TestCase
{
    /** **********************************************************************
     * Test "Stream::detach" provides recourse.
     *
     * @covers          Stream::detach
     * @dataProvider    dataProviderResources
     *
     * @param           resource $resource              Recourse.
     *
     * @return          void
     * @throws          Throwable
     ************************************************************************/
    public function testDetach($resource): void
    {
        $stream = new Stream($resource);

        self::assertEquals(
            $resource,
            $stream->detach(),
            "Action \"Stream->detach\" returned unexpected result.\n".
            "Expected result is \"THE SAME resource\".\n".
            "Caught result is \"NOT THE SAME resource\"."
        );
    }
    /** **********************************************************************
     * Test "Stream::detach" behavior with stream in a closed state.
     *
     * @covers          Stream::detach
     * @dataProvider    dataProviderResources
     *
     * @param           resource $resource              Recourse.
     *
     * @return          void
     * @throws          Throwable
     ************************************************************************/
    public function testDetachOnClosedStream($resource): void
    {
        $stream = new Stream($resource);
        $stream->close();

        self::assertNull(
            $stream->detach(),
            "Action \"Stream->close->detach\" returned unexpected result.\n".
            "Expected result is \"null\".\n".
            "Caught result is \"NOT null\"."
        );
    }
    /** **********************************************************************
     * Test "Stream::detach" behavior with stream in a detached state.
     *
     * @covers          Stream::detach
     * @dataProvider    dataProviderResources
     *
     * @param           resource $resource              Recourse.
     *
     * @return          void
     * @throws          Throwable
     ************************************************************************/
    public function testDetachOnDetachedStream($resource): void
    {
        $stream = new Stream($resource);
        $stream->detach();

        self::assertNull(
            $stream->detach(),
            "Action \"Stream->detach->detach\" returned unexpected result.\n".
            "Expected result is \"null\".\n".
            "Caught result is \"NOT null\"."
        );
    }
    /** **********************************************************************
     * Data provider: resources, readable and writable.
     *
     * @return  array                                   Data.
     ************************************************************************/
    public function dataProviderResources(): array
    {
        $modesReadableAndWritable   = AccessModeReadableAndWritable::get();
        $modesNonSuitable           = AccessModeNonSuitable::get();
        $result                     = [];

        foreach (array_diff($modesReadableAndWritable, $modesNonSuitable) as $mode) {
            $result[] = [(new ResourceGenerator($mode))->generate()];
        }

        return $result;
    }
}