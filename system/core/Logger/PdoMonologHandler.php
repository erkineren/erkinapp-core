<?php


namespace ErkinApp\Logger;


use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Logger;
use PDO;

class PdoMonologHandlerandler extends AbstractProcessingHandler
{
    /**
     * @var bool
     */
    private $initialized = false;
    /**
     * @var PDO
     */
    private $pdo;
    /**
     * @var \PDOStatement
     */
    private $statement;

    public function __construct(PDO $pdo, $level = Logger::DEBUG, bool $bubble = true)
    {
        $this->pdo = $pdo;
        parent::__construct($level, $bubble);
    }

    protected function write(array $record): void
    {
        if (!$this->initialized) {
            $this->initialize();
        }

        $this->statement->execute([
            'channel' => $record['channel'],
            'level' => $record['level'],
            'message' => $record['formatted'],
            'time' => $record['datetime']->format('U'),
        ]);
    }

    private function initialize()
    {
        $this->pdo->exec(
            'CREATE TABLE IF NOT EXISTS monolog '
            . '(channel VARCHAR(255), level INTEGER, message LONGTEXT, time INTEGER UNSIGNED)'
        );
        $this->statement = $this->pdo->prepare(
            'INSERT INTO monolog (channel, level, message, time) VALUES (:channel, :level, :message, :time)'
        );

        $this->initialized = true;
    }
}