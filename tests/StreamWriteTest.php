<?php

declare(strict_types=1);

namespace HNV\Http\StreamTests;

use HNV\Http\Helper\Collection\Resource\AccessModeType;
use HNV\Http\Helper\Generator\Text as TextGenerator;
use HNV\Http\Stream\Stream;
use PHPUnit\Framework\Attributes;
use RuntimeException;

use function ftell;
use function fwrite;
use function rewind;
use function stream_get_contents;
use function strlen;

/**
 * @internal
 */
#[Attributes\CoversClass(Stream::class)]
#[Attributes\Small]
class StreamWriteTest extends AbstractStreamTestCase
{
    /**
     * @param resource $resource
     */
    #[Attributes\Test]
    #[Attributes\DataProvider('dataProviderResourcesWithWriteParametersValid')]
    public function write($resource, string $data, int $resultExpected): void
    {
        $resultCaught = (new Stream($resource))->write($data);

        static::assertSame($resultExpected, $resultCaught);
    }

    /**
     * @param resource $resource
     */
    #[Attributes\Test]
    #[Attributes\DataProvider('dataProviderResourcesWithWriteParametersInvalid')]
    public function writeThrowsException($resource, string $data): void
    {
        $this->expectException(RuntimeException::class);

        (new Stream($resource))->write($data);

        static::fail('Expects exception on writing with invalid parameters');
    }

    /**
     * @param resource $resource
     */
    #[Attributes\Test]
    #[Attributes\DataProvider('dataProviderResourcesWithDoubleDataToWrite')]
    public function writeAddData($resource, string $data1, string $data2): void
    {
        $stream = new Stream($resource);

        $stream->write($data1);
        $stream->write($data2);

        rewind($resource);
        $contentCaught = stream_get_contents($resource);

        static::assertSame(
            $data1.$data2,
            $contentCaught,
            'Expects [write] WILL add content and WILL NOT rewrite exist'
        );
    }

    /**
     * @param resource $resource
     */
    #[Attributes\Test]
    #[Attributes\DataProvider('dataProviderResourcesWithCursorPointerParameters')]
    public function writeMovesCursorPosition(
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
            'Expects [write] WILL move cursor pointer'
        );
    }

    /**
     * @param resource $resource
     */
    #[Attributes\Test]
    #[Attributes\DataProvider('dataProviderResources')]
    public function writeOnClosedStream($resource): void
    {
        $this->expectException(RuntimeException::class);

        $stream = new Stream($resource);
        $data   = (new TextGenerator())->generate();

        $stream->close();
        $stream->write($data);

        static::fail('Expects exception during write on closed stream');
    }

    /**
     * @param resource $resource
     */
    #[Attributes\Test]
    #[Attributes\DataProvider('dataProviderResources')]
    public function writeOnDetachedStream($resource): void
    {
        $this->expectException(RuntimeException::class);

        $stream = new Stream($resource);
        $data   = (new TextGenerator())->generate();

        $stream->detach();
        $stream->write($data);

        static::fail('Expects exception during write on detached stream');
    }

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

    public function dataProviderResourcesWithWriteParametersInvalid(): array
    {
        $result = [];

        foreach ($this->generateResources(AccessModeType::READABLE_ONLY) as $resource) {
            $content    = (new TextGenerator())->generate();
            $result[]   = [$resource, $content];
        }

        return $result;
    }

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
