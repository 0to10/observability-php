<?php
declare(strict_types=1);
namespace Nouve\APM;

/**
 * ComponentInterface
 *
 * @author Ted Vossen <ted@nouve.nl>
 * @copyright Copyright (c) 2018 Nouvé B.V. <https://nouve.nl>
 * @package Nouve\APM
 */
interface ComponentInterface
{
    /**
     * @return bool
     */
    public function isEnabled(): bool;
}