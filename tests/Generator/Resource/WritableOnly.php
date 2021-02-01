<?php
declare(strict_types=1);

namespace HNV\Http\StreamTests\Generator\Resource;

use HNV\Http\Helper\Generator\GeneratorInterface;
use HNV\Http\Stream\Collection\ResourceAccessMode\WritableOnly as AccessModeWritableOnly;
/** ***********************************************************************************************
 * Writable only resources set generator.
 *
 * @package HNV\Psr\Http\Tests\Stream
 * @author  Hvorostenko
 *************************************************************************************************/
class WritableOnly extends AbstractResource implements GeneratorInterface
{
    /** **********************************************************************
     * @inheritDoc
     ************************************************************************/
    protected function buildAccessModes(): array
    {
        return AccessModeWritableOnly::get();
    }
}