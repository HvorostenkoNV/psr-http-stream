<?php
declare(strict_types=1);

namespace HNV\Http\Stream;

use RuntimeException;
use Psr\Http\Message\StreamInterface;
use HNV\Http\Helper\Normalizer\NormalizingException;
use HNV\Http\Stream\Normalizer\ResourceAccessMode as ResourceAccessModeNormalizer;
use HNV\Http\Stream\Collection\ResourceAccessMode\{
    Readable    as ResourceAccessModeReadable,
    Writable    as ResourceAccessModeWritable
};

use function in_array;
/** ***********************************************************************************************
 * PSR-7 StreamInterface abstract implementation.
 *
 * @package HNV\Psr\Http\Stream
 * @author  Hvorostenko
 *************************************************************************************************/
abstract class AbstractStream implements StreamInterface
{
    /** **********************************************************************
     * @inheritDoc
     ************************************************************************/
    public function eof(): bool
    {
        return $this->getMetadata('eof') === true;
    }
    /** **********************************************************************
     * @inheritDoc
     ************************************************************************/
    public function isSeekable(): bool
    {
        return $this->getMetadata('seekable') === true;
    }
    /** **********************************************************************
     * @inheritDoc
     ************************************************************************/
    public function rewind(): void
    {
        try {
            $this->seek(0);
        } catch (RuntimeException $exception) {
            throw $exception;
        }
    }
    /** **********************************************************************
     * @inheritDoc
     ************************************************************************/
    public function isReadable(): bool
    {
        try {
            $accessMode             = (string) $this->getMetadata('mode');
            $accessModeNormalized   = ResourceAccessModeNormalizer::normalize($accessMode);
            $availableValues        = ResourceAccessModeReadable::get();

            return in_array($accessModeNormalized, $availableValues);
        } catch (NormalizingException) {
            return false;
        }
    }
    /** **********************************************************************
     * @inheritDoc
     ************************************************************************/
    public function isWritable(): bool
    {
        try {
            $accessMode             = (string) $this->getMetadata('mode');
            $accessModeNormalized   = ResourceAccessModeNormalizer::normalize($accessMode);
            $availableValues        = ResourceAccessModeWritable::get();

            return in_array($accessModeNormalized, $availableValues);
        } catch (NormalizingException) {
            return false;
        }
    }
    /** **********************************************************************
     * @inheritDoc
     ************************************************************************/
    public function __toString(): string
    {
        try {
            $this->rewind();

            return $this->getContents();
        } catch (RuntimeException) {
            return '';
        }
    }
    /** **********************************************************************
     * Destructor.
     ************************************************************************/
    public function __destruct()
    {
        $this->close();
    }
}