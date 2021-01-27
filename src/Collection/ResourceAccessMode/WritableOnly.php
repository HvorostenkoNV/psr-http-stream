<?php
declare(strict_types=1);

namespace HNV\Http\Stream\Collection\ResourceAccessMode;

use HNV\Http\Helper\Collection\CollectionInterface;

use function array_diff;
/** ***********************************************************************************************
 * Resource access mode writable only collection.
 *
 * @package HNV\Psr\Http\Stream
 * @author  Hvorostenko
 *************************************************************************************************/
class WritableOnly implements CollectionInterface
{
    /** **********************************************************************
     * @inheritDoc
     ************************************************************************/
    public static function get(): array
    {
        return array_diff(Writable::get(), Readable::get());
    }
}