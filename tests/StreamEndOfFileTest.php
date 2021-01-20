<?php
declare(strict_types=1);

namespace HNV\Http\StreamTests;

use Throwable;
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

use function array_diff;
use function fwrite;
use function feof;
use function fgetc;
/** ***********************************************************************************************
 * PSR-7 StreamInterface implementation test.
 *
 * Testing stream end of file info providing.
 *
 * @package HNV\Psr\Http\Tests\Stream
 * @author  Hvorostenko
 *************************************************************************************************/
class StreamEndOfFileTest extends TestCase
{
    /** **********************************************************************
     * Test "Stream::eof" provides true if the stream is at the end of the stream.
     *
     * @covers          Stream::eof
     * @dataProvider    dataProviderResourcesWithValues
     *
     * @param           resource    $resource           Recourse.
     * @param           bool        $endOfFileExpected  Position pointer is in the end.
     *
     * @return          void
     * @throws          Throwable
     ************************************************************************/
    public function testEof($resource, bool $endOfFileExpected): void
    {
        $endOfFileCaught = (new Stream($resource))->eof();

        self::assertEquals(
            $endOfFileExpected,
            $endOfFileCaught,
            "Action \"Stream->eof\" returned unexpected result.\n".
            "Expected result is \"$endOfFileExpected\".\n".
            "Caught result is \"$endOfFileCaught\"."
        );
    }
    /** **********************************************************************
     * Test "Stream::eof" behavior with stream in a closed state.
     *
     * @covers          Stream::eof
     * @dataProvider    dataProviderResources
     *
     * @param           resource $resource              Recourse.
     *
     * @return  void
     * @throws  Throwable
     ************************************************************************/
    public function testEofInClosedState($resource): void
    {
        $stream = new Stream($resource);

        $stream->rewind();
        $stream->close();

        self::assertTrue(
            $stream->eof(),
            "Action \"Stream->close->eof\" returned unexpected result.\n".
            "Expected result is \"true\".\n".
            "Caught result is \"NOT true\"."
        );
    }
    /** **********************************************************************
     * Test "Stream::eof" behavior with stream in a detached state.
     *
     * @covers          Stream::eof
     * @dataProvider    dataProviderResources
     *
     * @param           resource $resource              Recourse.
     *
     * @return  void
     * @throws  Throwable
     ************************************************************************/
    public function testEofInDetachedState($resource): void
    {
        $stream = new Stream($resource);

        $stream->rewind();
        $stream->detach();

        self::assertTrue(
            $stream->eof(),
            "Action \"Stream->detach->eof\" returned unexpected result.\n".
            "Expected result is \"true\".\n".
            "Caught result is \"NOT true\"."
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
     * Data provider: resources with cursor pointer in the end.
     *
     * @return  array                                   Data.
     ************************************************************************/
    public function dataProviderResourcesWithValues(): array
    {
        $result = [];

        foreach ($this->dataProviderResources() as $set) {
            $resource   = $set[0];
            $result[]   = [$resource, false];
        }
        foreach ($this->dataProviderResources() as $set) {
            $resource   = $set[0];
            $content    = (new TextGenerator())->generate();
            fwrite($resource, $content);
            $result[]   = [$resource, false];
        }
        foreach ($this->dataProviderResources() as $set) {
            $resource   = $set[0];
            $content    = (new TextGenerator())->generate();
            fwrite($resource, $content);
            while (!feof($resource)) {
                fgetc($resource);
            }
            $result[]   = [$resource, true];
        }

        return $result;
    }
}