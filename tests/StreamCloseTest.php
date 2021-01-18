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
use function is_resource;
/** ***********************************************************************************************
 * PSR-7 StreamInterface implementation test.
 *
 * Testing stream closing behavior.
 *
 * @package HNV\Psr\Http\Tests\Stream
 * @author  Hvorostenko
 *************************************************************************************************/
class StreamCloseTest extends TestCase
{
    /** **********************************************************************
     * Test "Stream::close" closes underlying resource.
     *
     * @covers          Stream::close
     * @dataProvider    dataProviderResources
     *
     * @param           resource $resource              Recourse.
     *
     * @return          void
     * @throws          Throwable
     ************************************************************************/
    public function testClose($resource): void
    {
        $stream = new Stream($resource);
        $stream->close();

        self::assertFalse(
            is_resource($resource),
            "Action \"Stream->close\" showed unexpected behavior.\n".
            "Expects underlying resource will be closed\n".
            'Expects underlying resource is not closed'
        );
    }
    /** **********************************************************************
     * Test "Stream::close" DO NOT closes underlying resource, if stream is detached.
     *
     * @covers          Stream::close
     * @dataProvider    dataProviderResources
     *
     * @param           resource $resource              Recourse.
     *
     * @return          void
     * @throws          Throwable
     ************************************************************************/
    public function testCloseOnDetachedResource($resource): void
    {
        $stream             = new Stream($resource);
        $resourceDetached   = $stream->detach();
        $stream->close();

        self::assertTrue(
            is_resource($resourceDetached),
            "Action \"Stream->detach->close\" showed unexpected behavior.\n".
            "Expects underlying resource will be NOT closed\n".
            'Expects underlying resource is closed'
        );
    }
    /** **********************************************************************
     * Test "Stream::__destruct" closes underlying resource.
     *
     * @covers          Stream::__destruct
     * @dataProvider    dataProviderResources
     *
     * @param           resource $resource              Recourse.
     *
     * @return          void
     * @throws          Throwable
     ************************************************************************/
    public function testDestructorClosesResource($resource): void
    {
        $stream = new Stream($resource);
        unset($stream);

        self::assertFalse(
            is_resource($resource),
            "Action \"Stream->__destruct\" showed unexpected behavior.\n".
            "Expects underlying resource will be closed\n".
            'Expects underlying resource is not closed'
        );
    }
    /** **********************************************************************
     * Data provider: resources, readable and writable.
     *
     * @return  array                                   Data.
     ************************************************************************/
    public function dataProviderResources(): array
    {
        $modes  = array_diff(
            AccessModeReadableAndWritable::get(),
            AccessModeNonSuitable::get()
        );
        $result = [];

        foreach ($modes as $mode) {
            $result[] = [(new ResourceGenerator($mode))->generate()];
        }

        return $result;
    }
}