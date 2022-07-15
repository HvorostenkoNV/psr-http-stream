<?php

declare(strict_types=1);

namespace HNV\Http\Stream;

use HNV\Http\Helper\Collection\Resource\{
    AccessMode,
    AccessModeType,
};
use HNV\Http\Helper\Normalizer\{
    NormalizingException,
    Resource\AccessMode as AccessModeNormalizer,
};
use InvalidArgumentException;
use Psr\Http\{
    Message\StreamFactoryInterface,
    Message\StreamInterface,
};
use RuntimeException;

use function file_exists;
use function fopen;
use function strlen;

class StreamFactory implements StreamFactoryInterface
{
    /**
     * {@inheritDoc}
     */
    public function createStream(string $content = ''): StreamInterface
    {
        $mode       = AccessMode::get(AccessModeType::READABLE_AND_WRITABLE)[0];
        $resource   = fopen('php://temp', $mode->value);
        $stream     = $this->createStreamFromResource($resource);

        if (strlen($content) > 0) {
            try {
                $stream->write($content);
                $stream->rewind();
            } catch (RuntimeException) {
            }
        }

        return $stream;
    }

    /**
     * {@inheritDoc}
     */
    public function createStreamFromFile(string $filename, string $mode = 'r'): StreamInterface
    {
        if (!file_exists($filename)) {
            throw new RuntimeException("file {$filename} is not exist");
        }

        try {
            $modeNormalized = AccessModeNormalizer::normalize($mode);
            $resource       = fopen($filename, $modeNormalized->value);
        } catch (NormalizingException $exception) {
            throw new InvalidArgumentException("mode {$mode} is invalid", 0, $exception);
        }

        if ($resource === false) {
            throw new RuntimeException("file {$filename} cannot be opened");
        }

        return $this->createStreamFromResource($resource);
    }

    /**
     * {@inheritDoc}
     */
    public function createStreamFromResource($resource): StreamInterface
    {
        $stream = new Stream($resource);

        try {
            $stream->rewind();
        } catch (RuntimeException) {
        }

        return $stream;
    }
}
