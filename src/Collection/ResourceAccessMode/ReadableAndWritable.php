<?php
declare(strict_types=1);

namespace HNV\Http\Stream\Collection\ResourceAccessMode;

use HNV\Http\Helper\Collection\CollectionInterface;

use function array_values;
use function array_intersect;
/** ***********************************************************************************************
 * Resource access mode readable and writable values collection.
 *
 * @package HNV\Psr\Http\Stream
 * @author  Hvorostenko
 *************************************************************************************************/
class ReadableAndWritable implements CollectionInterface
{
    /** **********************************************************************
     * @inheritDoc
     ************************************************************************/
    public static function get(): array
    {
        $matches = array_intersect(Readable::get(), Writable::get());

        return array_values($matches);
    }
}