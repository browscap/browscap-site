<?php
declare(strict_types=1);

namespace BrowscapSite\Tool;

use DateTimeInterface;
use PDO;

class AnalyseStatistics
{
    private PDO $pdo;

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
            $this->truncateInsert('downloadsPerMonth', new \DateTime('24 months ago'), '%Y-%m-\0\1');
            $this->truncateInsert('downloadsLastMonth', new \DateTime('30 days ago'), '%Y-%m-%d');

            $this->pdo->commit();
        } catch (\Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    /**
     * @param string $dataTable
     * @param DateTimeInterface $since
     * @param string $dateFormat
     * @return void
     */
    private function truncateInsert(string $dataTable, DateTimeInterface $since, string $dateFormat): void
    {
        $dataTable = $this->sanitizeTable($dataTable);
        $dateFormat = $this->sanitiseDateFormat($dateFormat);

        $this->pdo->exec('TRUNCATE ' . $dataTable);

        $sql = "
            INSERT INTO $dataTable
            SELECT
                DATE_FORMAT(downloadDate, '$dateFormat') AS `date`,
                COUNT(*) AS count
            FROM
            downloadLog
            WHERE
                downloadDate >= :sinceDate
            GROUP BY DATE_FORMAT(downloadDate, '$dateFormat')
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue('sinceDate', $since->format('Y-m-d ') . ' 00:00:00');
        $stmt->execute();
    }

    /**
     * @param string $tableName
     * @return string
     */
    private function sanitizeTable(string $tableName): string
    {
        return preg_replace('/[^%a-zA-Z]/', '', $tableName);
    }

    /**
     * May only contain values valid in a MySQL date format string e.g. %Y-%m-%d
     *
     * @param string $format
     * @return string
     */
    private function sanitiseDateFormat(string $format): string
    {
        return preg_replace('/[^%a-zA-Z0-9-]/', '', $format);
    }
}
