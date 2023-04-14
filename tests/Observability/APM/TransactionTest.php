<?php
declare(strict_types=1);
namespace ZERO2TEN\Tests\Observability\APM;

use Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ZERO2TEN\Observability\APM\AgentInterface;
use ZERO2TEN\Observability\APM\Datastore;
use ZERO2TEN\Observability\APM\Transaction;
use ZERO2TEN\Observability\APM\TransactionInterface;
use ZERO2TEN\Tests\Observability\GenericDataProviders;

/**
 * TransactionTest
 *
 * @coversDefaultClass \ZERO2TEN\Observability\APM\Transaction
 *
 * @copyright Copyright (c) 2023 0TO10 B.V. <https://0to10.nl>
 * @package ZERO2TEN\Tests\Observability\APM
 */
class TransactionTest extends TestCase
{
    use GenericDataProviders;

    /** @var AgentInterface|MockObject */
    private $agentMock;
    /** @var Transaction */
    private $transaction;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->agentMock = $this->createMock(AgentInterface::class);

        $this->transaction = new Transaction($this->agentMock);
    }

    /**
     * @covers ::__construct
     * @group unit
     *
     * @return void
     */
    public function testTransaction(): void
    {
        $this->assertInstanceOf(TransactionInterface::class, $this->transaction);
    }

    /**
     * @covers ::start
     * @group unit
     *
     * @return void
     */
    public function testStart(): void
    {
        /** @var TransactionInterface|MockObject $newTransactionMock */
        $newTransactionMock = $this->createMock(TransactionInterface::class);

        $this->agentMock
            ->expects($this->once())
            ->method('startTransaction')
            ->willReturn($newTransactionMock)
        ;

        $newTransaction = $this->transaction->start(false);

        $this->assertFalse($newTransaction->isIgnored());

        $this->assertNotSame($newTransaction, $this->transaction);
        $this->assertFalse($this->transaction->isIgnored());
    }

    /**
     * @covers ::start
     * @group unit
     *
     * @return void
     */
    public function testStartIgnorePrevious(): void
    {
        /** @var TransactionInterface|MockObject $newTransactionMock */
        $newTransactionMock = $this->createMock(TransactionInterface::class);

        $this->agentMock
            ->expects($this->once())
            ->method('startTransaction')
            ->willReturn($newTransactionMock)
        ;

        $newTransaction = $this->transaction->start(true);

        $this->assertFalse($newTransaction->isIgnored());

        $this->assertTrue($this->transaction->isIgnored());
        $this->assertNotSame($newTransaction, $this->transaction);
    }

    /**
     * @covers ::changeName
     * @group unit
     *
     * @return void
     */
    public function testChangeName(): void
    {
        $this->agentMock
            ->expects($this->once())
            ->method('changeTransactionName')
            ->with('SomeName')
        ;

        $this->assertSame($this->transaction, $this->transaction->changeName('SomeName'));
    }

    /**
     * @covers ::addParameter
     * @covers ::getParameters
     * @group unit
     *
     * @dataProvider customParametersDataProvider
     *
     * @param array $parameters
     * @return void
     */
    public function testAddParameter(array $parameters): void
    {
        $with = [];
        foreach ($parameters as $key => $value) {
            $with[] = [$key, $value];
        }

        $this->agentMock
            ->expects($this->exactly(count($parameters)))
            ->method('addTransactionParameter')
            ->withConsecutive(...$with)
        ;

        foreach ($parameters as $key => $value) {
            $this->assertSame($this->transaction, $this->transaction->addParameter($key, $value));
        }

        $this->assertSame($parameters, $this->transaction->getParameters());
    }

    /**
     * @covers ::addParameters
     * @covers ::getParameters
     * @group unit
     *
     * @dataProvider customParametersDataProvider
     *
     * @param array $parameters
     * @return void
     */
    public function testAddParameters(array $parameters): void
    {
        $with = [];
        foreach ($parameters as $key => $value) {
            $with[] = [$key, $value];
        }

        $this->agentMock
            ->expects($this->exactly(count($parameters)))
            ->method('addTransactionParameter')
            ->withConsecutive(...$with)
        ;

        $this->assertSame($this->transaction, $this->transaction->addParameters($parameters));
        $this->assertSame($parameters, $this->transaction->getParameters());
    }

    /**
     * @covers ::markAsBackground
     * @group unit
     *
     * @dataProvider booleanDataProvider
     *
     * @param bool $background
     * @return void
     */
    public function testMarkAsBackground(bool $background): void
    {
        $this->agentMock
            ->expects($this->once())
            ->method('markTransactionAsBackground')
            ->with($background)
        ;

        $this->assertSame($this->transaction, $this->transaction->markAsBackground($background));
    }

    /**
     * @covers ::recordException
     * @group unit
     *
     * @return void
     */
    public function testRecordException(): void
    {
        $e = new Exception('This is the exception message!');

        $this->agentMock
            ->expects($this->once())
            ->method('recordTransactionException')
            ->with('This is the exception message!', $e)
        ;

        $this->assertSame($this->transaction, $this->transaction->recordException($e));
    }

    /**
     * @covers ::addDatastoreSegment
     * @group unit
     *
     * @dataProvider variousResultsDataProvider
     *
     * @param mixed $expectedResult
     * @return void
     */
    public function testAddDatastoreSegment($expectedResult): void
    {
        /** @var Datastore|MockObject $datastoreMock */
        $datastoreMock = $this->createMock(Datastore::class);

        $this->agentMock
            ->expects($this->once())
            ->method('addTransactionDatastoreSegment')
            ->with(
                $datastoreMock,
                $callable = function () {},
                'sql-dql-etc',
                'some-label',
                'input-query'
            )
            ->willReturn($expectedResult)
        ;

        $result = $this->transaction->addDatastoreSegment(
            $datastoreMock,
            $callable,
            'sql-dql-etc',
            'some-label',
            'input-query'
        );

        $this->assertSame($expectedResult, $result);
    }

    /**
     * @covers ::stopTiming
     * @group unit
     *
     * @return void
     */
    public function testStopTiming(): void
    {
        $this->agentMock
            ->expects($this->once())
            ->method('stopTransactionTiming')
        ;

        $this->assertSame($this->transaction, $this->transaction->stopTiming());
    }

    /**
     * @covers ::ignoreApdex
     * @group unit
     *
     * @return void
     */
    public function testIgnoreApdex(): void
    {
        $this->agentMock
            ->expects($this->once())
            ->method('ignoreTransactionApdex')
        ;

        $this->assertSame($this->transaction, $this->transaction->ignoreApdex());
        $this->assertFalse($this->transaction->isIgnored());
    }

    /**
     * @covers ::ignore
     * @covers ::isIgnored
     * @group unit
     *
     * @return void
     */
    public function testIgnore(): void
    {
        $this->agentMock
            ->expects($this->once())
            ->method('ignoreTransaction')
        ;

        $this->assertFalse($this->transaction->isIgnored());

        $this->assertSame($this->transaction, $this->transaction->ignore());
        $this->assertTrue($this->transaction->isIgnored());
    }

    /**
     * @covers ::isSampled
     * @group unit
     *
     * @dataProvider booleanDataProvider
     *
     * @param bool $sampled
     * @return void
     */
    public function testIsSampled(bool $sampled): void
    {
        $this->agentMock
            ->expects($this->once())
            ->method('isTransactionSampled')
            ->willReturn($sampled)
        ;

        $this->assertSame($sampled, $this->transaction->isSampled());
    }

    /**
     * @covers ::end
     * @covers ::isEnded
     * @group unit
     *
     * @return void
     */
    public function testEnd(): void
    {
        $this->agentMock
            ->expects($this->once())
            ->method('endTransaction')
        ;

        $this->agentMock
            ->expects($this->exactly(2))
            ->method('isTransactionEnded')
            ->willReturnOnConsecutiveCalls(
                false,
                true
            )
        ;

        $this->assertFalse($this->transaction->isEnded());

        $this->assertSame($this->transaction, $this->transaction->end());
        $this->assertTrue($this->transaction->isEnded());
    }








    ///////////////////////////////////////////////////////////////

    /**
     * @return array[]
     */
    public function customParametersDataProvider(): array
    {
        return [
            'empty' => [
                [],
            ],
            'mixed' => [
                [
                    'test_a' => 15000,
                    'test_b' => 1.0,
                    'test_c' => true,
                    'test_d' => 'some string',
                ],
            ],
        ];
    }
}
