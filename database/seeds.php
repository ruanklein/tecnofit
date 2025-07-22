<?php declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Tecnofit\Config\Database;

/**
 * Seeds Runner - Executes seed files in order
 */
class SeedsRunner
{
    private Database $database;
    private string $seedsPath;

    public function __construct()
    {
        $this->database = Database::getInstance();
        $this->seedsPath = __DIR__ . '/Seeds';
    }

    /**
     * Run all seed files in order
     */
    public function run(): void
    {
        echo "Starting seeds execution...\n\n";

        try {
            $files = $this->getSeedFiles();

            if (empty($files)) {
                echo "No seed files found in: {$this->seedsPath}\n";
                return;
            }

            echo "Found " . count($files) . " seed file(s):\n";
            foreach ($files as $file) {
                echo "   - {$file}\n";
            }
            echo "\n";

            foreach ($files as $file) {
                $this->executeSeedFile($file);
            }

            echo "All seeds executed successfully!\n";

        } catch (Exception $e) {
            echo "Seeds execution failed: " . $e->getMessage() . "\n";
            exit(1);
        }
    }

    /**
     * Get seed files sorted by name
     */
    private function getSeedFiles(): array
    {
        if (!is_dir($this->seedsPath)) {
            throw new Exception("Seeds directory not found: {$this->seedsPath}");
        }

        $files = glob($this->seedsPath . '/*.sql');
        
        if ($files === false) {
            throw new Exception("Error reading seeds directory: {$this->seedsPath}");
        }

        sort($files);
        
        return array_map('basename', $files);
    }

    /**
     * Execute a single seed file
     */
    private function executeSeedFile(string $filename): void
    {
        $filepath = $this->seedsPath . '/' . $filename;
        
        echo "Executing: {$filename}";
        
        if (!file_exists($filepath)) {
            throw new Exception("Seed file not found: {$filepath}");
        }

        $sql = file_get_contents($filepath);
        
        if ($sql === false) {
            throw new Exception("Could not read seed file: {$filepath}");
        }

        $sql = trim($sql);
        
        if (empty($sql)) {
            echo " (empty file, skipped)\n";
            return;
        }

        $statements = $this->splitSqlStatements($sql);

        $executedStatements = 0;
        foreach ($statements as $statement) {
            if (!empty(trim($statement))) {
                $this->database->query($statement);
                $executedStatements++;
            }
        }

        echo " ({$executedStatements} statements)\n";
    }

    /**
     * Split SQL content into individual statements
     */
    private function splitSqlStatements(string $sql): array
    {
        $sql = preg_replace('/--.*$/m', '', $sql);
        $sql = preg_replace('/\/\*.*?\*\//s', '', $sql);
        
        $statements = explode(';', $sql);
        
        return array_filter(
            array_map('trim', $statements),
            fn($stmt) => !empty($stmt)
        );
    }

    /**
     * Clear all data from tables (useful for re-seeding)
     */
    public function clear(): void
    {
        echo "Clearing existing data...\n";

        try {
            $this->database->query('SET FOREIGN_KEY_CHECKS = 0');
            
            $tables = ['personal_records', 'users', 'movements'];
            
            foreach ($tables as $table) {
                $this->database->query("DELETE FROM {$table}");
                echo "Cleared table: {$table}\n";
            }
            
            $this->database->query('SET FOREIGN_KEY_CHECKS = 1');
            
            echo "Data cleared successfully!\n\n";
            
        } catch (Exception $e) {
            echo "Warning: Could not clear data: " . $e->getMessage() . "\n\n";
        }
    }
}

try {
    $runner = new SeedsRunner();
    $options = getopt('', ['clear', 'help']);

    if (isset($options['help'])) {
        echo "Seeds Runner\n";
        echo "===========\n\n";
        echo "Usage: php seeds.php [options]\n\n";
        echo "Options:\n";
        echo "  --clear    Clear existing data before seeding\n";
        echo "  --help     Show this help message\n\n";
        echo "Examples:\n";
        echo "  php seeds.php           # Run seeds\n";
        echo "  php seeds.php --clear   # Clear data and run seeds\n\n";
        exit(0);
    }

    if (isset($options['clear'])) {
        $runner->clear();
    }

    $runner->run();

} catch (Exception $e) {
    echo "Fatal error: " . $e->getMessage() . "\n";
    exit(1);
}
