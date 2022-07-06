<?php

declare(strict_types=1);

namespace HNV\Http\StreamTests\Factory;

use HNV\Http\Helper\Generator\Text as TextGenerator;
use HNV\Http\Stream\StreamFactory;
use HNV\Http\StreamTests\AbstractStreamTest;

/**
 * PSR-7 StreamFactoryInterface implementation test.
 *
 * Testing building stream from string behavior.
 *
 * @internal
 * @covers StreamFactory
 * @small
 */
class StreamFactoryFromStringTest extends AbstractStreamTest
{
    /**
     * @covers          StreamFactory::createStream
     * @dataProvider    dataProviderValues
     */
    public function testCreating(string $content): void
    {
        $stream = (new StreamFactory())->createStream($content);

        static::assertSame(
            0,
            $stream->tell(),
            "Action \"StreamFactory->createStream->tell\" returned unexpected result.\n".
            "Expected result is \"0\".\n".
            'Caught result is "NOT 0".'
        );
        static::assertFalse(
            $stream->eof(),
            "Action \"StreamFactory->createStream->eof\" returned unexpected result.\n".
            "Expected result is \"false\".\n".
            'Caught result is "NOT false".'
        );
        static::assertTrue(
            $stream->isSeekable(),
            "Action \"StreamFactory->createStream->isSeekable\" returned unexpected result.\n".
            "Expected result is \"true\".\n".
            'Caught result is "NOT true".'
        );
        static::assertTrue(
            $stream->isReadable(),
            "Action \"StreamFactory->createStream->isReadable\" returned unexpected result.\n".
            "Expected result is \"true\".\n".
            'Caught result is "NOT true".'
        );
        static::assertTrue(
            $stream->isWritable(),
            "Action \"StreamFactory->createStream->isWritable\" returned unexpected result.\n".
            "Expected result is \"true\".\n".
            'Caught result is "NOT true".'
        );

        $contentCaught = $stream->getContents();
        static::assertSame(
            $content,
            $contentCaught,
            "Action \"StreamFactory->createStream->getContents\" returned unexpected result.\n".
            "Expected result is \"{$content}\".\n".
            "Caught result is \"{$contentCaught}\"."
        );
    }

    /**
     * Data provider: random text content.
     */
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
