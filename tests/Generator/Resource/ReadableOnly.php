<?php
declare(strict_types=1);

namespace HNV\Http\StreamTests\Generator\Resource;

use HNV\Http\Helper\Generator\GeneratorInterface;
use HNV\Http\Stream\Collection\ResourceAccessMode\ReadableOnly as AccessModeReadableOnly;
/** ***********************************************************************************************
 * Readable only resources set generator.
 *
 * @package HNV\Psr\Http\Tests\Stream
 * @author  Hvorostenko
 *************************************************************************************************/
class ReadableOnly extends AbstractResource implements GeneratorInterface
{
    /** **********************************************************************
     * @inheritDoc
     ************************************************************************/
    protected function buildAccessModes(): array
    {
        return AccessModeReadableOnly::get();
    }
}