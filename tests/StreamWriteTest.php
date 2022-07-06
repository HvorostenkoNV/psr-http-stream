<?php

declare(strict_types=1);

namespace HNV\Http\StreamTests;

use HNV\Http\Helper\Collection\Resource\AccessModeType;
use HNV\Http\Helper\Generator\Text as TextGenerator;
use HNV\Http\Stream\Stream;
use RuntimeException;

use function ftell;
use function fwrite;
use function rewind;
use function stream_get_contents;
use function strlen;

/**
 * PSR-7 StreamInterface implementation test.
 *
 * Testing stream writing behavior.
 *
 * @internal
 * @covers Stream
 * @small
 */
class StreamWriteTest extends AbstractStreamTest
{
    /**
     * @covers          Stream::write
     * @dataProvider    dataProviderResourcesWithWriteParametersValid
     *
     * @param resource $resource resource
     */
    public function testWrite($resource, string $data, int $resultExpected): void
    {
        $resultCaught = (new Stream($resource))->write($data);

        static::assertSame(
            $resultExpected,
            $resultCaught,
            "Action \"Stream->write\" returned unexpected result.\n".
            "Action was called with parameters (data => {$data}).\n".
            "Expected result is \"{$resultExpected}\".\n".
            "Caught result is \"{$resultCaught}\"."
        );
    }

    /**
     * @covers          Stream::write
     * @dataProvider    dataProviderResourcesWithWriteParametersInvalid
     *
     * @param resource $resource resource
     */
    public function testWriteThrowsException($resource, string $data): void
    {
        $this->expectException(RuntimeException::class);

        (new Stream($resource))->write($data);

        static::fail(
            "Action \"Stream->write\" threw no expected exception.\n".
            "Expects \"RuntimeException\" exception.\n".
            'Caught no exception.'
        );
    }

    /**
     * @covers          Stream::write
     * @dataProvider    dataProviderResourcesWithDoubleDataToWrite
     *
     * @param resource $resource resource
     */
    public function testWriteAddData($resource, string $data1, string $data2): void
    {
        $stream = new Stream($resource);

        $stream->write($data1);
        $stream->write($data2);

        rewind($resource);
        $contentCaught = stream_get_contents($resource);

        static::assertSame(
            $data1.$data2,
            $contentCaught,
            "Action \"Stream->write->write\" returned unexpected result.\n".
            "Action was called with parameters (data => {$data1}, data => {$data2}).\n".
            "Expected result is \"{$data1}{$data2}\".\n".
            "Caught result is \"{$contentCaught}\"."
        );
    }

    /**
     * @covers          Stream::write
     * @dataProvider    dataProviderResourcesWithCursorPointerParameters
     *
     * @param resource $resource resource
     */
    public function testWriteMovesCursorPosition(
        $resource,
        string $data,
        int $pointerExpected
    ): void {
        $stream         = new Stream($resource);
        $stream->write($data);
        $pointerCaught  = ftell($resource);

        static::assertSame(
            $pointerExpected,
            $pointerCaught,
            "Action \"Stream->write\" showed unexpected behavior.\n".
            "Action was called with parameters (data => {$data}) twice.\n".
            "Expects underlying resource cursor position is \"{$pointerExpected}\"\n".
            "Underlying resource cursor position is \"{$pointerCaught}\""
        );
    }

    /**
     * @covers          Stream::write
     * @dataProvider    dataProviderResources
     *
     * @param resource $resource resource
     */
    public function testWriteInClosedState($resource): void
    {
        $this->expectException(RuntimeException::class);

        $stream = new Stream($resource);
        $data   = (new TextGenerator())->generate();

        $stream->close();
        $stream->write($data);

        static::fail(
            "Action \"Stream->close->write\" threw no expected exception.\n".
            "Expects \"RuntimeException\" exception.\n".
            'Caught no exception.'
        );
    }

    /**
     * @covers          Stream::write
     * @dataProvider    dataProviderResources
     *
     * @param resource $resource resource
     */
    public function testWriteInDetachedState($resource): void
    {
        $this->expectException(RuntimeException::class);

        $stream = new Stream($resource);
        $data   = (new TextGenerator())->generate();

        $stream->detach();
        $stream->write($data);

        static::fail(
            "Action \"Stream->detach->write\" threw no expected exception.\n".
            "Expects \"RuntimeException\" exception.\n".
            'Caught no exception.'
        );
    }

    /**
     * Data provider: resources with data to write valid parameters.
     */
    public function dataProviderResourcesWithWriteParametersValid(): array
    {
        $result = [];

        foreach ($this->generateResources(AccessModeType::WRITABLE) as $resource) {
            $result[]   = [$resource, '', 0];
        }
        foreach ($this->generateResources(AccessModeType::WRITABLE) as $resource) {
            $content    = (new TextGenerator())->generate();
            $result[]   = [$resource, $content, strlen($content)];
        }
        foreach ($this->generateResources(AccessModeType::WRITABLE) as $resource) {
            $content1   = (new TextGenerator())->generate();
            $content2   = (new TextGenerator())->generate();
            fwrite($resource, $content1);
            $result[]   = [$resource, $content2, strlen($content2)];
        }

        return $result;
    }

    /**
     * Data provider: resources with data to write invalid parameters.
     */
    public function dataProviderResourcesWithWriteParametersInvalid(): array
    {
        $result = [];

        foreach ($this->generateResources(AccessModeType::READABLE_ONLY) as $resource) {
            $content    = (new TextGenerator())->generate();
            $result[]   = [$resource, $content];
        }

        return $result;
    }

    /**
     * Data provider: resources with double data to write.
     */
    public function dataProviderResourcesWithDoubleDataToWrite(): array
    {
        $result = [];

        foreach ($this->generateResources(AccessModeType::READABLE_AND_WRITABLE) as $resource) {
            $content1   = (new TextGenerator())->generate();
            $content2   = (new TextGenerator())->generate();
            $result[]   = [$resource, $content1, $content2];
        }

        return $result;
    }

    /**
     * Data provider: resources with data to write and expected cursor pointer position.
     */
    public function dataProviderResourcesWithCursorPointerParameters(): array
    {
        $result = [];

        foreach ($this->generateResources(AccessModeType::WRITABLE) as $resource) {
            $result[]   = [$resource, '', 0];
        }
        foreach ($this->generateResources(AccessModeType::WRITABLE) as $resource) {
            $content    = (new TextGenerator())->generate();
            $result[]   = [$resource, $content, strlen($content)];
        }
        foreach ($this->generateResources(AccessModeType::WRITABLE) as $resource) {
            $content1   = (new TextGenerator())->generate();
            $content2   = (new TextGenerator())->generate();
            fwrite($resource, $content1);
            $result[]   = [$resource, $content2, strlen($content1.$content2)];
        }

        return $result;
    }
}
