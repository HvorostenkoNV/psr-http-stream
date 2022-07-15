<?php

declare(strict_types=1);

namespace HNV\Http\StreamTests;

use HNV\Http\Helper\Collection\Resource\{
    AccessMode,
    AccessModeType,
};
use HNV\Http\Helper\Generator\{
    File        as FileGenerator,
    Resource    as ResourceGenerator,
};
use PHPUnit\Framework\TestCase;

use function feof;
use function fgetc;

abstract class AbstractStreamTestCase extends TestCase
{
    public function dataProviderResources(): array
    {
        $result = [];

        foreach ($this->generateResources(AccessModeType::ALL) as $resource) {
            $result[] = [$resource];
        }

        return $result;
    }

    /**
     * @return resource[]
     */
    protected function generateResources(AccessModeType $modesType): array
    {
        $accessModes    = AccessMode::get($modesType, AccessModeType::EXPECT_NO_FILE);
        $result         = [];

        foreach ($accessModes as $mode) {
            $file       = (new FileGenerator())->generate();
            $result[]   = (new ResourceGenerator($file, $mode))->generate();
        }

        return $result;
    }

    /**
     * @param resource $resource
     */
    protected function reachResourceEnd($resource): void
    {
        while (!feof($resource)) {
            $result = fgetc($resource);
            if ($result === false) {
                break;
            }
        }
    }
}
