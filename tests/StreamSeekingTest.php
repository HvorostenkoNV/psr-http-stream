<?php

declare(strict_types=1);

namespace HNV\Http\StreamTests;

use HNV\Http\Helper\Collection\Resource\AccessModeType;
use HNV\Http\Helper\Generator\Text as TextGenerator;
use HNV\Http\Stream\Stream;
use PHPUnit\Framework\Attributes;
use RuntimeException;

use function fseek;
use function ftell;
use function fwrite;
use function strlen;

use const SEEK_CUR;
use const SEEK_END;
use const SEEK_SET;

/**
 * @internal
 */
#[Attributes\CoversClass(Stream::class)]
#[Attributes\Small]
class StreamSeekingTest extends AbstractStreamTestCase
{
    /**
     * @param resource $resource
     */
    #[Attributes\Test]
    #[Attributes\DataProvider('dataProviderResourcesWithSeekValuesValid')]
    public function seek(
        $resource,
        int $offset,
        int $whence,
        int $positionExpected
    ): void {
        $stream = new Stream($resource);
        $stream->seek($offset, $whence);
        $positionCaught = ftell($resource);

        static::assertSame($positionExpected, $positionCaught);
    }

    /**
     * @param resource $resource
     */
    #[Attributes\Test]
    #[Attributes\DataProvider('dataProviderResourcesWithSeekValuesInvalid')]
    public function seekThrowsException($resource, int $offset, int $whence): void
    {
        $this->expectException(RuntimeException::class);

        (new Stream($resource))->seek($offset, $whence);

        static::fail('Expects exception on seeking with invalid parameters');
    }

    /**
     * @param resource $resource
     */
    #[Attributes\Test]
    #[Attributes\DataProvider('dataProviderResources')]
    public function seekOnClosedStream($resource): void
    {
        $this->expectException(RuntimeException::class);

        $stream = new Stream($resource);
        $stream->close();
        $stream->seek(0);

        static::fail('Expects exception on seeking closed stream');
    }

    /**
     * @param resource $resource
     */
    #[Attributes\Test]
    #[Attributes\DataProvider('dataProviderResources')]
    public function seekOnDetachedStream($resource): void
    {
        $this->expectException(RuntimeException::class);

        $stream = new Stream($resource);
        $stream->detach();
        $stream->seek(0);

        static::fail('Expects exception on seeking detached stream');
    }

    public function dataProviderResourcesWithSeekValuesValid(): iterable
    {
        foreach ($this->generateResources(AccessModeType::ALL) as $resource) {
            yield [
                $resource,
                0,
                SEEK_SET,
                0,
            ];
        }

        foreach ($this->generateResources(AccessModeType::ALL) as $resource) {
            yield [
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

            yield [
                $resource,
                $seekValue,
                SEEK_SET,
                $seekValue,
            ];
        }

        foreach ($this->generateResources(AccessModeType::WRITABLE) as $resource) {
            $content = (new TextGenerator())->generate();
            fwrite($resource, $content);

            yield [
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

            yield [
                $resource,
                $seekValueSecond,
                SEEK_CUR,
                $seekValueTotal,
            ];
        }

        foreach ($this->generateResources(AccessModeType::WRITABLE) as $resource) {
            $content = (new TextGenerator())->generate();
            fwrite($resource, $content);
            fseek($resource, strlen($content));

            yield [
                $resource,
                1,
                SEEK_CUR,
                strlen($content) + 1,
            ];
        }

        foreach ($this->generateResources(AccessModeType::WRITABLE) as $resource) {
            $content        = (new TextGenerator())->generate();
            $seekValue      = -1;
            $seekValueTotal = strlen($content) + $seekValue;
            fwrite($resource, $content);

            yield [
                $resource,
                $seekValue,
                SEEK_END,
                $seekValueTotal,
            ];
        }

        foreach ($this->generateResources(AccessModeType::WRITABLE) as $resource) {
            $content = (new TextGenerator())->generate();
            fwrite($resource, $content);

            yield [
                $resource,
                1,
                SEEK_END,
                strlen($content) + 1,
            ];
        }
    }

    public function dataProviderResourcesWithSeekValuesInvalid(): iterable
    {
        foreach ($this->generateResources(AccessModeType::ALL) as $resource) {
            yield [$resource, -1, SEEK_SET];
        }

        foreach ($this->generateResources(AccessModeType::ALL) as $resource) {
            yield [$resource, 0, SEEK_SET - 1];
        }

        foreach ($this->generateResources(AccessModeType::ALL) as $resource) {
            yield [$resource, 0, SEEK_END + 1];
        }
    }
}
