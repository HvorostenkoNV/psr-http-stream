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
    ResourceAccessMode\ReadableOnly         as AccessModeReadableOnly,
    ResourceAccessMode\WritableOnly         as AccessModeWritableOnly,
    ResourceAccessMode\ReadableAndWritable  as AccessModeReadableAndWritable,
    ResourceAccessMode\NonSuitable          as AccessModeNonSuitable
};

use function strlen;
use function array_diff;
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
     * @dataProvider    dataProviderResourcesSeekable
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
     * @dataProvider    dataProviderResourcesSeekable
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
     * Data provider: seekable resources.
     *
     * @return  array                                   Data.
     ************************************************************************/
    public function dataProviderResourcesSeekable(): array
    {
        $result = [];

        foreach ($this->getResourcesWithStates() as $parameters) {
            if ($parameters['isSeekable']) {
                $result[] = [$parameters['resource']];
            }
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

        foreach ($this->getResourcesWithSeekingParameters() as $parameters) {
            if ($parameters['seekingIsValid']) {
                $result[] = [
                    $parameters['resource'],
                    $parameters['seekingOffset'],
                    $parameters['seekingWhence'],
                    $parameters['seekingExpectedPosition'],
                ];
            }
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

        foreach ($this->getResourcesWithSeekingParameters() as $parameters) {
            if (!$parameters['seekingIsValid']) {
                $result[] = [
                    $parameters['resource'],
                    $parameters['seekingOffset'],
                    $parameters['seekingWhence'],
                ];
            }
        }

        return $result;
    }
    /** **********************************************************************
     * Data provider helper: resources with states parameters.
     *
     * @example
     *         [
     *          resource    => resources,
     *          isSeekable  => resources is seekable (boolean),
     *          isWritable  => resources is writable (boolean),
     *         ]
     * @return  array[]                                 Data set.
     ************************************************************************/
    private function getResourcesWithStates(): array
    {
        $modesReadableOnly          = AccessModeReadableOnly::get();
        $modesWritableOnly          = AccessModeWritableOnly::get();
        $modesReadableAndWritable   = AccessModeReadableAndWritable::get();
        $modesNonSuitable           = AccessModeNonSuitable::get();
        $result                     = [];

        foreach (array_diff($modesReadableOnly, $modesNonSuitable) as $mode) {
            $resource   = (new ResourceGenerator($mode))->generate();
            $result[]   = [
                'resource'      => $resource,
                'isSeekable'    => true,
                'isWritable'    => false,
            ];
        }
        foreach (array_diff($modesWritableOnly, $modesNonSuitable) as $mode) {
            $resource   = (new ResourceGenerator($mode))->generate();
            $result[]   = [
                'resource'      => $resource,
                'isSeekable'    => true,
                'isWritable'    => true,
            ];
        }
        foreach (array_diff($modesReadableAndWritable, $modesNonSuitable) as $mode) {
            $resource   = (new ResourceGenerator($mode))->generate();
            $result[]   = [
                'resource'      => $resource,
                'isSeekable'    => true,
                'isWritable'    => true,
            ];
        }

        return $result;
    }
    /** **********************************************************************
     * Data provider helper: resources with seeking params.
     *
     * @example
     *         [
     *          resource    => resources,
     *          offset      => seeking offset parameter (integer),
     *          whence      => seeking whence parameter (integer),
     *          position    => expected cursor position (integer),
     *          isValid     => parameters valid marker (boolean),
     *         ]
     * @return  array[]                                 Data set.
     ************************************************************************/
    private function getResourcesWithSeekingParameters(): array
    {
        $result = [];

        foreach ($this->getResourcesWithStates() as $parameters) {
            $result[] = [
                'resource'                  => $parameters['resource'],
                'seekingOffset'             => 0,
                'seekingWhence'             => SEEK_SET,
                'seekingExpectedPosition'   => 0,
                'seekingIsValid'            => $parameters['isSeekable'],
            ];
        }
        foreach ($this->getResourcesWithStates() as $parameters) {
            $result[] = [
                'resource'                  => $parameters['resource'],
                'seekingOffset'             => 1,
                'seekingWhence'             => SEEK_SET,
                'seekingExpectedPosition'   => 1,
                'seekingIsValid'            => $parameters['isSeekable'],
            ];
        }
        foreach ($this->getResourcesWithStates() as $parameters) {
            $result[] = [
                'resource'                  => $parameters['resource'],
                'seekingOffset'             => -1,
                'seekingWhence'             => SEEK_SET,
                'seekingExpectedPosition'   => 0,
                'seekingIsValid'            => false,
            ];
        }
        foreach ($this->getResourcesWithStates() as $parameters) {
            if ($parameters['isSeekable'] && $parameters['isWritable']) {
                $content    = (new TextGenerator())->generate();
                $seekValue  = (int) (strlen($content) / 2);

                fwrite($parameters['resource'], $content);

                $result[]   = [
                    'resource'                  => $parameters['resource'],
                    'seekingOffset'             => $seekValue,
                    'seekingWhence'             => SEEK_SET,
                    'seekingExpectedPosition'   => $seekValue,
                    'seekingIsValid'            => true,
                ];
            }
        }
        foreach ($this->getResourcesWithStates() as $parameters) {
            if ($parameters['isSeekable'] && $parameters['isWritable']) {
                $content    = (new TextGenerator())->generate();

                fwrite($parameters['resource'], $content);

                $result[]   = [
                    'resource'                  => $parameters['resource'],
                    'seekingOffset'             => strlen($content) + 1,
                    'seekingWhence'             => SEEK_SET,
                    'seekingExpectedPosition'   => strlen($content) + 1,
                    'seekingIsValid'            => true,
                ];
            }
        }

        foreach ($this->getResourcesWithStates() as $parameters) {
            if ($parameters['isSeekable'] && $parameters['isWritable']) {
                $content            = (new TextGenerator())->generate();
                $seekValueFirst     = (int) (strlen($content) / 2);
                $seekValueSecond    = (int) (strlen($content) / 4);
                $seekValueTotal     = $seekValueFirst + $seekValueSecond;

                fwrite($parameters['resource'], $content);
                fseek($parameters['resource'], $seekValueFirst);

                $result[]           = [
                    'resource'                  => $parameters['resource'],
                    'seekingOffset'             => $seekValueSecond,
                    'seekingWhence'             => SEEK_CUR,
                    'seekingExpectedPosition'   => $seekValueTotal,
                    'seekingIsValid'            => true,
                ];
            }
        }
        foreach ($this->getResourcesWithStates() as $parameters) {
            if ($parameters['isSeekable'] && $parameters['isWritable']) {
                $content    = (new TextGenerator())->generate();

                fwrite($parameters['resource'], $content);
                fseek($parameters['resource'], strlen($content));

                $result[]   = [
                    'resource'                  => $parameters['resource'],
                    'seekingOffset'             => 1,
                    'seekingWhence'             => SEEK_CUR,
                    'seekingExpectedPosition'   => strlen($content) + 1,
                    'seekingIsValid'            => true,
                ];
            }
        }

        foreach ($this->getResourcesWithStates() as $parameters) {
            if ($parameters['isSeekable'] && $parameters['isWritable']) {
                $content            = (new TextGenerator())->generate();
                $seekValue          = -1;
                $seekValueTotal     = strlen($content) + $seekValue;

                fwrite($parameters['resource'], $content);

                $result[]           = [
                    'resource'                  => $parameters['resource'],
                    'seekingOffset'             => $seekValue,
                    'seekingWhence'             => SEEK_END,
                    'seekingExpectedPosition'   => $seekValueTotal,
                    'seekingIsValid'            => true,
                ];
            }
        }
        foreach ($this->getResourcesWithStates() as $parameters) {
            if ($parameters['isSeekable'] && $parameters['isWritable']) {
                $content    = (new TextGenerator())->generate();

                fwrite($parameters['resource'], $content);

                $result[]   = [
                    'resource'                  => $parameters['resource'],
                    'seekingOffset'             => 1,
                    'seekingWhence'             => SEEK_END,
                    'seekingExpectedPosition'   => strlen($content) + 1,
                    'seekingIsValid'            => true,
                ];
            }
        }
        foreach ($this->getResourcesWithStates() as $parameters) {
            $result[] = [
                'resource'                  => $parameters['resource'],
                'seekingOffset'             => 0,
                'seekingWhence'             => SEEK_END + 1,
                'seekingExpectedPosition'   => 0,
                'seekingIsValid'            => false,
            ];
        }
        foreach ($this->getResourcesWithStates() as $parameters) {
            $result[] = [
                'resource'                  => $parameters['resource'],
                'seekingOffset'             => 0,
                'seekingWhence'             => SEEK_SET - 1,
                'seekingExpectedPosition'   => 0,
                'seekingIsValid'            => false,
            ];
        }

        return $result;
    }
}