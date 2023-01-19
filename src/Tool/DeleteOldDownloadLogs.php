<?php

declare(strict_types=1);

namespace BrowscapSite\Tool;

use LazyPDO\LazyPDO as PDO;
use Throwable;

use function sprintf;

class DeleteOldDownloadLogs
{
    public function __construct(private PDO $pdo)
    {
    }

    /** @throws Throwable */
    public function __invoke(int $deleteOlderThan): void
    {
        $this->pdo->beginTransaction();

        try {
            $this->pdo->exec(sprintf(
                'DELETE FROM downloadLog WHERE downloadDate <= SUBDATE(NOW(), INTERVAL %d MONTH)',
                $deleteOlderThan,
            ));

            $this->pdo->commit();
        } catch (Throwable $e) {
            $this->pdo->rollBack();

            throw $e;
        } finally {
            $this->pdo->exec('OPTIMIZE TABLE downloadLog');
        }
    }
}
