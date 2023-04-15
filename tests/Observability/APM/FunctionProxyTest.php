<?php
declare(strict_types=1);
namespace ZERO2TEN\Tests\Observability\APM;

use BadFunctionCallException;
use Exception;
use PHPUnit\Framework\TestCase;
use ZERO2TEN\Observability\APM\FunctionProxy;

/**
 * @return string
 */
function thisFunctionExists(): string {
    return 'function result!';
}

/**
 * @throws Exception
 * @return void
 */
function thisFunctionWillThrow(): void {
    throw new Exception('Something went really wrong...');
}

/**
 * FunctionProxyTest
 *
 * @coversDefaultClass \ZERO2TEN\Observability\APM\FunctionProxy
 *
 * @copyright Copyright (c) 2023 0TO10 B.V. <https://0to10.nl>
 * @package ZERO2TEN\Tests\Observability\APM
 */
class FunctionProxyTest extends TestCase
{
    /** @var FunctionProxy */
    private $functionProxyStub;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->functionProxyStub = new class {
            use FunctionProxy;

            /**
             * @param string $function
             * @return mixed
             */
            public function callProxyFunctionCall(string $function)
            {
                return $this->proxyFunctionCall($function, []);
            }
        };
    }

    /**
     * @covers ::proxyFunctionCall
     * @group unit
     *
     * @uses thisFunctionExists()
     *
     * @return void
     */
    public function testProxyFunctionCall(): void
    {
        $name = __NAMESPACE__ . '\thisFunctionExists';

        $this->assertSame('function result!', $this->functionProxyStub->callProxyFunctionCall($name));
    }

    /**
     * @covers ::proxyFunctionCall
     * @group unit
     *
     * @uses thisFunctionExists()
     *
     * @return void
     */
    public function testProxyFunctionCallNonExistingFunction(): void
    {
        $name = __NAMESPACE__ . '\non_existing_function';

        $this->expectException(BadFunctionCallException::class);
        $this->expectExceptionMessage('Function "' . $name . '" does not exist.');

        $this->functionProxyStub->callProxyFunctionCall($name);
    }

    /**
     * @covers ::proxyFunctionCall
     * @group unit
     *
     * @uses thisFunctionWillThrow()
     *
     * @return void
     */
    public function testProxyFunctionCallHandlesException(): void
    {
        $name = __NAMESPACE__ . '\thisFunctionWillThrow';

        $this->assertFalse($this->functionProxyStub->callProxyFunctionCall($name));
    }
}
