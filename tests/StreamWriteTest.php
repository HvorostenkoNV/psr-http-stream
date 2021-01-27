<?php
declare(strict_types=1);

namespace HNV\Http\StreamTests;

use Throwable;
use RuntimeException;
use PHPUnit\Framework\TestCase;
use HNV\Http\Helper\Generator\Text as TextGenerator;
use HNV\Http\StreamTests\Generator\{
    Resource\ReadableOnly           as ResourceGeneratorReadableOnly,
    Resource\Writable               as ResourceGeneratorWritable,
    Resource\ReadableAndWritable    as ResourceGeneratorReadableAndWritable,
    Resource\All                    as ResourceGeneratorAll
};
use HNV\Http\Stream\Stream;

use function strlen;
use function ftell;
use function fwrite;
use function rewind;
use function stream_get_contents;
/** ***********************************************************************************************
 * PSR-7 StreamInterface implementation test.
 *
 * Testing stream writing behavior.
 *
 * @package HNV\Psr\Http\Tests\Stream
 * @author  Hvorostenko
 *************************************************************************************************/
class StreamWriteTest extends TestCase
{
    /** **********************************************************************
     * Test "Stream::write" provides number of bytes written to the stream.
     *
     * @covers          Stream::write
     * @dataProvider    dataProviderResourcesWithWriteParametersValid
     *
     * @param           resource    $resource           Resource.
     * @param           string      $data               Data to write.
     * @param           int         $resultExpected     Expected number of bytes
     *                                                  written to the stream.
     *
     * @return          void
     * @throws          Throwable
     ************************************************************************/
    public function testWrite($resource, string $data, int $resultExpected): void
    {
        $resultCaught = (new Stream($resource))->write($data);

        self::assertEquals(
            $resultExpected,
            $resultCaught,
            "Action \"Stream->write\" returned unexpected result.\n".
            "Action was called with parameters (data => $data).\n".
            "Expected result is \"$resultExpected\".\n".
            "Caught result is \"$resultCaught\"."
        );
    }
    /** **********************************************************************
     * Test "Stream::write" throws exception with data writing error.
     *
     * @covers          Stream::write
     * @dataProvider    dataProviderResourcesWithWriteParametersInvalid
     *
     * @param           resource    $resource           Resource.
     * @param           string      $data               Data to write.
     *
     * @return          void
     * @throws          Throwable
     ************************************************************************/
    public function testWriteThrowsException($resource, string $data): void
    {
        $this->expectException(RuntimeException::class);

        (new Stream($resource))->write($data);

        self::fail(
            "Action \"Stream->write\" threw no expected exception.\n".
            "Expects \"RuntimeException\" exception.\n".
            'Caught no exception.'
        );
    }
    /** **********************************************************************
     * Test "Stream::write" add data to resource content.
     *
     * @covers          Stream::write
     * @dataProvider    dataProviderResourcesWithDoubleDataToWrite
     *
     * @param           resource    $resource           Resource.
     * @param           string      $data1              Data to write (first).
     * @param           string      $data2              Data to write (second).
     *
     * @return          void
     * @throws          Throwable
     ************************************************************************/
    public function testWriteAddData($resource, string $data1, string $data2): void
    {
        $stream = new Stream($resource);

        $stream->write($data1);
        $stream->write($data2);

        rewind($resource);
        $contentCaught = stream_get_contents($resource);

        self::assertEquals(
            $data1.$data2,
            $contentCaught,
            "Action \"Stream->write->write\" returned unexpected result.\n".
            "Action was called with parameters (data => $data1, data => $data2).\n".
            "Expected result is \"$data1$data2\".\n".
            "Caught result is \"$contentCaught\"."
        );
    }
    /** **********************************************************************
     * Test "Stream::write" moves underlying resource cursor position after success.
     *
     * @covers          Stream::write
     * @dataProvider    dataProviderResourcesWithCursorPointerParameters
     *
     * @param           resource    $resource           Resource.
     * @param           string      $data               Data to write.
     * @param           int         $pointerExpected    Cursor pointer expected value.
     *
     * @return          void
     * @throws          Throwable
     ************************************************************************/
    public function testWriteMovesCursorPosition(
                $resource,
        string  $data,
        int     $pointerExpected
    ): void {
        $stream         = new Stream($resource);
        $stream->write($data);
        $pointerCaught  = ftell($resource);

        self::assertEquals(
            $pointerExpected,
            $pointerCaught,
            "Action \"Stream->write\" showed unexpected behavior.\n".
            "Action was called with parameters (data => $data) twice.\n".
            "Expects underlying resource cursor position is \"$pointerExpected\"\n".
            "Underlying resource cursor position is \"$pointerCaught\""
        );
    }
    /** **********************************************************************
     * Test "Stream::write" behavior with stream in a closed state.
     *
     * @covers          Stream::write
     * @dataProvider    dataProviderResources
     *
     * @param           resource $resource              Resource.
     *
     * @return          void
     * @throws          Throwable
     ************************************************************************/
    public function testWriteInClosedState($resource): void
    {
        $this->expectException(RuntimeException::class);

        $stream = new Stream($resource);
        $data   = (new TextGenerator())->generate();

        $stream->close();
        $stream->write($data);

        self::fail(
            "Action \"Stream->close->write\" threw no expected exception.\n".
            "Expects \"RuntimeException\" exception.\n".
            'Caught no exception.'
        );
    }
    /** **********************************************************************
     * Test "Stream::write" behavior with stream in a detached state.
     *
     * @covers          Stream::write
     * @dataProvider    dataProviderResources
     *
     * @param           resource $resource              Resource.
     *
     * @return          void
     * @throws          Throwable
     ************************************************************************/
    public function testWriteInDetachedState($resource): void
    {
        $this->expectException(RuntimeException::class);

        $stream = new Stream($resource);
        $data   = (new TextGenerator())->generate();

        $stream->detach();
        $stream->write($data);

        self::fail(
            "Action \"Stream->detach->write\" threw no expected exception.\n".
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
     * Data provider: resources with data to write valid parameters.
     *
     * @return  array                                   Data.
     ************************************************************************/
    public function dataProviderResourcesWithWriteParametersValid(): array
    {
        $result = [];

        foreach ((new ResourceGeneratorWritable())->generate() as $resource) {
            $result[]   = [$resource, '', 0];
        }
        foreach ((new ResourceGeneratorWritable())->generate() as $resource) {
            $content    = (new TextGenerator())->generate();
            $result[]   = [$resource, $content, strlen($content)];
        }
        foreach ((new ResourceGeneratorWritable())->generate() as $resource) {
            $content1   = (new TextGenerator())->generate();
            $content2   = (new TextGenerator())->generate();
            fwrite($resource, $content1);
            $result[]   = [$resource, $content2, strlen($content2)];
        }

        return $result;
    }
    /** **********************************************************************
     * Data provider: resources with data to write invalid parameters.
     *
     * @return  array                                   Data.
     ************************************************************************/
    public function dataProviderResourcesWithWriteParametersInvalid(): array
    {
        $result = [];

        foreach ((new ResourceGeneratorReadableOnly())->generate() as $resource) {
            $content    = (new TextGenerator())->generate();
            $result[]   = [$resource, $content];
        }

        return $result;
    }
    /** **********************************************************************
     * Data provider: resources with double data to write.
     *
     * @return  array                                   Data.
     ************************************************************************/
    public function dataProviderResourcesWithDoubleDataToWrite(): array
    {
        $result = [];

        foreach ((new ResourceGeneratorReadableAndWritable())->generate() as $resource) {
            $content1   = (new TextGenerator())->generate();
            $content2   = (new TextGenerator())->generate();
            $result[]   = [$resource, $content1, $content2];
        }

        return $result;
    }
    /** **********************************************************************
     * Data provider: resources with data to write and expected cursor pointer position.
     *
     * @return  array                                   Data.
     ************************************************************************/
    public function dataProviderResourcesWithCursorPointerParameters(): array
    {
        $result = [];

        foreach ((new ResourceGeneratorWritable())->generate() as $resource) {
            $result[]   = [$resource, '', 0];
        }
        foreach ((new ResourceGeneratorWritable())->generate() as $resource) {
            $content    = (new TextGenerator())->generate();
            $result[]   = [$resource, $content, strlen($content)];
        }
        foreach ((new ResourceGeneratorWritable())->generate() as $resource) {
            $content1   = (new TextGenerator())->generate();
            $content2   = (new TextGenerator())->generate();
            fwrite($resource, $content1);
            $result[]   = [$resource, $content2, strlen($content1.$content2)];
        }

        return $result;
    }
}