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

    protected function getDownloadsPerMonth()
    {
        $cutoff = new \DateTime('24 months ago');

        $sql = "
            SELECT
                DATE_FORMAT(downloadDate, '%Y-%m') AS `date`,
                COUNT(*) AS count
            FROM
            downloadLog
            WHERE
                downloadDate >= :sinceDate
            GROUP BY DATE_FORMAT(downloadDate, '%Y-%m')
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue('sinceDate', $cutoff->format('Y-m-d ') . ' 00:00:00');
        $stmt->execute();

        $data = [];
        $data[] = ['Month', 'Number of Downloads'];
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $data[] = [
                $row['date'],
                (int)$row['count'],
            ];
        }

        return $data;
    }

    protected function getDownloadsPerDay()
    {
        $cutoff = new \DateTime('30 days ago');

        $sql = "
            SELECT
                DATE_FORMAT(downloadDate, '%m-%d') AS `date`,
                COUNT(*) AS count
            FROM
            downloadLog
            WHERE
                downloadDate >= :sinceDate
            GROUP BY DATE_FORMAT(downloadDate, '%Y-%m-%d')
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue('sinceDate', $cutoff->format('Y-m-d ') . ' 00:00:00');
        $stmt->execute();

        $data = [];
        $data[] = ['Date', 'Number of Downloads'];
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $data[] = [
                $row['date'],
                (int)$row['count'],
            ];
        }

        return $data;
    }
}
