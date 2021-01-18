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
    ResourceAccessMode\ReadableOnly         as AccessModeReadableOnly,
    ResourceAccessMode\WritableOnly         as AccessModeWritableOnly,
    ResourceAccessMode\ReadableAndWritable  as AccessModeReadableAndWritable,
    ResourceAccessMode\NonSuitable          as AccessModeNonSuitable
};

use function strlen;
use function array_diff;
use function fwrite;
/** ***********************************************************************************************
 * PSR-7 StreamInterface implementation test.
 *
 * Testing stream size info providing.
 *
 * @package HNV\Psr\Http\Tests\Stream
 * @author  Hvorostenko
 *************************************************************************************************/
class StreamSizeTest extends TestCase
{
    /** **********************************************************************
     * Test "Stream::getSize" provides recourse data size.
     *
     * @covers          Stream::getSize
     * @dataProvider    dataProviderResourcesWithSizeValue
     *
     * @param           resource    $resource           Recourse.
     * @param           int         $sizeExpected       Recourse expected size.
     *
     * @return          void
     * @throws          Throwable
     ************************************************************************/
    public function testGetSize($resource, int $sizeExpected): void
    {
        $sizeCaught = (new Stream($resource))->getSize();

        self::assertEquals(
            $sizeExpected,
            $sizeCaught,
            "Action \"Stream->getSize\" returned unexpected result.\n".
            "Expected result is \"$sizeExpected\".\n".
            "Caught result is \"$sizeCaught\"."
        );
    }
    /** **********************************************************************
     * Test "Stream::getSize" behavior with stream in a closed state.
     *
     * @covers          Stream::getSize
     * @dataProvider    dataProviderResourcesWithDataToWrite
     *
     * @param           resource    $resource           Recourse.
     * @param           string      $content            Content to write.
     *
     * @return          void
     * @throws          Throwable
     ************************************************************************/
    public function testGetSizeInClosedState($resource, string $content): void
    {
        $stream = new Stream($resource);

        $stream->write($content);
        $stream->close();

        self::assertNull(
            $stream->getSize(),
            "Action \"Stream->write->close->getSize\" returned unexpected result.\n".
            "Expected result is \"null\".\n".
            "Caught result is \"NOT null\"."
        );
    }
    /** **********************************************************************
     * Test "Stream::getSize" behavior with stream in a detached state.
     *
     * @covers          Stream::getSize
     * @dataProvider    dataProviderResourcesWithDataToWrite
     *
     * @param           resource    $resource           Recourse.
     * @param           string      $content            Content to write.
     *
     * @return          void
     * @throws          Throwable
     ************************************************************************/
    public function testGetSizeInDetachedState($resource, string $content): void
    {
        $stream = new Stream($resource);

        $stream->write($content);
        $stream->detach();

        self::assertNull(
            $stream->getSize(),
            "Action \"Stream->write->detach->getSize\" returned unexpected result.\n".
            "Expected result is \"null\".\n".
            "Caught result is \"NOT null\"."
        );
    }
    /** **********************************************************************
     * Data provider: resources with data size.
     *
     * @return  array                                   Data.
     ************************************************************************/
    public function dataProviderResourcesWithSizeValue(): array
    {
        $modesReadableOnly          = AccessModeReadableOnly::get();
        $modesWritableOnly          = AccessModeWritableOnly::get();
        $modesReadableAndWritable   = AccessModeReadableAndWritable::get();
        $modesNonSuitable           = AccessModeNonSuitable::get();
        $result                     = [];

        foreach (array_diff($modesReadableOnly, $modesNonSuitable) as $mode) {
            $resource   = (new ResourceGenerator($mode))->generate();
            $result[]   = [$resource, 0];
        }
        foreach (array_diff($modesWritableOnly, $modesNonSuitable) as $mode) {
            $resource   = (new ResourceGenerator($mode))->generate();
            $content    = (new TextGenerator())->generate();
            fwrite($resource, $content);
            $result[]   = [$resource, strlen($content)];
        }
        foreach (array_diff($modesReadableAndWritable, $modesNonSuitable) as $mode) {
            $resource   = (new ResourceGenerator($mode))->generate();
            $content    = (new TextGenerator())->generate();
            fwrite($resource, $content);
            $result[]   = [$resource, strlen($content)];
        }

        return $result;
    }
    /** **********************************************************************
     * Data provider: resources with data to write.
     *
     * @return  array                                   Data.
     ************************************************************************/
    public function dataProviderResourcesWithDataToWrite(): array
    {
        $modesReadableAndWritable   = AccessModeReadableAndWritable::get();
        $modesNonSuitable           = AccessModeNonSuitable::get();
        $result                     = [];

        foreach (array_diff($modesReadableAndWritable, $modesNonSuitable) as $mode) {
            $resource   = (new ResourceGenerator($mode))->generate();
            $content    = (new TextGenerator())->generate();
            $result[]   = [$resource, $content];
        }

        return $result;
    }
}