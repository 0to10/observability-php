<?php
declare(strict_types=1);
namespace Nouve\APM\Agents;

use GuzzleHttp\Client as HttpClient;
use Nouve\APM\AgentInterface;
use Nouve\APM\Transaction;

/**
 * NewRelicAgent
 *
 * @copyright Copyright (c) 2018 Nouvé B.V. <https://nouve.nl>
 * @package Nouve\APM\Agents
 */
final class NewRelicAgent implements AgentInterface
{
    public const EXTENSION_NAME = 'newrelic';
    public const METRICS_BASE_URL = '';

    /** @var string */
    private $applicationName;
    /** @var string */
    private $license;
    /** @var bool */
    private $transactionEnded = false;

    /** @var string[] */
    private static $reservedWords = [
        'accountId',
        'appId',
        'timestamp',
        'type',
        'eventType',
    ];

    /**
     * @param string $license
     * @throws \RuntimeException
     * @constructor
     */
    public function __construct(string $license = null)
    {
        if (!self::isSupported()) {
            throw new \RuntimeException('This agent is not supported on this platform.');
        }

        $this->applicationName = ini_get('newrelic.appname');
        $this->license = $license ?: ini_get('newrelic.license');
    }

    /**
     * @inheritdoc
     */
    public function setApplicationName(string $name): bool
    {
        return newrelic_set_appname($name, $this->license, false);
    }

    /**
     * @inheritdoc
     */
    public function recordEvent(string $name, array $attributes): void
    {
        $this->guardIsNotReservedWord($name);

        newrelic_record_custom_event($name, $attributes);
    }

    /**
     * @inheritdoc
     */
    public function recordException(\Throwable $exception): void
    {
        newrelic_notice_error($exception->getMessage(), $exception);
    }

    /**
     * @inheritdoc
     */
    public function startTransaction(): Transaction
    {
        if (!newrelic_start_transaction($this->applicationName, $this->license)) {
            newrelic_end_transaction();
            newrelic_start_transaction($this->applicationName, $this->license);
        }

        $this->transactionEnded = false;

        return new Transaction($this);
    }

    /**
     * @inheritdoc
     */
    public function setTransactionName(string $name): bool
    {
        return newrelic_name_transaction($name);
    }

    /**
     * @inheritdoc
     */
    public function addTransactionMetric(string $name, float $milliseconds): bool
    {
        $this->guardIsNotReservedWord($name);

        return newrelic_custom_metric(sprintf('Custom/%s', $name), $milliseconds);
    }

    /**
     * @inheritdoc
     */
    public function addTransactionParameter(string $name, $value): bool
    {
        $this->guardIsNotReservedWord($name);

        if (null !== $value && !is_scalar($value)) {
            throw new \InvalidArgumentException(sprintf(
                'Transaction parameter value must be scalar, "%s" given.',
                gettype($value)
            ));
        }

        return newrelic_add_custom_parameter($name, $value);
    }

    /**
     * @inheritdoc
     */
    public function flagTransaction(bool $background): void
    {
        newrelic_background_job($background);
    }

    /**
     * @inheritdoc
     */
    public function ignoreTransactionApdex(): void
    {
        newrelic_ignore_apdex();
    }

    /**
     * @inheritdoc
     */
    public function ignoreTransaction(): void
    {
        newrelic_ignore_transaction();
    }

    /**
     * @inheritdoc
     */
    public function stopTransactionTiming(): void
    {
        newrelic_end_of_transaction();
    }

    /**
     * @inheritdoc
     */
    public function endTransaction(): bool
    {
        if ($this->transactionEnded) {
            return true;
        }

        if (newrelic_end_transaction(false)) {
            $this->transactionEnded = true;
        }

        return $this->transactionEnded;
    }

    /**
     * @inheritdoc
     */
    public function disableAutomaticRealUserMonitoringScripts(): void
    {
        newrelic_disable_autorum();
    }

    /**
     * @inheritdoc
     */
    public function getRealUserMonitoringHeaderScript(): string
    {
        return newrelic_get_browser_timing_header(false);
    }

    /**
     * @inheritdoc
     */
    public function getRealUserMonitoringFooterScript(): string
    {
        return newrelic_get_browser_timing_footer(false);
    }

    /**
     * @inheritdoc
     */
    public function createHttpClient(): HttpClient
    {
        return new HttpClient([
            'base_url' => self::METRICS_BASE_URL,
            'defaults' => [
                'headers' => [
                    'X-License-Key' => $this->license,
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ],
            ],
        ]);
    }

    /**
     * @inheritdoc
     */
    public static function isSupported(): bool
    {
        return extension_loaded(self::EXTENSION_NAME);
    }

    /**
     * @param string $word
     * @throws \InvalidArgumentException
     * @return void
     */
    private function guardIsNotReservedWord(string $word): void
    {
        if (!in_array($word, self::$reservedWords, true)) {
            return;
        }

        throw new \InvalidArgumentException(sprintf(
            'Cannot use reserved word "%s" as metric name.',
            $word
        ));
    }
}
