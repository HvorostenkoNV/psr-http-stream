<?php
declare(strict_types=1);

namespace HNV\Http\StreamTests\Generator\Resource;

use HNV\Http\Helper\Generator\GeneratorInterface;
use HNV\Http\Stream\Collection\ResourceAccessMode\Writable as AccessModeWritable;
/** ***********************************************************************************************
 * Writable resources set generator.
 *
 * @package HNV\Psr\Http\Tests\Stream
 * @author  Hvorostenko
 *************************************************************************************************/
class Writable extends AbstractResource implements GeneratorInterface
{
    /** **********************************************************************
     * @inheritDoc
     ************************************************************************/
    protected function buildAccessModes(): array
    {
        return AccessModeWritable::get();
    }
}