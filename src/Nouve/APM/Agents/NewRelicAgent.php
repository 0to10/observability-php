<?php
declare(strict_types=1);
namespace Nouve\APM\Agents;

use GuzzleHttp\Client as HttpClient;
use InvalidArgumentException;
use Nouve\APM\AgentInterface;
use Nouve\APM\Transaction;
use RuntimeException;
use Throwable;
use ZERO2TEN\Observability\APM\Agent\NewRelic\NewRelicAgent as NewRelicAgentV2;
use ZERO2TEN\Observability\APM\NativeExtensions;

/**
 * NewRelicAgent
 *
 * @deprecated Use \ZERO2TEN\Observability\APM\Agent\NewRelic\NewRelicAgent instead
 *
 * @copyright Copyright (c) 2018 Nouvé B.V. <https://nouve.nl>
 * @package Nouve\APM\Agents
 */
class NewRelicAgent implements AgentInterface
{
    use NativeExtensions;

    public const EXTENSION_NAME = 'newrelic';
    public const METRICS_BASE_URL = '';

    /** @var NewRelicAgentV2 */
    private $agent;

    /** @var string */
    private $applicationName;
    /** @var string */
    private $license;

    /** @var string[] */
    private static $reservedWords = [
        'accountId',
        'appId',
        'timestamp',
        'type',
        'eventType',
    ];

    /**
     * @param string|null $license
     * @param NewRelicAgentV2|null $newAgent
     * @throws RuntimeException
     * @constructor
     */
    public function __construct(string $license = null, NewRelicAgentV2 $newAgent = null)
    {
        $this->agent = $newAgent ?: new NewRelicAgentV2();

        $this->applicationName = $this->getConfigurationOption('newrelic.appname') ?: 'unknown';
        $this->license = $license ?: $this->getConfigurationOption('newrelic.license') ?: 'unknown';
    }

    /**
     * @inheritdoc
     */
    public function setApplicationName(string $name): bool
    {
        return $this->agent->newrelic_set_appname($name, $this->license);
    }

    /**
     * @inheritdoc
     */
    public function recordEvent(string $name, array $attributes): void
    {
        $this->guardIsNotReservedWord($name);

        $this->agent->newrelic_record_custom_event($name, $attributes);
    }

    /**
     * @inheritdoc
     */
    public function recordException(Throwable $exception): void
    {
        $this->agent->newrelic_notice_error($exception->getMessage(), $exception);
    }

    /**
     * @inheritdoc
     */
    public function startTransaction(): Transaction
    {
        if (!$this->agent->newrelic_start_transaction($this->applicationName, $this->license)) {
            $this->agent->newrelic_end_transaction();
            $this->agent->newrelic_start_transaction($this->applicationName, $this->license);
        }

        return new Transaction($this);
    }

    /**
     * @inheritdoc
     */
    public function setTransactionName(string $name): bool
    {
        return $this->agent->newrelic_name_transaction($name);
    }

    /**
     * @inheritdoc
     */
    public function addTransactionMetric(string $name, float $milliseconds): bool
    {
        $this->guardIsNotReservedWord($name);

        return $this->agent->newrelic_custom_metric(sprintf('Custom/%s', $name), $milliseconds);
    }

    /**
     * @inheritdoc
     */
    public function addTransactionParameter(string $name, $value): bool
    {
        $this->guardIsNotReservedWord($name);

        if (null !== $value && !is_scalar($value)) {
            throw new InvalidArgumentException(sprintf(
                'Transaction parameter value must be scalar, "%s" given.',
                gettype($value)
            ));
        }

        return $this->agent->newrelic_add_custom_parameter($name, $value);
    }

    /**
     * @inheritdoc
     */
    public function flagTransaction(bool $background): void
    {
        $this->agent->newrelic_background_job($background);
    }

    /**
     * @inheritdoc
     */
    public function ignoreTransactionApdex(): void
    {
        $this->agent->newrelic_ignore_apdex();
    }

    /**
     * @inheritdoc
     */
    public function ignoreTransaction(): void
    {
        $this->agent->newrelic_ignore_transaction();
    }

    /**
     * @inheritdoc
     */
    public function stopTransactionTiming(): void
    {
        $this->agent->newrelic_end_of_transaction();
    }

    /**
     * @inheritdoc
     */
    public function endTransaction(): bool
    {
        return $this->agent->newrelic_end_transaction();
    }

    /**
     * @inheritdoc
     */
    public function disableAutomaticRealUserMonitoringScripts(): void
    {
        $this->agent->newrelic_disable_autorum();
    }

    /**
     * @inheritdoc
     */
    public function getRealUserMonitoringHeaderScript(): string
    {
        return $this->agent->newrelic_get_browser_timing_header(false);
    }

    /**
     * @inheritdoc
     */
    public function getRealUserMonitoringFooterScript(): string
    {
        return $this->agent->newrelic_get_browser_timing_footer(false);
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
     * @throws InvalidArgumentException
     * @return void
     */
    private function guardIsNotReservedWord(string $word): void
    {
        if (!in_array($word, self::$reservedWords, true)) {
            return;
        }

        throw new InvalidArgumentException(sprintf(
            'Cannot use reserved word "%s" as metric name.',
            $word
        ));
    }
}
