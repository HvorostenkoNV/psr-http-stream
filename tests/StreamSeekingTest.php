<?php

declare(strict_types=1);

namespace HNV\Http\StreamTests;

use HNV\Http\Helper\Collection\Resource\AccessModeType;
use HNV\Http\Helper\Generator\Text as TextGenerator;
use HNV\Http\Stream\Stream;
use RuntimeException;

use function fseek;
use function ftell;
use function fwrite;
use function strlen;

use const SEEK_CUR;
use const SEEK_END;
use const SEEK_SET;

/**
 * PSR-7 StreamInterface implementation test.
 *
 * Testing stream seeking behavior.
 *
 * @internal
 * @covers Stream
 * @small
 */
class StreamSeekingTest extends AbstractStreamTest
{
    /**
     * @covers          Stream::seek
     * @dataProvider    dataProviderResourcesWithSeekValuesValid
     *
     * @param resource $resource recourse
     */
    public function testSeek(
        $resource,
        int $offset,
        int $whence,
        int $positionExpected
    ): void {
        $stream = new Stream($resource);
        $stream->seek($offset, $whence);
        $positionCaught = ftell($resource);

        static::assertSame(
            $positionExpected,
            $positionCaught,
            "Action \"Stream->seek\" returned unexpected result.\n".
            "Action was called with parameters (offset => {$offset}, whence => {$whence}).\n".
            "Expected result is \"{$positionExpected}\".\n".
            "Caught result is \"{$positionCaught}\"."
        );
    }

    /**
     * @covers          Stream::seek
     * @dataProvider    dataProviderResourcesWithSeekValuesInvalid
     *
     * @param resource $resource recourse
     */
    public function testSeekThrowsException($resource, int $offset, int $whence): void
    {
        $this->expectException(RuntimeException::class);

        (new Stream($resource))->seek($offset, $whence);

        static::fail(
            "Action \"Stream->seek\" threw no expected exception.\n".
            "Action was called with parameters (offset => {$offset}, whence => {$whence}).\n".
            "Expects \"RuntimeException\" exception.\n".
            'Caught no exception.'
        );
    }

    /**
     * @covers          Stream::seek
     * @dataProvider    dataProviderResources
     *
     * @param resource $resource recourse
     */
    public function testSeekInClosedState($resource): void
    {
        $this->expectException(RuntimeException::class);

        $stream = new Stream($resource);
        $stream->close();
        $stream->seek(0);

        static::fail(
            "Action \"Stream->close->seek\" threw no expected exception.\n".
            "Expects \"RuntimeException\" exception.\n".
            'Caught no exception.'
        );
    }

    /**
     * @covers          Stream::seek
     * @dataProvider    dataProviderResources
     *
     * @param resource $resource recourse
     */
    public function testSeekInDetachedState($resource): void
    {
        $this->expectException(RuntimeException::class);

        $stream = new Stream($resource);
        $stream->detach();
        $stream->seek(0);

        static::fail(
            "Action \"Stream->detach->seek\" threw no expected exception.\n".
            "Expects \"RuntimeException\" exception.\n".
            'Caught no exception.'
        );
    }

    /**
     * Data provider: resources with seek valid params.
     */
    public function dataProviderResourcesWithSeekValuesValid(): array
    {
        $result = [];

        foreach ($this->generateResources(AccessModeType::ALL) as $resource) {
            $result[] = [
                $resource,
                0,
                SEEK_SET,
                0,
            ];
        }
        foreach ($this->generateResources(AccessModeType::ALL) as $resource) {
            $result[] = [
                $resource,
                1,
                SEEK_SET,
                1,
            ];
        }
        foreach ($this->generateResources(AccessModeType::WRITABLE) as $resource) {
            $content    = (new TextGenerator())->generate();
            $seekValue  = (int) (strlen($content) / 2);
            fwrite($resource, $content);
            $result[]   = [
                $resource,
                $seekValue,
                SEEK_SET,
                $seekValue,
            ];
        }
        foreach ($this->generateResources(AccessModeType::WRITABLE) as $resource) {
            $content    = (new TextGenerator())->generate();
            fwrite($resource, $content);
            $result[]   = [
                $resource,
                strlen($content) + 1,
                SEEK_SET,
                strlen($content) + 1,
            ];
        }

        foreach ($this->generateResources(AccessModeType::WRITABLE) as $resource) {
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
                $seekValueTotal,
            ];
        }
        foreach ($this->generateResources(AccessModeType::WRITABLE) as $resource) {
            $content    = (new TextGenerator())->generate();
            fwrite($resource, $content);
            fseek($resource, strlen($content));
            $result[]   = [
                $resource,
                1,
                SEEK_CUR,
                strlen($content) + 1,
            ];
        }

        foreach ($this->generateResources(AccessModeType::WRITABLE) as $resource) {
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
        foreach ($this->generateResources(AccessModeType::WRITABLE) as $resource) {
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

    /**
     * Data provider: resources with seek invalid params.
     */
    public function dataProviderResourcesWithSeekValuesInvalid(): array
    {
        $result = [];

        foreach ($this->generateResources(AccessModeType::ALL) as $resource) {
            $result[] = [
                $resource,
                -1,
                SEEK_SET,
            ];
        }
        foreach ($this->generateResources(AccessModeType::ALL) as $resource) {
            $result[] = [
                $resource,
                0,
                SEEK_SET - 1,
            ];
        }

        foreach ($this->generateResources(AccessModeType::ALL) as $resource) {
            $result[] = [
                $resource,
                0,
                SEEK_END + 1,
            ];
        }

        return $result;
    }
}
