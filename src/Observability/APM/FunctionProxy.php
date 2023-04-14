<?php
declare(strict_types=1);
namespace ZERO2TEN\Observability\APM;

use BadFunctionCallException;
use Exception;
use Throwable;

use function call_user_func;
use function function_exists;
use function sprintf;

/**
 * FunctionProxy
 *
 * @copyright Copyright (c) 2023 0TO10 B.V. <https://0to10.nl>
 * @package ZERO2TEN\Observability\APM
 */
trait FunctionProxy
{
    /**
     * This method serves as a proxy for calling (native) functions for Agents.
     *
     * Extending classes must ensure that the arguments passed to the function
     * are correct, as this method will just pass through all arguments.
     *
     * Returns the result of the called function (which may be `false`), or `false`
     * on failure.
     *
     * @param string $name
     * @param array $arguments
     * @throws BadFunctionCallException
     * @return mixed|false
     */
    protected function proxyFunctionCall(string $name, array $arguments)
    {
        if (!function_exists($name)) {
            throw new BadFunctionCallException(sprintf('Function "%s" does not exist.', $name));
        }

        try {
            return call_user_func($name, ...$arguments);
        } catch (Exception | Throwable $e) {
            return false;
        }
    }
}
