<?php

declare(strict_types=1);

namespace HNV\Http\StreamTests\Factory;

use HNV\Http\Helper\Collection\Resource\AccessModeType;
use HNV\Http\Helper\Generator\Text as TextGenerator;
use HNV\Http\Stream\StreamFactory;
use HNV\Http\StreamTests\AbstractStreamTestCase;
use PHPUnit\Framework\Attributes;

use function fwrite;
use function rewind;

/**
 * @internal
 */
#[Attributes\CoversClass(StreamFactory::class)]
#[Attributes\Small]
class StreamFactoryFromResourceTest extends AbstractStreamTestCase
{
    /**
     * @param resource $resource
     */
    #[Attributes\Test]
    #[Attributes\DataProvider('dataProviderResourcesWithValues')]
    public function create(
        $resource,
        string $content,
        bool $isWritable
    ): void {
        $stream = (new StreamFactory())->createStreamFromResource($resource);

        static::assertSame(
            0,
            $stream->tell(),
            'Expects built stream is rewound'
        );
        static::assertFalse(
            $stream->eof(),
            'Expects built stream is rewound'
        );
        static::assertTrue(
            $stream->isSeekable(),
            'Expects built stream is seekable'
        );
        static::assertTrue(
            $stream->isReadable(),
            'Expects built stream is readable'
        );

        static::assertSame($isWritable, $stream->isWritable());
        static::assertSame($content, $stream->getContents());
    }

    public function dataProviderResourcesWithValues(): iterable
    {
        foreach ($this->generateResources(AccessModeType::READABLE_ONLY) as $resource) {
            yield [$resource, '', false];
        }

        foreach ($this->generateResources(AccessModeType::READABLE_AND_WRITABLE) as $resource) {
            $content = (new TextGenerator())->generate();
            fwrite($resource, $content);
            rewind($resource);

            yield [$resource, $content, true];
        }
    }
}
