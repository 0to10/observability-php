<?php
declare(strict_types=1);
namespace ZERO2TEN\Observability\APM;

/**
 * Datastore
 *
 * @copyright Copyright (c) 2023 0TO10 B.V. <https://0to10.nl>
 * @package ZERO2TEN\Observability\APM
 */
interface Datastore
{
    /**
     * @return string
     */
    public function product(): string;

    /**
     * @return string|null
     */
    public function host(): ?string;

    /**
     * @return string
     */
    public function database(): string;

    /**
     * @return string|null
     */
    public function collection(): ?string;

    /**
     * @return string
     */
    public function operation(): string;
}
