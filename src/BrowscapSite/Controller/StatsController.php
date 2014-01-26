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
        $downloads = $this->getDownloadsPerDay();

        return $this->app['twig']->render('stats.html', array(
            'downloads' => $downloads,
        ));
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

        $data = array();
        $data[] = array('Date', 'Number of Downloads');
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $data[] = array(
            	$row['date'],
                (int)$row['count'],
            );
        }

        return $data;
    }
}
