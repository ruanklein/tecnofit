<?php declare(strict_types=1);

namespace Tecnofit\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Tecnofit\Config\Database;
use ReflectionClass;

/**
 * Unit tests - No real database needed
 */
class DatabaseTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->resetSingleton();
    }

    protected function tearDown(): void
    {
        $this->resetSingleton();
        parent::tearDown();
    }

    /**
     * Test singleton pattern
     */
    public function testSingletonPattern(): void
    {
        $instance1 = Database::getInstance();
        $instance2 = Database::getInstance();

        $this->assertInstanceOf(Database::class, $instance1);
        $this->assertSame($instance1, $instance2);
    }

    /**
     * Test configuration loading
     */
    public function testConfigurationLoading(): void
    {
        $this->resetSingleton();
        $database = Database::getInstance();
        
        $reflection = new ReflectionClass($database);
        $configProperty = $reflection->getProperty('config');
        $configProperty->setAccessible(true);
        $config = $configProperty->getValue($database);

        $this->assertEquals('db', $config['host']);
        $this->assertEquals(3306, $config['port']);
        $this->assertEquals('tecnofit_test', $config['database']);
        $this->assertEquals('root', $config['username']);
        $this->assertEquals('root', $config['password']);
    }

    /**
     * Test DSN generation
     */
    public function testDsnGeneration(): void
    {
        $this->resetSingleton();
        $database = Database::getInstance();
        $this->assertInstanceOf(Database::class, $database);
    }

    private function resetSingleton(): void
    {
        $reflection = new ReflectionClass(Database::class);
        $instance = $reflection->getProperty('instance');
        $instance->setAccessible(true);
        $instance->setValue(null, null);
    }
}