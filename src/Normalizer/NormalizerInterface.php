<?php
declare(strict_types=1);

namespace HNV\Http\Stream\Normalizer;
/** ***********************************************************************************************
 * Normalizer interface.
 *
 * @package HNV\Psr\Http\Stream
 * @author  Hvorostenko
 *************************************************************************************************/
interface NormalizerInterface
{
    /** **********************************************************************
     * Normalize data.
     *
     * @param   mixed $value                Value.
     *
     * @return  mixed                       Normalized value.
     * @throws  NormalizingException        Normalizing error.
     ************************************************************************/
    public static function normalize(mixed $value): mixed;
}