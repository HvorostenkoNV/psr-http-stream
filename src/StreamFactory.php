<?php
declare(strict_types=1);

namespace HNV\Http\Stream;

use InvalidArgumentException;
use RuntimeException;
use Psr\Http\{
    Message\StreamInterface,
    Message\StreamFactoryInterface
};
use HNV\Http\Stream\Normalizer\{
    NormalizingException,
    ResourceAccessMode as ResourceAccessModeNormalizer
};
use HNV\Http\Stream\Collection\ResourceAccessMode\{
    ReadableAndWritable as ResourceAccessModeReadableAndWritable
};

use function strlen;
use function fopen;
use function file_exists;
/** ***********************************************************************************************
 * PSR-7 StreamFactoryInterface implementation.
 *
 * @package HNV\Psr\Http\Stream
 * @author  Hvorostenko
 *************************************************************************************************/
class StreamFactory implements StreamFactoryInterface
{
    /** **********************************************************************
     * @inheritDoc
     ************************************************************************/
    public function createStream(string $content = ''): StreamInterface
    {
        $mode       = ResourceAccessModeReadableAndWritable::get()[0];
        $resource   = fopen('php://temp', $mode);
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
    /** **********************************************************************
     * @inheritDoc
     ************************************************************************/
    public function createStreamFromFile(string $filename, string $mode = 'r'): StreamInterface
    {
        if (!file_exists($filename)) {
            throw new RuntimeException("file $filename is not exist");
        }

        try {
            $modeNormalized = ResourceAccessModeNormalizer::normalize($mode);
            $resource       = fopen($filename, $modeNormalized);
        } catch (NormalizingException $exception) {
            throw new InvalidArgumentException("mode $mode is invalid", 0, $exception);
        }

        if ($resource === false) {
            throw new RuntimeException("file $filename cannot be opened");
        }

        return $this->createStreamFromResource($resource);
    }
    /** **********************************************************************
     * @inheritDoc
     ************************************************************************/
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