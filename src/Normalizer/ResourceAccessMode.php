<?php
declare(strict_types=1);

namespace HNV\Http\Stream\Normalizer;

use HNV\Http\Stream\Collection\ResourceAccessMode\All as ResourceAccessModeAll;

use function str_replace;
use function str_contains;
use function in_array;
/** ***********************************************************************************************
 * Stream access mode normalizer.
 *
 * @package HNV\Psr\Http\Stream
 * @author  Hvorostenko
 *************************************************************************************************/
class ResourceAccessMode implements NormalizerInterface
{
    private const SPECIAL_FLAG  = 'b';
    private const POSTFIX       = '+';
    /** **********************************************************************
     * @inheritDoc
     ************************************************************************/
    public static function normalize($value): string
    {
        $hasPostfix         = str_contains($value, self::POSTFIX);
        $valueClear         = str_replace([self::SPECIAL_FLAG, self::POSTFIX], '', $value);
        $valueNormalized    = $hasPostfix
            ? $valueClear.self::SPECIAL_FLAG.self::POSTFIX
            : $valueClear.self::SPECIAL_FLAG;
        $availableValues    = ResourceAccessModeAll::get();

        if (!in_array($valueNormalized, $availableValues)) {
            throw new NormalizingException("mode $value is unknown");
        }

        return $valueNormalized;
    }
}