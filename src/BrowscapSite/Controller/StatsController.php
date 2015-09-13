<?php

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
     * May only contain values valid in a MySQL date format string e.g. %Y-%m-%d
     *
     * @param $format
     * @return mixed
     */
    private function sanitiseDateFormat($format)
    {
        return preg_replace('/[^%a-zA-Z-]/', '', $format);
    }

    /**
     * @return array
     */
    private function getDownloadsPerMonth()
    {
        return $this->getDownloadStats(
            new \DateTime('24 months ago'),
            '%Y-%m',
            '%Y-%m',
            'Month'
        );
    }

    /**
     * @return array
     */
    private function getDownloadsPerDay()
    {
        return $this->getDownloadStats(
            new \DateTime('30 days ago'),
            '%m-%d',
            '%Y-%m-%d',
            'Date'
        );
    }

    /**
     * Fetch some download stats
     *
     * @param \DateTime $since
     * @param string $selectDateFormat
     * @param string $groupDateFormat
     * @param string $columnName
     * @return array
     */
    private function getDownloadStats(\DateTime $since, $selectDateFormat, $groupDateFormat, $columnName)
    {
        $selectDateFormat = $this->sanitiseDateFormat($selectDateFormat);
        $groupDateFormat = $this->sanitiseDateFormat($groupDateFormat);

        $sql = "
            SELECT
                DATE_FORMAT(downloadDate, '" . $selectDateFormat . "') AS `date`,
                COUNT(*) AS count
            FROM
            downloadLog
            WHERE
                downloadDate >= :sinceDate
            GROUP BY DATE_FORMAT(downloadDate, '" . $groupDateFormat . "')
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue('sinceDate', $since->format('Y-m-d ') . ' 00:00:00');
        $stmt->execute();

        $data = [];
        $data[] = [$columnName, 'Number of Downloads'];
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $data[] = [
                $row['date'],
                (int)$row['count'],
            ];
        }

        return $data;
    }
}
