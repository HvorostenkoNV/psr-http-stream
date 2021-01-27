<?php
declare(strict_types=1);

namespace HNV\Http\Stream\Collection\ResourceAccessMode;

use HNV\Http\Helper\Collection\CollectionInterface;
/** ***********************************************************************************************
 * Resource access mode non suitable collection.
 *
 * @package HNV\Psr\Http\Stream
 * @author  Hvorostenko
 *************************************************************************************************/
class NonSuitable implements CollectionInterface
{
    /** **********************************************************************
     * @inheritDoc
     ************************************************************************/
    public static function get(): array
    {
        return [
            'x',    'xb',
            'x+',   'xb+',
        ];
    }
}