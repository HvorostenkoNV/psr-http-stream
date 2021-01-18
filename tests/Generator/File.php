<?php
declare(strict_types=1);

namespace HNV\Http\StreamTests\Generator;

use LogicException;

use function is_file;
use function tempnam;
use function unlink;
use function sys_get_temp_dir;
use function register_shutdown_function;
/** ***********************************************************************************************
 * File generator.
 *
 * @package HNV\Psr\Http\Tests\Stream
 * @author  Hvorostenko
 *************************************************************************************************/
class File implements GeneratorInterface
{
    /** **********************************************************************
     * @inheritDoc
     *
     * @return string                       Generated temporary file path.
     ************************************************************************/
    public function generate(): string
    {
        $temporaryDirectory = sys_get_temp_dir();
        $temporaryFile      = tempnam($temporaryDirectory, 'UT');

        if ($temporaryFile === false) {
            throw new LogicException('temporary file creating failed');
        }

        register_shutdown_function(function() use ($temporaryFile) {
            if (is_file($temporaryFile)) {
                unlink($temporaryFile);
            }
        });

        return $temporaryFile;
    }
}