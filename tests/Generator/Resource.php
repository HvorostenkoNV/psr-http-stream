<?php
declare(strict_types=1);

namespace HNV\Http\StreamTests\Generator;

use LogicException;

use function is_resource;
use function fopen;
use function fclose;
use function register_shutdown_function;
/** ***********************************************************************************************
 * Resource generator.
 *
 * @package HNV\Psr\Http\Tests\Stream
 * @author  Hvorostenko
 *************************************************************************************************/
class Resource implements GeneratorInterface
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