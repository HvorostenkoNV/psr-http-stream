<?php
declare(strict_types=1);

namespace HNV\Http\StreamTests\Factory;

use Throwable;
use InvalidArgumentException;
use RuntimeException;
use PHPUnit\Framework\TestCase;
use HNV\Http\StreamTests\Generator\{
    File    as FileGenerator,
    Text    as TextGenerator
};
use HNV\Http\Stream\Collection\ResourceAccessMode\{
    ReadableOnly        as ResourceAccessModeReadableOnly,
    ReadableAndWritable as ResourceAccessModeReadableAndWritable,
    Rewrite             as ResourceAccessModeRewrite,
    NonSuitable         as ResourceAccessModeNonSuitable,
    All                 as ResourceAccessModeAll
};
use HNV\Http\Stream\StreamFactory;

use function chr;
use function range;
use function in_array;
use function array_filter;
use function array_diff;
use function array_map;
use function file_put_contents;
/** ***********************************************************************************************
 * PSR-7 StreamFactoryInterface implementation test.
 *
 * Testing building stream from file behavior.
 *
 * @package HNV\Psr\Http\Tests\Stream
 * @author  Hvorostenko
 *************************************************************************************************/
class StreamFactoryFromFileTest extends TestCase
{
    /** **********************************************************************
     * Test "StreamFactory::createStreamFromFile" creates a new stream with expected condition.
     *
     * @covers              StreamFactory::createStreamFromFile
     * @dataProvider        dataProviderFilesWithFullParameters
     *
     * @param               string  $filename           File path.
     * @param               string  $mode               Open mode.
     * @param               string  $content            File content.
     * @param               bool    $isWritable         File mode is writable.
     *
     * @return              void
     * @throws              Throwable
     ************************************************************************/
    public function testCreating(
        string  $filename,
        string  $mode,
        string  $content,
        bool    $isWritable
    ): void {
        $stream = (new StreamFactory())->createStreamFromFile($filename, $mode);

        self::assertEquals(
            0,
            $stream->tell(),
            "Action \"StreamFactory->createStreamFromFile->tell\" returned unexpected result.\n".
            "Expected result is \"0\".\n".
            "Caught result is \"NOT 0\"."
        );
        self::assertFalse(
            $stream->eof(),
            "Action \"StreamFactory->createStreamFromFile->eof\" returned unexpected result.\n".
            "Expected result is \"false\".\n".
            "Caught result is \"NOT false\"."
        );
        self::assertTrue(
            $stream->isSeekable(),
            "Action \"StreamFactory->createStreamFromFile->isSeekable\" returned unexpected result.\n".
            "Expected result is \"true\".\n".
            "Caught result is \"NOT true\"."
        );
        self::assertTrue(
            $stream->isReadable(),
            "Action \"StreamFactory->createStreamFromFile->isReadable\" returned unexpected result.\n".
            "Expected result is \"true\".\n".
            "Caught result is \"NOT true\"."
        );

        self::assertEquals(
            $isWritable,
            $stream->isWritable(),
            "Action \"StreamFactory->createStreamFromFile->isWritable\" returned unexpected result.\n".
            "Expected result is \"$isWritable\".\n".
            "Caught result is \"NOT the same\"."
        );

        $contentCaught = $stream->getContents();
        self::assertEquals(
            $content,
            $contentCaught,
            "Action \"StreamFactory->createStreamFromFile->getContents\" returned unexpected result.\n".
            "Expected result is \"$content\".\n".
            "Caught result is \"$contentCaught\"."
        );
    }
    /** **********************************************************************
     * Test "StreamFactory::createStreamFromFile" throws exception with invalid
     * file open mode value.
     *
     * @covers          StreamFactory::createStreamFromFile
     * @dataProvider    dataProviderFilesValidWithOpenModeInvalid
     *
     * @param           string  $filePath               File path valid.
     * @param           string  $mode                   Open mode invalid.
     *
     * @return          void
     * @throws          Throwable
     ************************************************************************/
    public function testCreatingThrowsException1(string $filePath, string $mode): void
    {
        $this->expectException(InvalidArgumentException::class);

        (new StreamFactory())->createStreamFromFile($filePath, $mode);

        self::fail(
            "Action \"StreamFactory->createStreamFromFile\" threw no expected exception.\n".
            "Action was called with parameters (file open mode => $mode).\n".
            "Expects \"InvalidArgumentException\" exception.\n".
            'Caught no exception.'
        );
    }
    /** **********************************************************************
     * Test "StreamFactory::createStreamFromFile" throws exception with file
     * that can not be opened.
     *
     * @covers          StreamFactory::createStreamFromFile
     * @dataProvider    dataProviderFilesInvalidWithOpenModeValid
     *
     * @param           string  $filename               File path.
     * @param           string  $mode                   Mode.
     *
     * @return          void
     * @throws          Throwable
     ************************************************************************/
    public function testCreatingThrowsException2(string $filename, string $mode)
    {
        $this->expectException(RuntimeException::class);

        (new StreamFactory())->createStreamFromFile($filename, $mode);

        self::fail(
            "Action \"StreamFactory->createStreamFromFile\" threw no expected exception.\n".
            "Action was called with parameters (filePath => unreachable file).\n".
            "Expects \"RuntimeException\" exception.\n".
            'Caught no exception.'
        );
    }
    /** **********************************************************************
     * Data provider: files with full params.
     *
     * @return  array                                   Data.
     ************************************************************************/
    public function dataProviderFilesWithFullParameters(): array
    {
        $modeReadableOnly           = array_diff(
            ResourceAccessModeReadableOnly::get(),
            ResourceAccessModeNonSuitable::get()
        );
        $modeReadableAndWritable    = array_diff(
            ResourceAccessModeReadableAndWritable::get(),
            ResourceAccessModeNonSuitable::get(),
            ResourceAccessModeRewrite::get()
        );
        $result                     = [];

        foreach ($modeReadableOnly as $mode) {
            $filePath   = (new FileGenerator())->generate();
            $result[]   = [$filePath, $mode, '', false];
        }
        foreach ($modeReadableOnly as $mode) {
            $filePath   = (new FileGenerator())->generate();
            $content    = (new TextGenerator())->generate();
            file_put_contents($filePath, $content);
            $result[]   = [$filePath, $mode, $content, false];
        }
        foreach ($modeReadableAndWritable as $mode) {
            $filePath   = (new FileGenerator())->generate();
            $content    = (new TextGenerator())->generate();
            file_put_contents($filePath, $content);
            $result[]   = [$filePath, $mode, $content, true];
        }

        return $result;
    }
    /** **********************************************************************
     * Data provider: files with open mode invalid values.
     *
     * @return  array                                   Data.
     ************************************************************************/
    public function dataProviderFilesValidWithOpenModeInvalid(): array
    {
        $rangeOfNumbers     = range(0, 9);
        $rangeOfCharacters  = array_map(function($number) {
            return chr($number);
        }, range(65, 112));
        $validModes         = ResourceAccessModeAll::get();
        $invalidModes       = array_filter(
            $rangeOfCharacters,
            function(string $character) use ($validModes): bool {
                return !in_array($character, $validModes);
            }
        );
        $result             = [];

        foreach ($rangeOfNumbers as $number) {
            $filePath   = (new FileGenerator())->generate();
            $result[]   = [$filePath, "$number"];
        }
        foreach ($invalidModes as $character) {
            $filePath   = (new FileGenerator())->generate();
            $result[]   = [$filePath, $character];
        }

        return $result;
    }
    /** **********************************************************************
     * Data provider: files that can not be opened.
     *
     * @return  array                                   Data.
     ************************************************************************/
    public function dataProviderFilesInvalidWithOpenModeValid(): array
    {
        $modeReadableOnly   = array_diff(
            ResourceAccessModeReadableOnly::get(),
            ResourceAccessModeNonSuitable::get()
        );
        $mode               = $modeReadableOnly[0];
        $result             = [];

        for ($iterator = 1; $iterator <= 5; $iterator++) {
            $result[] = ["incorrectFilePath-$iterator", $mode];
        }

        return $result;
    }
}