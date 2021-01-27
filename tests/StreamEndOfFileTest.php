<?php
declare(strict_types=1);

namespace HNV\Http\StreamTests;

use Throwable;
use PHPUnit\Framework\TestCase;
use HNV\Http\Helper\Generator\Text as TextGenerator;
use HNV\Http\StreamTests\Generator\{
    Resource\Writable               as ResourceGeneratorWritable,
    Resource\WritableOnly           as ResourceGeneratorWritableOnly,
    Resource\ReadableAndWritable    as ResourceGeneratorReadableAndWritable,
    Resource\All                    as ResourceGeneratorAll
};
use HNV\Http\Stream\Stream;

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
        $result = [];

        foreach ((new ResourceGeneratorAll())->generate() as $resource) {
            $result[] = [$resource];
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

        foreach ((new ResourceGeneratorAll())->generate() as $resource) {
            $result[] = [$resource, false];
        }
        foreach ( (new ResourceGeneratorWritable())->generate() as $resource) {
            $content    = (new TextGenerator())->generate();
            fwrite($resource, $content);
            $result[]   = [$resource, false];
        }
        foreach ((new ResourceGeneratorWritableOnly())->generate() as $resource) {
            $content    = (new TextGenerator())->generate();
            fwrite($resource, $content);
            $this->reachResourceEnd($resource);
            $result[]   = [$resource, false];
        }
        foreach ((new ResourceGeneratorReadableAndWritable())->generate() as $resource) {
            $content    = (new TextGenerator())->generate();
            fwrite($resource, $content);
            $this->reachResourceEnd($resource);
            $result[]   = [$resource, true];
        }

        return $result;
    }
    /** **********************************************************************
     * Rewind resource to the end.
     *
     * @param   resource $resource                      Resource.
     *
     * @return  void
     ************************************************************************/
    private function reachResourceEnd($resource): void
    {
        while (!feof($resource)) {
            $result = fgetc($resource);
            if($result === false) {
                break;
            }
        }
    }
}