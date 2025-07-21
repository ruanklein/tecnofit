<?php declare(strict_types=1);

namespace Tecnofit\Tests\Integration;

use PHPUnit\Framework\TestCase;
use Tecnofit\Config\Database;
use PDO;
use ReflectionClass;
use Exception;

/**
 * Integration tests - Requires actual database
 * 
 * Prerequisites: 
 * - MySQL server running
 * - Database 'tecnofit_test' exists
 * - User has access to the database
 */
class DatabaseConnectionTest extends TestCase
{
    /**
     * Get database configuration
     */
    private function getConfig(): array
    {
        return [
            'host' => $_ENV['DB_HOST'] ?? '127.0.0.1',
            'port' => $_ENV['DB_PORT'] ?? '3306',
            'database' => $_ENV['DB_DATABASE'] ?? 'tecnofit_test',
            'username' => $_ENV['DB_USERNAME'] ?? 'root',
            'password' => $_ENV['DB_PASSWORD'] ?? 'root',
        ];
    }

    /**
     * Create a test connection
     */
    private function createTestConnection(): PDO
    {
        $config = $this->getConfig();
        $dsn = sprintf("mysql:host=%s;port=%s", $config['host'], $config['port']);

        return new PDO($dsn, $config['username'], $config['password']);
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->resetSingleton();
        
        // Ensure test database exists before running tests
        $this->ensureTestDatabaseExists();
    }

    protected function tearDown(): void
    {
        $this->resetSingleton();
        parent::tearDown();
    }

    /**
     * Test real database connection
     */
    public function testRealDatabaseConnection(): void
    {
        $database = Database::getInstance();
        $connection = $database->getConnection();

        $this->assertInstanceOf(PDO::class, $connection);
        
        // Test simple query
        $stmt = $database->query('SELECT 1 as test');
        $result = $stmt->fetch();
        
        $this->assertEquals(1, $result['test']);
    }

    /**
     * Test connection reuse
     */
    public function testConnectionReuse(): void
    {
        $database = Database::getInstance();
        $connection1 = $database->getConnection();
        $connection2 = $database->getConnection();

        $this->assertSame($connection1, $connection2);
    }

    /**
     * Drop the test database
     */
    private function dropTestDatabase(): void
    {
        try {
            $config = $this->getConfig();
            $pdo = $this->createTestConnection();
    
            $pdo->exec(sprintf("DROP DATABASE IF EXISTS `%s`", $config['database']));
        } catch (Exception $e) {
            error_log("Cannot drop test database: " . $e->getMessage());
            $this->markTestSkipped("Cannot drop test database: " . $e->getMessage());
        }
    }

    /**
     * Ensure test database exists (prerequisite)
     */
    private function ensureTestDatabaseExists(): void
    {
        try {
            $config = $this->getConfig();
            $dsn = sprintf("mysql:host=%s;port=%s", $config['host'], $config['port']);
            $pdo = new PDO($dsn, $config['username'], $config['password']);

            $pdo->exec(sprintf("CREATE DATABASE IF NOT EXISTS `%s` 
                       CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci", $config['database']));
            $pdo->exec(sprintf("USE `%s`", $config['database']));
        } catch (Exception $e) {
            error_log("Cannot setup test database: " . $e->getMessage());
            $this->markTestSkipped("Cannot setup test database: " . $e->getMessage());
        }
    }

    private function resetSingleton(): void
    {
        $reflection = new ReflectionClass(Database::class);
        $instance = $reflection->getProperty('instance');
        $instance->setAccessible(true);
        $instance->setValue(null, null);
    }
}