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
use Psr\Http\Message\StreamInterface;
use RuntimeException;

use function array_map;
use function in_array;

abstract class AbstractStream implements StreamInterface
{
    public function __destruct()
    {
        $this->close();
    }

    /**
     * {@inheritDoc}
     */
    public function __toString(): string
    {
        try {
            $this->rewind();

            return $this->getContents();
        } catch (RuntimeException) {
            return '';
        }
    }

    /**
     * {@inheritDoc}
     */
    public function eof(): bool
    {
        return $this->getMetadata('eof') === true;
    }

    /**
     * {@inheritDoc}
     */
    public function isSeekable(): bool
    {
        return $this->getMetadata('seekable') === true;
    }

    /**
     * {@inheritDoc}
     */
    public function rewind(): void
    {
        $this->seek(0);
    }

    /**
     * {@inheritDoc}
     */
    public function isReadable(): bool
    {
        return $this->checkAccessModeIs(AccessModeType::READABLE);
    }

    /**
     * {@inheritDoc}
     */
    public function isWritable(): bool
    {
        return $this->checkAccessModeIs(AccessModeType::WRITABLE);
    }

    private function checkAccessModeIs(AccessModeType $modeType): bool
    {
        try {
            $accessMode             = (string) $this->getMetadata('mode');
            $accessModeNormalized   = AccessModeNormalizer::normalize($accessMode);
            $availableModes         = AccessMode::get($modeType);
            $availableValues        = array_map(
                fn (AccessMode $mode): string => $mode->value,
                $availableModes
            );

            return in_array($accessModeNormalized->value, $availableValues, true);
        } catch (NormalizingException) {
            return false;
        }
    }
}
