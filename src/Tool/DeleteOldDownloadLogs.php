<?php
declare(strict_types=1);

namespace BrowscapSite\Tool;

use PDO;

class DeleteOldDownloadLogs
{
    /**
     * @var PDO
     */
    private $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * @throws \Exception
     * @return void
     */
    public function __invoke(): void
    {
        $this->pdo->beginTransaction();

        try {
            $this->pdo->exec('DELETE FROM downloadlog WHERE downloadDate <= SUBDATE(NOW(), INTERVAL 12 MONTH)');

            $this->pdo->commit();
        } catch (\Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }

        $this->pdo->exec('OPTIMIZE TABLE downloadlog');
    }
}
