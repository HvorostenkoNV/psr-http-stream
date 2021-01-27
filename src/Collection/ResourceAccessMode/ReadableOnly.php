<?php
declare(strict_types=1);

namespace HNV\Http\Stream\Collection\ResourceAccessMode;

use HNV\Http\Helper\Collection\CollectionInterface;

use function array_diff;
/** ***********************************************************************************************
 * Resource access mode readable only collection.
 *
 * @package HNV\Psr\Http\Stream
 * @author  Hvorostenko
 *************************************************************************************************/
class ReadableOnly implements CollectionInterface
{
    /** **********************************************************************
     * @inheritDoc
     ************************************************************************/
    public static function get(): array
    {
        return array_diff(Readable::get(), Writable::get());
    }
}