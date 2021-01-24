<?php
declare(strict_types=1);

namespace HNV\Http\StreamTests;

use Throwable;
use RuntimeException;
use PHPUnit\Framework\TestCase;
use HNV\Http\StreamTests\Generator\{
    Resource\Writable   as ResourceGeneratorWritable,
    Resource\All        as ResourceGeneratorAll,
    Text                as TextGenerator
};
use HNV\Http\Stream\Stream;

use function strlen;
use function fseek;
use function fwrite;
use function ftell;

use const SEEK_SET;
use const SEEK_CUR;
use const SEEK_END;
/** ***********************************************************************************************
 * PSR-7 StreamInterface implementation test.
 *
 * Testing stream seeking behavior.
 *
 * @package HNV\Psr\Http\Tests\Stream
 * @author  Hvorostenko
 *************************************************************************************************/
class StreamSeekingTest extends TestCase
{
    /** **********************************************************************
     * Test "Stream::seek" works in expected way.
     *
     * @covers          Stream::seek
     * @dataProvider    dataProviderResourcesWithSeekValuesValid
     *
     * @param           resource    $resource           Recourse.
     * @param           int         $offset             Seek value.
     * @param           int         $whence             Seek value calculation type.
     * @param           int         $positionExpected   Recourse cursor pointer expected position.
     *
     * @return          void
     * @throws          Throwable
     ************************************************************************/
    public function testSeek(
            $resource,
        int $offset,
        int $whence,
        int $positionExpected
    ): void {
        $stream = new Stream($resource);
        $stream->seek($offset, $whence);
        $positionCaught = ftell($resource);

        self::assertEquals(
            $positionExpected,
            $positionCaught,
            "Action \"Stream->seek\" returned unexpected result.\n".
            "Action was called with parameters (offset => $offset, whence => $whence).\n".
            "Expected result is \"$positionExpected\".\n".
            "Caught result is \"$positionCaught\"."
        );
    }
    /** **********************************************************************
     * Test "Stream::seek" throws exception with invalid arguments.
     *
     * @covers          Stream::seek
     * @dataProvider    dataProviderResourcesWithSeekValuesInvalid
     *
     * @param           resource    $resource           Recourse.
     * @param           int         $offset             Seek value.
     * @param           int         $whence             Seek value calculation type.
     *
     * @return          void
     * @throws          Throwable
     ************************************************************************/
    public function testSeekThrowsException($resource, int $offset, int $whence): void
    {
        $this->expectException(RuntimeException::class);

        (new Stream($resource))->seek($offset, $whence);

        self::fail(
            "Action \"Stream->seek\" threw no expected exception.\n".
            "Action was called with parameters (offset => $offset, whence => $whence).\n".
            "Expects \"RuntimeException\" exception.\n".
            'Caught no exception.'
        );
    }
    /** **********************************************************************
     * Test "Stream::seek" behavior with stream in a closed state.
     *
     * @covers          Stream::seek
     * @dataProvider    dataProviderResources
     *
     * @param           resource $resource              Recourse.
     *
     * @return          void
     * @throws          Throwable
     ************************************************************************/
    public function testSeekInClosedState($resource): void
    {
        $this->expectException(RuntimeException::class);

        $stream = new Stream($resource);
        $stream->close();
        $stream->seek(0);

        self::fail(
            "Action \"Stream->close->seek\" threw no expected exception.\n".
            "Expects \"RuntimeException\" exception.\n".
            'Caught no exception.'
        );
    }
    /** **********************************************************************
     * Test "Stream::seek" behavior with stream in a detached state.
     *
     * @covers          Stream::seek
     * @dataProvider    dataProviderResources
     *
     * @param           resource $resource              Recourse.
     *
     * @return          void
     * @throws          Throwable
     ************************************************************************/
    public function testSeekInDetachedState($resource): void
    {
        $this->expectException(RuntimeException::class);

        $stream = new Stream($resource);
        $stream->detach();
        $stream->seek(0);

        self::fail(
            "Action \"Stream->detach->seek\" threw no expected exception.\n".
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
     * Data provider: resources with seek valid params.
     *
     * @return  array                                   Data.
     ************************************************************************/
    public function dataProviderResourcesWithSeekValuesValid(): array
    {
        $result = [];

        foreach ((new ResourceGeneratorAll())->generate() as $resource) {
            $result[] = [
                $resource,
                0,
                SEEK_SET,
                0
            ];
        }
        foreach ((new ResourceGeneratorAll())->generate() as $resource) {
            $result[] = [
                $resource,
                1,
                SEEK_SET,
                1
            ];
        }
        foreach ((new ResourceGeneratorWritable())->generate() as $resource) {
            $content    = (new TextGenerator())->generate();
            $seekValue  = (int) (strlen($content) / 2);
            fwrite($resource, $content);
            $result[]   = [
                $resource,
                $seekValue,
                SEEK_SET,
                $seekValue
            ];
        }
        foreach ((new ResourceGeneratorWritable())->generate() as $resource) {
            $content    = (new TextGenerator())->generate();
            fwrite($resource, $content);
            $result[]   = [
                $resource,
                strlen($content) + 1,
                SEEK_SET,
                strlen($content) + 1
            ];
        }

        foreach ((new ResourceGeneratorWritable())->generate() as $resource) {
            $content            = (new TextGenerator())->generate();
            $seekValueFirst     = (int) (strlen($content) / 2);
            $seekValueSecond    = (int) (strlen($content) / 4);
            $seekValueTotal     = $seekValueFirst + $seekValueSecond;
            fwrite($resource, $content);
            fseek($resource, $seekValueFirst);
            $result[]           = [
                $resource,
                $seekValueSecond,
                SEEK_CUR,
                $seekValueTotal
            ];
        }
        foreach ((new ResourceGeneratorWritable())->generate() as $resource) {
            $content    = (new TextGenerator())->generate();
            fwrite($resource, $content);
            fseek($resource, strlen($content));
            $result[]   = [
                $resource,
                1,
                SEEK_CUR,
                strlen($content) + 1
            ];
        }

        foreach ((new ResourceGeneratorWritable())->generate() as $resource) {
            $content            = (new TextGenerator())->generate();
            $seekValue          = -1;
            $seekValueTotal     = strlen($content) + $seekValue;
            fwrite($resource, $content);
            $result[]           = [
                $resource,
                $seekValue,
                SEEK_END,
                $seekValueTotal,
            ];
        }
        foreach ((new ResourceGeneratorWritable())->generate() as $resource) {
            $content    = (new TextGenerator())->generate();
            fwrite($resource, $content);
            $result[]   = [
                $resource,
                1,
                SEEK_END,
                strlen($content) + 1,
            ];
        }

        return $result;
    }
    /** **********************************************************************
     * Data provider: resources with seek invalid params.
     *
     * @return  array                                   Data.
     ************************************************************************/
    public function dataProviderResourcesWithSeekValuesInvalid(): array
    {
        $result = [];

        foreach ((new ResourceGeneratorAll())->generate() as $resource) {
            $result[] = [
                $resource,
                -1,
                SEEK_SET,
            ];
        }
        foreach ((new ResourceGeneratorAll())->generate() as $resource) {
            $result[] = [
                $resource,
                0,
                SEEK_SET - 1,
            ];
        }

        foreach ((new ResourceGeneratorAll())->generate() as $resource) {
            $result[] = [
                $resource,
                0,
                SEEK_END + 1,
            ];
        }

        return $result;
    }
}