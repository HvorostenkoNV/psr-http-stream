<?php
declare(strict_types=1);

namespace HNV\Http\StreamTests\Generator;
/** ***********************************************************************************************
 * Generator interface.
 *
 * @package HNV\Psr\Http\Tests\Stream
 * @author  Hvorostenko
 *************************************************************************************************/
interface GeneratorInterface
{
    /** **********************************************************************
     * Generate data.
     *
     * @return  mixed                       Generation result.
     ************************************************************************/
    public function generate(): mixed;
}