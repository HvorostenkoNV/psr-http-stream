<?php
declare(strict_types=1);

namespace HNV\Http\StreamTests\Factory;

use Throwable;
use PHPUnit\Framework\TestCase;
use HNV\Http\Helper\Generator\Text as TextGenerator;
use HNV\Http\StreamTests\Generator\{
    Resource\ReadableOnly           as ResourceGeneratorReadableOnly,
    Resource\ReadableAndWritable    as ResourceGeneratorReadableAndWritable
};
use HNV\Http\Stream\StreamFactory;

use function fwrite;
use function rewind;
/** ***********************************************************************************************
 * PSR-7 StreamFactoryInterface implementation test.
 *
 * Testing building stream from resource behavior.
 *
 * @package HNV\Psr\Http\Tests\Stream
 * @author  Hvorostenko
 *************************************************************************************************/
class StreamFactoryFromResourceTest extends TestCase
{
    /** **********************************************************************
     * Test "StreamFactory::createStreamFromResource" creates a new stream with expected condition.
     *
     * @covers          StreamFactory::createStreamFromResource
     * @dataProvider    dataProviderResourcesWithValues
     *
     * @param           resource    $resource           Resource.
     * @param           string      $content            Resource content.
     * @param           bool        $isWritable         Resource is writable.
     *
     * @return          void
     * @throws          Throwable
     ************************************************************************/
    public function testCreateStreamFromResource(
        $resource,
        string  $content,
        bool    $isWritable
    ): void {
        $stream = (new StreamFactory())->createStreamFromResource($resource);

        self::assertEquals(
            0,
            $stream->tell(),
            "Action \"StreamFactory->createStreamFromResource->tell\" returned unexpected result.\n".
            "Expected result is \"0\".\n".
            "Caught result is \"NOT 0\"."
        );
        self::assertFalse(
            $stream->eof(),
            "Action \"StreamFactory->createStreamFromResource->eof\" returned unexpected result.\n".
            "Expected result is \"false\".\n".
            "Caught result is \"NOT false\"."
        );
        self::assertTrue(
            $stream->isSeekable(),
            "Action \"StreamFactory->createStreamFromResource->isSeekable\" returned unexpected result.\n".
            "Expected result is \"true\".\n".
            "Caught result is \"NOT true\"."
        );
        self::assertTrue(
            $stream->isReadable(),
            "Action \"StreamFactory->createStreamFromResource->isReadable\" returned unexpected result.\n".
            "Expected result is \"true\".\n".
            "Caught result is \"NOT true\"."
        );
        self::assertEquals(
            $isWritable,
            $stream->isWritable(),
            "Action \"StreamFactory->createStreamFromResource->isWritable\" returned unexpected result.\n".
            "Expected result is \"true\".\n".
            "Caught result is \"NOT true\"."
        );

        $contentCaught = $stream->getContents();
        self::assertEquals(
            $content,
            $contentCaught,
            "Action \"StreamFactory->createStreamFromResource->getContents\" returned unexpected result.\n".
            "Expected result is \"$content\".\n".
            "Caught result is \"$contentCaught\"."
        );
    }
    /** **********************************************************************
     * Data provider: resources with full params.
     *
     * @return  array                                   Data.
     ************************************************************************/
    public function dataProviderResourcesWithValues(): array
    {
        $result = [];

        foreach ((new ResourceGeneratorReadableOnly())->generate() as $resource) {
            $result[]   = [$resource, '', false];
        }
        foreach ((new ResourceGeneratorReadableAndWritable())->generate() as $resource) {
            $content    = (new TextGenerator())->generate();
            fwrite($resource, $content);
            rewind($resource);
            $result[]   = [$resource, $content, true];
        }

        return $result;
    }
}