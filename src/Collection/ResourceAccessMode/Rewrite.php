<?php
declare(strict_types=1);

namespace HNV\Http\Stream\Collection\ResourceAccessMode;

use HNV\Http\Stream\Collection\CollectionInterface;
/** ***********************************************************************************************
 * Resource access mode rewrite collection.
 *
 * @package HNV\Psr\Http\Stream
 * @author  Hvorostenko
 *************************************************************************************************/
class Rewrite implements CollectionInterface
{
    /** **********************************************************************
     * @inheritDoc
     ************************************************************************/
    public static function get(): array
    {
        return [
            'w',    'wb',
            'w+',   'wb+',
        ];
    }
}