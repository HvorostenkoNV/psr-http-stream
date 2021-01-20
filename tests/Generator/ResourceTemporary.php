<?php
declare(strict_types=1);

namespace HNV\Http\StreamTests\Generator;

use LogicException;
use HNV\Http\Stream\Collection\{
    ResourceAccessMode\ReadableAndWritable as AccessModeReadableAndWritable
};

use function array_shift;
use function is_resource;
use function fopen;
use function fclose;
use function register_shutdown_function;
/** ***********************************************************************************************
 * Temporary resource generator.
 *
 * @package HNV\Psr\Http\Tests\Stream
 * @author  Hvorostenko
 *************************************************************************************************/
class ResourceTemporary implements GeneratorInterface
{
    private string $mode;
    /** **********************************************************************
     * Constructor.
     ************************************************************************/
    public function __construct()
    {
        $mode       = AccessModeReadableAndWritable::get();
        $this->mode = array_shift($mode);
    }
    /** **********************************************************************
     * @inheritDoc
     *
     * @return resource                     Generated resource.
     ************************************************************************/
    public function generate(): mixed
    {
        $file       = (new File())->generate();
        $resource   = fopen($file, $this->mode);

        if ($resource === false) {
            throw new LogicException(
                "resource creating failed, access mode is \"{$this->mode}\""
            );
        }

        register_shutdown_function(function() use ($resource) {
            if (is_resource($resource)) {
                fclose($resource);
            }
        });

        return $resource;
    }
}