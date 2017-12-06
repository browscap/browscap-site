<?php
declare(strict_types=1);

if (!getenv('HEROKU')) {
    return [];
}

return [
    'debug' => getenv('DEBUG') === '1',
    \Zend\ConfigAggregator\ConfigAggregator::ENABLE_CACHE => true,
    'db' => [
       'dsn' => getenv('CLEARDB_DATABASE_URL'),
    ],
    'rateLimiter' => [
        'rateLimitDownloads' => 30, // How many downloads per $rateLimitPeriod
        'rateLimitPeriod' => 1,     // Download limit period in HOURS
        'tempBanPeriod' => 3,       // Tempban period in DAYS
        'tempBanLimit' => 5,        // How many tempbans allowed in $tempBanPeriod before permaban
    ],
];
