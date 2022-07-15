<?php

declare(strict_types=1);

namespace HNV\Http\StreamTests\Factory;

use HNV\Http\Helper\Generator\Text as TextGenerator;
use HNV\Http\Stream\StreamFactory;
use HNV\Http\StreamTests\AbstractStreamTestCase;
use PHPUnit\Framework\Attributes;

/**
 * @internal
 */
#[Attributes\CoversClass(StreamFactory::class)]
#[Attributes\Small]
class StreamFactoryFromStringTest extends AbstractStreamTestCase
{
    #[Attributes\Test]
    #[Attributes\DataProvider('dataProviderValues')]
    public function create(string $content): void
    {
        $stream = (new StreamFactory())->createStream($content);

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
        static::assertTrue(
            $stream->isWritable(),
            'Expects built stream is writable'
        );

        static::assertSame($content, $stream->getContents());
    }

    public function dataProviderValues(): array
    {
        $result = [];

        for ($iterator = 10; $iterator >= 0; $iterator--) {
            $content    = (new TextGenerator())->generate();
            $result[]   = [$content];
        }

        return $result;
    }
}
