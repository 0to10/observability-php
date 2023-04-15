<?php
declare(strict_types=1);
namespace ZERO2TEN\Tests\Observability;

use stdClass;

/**
 * GenericDataProviders
 *
 * @copyright Copyright (c) 2023 0TO10 B.V. <https://0to10.nl>
 * @package ZERO2TEN\Tests\Observability
 */
trait GenericDataProviders
{
    /**
     * @return array[]
     */
    public function booleanDataProvider(): array
    {
        return [
            [true],
            [false],
        ];
    }

    /**
     * @return array[]
     */
    public function variousResultsDataProvider(): array
    {
        return [
            'array' => [
                [],
            ],
            'object' => [
                new stdClass(),
            ],
            'string' => [
                'some string',
            ],
            'float' => [
                15.16,
            ],
            'integer' => [
                123456789,
            ],
            'boolean' => [
                true,
            ],
        ];
    }
}