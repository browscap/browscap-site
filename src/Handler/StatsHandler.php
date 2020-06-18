<?php

declare(strict_types=1);

namespace BrowscapSite\Handler;

use BrowscapSite\Renderer\Renderer;
use DateTimeImmutable;
use Exception;
use PDO;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

use function sprintf;

final class StatsHandler implements RequestHandlerInterface
{
    private Renderer $renderer;
    private PDO $pdo;

    public function __construct(Renderer $renderer, PDO $pdo)
    {
        $this->renderer = $renderer;
        $this->pdo      = $pdo;
    }

    /**
     * @throws Exception
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
     * @throws Exception
     *
     * @psalm-return list<array{0: string, 1: string|int}>
     */
    private function getDownloadsPerMonth(): array
    {
        return $this->getDownloadStats('downloadsPerMonth', 'monthPeriod', 'Month', 'M Y');
    }

    /**
     * @throws Exception
     *
     * @psalm-return list<array{0: string, 1: string|int}>
     */
    private function getDownloadsPerDay(): array
    {
        return $this->getDownloadStats('downloadsLastMonth', 'dayPeriod', 'Date', 'd/m');
    }

    /**
     * Fetch some download stats
     *
     * @throws Exception
     *
     * @psalm-return list<array{0: string, 1: string|int}>
     */
    private function getDownloadStats(
        string $tableName,
        string $tableColumnName,
        string $dataColumnName,
        string $dataColumnFormat
    ): array {
        $sql  = sprintf('SELECT * FROM %s ORDER BY %s ASC', $tableName, $tableColumnName);
        $stmt = $this->pdo->query($sql);

        $data   = [];
        $data[] = [$dataColumnName, 'Number of Downloads'];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $data[] = [
                (new DateTimeImmutable((string) $row[$tableColumnName]))->format($dataColumnFormat),
                (int) $row['downloadCount'],
            ];
        }

        return $data;
    }
}
