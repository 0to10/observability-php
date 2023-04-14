<?php
declare(strict_types=1);
namespace ZERO2TEN\Tests\Observability\APM;

use PHPUnit\Framework\TestCase;
use ZERO2TEN\Observability\APM\NativeExtensions;

/**
 * NativeExtensionsTest
 *
 * @coversDefaultClass \ZERO2TEN\Observability\APM\NativeExtensions
 *
 * @copyright Copyright (c) 2023 0TO10 B.V. <https://0to10.nl>
 * @package ZERO2TEN\Tests\Observability\APM
 */
class NativeExtensionsTest extends TestCase
{
    private const INI_EXISTING = 'extension_dir';
    private const INI_NON_EXISTING = 'test.this_would_normally_not_exist_like_ever';

    /** @var NativeExtensions */
    private $nativeExtensionsStub;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->nativeExtensionsStub = new class {
            use NativeExtensions;

            /**
             * @param string $name
             * @return bool
             */
            public function callIsExtensionLoaded(string $name): bool
            {
                return $this->isExtensionLoaded($name);
            }

            /**
             * @param string $name
             * @return string|null
             */
            public function callGetConfigurationOption(string $name): ?string
            {
                return $this->getConfigurationOption($name);
            }
        };
    }

    /**
     * @covers ::isExtensionLoaded
     * @group unit
     *
     * @return void
     */
    public function testIsExtensionLoaded(): void
    {
        $this->assertTrue($this->nativeExtensionsStub->callIsExtensionLoaded('Core'));
        $this->assertFalse($this->nativeExtensionsStub->callIsExtensionLoaded('CoreBlaNotReal'));
    }

    /**
     * @covers ::getConfigurationOption
     * @group unit
     *
     * @return void
     */
    public function testGetConfigurationOption(): void
    {
        $directory = ini_get(self::INI_EXISTING);

        if (!is_string($directory) || empty($directory)) {
            $this->markAsRisky();
            $this->fail('Returned value for "extension_dir" is expected to be a non-empty string.');
        }

        $this->assertSame($directory, $this->nativeExtensionsStub->callGetConfigurationOption('extension_dir'));
    }

    /**
     * @covers ::getConfigurationOption
     * @group unit
     *
     * @return void
     */
    public function testConfigurationOptionNonExisting(): void
    {
        $this->assertNull($this->nativeExtensionsStub->callGetConfigurationOption(self::INI_NON_EXISTING));
    }
}
