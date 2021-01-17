<?php
declare(strict_types=1);

namespace HNV\Http\Stream\Collection\ResourceAccessMode;

use HNV\Http\Stream\Collection\CollectionInterface;

use function array_merge;
use function array_unique;
use function array_values;
/** ***********************************************************************************************
 * Resource access mode all values collection.
 *
 * @package HNV\Psr\Http\Stream
 * @author  Hvorostenko
 *************************************************************************************************/
class All implements CollectionInterface
{
    /** **********************************************************************
     * @inheritDoc
     ************************************************************************/
    public static function get(): array
    {
        $allValues      = array_merge(Readable::get(), Writable::get());
        $uniqueValues   = array_unique($allValues);

        return array_values($uniqueValues);
    }
}