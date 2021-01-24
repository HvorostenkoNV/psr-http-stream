<?php
declare(strict_types=1);

namespace HNV\Http\StreamTests\Generator\Resource;

use LogicException;
use HNV\Http\StreamTests\Generator\{
    GeneratorInterface,
    File as FileGenerator
};

use function is_resource;
use function fopen;
use function fclose;
use function register_shutdown_function;
/** ***********************************************************************************************
 * Single resource generator.
 *
 * @package HNV\Psr\Http\Tests\Stream
 * @author  Hvorostenko
 *************************************************************************************************/
class Single implements GeneratorInterface
{
    /** **********************************************************************
     * Constructor.
     *
     * @param string $mode                  Resource access mode.
     ************************************************************************/
    public function __construct(public string $mode) {}
    /** **********************************************************************
     * @inheritDoc
     *
     * @return resource                     Generated resource.
     ************************************************************************/
    public function generate(): mixed
    {
        $file       = (new FileGenerator())->generate();
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