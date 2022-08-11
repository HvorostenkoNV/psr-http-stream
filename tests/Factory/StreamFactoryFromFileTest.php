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
use HNV\Http\StreamTests\AbstractStreamTestCase;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes;
use RuntimeException;

use function array_map;
use function chr;
use function file_put_contents;
use function in_array;
use function range;

/**
 * @internal
 */
#[Attributes\CoversClass(StreamFactory::class)]
#[Attributes\Small]
class StreamFactoryFromFileTest extends AbstractStreamTestCase
{
    #[Attributes\Test]
    #[Attributes\DataProvider('dataProviderFilesWithFullParameters')]
    public function create(
        string $filename,
        string $mode,
        string $content,
        bool $isWritable
    ): void {
        $stream = (new StreamFactory())->createStreamFromFile($filename, $mode);

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

    #[Attributes\Test]
    #[Attributes\DataProvider('dataProviderFilesValidWithOpenModeInvalid')]
    public function throwsException1(string $filePath, string $mode): void
    {
        $this->expectException(InvalidArgumentException::class);

        (new StreamFactory())->createStreamFromFile($filePath, $mode);

        static::fail("Expects exception with file open mode [{$mode}]");
    }

    #[Attributes\Test]
    #[Attributes\DataProvider('dataProviderFilesInvalidWithOpenModeValid')]
    public function throwsException2(string $filename, string $mode): void
    {
        $this->expectException(RuntimeException::class);

        (new StreamFactory())->createStreamFromFile($filename, $mode);

        static::fail('Expects exception with unreachable file');
    }

    public function dataProviderFilesWithFullParameters(): iterable
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

        foreach ($modeReadableOnly as $mode) {
            $filePath = (new FileGenerator())->generate();

            yield [$filePath, $mode->value, '', false];
        }
        foreach ($modeReadableOnly as $mode) {
            $filePath   = (new FileGenerator())->generate();
            $content    = (new TextGenerator())->generate();
            file_put_contents($filePath, $content);

            yield [$filePath, $mode->value, $content, false];
        }
        foreach ($modeReadableAndWritable as $mode) {
            if (in_array($mode, $modeRewrite, true)) {
                continue;
            }

            $filePath   = (new FileGenerator())->generate();
            $content    = (new TextGenerator())->generate();
            file_put_contents($filePath, $content);

            yield [$filePath, $mode->value, $content, true];
        }
    }

    public function dataProviderFilesValidWithOpenModeInvalid(): iterable
    {
        foreach ($this->generateInvalidModes() as $mode) {
            $filePath = (new FileGenerator())->generate();

            yield [$filePath, $mode];
        }
    }

    public function dataProviderFilesInvalidWithOpenModeValid(): iterable
    {
        for ($iterator = 1; $iterator <= 5; $iterator++) {
            yield ["incorrectFilePath-{$iterator}", AccessMode::READ_ONLY_POINTER_START->value];
        }
    }

    /**
     * @return string[]
     */
    private function generateInvalidModes(): iterable
    {
        $rangeOfNumbers     = range(0, 9);
        $rangeOfCharacters  = array_map(fn ($number) => chr($number), range(65, 112));

        foreach ($rangeOfNumbers as $number) {
            yield "{$number}";
        }

        foreach ($rangeOfCharacters as $character) {
            try {
                ResourceAccessModeNormalizer::normalize($character);
            } catch (NormalizingException) {
                yield $character;
            }
        }
    }
}
