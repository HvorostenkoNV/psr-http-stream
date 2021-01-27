<?php
declare(strict_types=1);

namespace HNV\Http\StreamTests\Generator\Resource;

use HNV\Http\Helper\Generator\{
    GeneratorInterface,
    Resource as ResourceGenerator
};
use HNV\Http\Stream\Collection\ResourceAccessMode\{
    Writable    as AccessModeWritable,
    NonSuitable as AccessModeNonSuitable
};

use function array_diff;
/** ***********************************************************************************************
 * Writable resources set generator.
 *
 * @package HNV\Psr\Http\Tests\Stream
 * @author  Hvorostenko
 *************************************************************************************************/
class Writable implements GeneratorInterface
{
    /** **********************************************************************
     * @inheritDoc
     *
     * @return resource[]                   Generated resources set.
     ************************************************************************/
    public function generate(): array
    {
        $accessModes    = array_diff(
            AccessModeWritable::get(),
            AccessModeNonSuitable::get()
        );
        $result         = [];

        foreach ($accessModes as $mode) {
            $result[] = (new ResourceGenerator($mode))->generate();
        }

        return $result;
    }
}