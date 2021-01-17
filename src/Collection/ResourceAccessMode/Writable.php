<?php
declare(strict_types=1);

namespace HNV\Http\Stream\Collection\ResourceAccessMode;

use HNV\Http\Stream\Collection\CollectionInterface;
/** ***********************************************************************************************
 * Resource access mode writable values collection.
 *
 * @package HNV\Psr\Http\Stream
 * @author  Hvorostenko
 *************************************************************************************************/
class Writable implements CollectionInterface
{
    /** **********************************************************************
     * @inheritDoc
     ************************************************************************/
    public static function get(): array
    {
        return [
            'r+',   'rb+',
            'w',    'wb',
            'w+',   'wb+',
            'a',    'ab',
            'a+',   'ab+',
            'x',    'xb',
            'x+',   'xb+',
            'c',    'cb',
            'c+',   'cb+',
        ];
    }
}