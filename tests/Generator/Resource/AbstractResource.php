<?php
declare(strict_types=1);

namespace HNV\Http\StreamTests\Generator\Resource;

use HNV\Http\Helper\Generator\{
    GeneratorInterface,
    File        as FileGenerator,
    Resource    as ResourceGenerator
};
use HNV\Http\Stream\Collection\ResourceAccessMode\NonSuitable as AccessModeNonSuitable;

use function array_diff;
/** ***********************************************************************************************
 * Abstract resources set generator.
 *
 * @package HNV\Psr\Http\Tests\Stream
 * @author  Hvorostenko
 *************************************************************************************************/
abstract class AbstractResource implements GeneratorInterface
{
    private array $accessModes;
    /** **********************************************************************
     * Constructor.
     ************************************************************************/
    public function __construct()
    {
        $this->accessModes = $this->buildAccessModes();
    }
    /** **********************************************************************
     * @inheritDoc
     *
     * @return resource[]                   Generated resources set.
     ************************************************************************/
    public function generate(): array
    {
        $accessModes    = array_diff(
            $this->accessModes,
            AccessModeNonSuitable::get()
        );
        $result         = [];

        foreach ($accessModes as $mode) {
            $file       = (new FileGenerator())->generate();
            $result[]   = (new ResourceGenerator($file, $mode))->generate();
        }

        return $result;
    }
    /** **********************************************************************
     * Build need access modes
     *
     * @return string[]                     Access modes set.
     ************************************************************************/
    abstract protected function buildAccessModes(): array;
}