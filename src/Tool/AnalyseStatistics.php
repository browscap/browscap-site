<?php

declare(strict_types=1);

namespace BrowscapSite\Tool;

use DateTime;
use DateTimeInterface;
use LazyPDO\LazyPDO as PDO;
use Throwable;

use function preg_replace;
use function sprintf;

class AnalyseStatistics
{
    public function __construct(private PDO $pdo)
    {
    }

    /** @throws Throwable */
    public function __invoke(): void
    {
        $this->pdo->beginTransaction();

        try {
            $this->truncateInsert('downloadsPerMonth', new DateTime('24 months ago'), '%Y-%m-\0\1');
            $this->truncateInsert('downloadsLastMonth', new DateTime('30 days ago'), '%Y-%m-%d');

            if ($this->pdo->inTransaction()) {
                $this->pdo->commit();
            }
        } catch (Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            throw $e;
        }
    }

    private function truncateInsert(string $dataTable, DateTimeInterface $since, string $dateFormat): void
    {
        $dataTable  = $this->sanitizeTable($dataTable);
        $dateFormat = $this->sanitiseDateFormat($dateFormat);

        $this->pdo->exec(sprintf('TRUNCATE %s', $dataTable));

        $sql = <<<'SQL'
INSERT INTO %s
SELECT
    DATE_FORMAT(downloadDate, '%s') AS `date`,
    COUNT(*) AS count
FROM
downloadLog
WHERE
    downloadDate >= :sinceDate
GROUP BY DATE_FORMAT(downloadDate, '%s')
SQL;

        $stmt = $this->pdo->prepare(sprintf($sql, $dataTable, $dateFormat, $dateFormat));
        $stmt->bindValue('sinceDate', $since->format('Y-m-d ') . ' 00:00:00');
        $stmt->execute();
    }

    private function sanitizeTable(string $tableName): string
    {
        return preg_replace('/[^%a-zA-Z]/', '', $tableName);
    }

    /**
     * May only contain values valid in a MySQL date format string e.g. %Y-%m-%d
     */
    private function sanitiseDateFormat(string $format): string
    {
        return preg_replace('/[^%a-zA-Z0-9-]/', '', $format);
    }
}
