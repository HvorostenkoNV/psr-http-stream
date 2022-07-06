<?php

declare(strict_types=1);

namespace HNV\Http\StreamTests\Factory;

use HNV\Http\Helper\Collection\Resource\AccessModeType;
use HNV\Http\Helper\Generator\Text as TextGenerator;
use HNV\Http\Stream\StreamFactory;
use HNV\Http\StreamTests\AbstractStreamTest;

use function fwrite;
use function rewind;

/**
 * PSR-7 StreamFactoryInterface implementation test.
 *
 * Testing building stream from resource behavior.
 *
 * @internal
 * @covers StreamFactory
 * @small
 */
class StreamFactoryFromResourceTest extends AbstractStreamTest
{
    /**
     * @covers          StreamFactory::createStreamFromResource
     * @dataProvider    dataProviderResourcesWithValues
     *
     * @param resource $resource resource
     */
    public function testCreateStreamFromResource(
        $resource,
        string $content,
        bool $isWritable
    ): void {
        $stream = (new StreamFactory())->createStreamFromResource($resource);

        static::assertSame(
            0,
            $stream->tell(),
            "Action \"StreamFactory->createStreamFromResource->tell\" returned unexpected result.\n".
            "Expected result is \"0\".\n".
            'Caught result is "NOT 0".'
        );
        static::assertFalse(
            $stream->eof(),
            "Action \"StreamFactory->createStreamFromResource->eof\" returned unexpected result.\n".
            "Expected result is \"false\".\n".
            'Caught result is "NOT false".'
        );
        static::assertTrue(
            $stream->isSeekable(),
            "Action \"StreamFactory->createStreamFromResource->isSeekable\" returned unexpected result.\n".
            "Expected result is \"true\".\n".
            'Caught result is "NOT true".'
        );
        static::assertTrue(
            $stream->isReadable(),
            "Action \"StreamFactory->createStreamFromResource->isReadable\" returned unexpected result.\n".
            "Expected result is \"true\".\n".
            'Caught result is "NOT true".'
        );
        static::assertSame(
            $isWritable,
            $stream->isWritable(),
            "Action \"StreamFactory->createStreamFromResource->isWritable\" returned unexpected result.\n".
            "Expected result is \"true\".\n".
            'Caught result is "NOT true".'
        );

        $contentCaught = $stream->getContents();
        static::assertSame(
            $content,
            $contentCaught,
            "Action \"StreamFactory->createStreamFromResource->getContents\" returned unexpected result.\n".
            "Expected result is \"{$content}\".\n".
            "Caught result is \"{$contentCaught}\"."
        );
    }

    /**
     * Data provider: resources with full params.
     */
    public function dataProviderResourcesWithValues(): array
    {
        $result = [];

        foreach ($this->generateResources(AccessModeType::READABLE_ONLY) as $resource) {
            $result[]   = [$resource, '', false];
        }
        foreach ($this->generateResources(AccessModeType::READABLE_AND_WRITABLE) as $resource) {
            $content    = (new TextGenerator())->generate();
            fwrite($resource, $content);
            rewind($resource);
            $result[]   = [$resource, $content, true];
        }

        return $result;
    }
}
