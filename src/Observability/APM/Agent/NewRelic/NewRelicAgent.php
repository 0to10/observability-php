<?php
declare(strict_types=1);
namespace ZERO2TEN\Observability\APM\Agent\NewRelic;

use InvalidArgumentException;
use Throwable;
use ZERO2TEN\Observability\APM\Agent\Agent;
use ZERO2TEN\Observability\APM\Datastore;
use ZERO2TEN\Observability\APM\NativeExtensions;
use ZERO2TEN\Observability\APM\TransactionInterface;

use function array_filter;
use function gettype;

/**
 * NewRelicAgent
 *
 * Implementation of the AgentInterface for working with the PHP extension provided by New Relic.
 *
 * @method bool newrelic_add_custom_tracer(string $functionName)
 * @method void newrelic_capture_params(bool $enableFlag = true)
 * @method void newrelic_record_custom_event(string $name, array $attributes)
 * @method bool newrelic_custom_metric(string $name, float $value)
 * @method bool newrelic_set_appname(string $name, string $license, bool $xmit = false)
 *
 * -- Transaction --
 * @method bool newrelic_add_custom_parameter(string $key, bool|float|int|string $value)
 * @method void newrelic_background_job(bool $flag = true)
 * @method void newrelic_end_of_transaction()
 * @method bool newrelic_end_transaction(bool $ignore = false)
 * @method void newrelic_ignore_apdex()
 * @method void newrelic_ignore_transaction()
 * @method bool newrelic_is_sampled()
 * @method bool newrelic_name_transaction(string $name)
 * @method void newrelic_notice_error(string $message, Throwable $e)
 * @method bool newrelic_start_transaction(string $applicationName, string $license = null)
 * @method mixed newrelic_record_datastore_segment(callable $function, array $parameters)
 * @method bool newrelic_set_user_attributes(string $user, string $account, string $product)
 *
 * -- Browser --
 * @method bool|null newrelic_disable_autorum()
 * @method string newrelic_get_browser_timing_header(bool $includeTags = true)
 * @method string newrelic_get_browser_timing_footer(bool $includeTags = true)
 *
 * -- Distributed tracing --
 * @method bool newrelic_accept_distributed_trace_headers(array $headers, string $transport_type = 'HTTP')
 * @method bool newrelic_add_custom_span_parameter(string $key, bool|float|int|string $value)
 * @method array newrelic_get_linking_metadata()
 * @method array newrelic_get_trace_metadata()
 * @method bool newrelic_insert_distributed_trace_headers(array $headers)
 *
 * @copyright Copyright (c) 2023 0TO10 B.V. <https://0to10.nl>
 * @package ZERO2TEN\Observability\APM\Agent\NewRelic
 */
class NewRelicAgent extends Agent
{
    use NativeExtensions;

    private const EXTENSION_NAME = 'newrelic';
    private const EXTENSION_INI_APP_NAME = self::EXTENSION_NAME . '.appname';
    private const EXTENSION_INI_LICENSE = self::EXTENSION_NAME . '.license';

    private const IGNORE_TRANSACTION_ON_END = false;

    private const RESERVED_WORDS = [
        'accountId',
        'appId',
        'timestamp',
        'type',
        'eventType',
    ];

    /** @var string */
    private $applicationName = '';
    /** @var string */
    private $license = '';
    /** @var bool */
    private $transactionEnded = false;

    /**
     * @inheritDoc
     */
    protected function initialise(): bool
    {
        if (!$this->isExtensionLoaded(self::EXTENSION_NAME)) {
            return false;
        }

        $this->reserveWords(...self::RESERVED_WORDS);

        $this->applicationName = $this->getConfigurationOption(self::EXTENSION_INI_APP_NAME);
        $this->license = $this->getConfigurationOption(self::EXTENSION_INI_LICENSE);

        return !empty($this->applicationName) && !empty($this->license);
    }

    /**
     * @inheritdoc
     */
    public function changeApplicationName(string $name, bool $ignoreTransaction = true): void
    {
        $this->newrelic_set_appname($name, $this->license, !$ignoreTransaction);
    }

    /**
     * @inheritDoc
     */
    public function captureUrlParameters(bool $enable = true): void
    {
        $this->newrelic_capture_params($enable);
    }

    /**
     * @inheritdoc
     */
    public function recordCustomEvent(string $name, array $attributes): void
    {
        $this->guardIsNotReservedWord($name);

        $this->newrelic_record_custom_event($name, $attributes);
    }

    /**
     * @inheritdoc
     */
    public function addCustomMetric(string $name, float $milliseconds): void
    {
        $this->guardIsNotReservedWord($name);

        $this->newrelic_custom_metric('Custom/' . $name, $milliseconds);
    }

    /**
     * @inheritdoc
     */
    public function disableAutomaticBrowserMonitoringScripts(): void
    {
        $this->newrelic_disable_autorum();
    }

    /**
     * @inheritdoc
     */
    public function getBrowserMonitoringHeaderScript(): string
    {
        return $this->newrelic_get_browser_timing_header(false);
    }

    /**
     * @inheritdoc
     */
    public function getBrowserMonitoringFooterScript(): string
    {
        return $this->newrelic_get_browser_timing_footer(false);
    }

    /**
     * @inheritdoc
     */
    public function startTransaction(bool $ignorePrevious = false): TransactionInterface
    {
        $this->transactionEnded = !$this->newrelic_start_transaction($this->applicationName, $this->license);

        return $this->createTransaction($this);
    }

    /**
     * @inheritdoc
     */
    public function changeTransactionName(string $name): void
    {
        if (!$this->newrelic_name_transaction($name)) {
            $this->logger->info('[APM] Unable to change current transaction name.');
        }
    }

    /**
     * @inheritdoc
     */
    public function addTransactionParameter(string $name, $value): void
    {
        $this->guardIsNotReservedWord($name);

        if (null !== $value && !is_scalar($value)) {
            throw new InvalidArgumentException('Transaction parameter value must be scalar, "' . gettype($value) . '" given.');
        }

        $this->newrelic_add_custom_parameter($name, $value);
    }

    /**
     * @inheritdoc
     */
    public function markTransactionAsBackground(bool $background): void
    {
        $this->newrelic_background_job($background);
    }

    /**
     * @inheritdoc
     */
    public function recordTransactionException(string $message, Throwable $e): void
    {
        $this->newrelic_notice_error($message, $e);
    }

    /**
     * @inheritDoc
     */
    public function addTransactionDatastoreSegment(
        Datastore $datastore,
        callable $callable,
        string $query = null,
        string $inputQueryLabel = null,
        string $inputQuery = null
    )
    {
        $parameters = [
            'product' => $datastore->product(),
            'collection' => $datastore->collection(),
            'operation' => $datastore->operation(),
            'host' => $datastore->host(),
            'portPathOrId' => null,
            'databaseName' => $datastore->database(),
            'query' => $query,
            'inputQueryLabel' => $inputQueryLabel,
            'inputQuery' => $inputQuery,
        ];

        $parameters = array_filter($parameters, static function ($value): bool {
            return null !== $value;
        });

        return $this->newrelic_record_datastore_segment($callable, $parameters);
    }

    /**
     * @inheritdoc
     */
    public function stopTransactionTiming(): void
    {
        $this->newrelic_end_of_transaction();
    }

    /**
     * @inheritdoc
     */
    public function ignoreTransactionApdex(): void
    {
        $this->newrelic_ignore_apdex();
    }

    /**
     * @inheritdoc
     */
    public function ignoreTransaction(): void
    {
        $this->newrelic_ignore_transaction();
    }

    /**
     * @inheritDoc
     */
    public function isTransactionSampled(): bool
    {
        return $this->newrelic_is_sampled();
    }

    /**
     * @inheritdoc
     */
    public function endTransaction(): void
    {
        if ($this->transactionEnded) {
            return;
        }

        $this->transactionEnded = $this->newrelic_end_transaction(self::IGNORE_TRANSACTION_ON_END);
    }

    /**
     * @inheritDoc
     */
    public function isTransactionEnded(): bool
    {
        return $this->transactionEnded;
    }
}
