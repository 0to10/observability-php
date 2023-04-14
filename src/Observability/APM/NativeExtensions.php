<?php
declare(strict_types=1);
namespace ZERO2TEN\Observability\APM;

use function extension_loaded;
use function ini_get;

/**
 * NativeExtensions
 *
 * @copyright Copyright (c) 2023 0TO10 B.V. <https://0to10.nl>
 * @package ZERO2TEN\Observability\APM
 */
trait NativeExtensions
{
    /**
     * @param string $name
     * @return bool
     */
    protected function isExtensionLoaded(string $name): bool
    {
        return extension_loaded($name);
    }

    /**
     * @param string $name
     * @return string|null
     */
    protected function getConfigurationOption(string $name): ?string
    {
        if (false === ($value = ini_get($name))) {
            return null;
        }

        return $value;
    }
}
