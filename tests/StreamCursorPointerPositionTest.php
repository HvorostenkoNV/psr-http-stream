<?php
declare(strict_types=1);

namespace HNV\Http\StreamTests;

use Throwable;
use RuntimeException;
use PHPUnit\Framework\TestCase;
use HNV\Http\StreamTests\Generator\{
    Resource    as ResourceGenerator,
    Text        as TextGenerator
};
use HNV\Http\Stream\Stream;
use HNV\Http\Stream\Collection\{
    ResourceAccessMode\ReadableAndWritable  as AccessModeReadableAndWritable,
    ResourceAccessMode\NonSuitable          as AccessModeNonSuitable
};

use function strlen;
use function array_diff;
use function fwrite;
use function fseek;
/** ***********************************************************************************************
 * PSR-7 StreamInterface implementation test.
 *
 * Testing stream current position pointer providing.
 *
 * @package HNV\Psr\Http\Tests\Stream
 * @author  Hvorostenko
 *************************************************************************************************/
class StreamCursorPointerPositionTest extends TestCase
{
    /** **********************************************************************
     * Test "Stream::tell" provides recourse current position pointer.
     *
     * @covers          Stream::tell
     * @dataProvider    dataProviderResourcesInValidState
     *
     * @param           resource    $resource           Recourse.
     * @param           int         $positionExpected   Pointer expected position.
     *
     * @return          void
     * @throws          Throwable
     ************************************************************************/
    public function testTell($resource, int $positionExpected): void
    {
        $positionCaught = (new Stream($resource))->tell();

        self::assertEquals(
            $positionExpected,
            $positionCaught,
            "Action \"Stream->tell\" returned unexpected result.\n".
            "Expected result is \"$positionExpected\".\n".
            "Caught result is \"$positionCaught\"."
        );
    }
    /** **********************************************************************
     * Test "Stream::tell" throws exception with stream reading error.
     *
     * @covers          Stream::tell
     * @dataProvider    dataProviderResourcesInInvalidState
     *
     * @param           resource $resource              Recourse.
     *
     * @return          void
     * @throws          Throwable
     ************************************************************************/
    public function testTellThrowsException($resource): void
    {
        $this->expectException(RuntimeException::class);

        (new Stream($resource))->tell();

        self::fail(
            "Action \"Stream->tell\" threw no expected exception.\n".
            "Expects \"RuntimeException\" exception.\n".
            'Caught no exception.'
        );
    }
    /** **********************************************************************
     * Test "Stream::tell" behavior with stream in a closed state.
     *
     * @covers          Stream::tell
     * @dataProvider    dataProviderResources
     *
     * @param           resource $resource              Recourse.
     *
     * @return          void
     * @throws          Throwable
     ************************************************************************/
    public function testTellInClosedState($resource): void
    {
        $this->expectException(RuntimeException::class);

        $stream = new Stream($resource);

        $stream->close();
        $stream->tell();

        self::fail(
            "Action \"Stream->close->tell\" threw no expected exception.\n".
            "Expects \"RuntimeException\" exception.\n".
            'Caught no exception.'
        );
    }
    /** **********************************************************************
     * Test "Stream::tell" behavior with stream in a detached state.
     *
     * @covers          Stream::tell
     * @dataProvider    dataProviderResources
     *
     * @param           resource $resource              Recourse.
     *
     * @return          void
     * @throws          Throwable
     ************************************************************************/
    public function testTellInDetachedState($resource): void
    {
        $this->expectException(RuntimeException::class);

        $stream = new Stream($resource);

        $stream->detach();
        $stream->tell();

        self::fail(
            "Action \"Stream->detach->tell\" threw no expected exception.\n".
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
        $modesReadableAndWritable   = AccessModeReadableAndWritable::get();
        $modesNonSuitable           = AccessModeNonSuitable::get();
        $result                     = [];

        foreach (array_diff($modesReadableAndWritable, $modesNonSuitable) as $mode) {
            $resource   = (new ResourceGenerator($mode))->generate();
            $result[]   = [$resource];
        }

        return $result;
    }
    /** **********************************************************************
     * Data provider: resources with cursor pointer in valid positions.
     *
     * @return  array                                   Data.
     ************************************************************************/
    public function dataProviderResourcesInValidState(): array
    {
        $result = [];

        foreach ($this->dataProviderResources() as $set) {
            $resource   = $set[0];
            $result[]   = [$resource, 0];
        }
        foreach ($this->dataProviderResources() as $set) {
            $resource   = $set[0];
            $content    = (new TextGenerator())->generate();
            fwrite($resource, $content);
            $result[]   = [$resource, strlen($content)];
        }
        foreach ($this->dataProviderResources() as $set) {
            $resource   = $set[0];
            $content    = (new TextGenerator())->generate();
            $seekValue  = (int) (strlen($content) / 2);
            fwrite($resource, $content);
            fseek($resource, $seekValue);
            $result[]   = [$resource, $seekValue];
        }

        return $result;
    }
    /** **********************************************************************
     * Data provider: resources with cursor pointer in invalid values.
     *
     * @return  array                                   Data.
     ************************************************************************/
    public function dataProviderResourcesInInvalidState(): array
    {
        return [];
    }
}