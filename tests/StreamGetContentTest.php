<?php
declare(strict_types=1);

namespace HNV\Http\StreamTests;

use Throwable;
use RuntimeException;
use PHPUnit\Framework\TestCase;
use HNV\Http\Helper\Generator\Text as TextGenerator;
use HNV\Http\StreamTests\Generator\{
    Resource\Readable               as ResourceGeneratorReadable,
    Resource\WritableOnly           as ResourceGeneratorWritableOnly,
    Resource\ReadableAndWritable    as ResourceGeneratorReadableAndWritable,
    Resource\All                    as ResourceGeneratorAll
};
use HNV\Http\Stream\Stream;

use function strlen;
use function substr;
use function fseek;
use function fwrite;
use function rewind;
/** ***********************************************************************************************
 * PSR-7 StreamInterface implementation test.
 *
 * Testing stream content info providing.
 *
 * @package HNV\Psr\Http\Tests\Stream
 * @author  Hvorostenko
 *************************************************************************************************/
class StreamGetContentTest extends TestCase
{
    /** **********************************************************************
     * Test "Stream::getContents" provides remaining contents in a string.
     *
     * @covers          Stream::getContents
     * @dataProvider    dataProviderResourcesWithReadParametersValid
     *
     * @param           resource    $resource           Resource.
     * @param           string      $contentFull        Resource content.
     * @param           string      $contentExpected    Content expected on reading resource
     *                                                  (considering resource seek position).
     *
     * @return          void
     * @throws          Throwable
     ************************************************************************/
    public function testGetContents($resource, string $contentFull, string $contentExpected): void
    {
        $stream = new Stream($resource);

        $contentCaught = $stream->getContents();
        self::assertEquals(
            $contentExpected,
            $contentCaught,
            "Action \"Stream->getContents\" returned unexpected result.\n".
            "Expected result is \"$contentExpected\".\n".
            "Caught result is \"$contentCaught\"."
        );

        $stream->rewind();
        $contentCaught = $stream->getContents();
        self::assertEquals(
            $contentFull,
            $contentCaught,
            "Action \"Stream->getContents->rewind->getContents\" returned unexpected result.\n".
            "Expected result is \"$contentFull\".\n".
            "Caught result is \"$contentCaught\"."
        );
    }
    /** **********************************************************************
     * Test "Stream::getContents" change stream current seek position.
     *
     * @covers          Stream::getContents
     * @dataProvider    dataProviderResourcesWithReadParametersValid
     *
     * @param           resource    $resource           Recourse.
     * @param           string      $content            Recourse content.
     *
     * @return          void
     * @throws          Throwable
     ************************************************************************/
    public function testGetContentsChangesSeekPosition($resource, string $content): void
    {
        $stream = new Stream($resource);

        $stream->rewind();
        self::assertEquals(
            $content,
            $stream->getContents(),
            "Action \"Stream->rewind->getContents\" returned unexpected result.\n".
            "Expected result is \"same content as was set\".\n".
            "Caught result is \"NOT the same\"."
        );
        self::assertEquals(
            '',
            $stream->getContents(),
            "Action \"Stream->rewind->getContents->getContents\" returned unexpected result.\n".
            "Expected result is \"empty string\".\n".
            "Caught result is \"NOT empty string\"."
        );
        $stream->rewind();
        self::assertEquals(
            $content,
            $stream->getContents(),
            "Action \"Stream->rewind->getContents->getContents->rewind->getContents\"".
            " returned unexpected result.\n".
            "Expected result is \"same content as was set\".\n".
            "Caught result is \"NOT the same\"."
        );
    }
    /** **********************************************************************
     * Test "Stream::getContents" throws exception with data reading error.
     *
     * @covers          Stream::getContents
     * @dataProvider    dataProviderResourcesWithReadParametersInvalid
     *
     * @param           resource $resource              Resource.
     *
     * @return          void
     * @throws          Throwable
     ************************************************************************/
    public function testGetContentsThrowsException($resource): void
    {
        $this->expectException(RuntimeException::class);

        (new Stream($resource))->getContents();

        self::fail(
            "Action \"Stream->getContents\" threw no expected exception.\n".
            "Expects \"RuntimeException\" exception.\n".
            'Caught no exception.'
        );
    }
    /** **********************************************************************
     * Test "Stream::getContents" behavior with stream in a closed state.
     *
     * @covers          Stream::getContents
     * @dataProvider    dataProviderResources
     *
     * @param           resource $resource              Resource.
     *
     * @return          void
     * @throws          Throwable
     ************************************************************************/
    public function testGetContentsInClosedState($resource): void
    {
        $this->expectException(RuntimeException::class);

        $stream = new Stream($resource);

        $stream->close();
        $stream->getContents();

        self::fail(
            "Action \"Stream->close->getContents\" threw no expected exception.\n".
            "Expects \"RuntimeException\" exception.\n".
            'Caught no exception.'
        );
    }
    /** **********************************************************************
     * Test "Stream::getContents" behavior with stream in a detached state.
     *
     * @covers          Stream::getContents
     * @dataProvider    dataProviderResources
     *
     * @param           resource $resource              Resource.
     *
     * @return          void
     * @throws          Throwable
     ************************************************************************/
    public function testGetContentsInDetachedState($resource): void
    {
        $this->expectException(RuntimeException::class);

        $stream = new Stream($resource);

        $stream->detach();
        $stream->getContents();

        self::fail(
            "Action \"Stream->detach->getContents\" threw no expected exception.\n".
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
            $result[] = [$resource, '', ''];
        }
        foreach ((new ResourceGeneratorReadableAndWritable())->generate() as $resource) {
            $content    = (new TextGenerator())->generate();
            fwrite($resource, $content);
            rewind($resource);
            $result[]   = [$resource, $content, $content];
        }
        foreach ((new ResourceGeneratorReadableAndWritable())->generate() as $resource) {
            $content    = (new TextGenerator())->generate();
            fwrite($resource, $content);
            $result[]   = [$resource, $content, ''];
        }
        foreach ((new ResourceGeneratorReadableAndWritable())->generate() as $resource) {
            $content            = (new TextGenerator())->generate();
            $seekValue          = (int) (strlen($content) / 2);
            $contentExpected    = substr($content, $seekValue);
            fwrite($resource, $content);
            fseek($resource, $seekValue);
            $result[]           = [$resource, $content, $contentExpected];
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
            $result[] = [$resource];
        }

        return $result;
    }
}