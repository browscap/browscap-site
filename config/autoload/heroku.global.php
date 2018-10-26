<?php
declare(strict_types=1);

if (!getenv('HEROKU')) {
    return [];
}

$url = parse_url(getenv("CLEARDB_DATABASE_URL"));

return [
    'debug' => getenv('DEBUG') === '1',
    \Zend\ConfigAggregator\ConfigAggregator::ENABLE_CACHE => true,
    'db' => [
        'dsn' => sprintf('mysql:host=%s;dbname=%s', $url['host'], substr($url['path'], 1)),
        'user' => $url['user'],
        'pass' => $url['pass'],
    ],
];
