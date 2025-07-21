<?php declare(strict_types=1);

namespace Tecnofit\Config;

use PDO;
use PDOException;
use Exception;
use PDOStatement;

/**
 * Database class - Manages database connections
 * 
 * Implements the singleton pattern to ensure a single active connection
 * Uses PDO for secure communication with the database
 */
class Database
{
    private static ?Database $instance = null;
    private ?PDO $connection = null;
    private array $config = [];

    /**
     * Default database configurations
     */
    private const DEFAULT_OPTIONS = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
        PDO::ATTR_STRINGIFY_FETCHES => false,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci",
        PDO::ATTR_TIMEOUT => 30,
        PDO::ATTR_PERSISTENT => false,
        PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true,
        PDO::MYSQL_ATTR_FOUND_ROWS => true
    ];

    /**
     * Private constructor to implement Singleton
     */
    private function __construct()
    {
        $this->loadConfiguration();
    }

    /**
     * Get the unique instance of the Database class
     * 
     * @return Database
     */
    public static function getInstance(): Database
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Loads the database configuration
     * 
     * @throws Exception
     */
    private function loadConfiguration(): void
    {
        $this->config = [
            'host' => $_ENV['DB_HOST'] ?? 'db',
            'port' => (int)($_ENV['DB_PORT'] ?? 3306),
            'database' => $_ENV['DB_DATABASE'] ?? 'tecnofit',
            'username' => $_ENV['DB_USERNAME'] ?? 'tecnofit',
            'password' => $_ENV['DB_PASSWORD'] ?? 'tecnofit',
            'charset' => $_ENV['DB_CHARSET'] ?? 'utf8mb4',
            'collation' => $_ENV['DB_COLLATION'] ?? 'utf8mb4_unicode_ci'
        ];
    }

    /**
     * Establishes a connection to the database
     * 
     * @return PDO
     * @throws Exception
     */
    public function getConnection(): PDO
    {
        if ($this->connection === null) {
            $this->connect();
        }

        return $this->connection;
    }

    /**
     * Connects to the MySQL database
     * 
     * @throws Exception
     */
    private function connect(): void
    {
        try {
            $dsn = sprintf(
                'mysql:host=%s;port=%d;dbname=%s;charset=%s',
                $this->config['host'],
                $this->config['port'],
                $this->config['database'],
                $this->config['charset']
            );

            $this->connection = new PDO(
                $dsn,
                $this->config['username'],
                $this->config['password'],
                self::DEFAULT_OPTIONS
            );

            $timezone = $_ENV['APP_TIMEZONE'] ?? 'America/Sao_Paulo';
            $this->connection->exec("SET time_zone = '{$timezone}'");

        } catch (PDOException $e) {
            $this->logError('Error connecting to database', $e);
            throw new Exception('Error connecting to database: ' . $e->getMessage());
        }
    }

    /**
     * Executes a prepared query
     * 
     * @param string $sql
     * @param array $params
     * @return PDOStatement
     * @throws Exception
     */
    public function query(string $sql, array $params = []): PDOStatement
    {
        try {
            $stmt = $this->getConnection()->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            $this->logError('Error executing query', $e, $sql, $params);
            throw new Exception('Error executing query: ' . $e->getMessage());
        }
    }

    /**
     * Closes the database connection
     */
    public function disconnect(): void
    {
        $this->connection = null;
    }

    /**
     * Logs database errors
     * 
     * @param string $message
     * @param Exception $exception
     * @param string|null $sql
     * @param array $params
     */
    private function logError(string $message, Exception $exception, ?string $sql = null, array $params = []): void
    {
        $logData = [
            'message' => $message,
            'error' => $exception->getMessage(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'timestamp' => date('Y-m-d H:i:s')
        ];

        if ($sql) {
            $logData['sql'] = $sql;
            $logData['params'] = $params;
        }

        if (($_ENV['APP_ENV'] ?? 'development') === 'development') {
            error_log('Database Error: ' . json_encode($logData, JSON_PRETTY_PRINT));
        } else {
            error_log('Database Error: ' . $message);
        }
    }

    /**
     * Prevents cloning of the instance
     */
    private function __clone() {}

    /**
     * Prevents deserialization of the instance
     */
    public function __wakeup()
    {
        throw new Exception("Cannot unserialize singleton");
    }

    /**
     * Destructor - ensures that connections are closed properly
     */
    public function __destruct()
    {
        $this->disconnect();
    }
}