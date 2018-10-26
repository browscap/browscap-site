<?php
declare(strict_types=1);

namespace BrowscapSite\Handler;

use BrowscapSite\Renderer\Renderer;
use PDO;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class StatsHandler implements RequestHandlerInterface
{
    /** @var Renderer */
    private $renderer;

    /** @var PDO */
    private $pdo;

    public function __construct(Renderer $renderer, PDO $pdo)
    {
        $this->renderer = $renderer;
        $this->pdo = $pdo;
    }

    /**
     * @throws \Exception
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        return $this->renderer->render(
            'stats.html',
            [
                'downloadsPerDay' => $this->getDownloadsPerDay(),
                'downloadsPerMonth' => $this->getDownloadsPerMonth(),
            ]
        );
    }

    /**
     * @return array
     * @throws \Exception
     */
    private function getDownloadsPerMonth() : array
    {
        return $this->getDownloadStats('downloadsPerMonth', 'monthPeriod', 'Month', 'M Y');
    }

    /**
     * @return array
     * @throws \Exception
     */
    private function getDownloadsPerDay() : array
    {
        return $this->getDownloadStats('downloadsLastMonth', 'dayPeriod', 'Date', 'd/m');
    }

    /**
     * Fetch some download stats
     *
     * @return array
     * @throws \Exception
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
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $data[] = [
                (new \DateTimeImmutable($row[$tableColumnName]))->format($dataColumnFormat),
                (int)$row['downloadCount'],
            ];
        }

        return $data;
    }
}
