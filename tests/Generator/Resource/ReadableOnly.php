<?php
declare(strict_types=1);

namespace HNV\Http\StreamTests\Generator\Resource;

use HNV\Http\Stream\Collection\ResourceAccessMode\{
    ReadableOnly    as AccessModeReadableOnly,
    NonSuitable     as AccessModeNonSuitable
};
use HNV\Http\StreamTests\Generator\GeneratorInterface;

use function array_diff;
/** ***********************************************************************************************
 * Readable only resources set generator.
 *
 * @package HNV\Psr\Http\Tests\Stream
 * @author  Hvorostenko
 *************************************************************************************************/
class ReadableOnly implements GeneratorInterface
{
    /** **********************************************************************
     * @inheritDoc
     *
     * @return resource[]                   Generated resources set.
     ************************************************************************/
    public function generate(): array
    {
        $accessModes    = array_diff(
            AccessModeReadableOnly::get(),
            AccessModeNonSuitable::get()
        );
        $result         = [];

        foreach ($accessModes as $mode) {
            $result[] = (new Single($mode))->generate();
        }

        return $result;
    }
}