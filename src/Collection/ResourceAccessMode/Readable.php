<?php
declare(strict_types=1);

namespace HNV\Http\Stream\Collection\ResourceAccessMode;

use HNV\Http\Stream\Collection\CollectionInterface;
/** ***********************************************************************************************
 * Resource access mode readable values collection.
 *
 * @package HNV\Psr\Http\Stream
 * @author  Hvorostenko
 *************************************************************************************************/
class Readable implements CollectionInterface
{
    /** **********************************************************************
     * @inheritDoc
     ************************************************************************/
    public static function get(): array
    {
        return [
            'r',    'rb',
            'r+',   'rb+',
            'w+',   'wb+',
            'a+',   'ab+',
            'x+',   'xb+',
            'c+',   'cb+',
        ];
    }
}