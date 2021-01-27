<?php
declare(strict_types=1);

namespace HNV\Http\StreamTests;

use Throwable;
use RuntimeException;
use PHPUnit\Framework\TestCase;
use HNV\Http\Helper\Generator\Text as TextGenerator;
use HNV\Http\StreamTests\Generator\{
    Resource\Readable               as ResourceGeneratorReadable,
    Resource\ReadableOnly           as ResourceGeneratorReadableOnly,
    Resource\WritableOnly           as ResourceGeneratorWritableOnly,
    Resource\ReadableAndWritable    as ResourceGeneratorReadableAndWritable,
    Resource\All                    as ResourceGeneratorAll
};
use HNV\Http\Stream\Stream;

use function strlen;
use function substr;
use function fwrite;
use function feof;
use function fgetc;
use function rewind;
/** ***********************************************************************************************
 * PSR-7 StreamInterface implementation test.
 *
 * Testing stream reading behavior.
 *
 * @package HNV\Psr\Http\Tests\Stream
 * @author  Hvorostenko
 *************************************************************************************************/
class StreamReadTest extends TestCase
{
    /** **********************************************************************
     * Test "Stream::read" provides data from the stream.
     *
     * @covers          Stream::read
     * @dataProvider    dataProviderResourcesWithReadParametersValid
     *
     * @param           resource    $resource           Resource.
     * @param           int         $length             Read data length.
     * @param           string      $contentExpected    Expected read data.
     *
     * @return          void
     * @throws          Throwable
     ************************************************************************/
    public function testRead($resource, int $length, string $contentExpected): void
    {
        $contentCaught = (new Stream($resource))->read($length);

        self::assertEquals(
            $contentExpected,
            $contentCaught,
            "Action \"Stream->read\" returned unexpected result.\n".
            "Action was called with parameters (length => $length).\n".
            "Expected result is \"$contentExpected\".\n".
            "Caught result is \"$contentCaught\"."
        );
    }
    /** **********************************************************************
     * Test "Stream::read" throws exception with data reading error.
     *
     * @covers          Stream::read
     * @dataProvider    dataProviderResourcesWithReadParametersInvalid
     *
     * @param           resource    $resource           Resource.
     * @param           int         $length             Read data length.
     *
     * @return          void
     * @throws          Throwable
     ************************************************************************/
    public function testReadThrowsException($resource, int $length): void
    {
        $this->expectException(RuntimeException::class);

        (new Stream($resource))->read($length);

        self::fail(
            "Action \"Stream->read\" threw no expected exception.\n".
            "Expects \"RuntimeException\" exception.\n".
            'Caught no exception.'
        );
    }
    /** **********************************************************************
     * Test "Stream::read" behavior with stream in a closed state.
     *
     * @covers          Stream::read
     * @dataProvider    dataProviderResources
     *
     * @param           resource $resource              Recourse.
     *
     * @return          void
     * @throws          Throwable
     ************************************************************************/
    public function testReadInClosedState($resource): void
    {
        $this->expectException(RuntimeException::class);

        $stream = new Stream($resource);
        $stream->close();
        $stream->read(0);

        self::fail(
            "Action \"Stream->close->read\" threw no expected exception.\n".
            "Expects \"RuntimeException\" exception.\n".
            'Caught no exception.'
        );
    }
    /** **********************************************************************
     * Test "Stream::read" behavior with stream in a detached state.
     *
     * @covers          Stream::read
     * @dataProvider    dataProviderResources
     *
     * @param           resource $resource              Recourse.
     *
     * @return          void
     * @throws          Throwable
     ************************************************************************/
    public function testReadInDetachedState($resource): void
    {
        $this->expectException(RuntimeException::class);

        $stream = new Stream($resource);
        $stream->detach();
        $stream->read(0);

        self::fail(
            "Action \"Stream->detach->read\" threw no expected exception.\n".
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
     * Data provider: resources with data to read valid value.
     *
     * @return  array                                   Data.
     ************************************************************************/
    public function dataProviderResourcesWithReadParametersValid(): array
    {
        $result = [];

        foreach ((new ResourceGeneratorReadable())->generate() as $resource) {
            $result[] = [$resource, 0, ''];
        }
        foreach ((new ResourceGeneratorReadableAndWritable())->generate() as $resource) {
            $content    = (new TextGenerator())->generate();
            fwrite($resource, $content);
            rewind($resource);
            $result[]   = [$resource, strlen($content), $content];
        }
        foreach ((new ResourceGeneratorReadableAndWritable())->generate() as $resource) {
            $content            = (new TextGenerator())->generate();
            $readLength         = (int) (strlen($content) / 2);
            $expectedContent    = substr($content, 0, $readLength);
            fwrite($resource, $content);
            rewind($resource);
            $result[]           = [$resource, $readLength, $expectedContent];
        }
        foreach ((new ResourceGeneratorReadableAndWritable())->generate() as $resource) {
            $content    = (new TextGenerator())->generate();
            fwrite($resource, $content);
            $this->reachResourceEnd($resource);
            $result[]   = [$resource, strlen($content), ''];
        }

        return $result;
    }
    /** **********************************************************************
     * Data provider: resources with data to read invalid value.
     *
     * @return  array                                   Data.
     ************************************************************************/
    public function dataProviderResourcesWithReadParametersInvalid(): array
    {
        $result = [];

        foreach ((new ResourceGeneratorWritableOnly())->generate() as $resource) {
            $result[] = [$resource, 1];
        }
        foreach ((new ResourceGeneratorReadableOnly())->generate() as $resource) {
            $result[] = [$resource, -1];
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