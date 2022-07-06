<?php

declare(strict_types=1);

namespace HNV\Http\StreamTests\Factory;

use HNV\Http\Helper\Collection\Resource\{
    AccessMode,
    AccessModeType,
};
use HNV\Http\Helper\Generator\{
    File    as FileGenerator,
    Text    as TextGenerator,
};
use HNV\Http\Helper\Normalizer\{
    NormalizingException,
    Resource\AccessMode as ResourceAccessModeNormalizer,
};
use HNV\Http\Stream\StreamFactory;
use HNV\Http\StreamTests\AbstractStreamTest;
use InvalidArgumentException;
use RuntimeException;

use function array_map;
use function chr;
use function file_put_contents;
use function in_array;
use function range;

/**
 * PSR-7 StreamFactoryInterface implementation test.
 *
 * Testing building stream from file behavior.
 *
 * @internal
 * @covers StreamFactory
 * @small
 */
class StreamFactoryFromFileTest extends AbstractStreamTest
{
    /**
     * @covers          StreamFactory::createStreamFromFile
     * @dataProvider    dataProviderFilesWithFullParameters
     */
    public function testCreating(
        string $filename,
        string $mode,
        string $content,
        bool $isWritable
    ): void {
        $stream = (new StreamFactory())->createStreamFromFile($filename, $mode);

        static::assertSame(
            0,
            $stream->tell(),
            "Action \"StreamFactory->createStreamFromFile->tell\" returned unexpected result.\n".
            "Expected result is \"0\".\n".
            'Caught result is "NOT 0".'
        );
        static::assertFalse(
            $stream->eof(),
            "Action \"StreamFactory->createStreamFromFile->eof\" returned unexpected result.\n".
            "Expected result is \"false\".\n".
            'Caught result is "NOT false".'
        );
        static::assertTrue(
            $stream->isSeekable(),
            "Action \"StreamFactory->createStreamFromFile->isSeekable\" returned unexpected result.\n".
            "Expected result is \"true\".\n".
            'Caught result is "NOT true".'
        );
        static::assertTrue(
            $stream->isReadable(),
            "Action \"StreamFactory->createStreamFromFile->isReadable\" returned unexpected result.\n".
            "Expected result is \"true\".\n".
            'Caught result is "NOT true".'
        );

        static::assertSame(
            $isWritable,
            $stream->isWritable(),
            "Action \"StreamFactory->createStreamFromFile->isWritable\" returned unexpected result.\n".
            "Expected result is \"{$isWritable}\".\n".
            'Caught result is "NOT the same".'
        );

        $contentCaught = $stream->getContents();
        static::assertSame(
            $content,
            $contentCaught,
            "Action \"StreamFactory->createStreamFromFile->getContents\" returned unexpected result.\n".
            "Expected result is \"{$content}\".\n".
            "Caught result is \"{$contentCaught}\"."
        );
    }

    /**
     * @covers          StreamFactory::createStreamFromFile
     * @dataProvider    dataProviderFilesValidWithOpenModeInvalid
     */
    public function testCreatingThrowsException1(string $filePath, string $mode): void
    {
        $this->expectException(InvalidArgumentException::class);

        (new StreamFactory())->createStreamFromFile($filePath, $mode);

        static::fail(
            "Action \"StreamFactory->createStreamFromFile\" threw no expected exception.\n".
            "Action was called with parameters (file open mode => {$mode}).\n".
            "Expects \"InvalidArgumentException\" exception.\n".
            'Caught no exception.'
        );
    }

    /**
     * @covers          StreamFactory::createStreamFromFile
     * @dataProvider    dataProviderFilesInvalidWithOpenModeValid
     */
    public function testCreatingThrowsException2(string $filename, string $mode): void
    {
        $this->expectException(RuntimeException::class);

        (new StreamFactory())->createStreamFromFile($filename, $mode);

        static::fail(
            "Action \"StreamFactory->createStreamFromFile\" threw no expected exception.\n".
            "Action was called with parameters (filePath => unreachable file).\n".
            "Expects \"RuntimeException\" exception.\n".
            'Caught no exception.'
        );
    }

    /**
     * Data provider: files with full params.
     */
    public function dataProviderFilesWithFullParameters(): array
    {
        $modeReadableOnly           = AccessMode::get(
            AccessModeType::READABLE_ONLY,
            AccessModeType::EXPECT_NO_FILE
        );
        $modeReadableAndWritable    = AccessMode::get(
            AccessModeType::READABLE_AND_WRITABLE,
            AccessModeType::EXPECT_NO_FILE
        );
        $modeRewrite                = AccessMode::get(AccessModeType::FORCE_CLEAR);
        $result                     = [];

        foreach ($modeReadableOnly as $mode) {
            $filePath   = (new FileGenerator())->generate();
            $result[]   = [$filePath, $mode->value, '', false];
        }
        foreach ($modeReadableOnly as $mode) {
            $filePath   = (new FileGenerator())->generate();
            $content    = (new TextGenerator())->generate();
            file_put_contents($filePath, $content);
            $result[]   = [$filePath, $mode->value, $content, false];
        }
        foreach ($modeReadableAndWritable as $mode) {
            if (in_array($mode, $modeRewrite, true)) {
                continue;
            }

            $filePath   = (new FileGenerator())->generate();
            $content    = (new TextGenerator())->generate();
            file_put_contents($filePath, $content);
            $result[]   = [$filePath, $mode->value, $content, true];
        }

        return $result;
    }

    /**
     * Data provider: files with open mode invalid values.
     */
    public function dataProviderFilesValidWithOpenModeInvalid(): array
    {
        $invalidModes   = $this->generateInvalidModes();
        $result         = [];

        foreach ($invalidModes as $mode) {
            $filePath   = (new FileGenerator())->generate();
            $result[]   = [$filePath, $mode];
        }

        return $result;
    }

    /**
     * Data provider: files that can not be opened.
     */
    public function dataProviderFilesInvalidWithOpenModeValid(): array
    {
        $result = [];

        for ($iterator = 1; $iterator <= 5; $iterator++) {
            $result[] = ["incorrectFilePath-{$iterator}", AccessMode::READ_ONLY_POINTER_START->value];
        }

        return $result;
    }

    /**
     * @return string[]
     */
    private function generateInvalidModes(): array
    {
        $result             = [];
        $rangeOfNumbers     = range(0, 9);
        $rangeOfCharacters  = array_map(fn ($number) => chr($number), range(65, 112));

        foreach ($rangeOfNumbers as $number) {
            $result[] = "{$number}";
        }
        foreach ($rangeOfCharacters as $character) {
            try {
                ResourceAccessModeNormalizer::normalize($character);
            } catch (NormalizingException) {
                $result[] = $character;
            }
        }

        return $result;
    }
}
