<?php

use Illuminate\Support\Str;

// Use the real connection host when Laravel is configured through DB_URL.
$mysqlHost = (string) env('DB_HOST', '127.0.0.1');
$mysqlUrl = env('DB_URL');

if (is_string($mysqlUrl) && $mysqlUrl !== '') {
    $urlHost = parse_url($mysqlUrl, PHP_URL_HOST);

    if (is_string($urlHost) && $urlHost !== '') {
        $mysqlHost = $urlHost;
    }
}

$configuredCa = env('MYSQL_ATTR_SSL_CA');
$sslRequired = (bool) env('DB_SSL_REQUIRED', false)
    || str_ends_with(strtolower($mysqlHost), '.tidbcloud.com')
    || (is_string($configuredCa) && $configuredCa !== '');

$mysqlSslOptions = [];

if (extension_loaded('pdo_mysql') && $sslRequired) {
    $caFile = is_string($configuredCa) && $configuredCa !== ''
        ? $configuredCa
        : 'certs/isrgrootx1.pem';

    // Accept a project-relative path or an explicit absolute path.
    $normalizedPath = str_replace('\\', '/', $caFile);
    $absolutePath = str_starts_with($normalizedPath, '/')
        || (strlen($normalizedPath) > 2
            && ctype_alpha($normalizedPath[0])
            && $normalizedPath[1] === ':'
            && $normalizedPath[2] === '/');

    $caPath = $absolutePath ? $caFile : base_path($caFile);

    if (! is_file($caPath) || ! is_readable($caPath)) {
        throw new RuntimeException(
            'TiDB CA certificate is missing or unreadable. Deploy certs/isrgrootx1.pem '
            .'and set MYSQL_ATTR_SSL_CA=certs/isrgrootx1.pem. '
            .'Clear any cached Laravel configuration during deployment.'
        );
    }

    // PDO::MYSQL_* works on PHP 8.3; PHP 8.5 uses the newer constants.
    $caOption = PHP_VERSION_ID >= 80500
        ? \Pdo\Mysql::ATTR_SSL_CA
        : \PDO::MYSQL_ATTR_SSL_CA;

    $verifyOption = PHP_VERSION_ID >= 80500
        ? \Pdo\Mysql::ATTR_SSL_VERIFY_SERVER_CERT
        : \PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT;

    $mysqlSslOptions = [
        $caOption => $caPath,
        $verifyOption => true,
    ];
}

return [
    'default' => env('DB_CONNECTION', 'mysql'),

    'connections' => [
        'sqlite' => [
            'driver' => 'sqlite',
            'url' => env('DB_URL'),
            'database' => env('DB_DATABASE', database_path('database.sqlite')),
            'prefix' => '',
            'foreign_key_constraints' => env('DB_FOREIGN_KEYS', true),
            'busy_timeout' => null,
            'journal_mode' => null,
            'synchronous' => null,
            'transaction_mode' => 'DEFERRED',
        ],

        'mysql' => [
            'driver' => 'mysql',
            'url' => env('DB_URL'),
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '3306'),
            'database' => env('DB_DATABASE', 'laravel'),
            'username' => env('DB_USERNAME', 'root'),
            'password' => env('DB_PASSWORD', ''),
            'unix_socket' => env('DB_SOCKET', ''),
            'charset' => env('DB_CHARSET', 'utf8mb4'),
            'collation' => env('DB_COLLATION', 'utf8mb4_unicode_ci'),
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => true,
            'engine' => null,
            'options' => $mysqlSslOptions,
        ],

        'mariadb' => [
            'driver' => 'mariadb',
            'url' => env('DB_URL'),
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '3306'),
            'database' => env('DB_DATABASE', 'laravel'),
            'username' => env('DB_USERNAME', 'root'),
            'password' => env('DB_PASSWORD', ''),
            'unix_socket' => env('DB_SOCKET', ''),
            'charset' => env('DB_CHARSET', 'utf8mb4'),
            'collation' => env('DB_COLLATION', 'utf8mb4_unicode_ci'),
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => true,
            'engine' => null,
            'options' => $mysqlSslOptions,
        ],

        'pgsql' => [
            'driver' => 'pgsql',
            'url' => env('DB_URL'),
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '5432'),
            'database' => env('DB_DATABASE', 'laravel'),
            'username' => env('DB_USERNAME', 'root'),
            'password' => env('DB_PASSWORD', ''),
            'charset' => env('DB_CHARSET', 'utf8'),
            'prefix' => '',
            'prefix_indexes' => true,
            'search_path' => 'public',
            'sslmode' => env('DB_SSLMODE', 'prefer'),
        ],

        'sqlsrv' => [
            'driver' => 'sqlsrv',
            'url' => env('DB_URL'),
            'host' => env('DB_HOST', 'localhost'),
            'port' => env('DB_PORT', '1433'),
            'database' => env('DB_DATABASE', 'laravel'),
            'username' => env('DB_USERNAME', 'root'),
            'password' => env('DB_PASSWORD', ''),
            'charset' => env('DB_CHARSET', 'utf8'),
            'prefix' => '',
            'prefix_indexes' => true,
        ],
    ],

    'migrations' => [
        'table' => 'migrations',
        'update_date_on_publish' => true,
    ],

    'redis' => [
        'client' => env('REDIS_CLIENT', 'phpredis'),

        'options' => [
            'cluster' => env('REDIS_CLUSTER', 'redis'),
            'prefix' => env('REDIS_PREFIX', Str::slug((string) env('APP_NAME', 'laravel')).'-database-'),
            'persistent' => env('REDIS_PERSISTENT', false),
        ],

        'default' => [
            'url' => env('REDIS_URL'),
            'host' => env('REDIS_HOST', '127.0.0.1'),
            'username' => env('REDIS_USERNAME'),
            'password' => env('REDIS_PASSWORD'),
            'port' => env('REDIS_PORT', '6379'),
            'database' => env('REDIS_DB', '0'),
            'max_retries' => env('REDIS_MAX_RETRIES', 3),
            'backoff_algorithm' => env('REDIS_BACKOFF_ALGORITHM', 'decorrelated_jitter'),
            'backoff_base' => env('REDIS_BACKOFF_BASE', 100),
            'backoff_cap' => env('REDIS_BACKOFF_CAP', 1000),
        ],

        'cache' => [
            'url' => env('REDIS_URL'),
            'host' => env('REDIS_HOST', '127.0.0.1'),
            'username' => env('REDIS_USERNAME'),
            'password' => env('REDIS_PASSWORD'),
            'port' => env('REDIS_PORT', '6379'),
            'database' => env('REDIS_CACHE_DB', '1'),
            'max_retries' => env('REDIS_MAX_RETRIES', 3),
            'backoff_algorithm' => env('REDIS_BACKOFF_ALGORITHM', 'decorrelated_jitter'),
            'backoff_base' => env('REDIS_BACKOFF_BASE', 100),
            'backoff_cap' => env('REDIS_BACKOFF_CAP', 1000),
        ],
    ],
];
