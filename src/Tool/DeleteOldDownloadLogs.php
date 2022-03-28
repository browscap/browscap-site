<?php

declare(strict_types=1);

namespace BrowscapSite\Tool;

use LazyPDO\LazyPDO as PDO;
use Throwable;

class DeleteOldDownloadLogs
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * @throws Throwable
     */
    public function __invoke(): void
    {
        $this->pdo->beginTransaction();

        try {
            $this->pdo->exec('DELETE FROM downloadLog WHERE downloadDate <= SUBDATE(NOW(), INTERVAL 12 MONTH)');

            $this->pdo->commit();
        } catch (Throwable $e) {
            $this->pdo->rollBack();

            throw $e;
        } finally {
            $this->pdo->exec('OPTIMIZE TABLE downloadLog');
        }
    }
}
