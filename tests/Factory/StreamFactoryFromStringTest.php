<?php
declare(strict_types=1);

namespace HNV\Http\StreamTests\Factory;

use Throwable;
use PHPUnit\Framework\TestCase;
use HNV\Http\StreamTests\Generator\Text as TextGenerator;
use HNV\Http\Stream\StreamFactory;
/** ***********************************************************************************************
 * PSR-7 StreamFactoryInterface implementation test.
 *
 * Testing building stream from string behavior.
 *
 * @package HNV\Psr\Http\Tests\Stream
 * @author  Hvorostenko
 *************************************************************************************************/
class StreamFactoryFromStringTest extends TestCase
{
    /** **********************************************************************
     * Test "StreamFactory::createStream" creates a new stream with expected condition.
     *
     * @covers          StreamFactory::createStream
     * @dataProvider    dataProviderValues
     *
     * @param           string $content                 Content.
     *
     * @return          void
     * @throws          Throwable
     ************************************************************************/
    public function testCreating(string $content): void
    {
        $stream = (new StreamFactory())->createStream($content);

        self::assertEquals(
            0,
            $stream->tell(),
            "Action \"StreamFactory->createStream->tell\" returned unexpected result.\n".
            "Expected result is \"0\".\n".
            "Caught result is \"NOT 0\"."
        );
        self::assertFalse(
            $stream->eof(),
            "Action \"StreamFactory->createStream->eof\" returned unexpected result.\n".
            "Expected result is \"false\".\n".
            "Caught result is \"NOT false\"."
        );
        self::assertTrue(
            $stream->isSeekable(),
            "Action \"StreamFactory->createStream->isSeekable\" returned unexpected result.\n".
            "Expected result is \"true\".\n".
            "Caught result is \"NOT true\"."
        );
        self::assertTrue(
            $stream->isReadable(),
            "Action \"StreamFactory->createStream->isReadable\" returned unexpected result.\n".
            "Expected result is \"true\".\n".
            "Caught result is \"NOT true\"."
        );
        self::assertTrue(
            $stream->isWritable(),
            "Action \"StreamFactory->createStream->isWritable\" returned unexpected result.\n".
            "Expected result is \"true\".\n".
            "Caught result is \"NOT true\"."
        );

        $contentCaught = $stream->getContents();
        self::assertEquals(
            $content,
            $contentCaught,
            "Action \"StreamFactory->createStream->getContents\" returned unexpected result.\n".
            "Expected result is \"$content\".\n".
            "Caught result is \"$contentCaught\"."
        );
    }
    /** **********************************************************************
     * Data provider: random text content.
     *
     * @return  array                                   Data.
     ************************************************************************/
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