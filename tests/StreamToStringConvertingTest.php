<?php
declare(strict_types=1);

namespace HNV\Http\StreamTests;

use Throwable;
use PHPUnit\Framework\TestCase;
use HNV\Http\Helper\Generator\Text as TextGenerator;
use HNV\Http\StreamTests\Generator\{
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
 * Testing stream to string converting behavior.
 *
 * @package HNV\Psr\Http\Tests\Stream
 * @author  Hvorostenko
 *************************************************************************************************/
class StreamToStringConvertingTest extends TestCase
{
    /** **********************************************************************
     * Test Stream object converts to string.
     *
     * @covers          Stream::__toString
     * @dataProvider    dataProviderResourcesWithContent
     *
     * @param           resource    $resource           Recourse.
     * @param           string      $content            Recourse content.
     *
     * @return          void
     * @throws          Throwable
     ************************************************************************/
    public function testRunProcess($resource, string $content): void
    {
        $stream         = new Stream($resource);
        $streamAsString = (string) $stream;

        self::assertEquals(
            $content,
            $streamAsString,
            "Action \"Stream->__toString\" returned unexpected result.\n".
            "Expected result is \"$content\".\n".
            "Caught result is \"$streamAsString\"."
        );
    }
    /** **********************************************************************
     * Test Stream object converting to string behavior with stream in a closed state.
     *
     * @covers          Stream::__toString
     * @dataProvider    dataProviderResources
     *
     * @param           resource $resource              Recourse.
     *
     * @return          void
     * @throws          Throwable
     ************************************************************************/
    public function testRunProcessInClosedState($resource): void
    {
        $stream = new Stream($resource);
        $stream->close();

        self::assertEquals(
            '',
            (string) $stream,
            "Action \"Stream->close->__toString\" returned unexpected result.\n".
            "Expected result is \"empty string\".\n".
            "Caught result is \"NOT empty string\"."
        );
    }
    /** **********************************************************************
     * Test Stream object converting to string behavior with stream in a detached state.
     *
     * @covers          Stream::__toString
     * @dataProvider    dataProviderResources
     *
     * @param           resource $resource              Recourse.
     *
     * @return          void
     * @throws          Throwable
     ************************************************************************/
    public function testRunProcessInDetachedState($resource): void
    {
        $stream = new Stream($resource);
        $stream->detach();

        self::assertEquals(
            '',
            (string) $stream,
            "Action \"Stream->detach->__toString\" returned unexpected result.\n".
            "Expected result is \"empty string\".\n".
            "Caught result is \"NOT empty string\"."
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
     * Data provider: resources with data.
     *
     * @return  array                                   Data.
     ************************************************************************/
    public function dataProviderResourcesWithContent(): array
    {
        $result = [];

        foreach ((new ResourceGeneratorAll())->generate() as $resource) {
            $result[] = [$resource, '', ''];
        }
        foreach ((new ResourceGeneratorReadableAndWritable())->generate() as $resource) {
            $content    = (new TextGenerator())->generate();
            fwrite($resource, $content);
            $result[]   = [$resource, $content, ''];
        }
        foreach ((new ResourceGeneratorReadableAndWritable())->generate() as $resource) {
            $content    = (new TextGenerator())->generate();
            fwrite($resource, $content);
            rewind($resource);
            $result[]   = [$resource, $content, $content];
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
}