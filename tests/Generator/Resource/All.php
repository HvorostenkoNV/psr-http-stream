<?php
declare(strict_types=1);

namespace HNV\Http\StreamTests\Generator\Resource;

use HNV\Http\Helper\Generator\GeneratorInterface;
use HNV\Http\Stream\Collection\ResourceAccessMode\All as AccessModeAll;
/** ***********************************************************************************************
 * Resources (all mode types) set generator.
 *
 * @package HNV\Psr\Http\Tests\Stream
 * @author  Hvorostenko
 *************************************************************************************************/
class All extends AbstractResource implements GeneratorInterface
{
    /** **********************************************************************
     * @inheritDoc
     ************************************************************************/
    protected function buildAccessModes(): array
    {
        return AccessModeAll::get();
    }
}