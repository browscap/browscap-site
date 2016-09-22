<?php
declare(strict_types=1);

namespace BrowscapSite\Controller;

use BrowscapSite\BrowscapSiteWeb;

class StatsController
{
    protected $app;

    /**
     * @var \PDO
     */
    protected $pdo;

    public function __construct(BrowscapSiteWeb $app, \PDO $pdo)
    {
        $this->app = $app;
        $this->pdo = $pdo;
    }

    public function indexAction()
    {
        return $this->app['twig']->render('stats.html', [
            'downloadsPerDay' => $this->getDownloadsPerDay(),
            'downloadsPerMonth' => $this->getDownloadsPerMonth(),
        ]);
    }

    /**
     * @return array
     */
    private function getDownloadsPerMonth() : array
    {
        return $this->getDownloadStats('downloadsPerMonth', 'monthPeriod', 'Month', 'M Y');
    }

    /**
     * @return array
     */
    private function getDownloadsPerDay() : array
    {
        return $this->getDownloadStats('downloadsLastMonth', 'dayPeriod', 'Date', 'd/m');
    }

    /**
     * Fetch some download stats
     *
     * @param string $tableName Name of the table to grab stats from
     * @param string $tableColumnName Name of the date column in the DB
     * @param string $dataColumnName Name of the column for data
     * @param string $dataColumnFormat Format of the output date
     * @return array
     */
    private function getDownloadStats(
        string $tableName,
        string $tableColumnName,
        string $dataColumnName,
        string $dataColumnFormat
    ) : array {
        $sql = "
            SELECT *
            FROM $tableName
            ORDER BY $tableColumnName ASC
        ";
        $stmt = $this->pdo->query($sql);

        $data = [];
        $data[] = [$dataColumnName, 'Number of Downloads'];
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $data[] = [
                (new \DateTimeImmutable($row[$tableColumnName]))->format($dataColumnFormat),
                (int)$row['downloadCount'],
            ];
        }

        return $data;
    }
}
