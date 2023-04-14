<?php
declare(strict_types=1);
namespace Nouve\APM\Metrics;

/**
 * Metric
 *
 * @copyright Copyright (c) 2018 Nouvé B.V. <https://nouve.nl>
 * @package Nouve\APM\Metrics
 */
abstract class Metric
{
    private const NAME_RESERVED_CHARS = '/][|*';

    /** @var string */
    private $name;
    /** @var string */
    private $type;

    /**
     * @param string $name
     * @param string $type
     * @constructor
     */
    public function __construct(string $name, string $type)
    {
        $this->setName($name);
        $this->setType($type);
    }

    /**
     * @param mixed $value
     * @return mixed
     */
    abstract protected function finalizeValue($value);

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return static
     */
    public function setName(string $name): self
    {
        $this->guardHasNoReservedCharacters($name);

        $this->name = $name;
        return $this;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param string $type
     * @return static
     */
    public function setType(string $type): self
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @param string $value
     * @throws \InvalidArgumentException
     * @return void
     */
    private function guardHasNoReservedCharacters(string $value): void
    {
        $regex = sprintf('/[%s]/', preg_quote(self::NAME_RESERVED_CHARS, '/'));

        if (1 === preg_match($regex, $value)) {
            throw new \InvalidArgumentException(sprintf(
                'Metric name cannot contain "%s" characters.',
                self::NAME_RESERVED_CHARS
            ));
        }
    }

    /**
     * @inheritdoc
     */
    public function __toString(): string
    {
        return sprintf('%s[%s]', $this->name, $this->type);
    }
}
